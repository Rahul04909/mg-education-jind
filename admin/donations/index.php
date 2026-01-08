<?php
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Filter Logic
$where_clauses = ["1=1"];
$params = [];
$types = "";

// Search Filter
if (!empty($_GET['search'])) {
    $search = "%" . trim($_GET['search']) . "%";
    $where_clauses[] = "(full_name LIKE ? OR email LIKE ? OR mobile LIKE ? OR payment_id LIKE ? OR receipt_no LIKE ?)";
    $params[] = $search; $params[] = $search; $params[] = $search; $params[] = $search; $params[] = $search;
    $types .= "sssss";
}

// Status Filter
if (!empty($_GET['status'])) {
    $where_clauses[] = "status = ?";
    $params[] = $_GET['status'];
    $types .= "s";
}

// Date Range Filter
if (!empty($_GET['from_date']) && !empty($_GET['to_date'])) {
    $where_clauses[] = "DATE(created_at) BETWEEN ? AND ?";
    $params[] = $_GET['from_date'];
    $params[] = $_GET['to_date'];
    $types .= "ss";
}

$where_sql = implode(" AND ", $where_clauses);

// Fetch Donations
$sql = "SELECT * FROM donations WHERE $where_sql ORDER BY created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Total Count (for pagination)
$count_sql = "SELECT COUNT(*) as total FROM donations WHERE $where_sql";
$count_stmt = $conn->prepare($count_sql);

// Remove limit/offset params for count query
if (!empty($types)) {
    $count_types = substr($types, 0, -2);
    $count_params = array_slice($params, 0, -2);
    if ($count_params) {
        $count_stmt->bind_param($count_types, ...$count_params);
    }
}
$count_stmt->execute();
$count_res = $count_stmt->get_result();
$total_rows = $count_res->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Donations - MG Skills</title>
    <style>
        :root{--active:#22c55e;--indigo:#4f46e5;--line:#e2e8f0;--text:#1e293b;--muted:#64748b;--bg:#f8fafc}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit',system-ui,sans-serif;background:var(--bg);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:30px;transition:margin-left .3s ease}
        body.sidebar-collapsed .admin-content{margin-left:80px}
        .admin-wrap{max-width:1400px;margin:0 auto}
        
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
        .page-title{font-size:24px;font-weight:700;color:var(--text)}
        
        .table-card{background:#fff;border-radius:12px;overflow-x:auto;box-shadow:0 1px 3px rgba(0,0,0,0.05);border:1px solid var(--line)}
        table{width:100%;border-collapse:collapse;font-size:14px;white-space:nowrap}
        th{background:#f8fafc;padding:12px 20px;text-align:left;font-weight:600;color:var(--muted);border-bottom:1px solid var(--line);font-size:12px;text-transform:uppercase;letter-spacing:0.5px}
        td{padding:14px 20px;border-bottom:1px solid var(--line);vertical-align:middle}
        tr:last-child td{border-bottom:none}
        tr:hover td{background:#f8fafc}
        
        .pagination{display:flex;justify-content:center;gap:6px;margin-top:24px}
        .page-link{width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:8px;background:#fff;border:1px solid var(--line);color:var(--text);text-decoration:none;font-weight:500;transition:all 0.2s}
        .page-link:hover{border-color:var(--indigo);color:var(--indigo)}
        .page-link.active{background:var(--indigo);color:#fff;border-color:var(--indigo)}
        
        .user-info{display:flex;flex-direction:column}
        .user-name{font-weight:600;color:var(--text)}
        .user-email{font-size:12px;color:var(--muted)}
        
        .badge{padding:4px 8px;border-radius:6px;font-size:12px;font-weight:600;display:inline-block}
        .badge-success{background:#dcfce7;color:#166534}
        .badge-pending{background:#fef9c3;color:#854d0e}
        .badge-failed{background:#fee2e2;color:#991b1b}

        /* Filter Styles */
        .filter-card{background:#fff;padding:20px;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.05);margin-bottom:24px}
        .filter-form{display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;align-items:end}
        .form-group label{display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--muted)}
        .form-control{width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;font-size:14px;transition:all 0.2s}
        .form-control:focus{outline:none;border-color:var(--indigo);box-shadow:0 0 0 3px rgba(79, 70, 229, 0.1)}
        .btn{padding:10px 20px;border-radius:8px;font-weight:500;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px;font-size:14px;border:none;cursor:pointer;transition:all 0.2s}
        .btn-primary{background:var(--indigo);color:#fff}
        .btn-primary:hover{background:#4338ca}
        .btn-secondary{background:#fff;border:1px solid var(--line);color:var(--text)}
        .btn-secondary:hover{background:#f8fafc}
        .btn-sm{padding:6px 12px;font-size:12px;}
    </style>
</head>
<body>
    <?php include __DIR__ . "/../sidebar.php"; ?>
    
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Real Money Donations</h1>
                    <p style="color:var(--muted); font-size:14px; margin-top:4px">View and manage online donations via Razorpay.</p>
                </div>
            </div>
            
            <div class="filter-card">
                <form method="GET" class="filter-form">
                    <div class="form-group">
                        <label>Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Name, Email, Receipt, Txn ID..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="success" <?php echo (isset($_GET['status']) && $_GET['status'] == "success") ? 'selected' : ''; ?>>Success</option>
                            <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] == "pending") ? 'selected' : ''; ?>>Pending</option>
                            <option value="failed" <?php echo (isset($_GET['status']) && $_GET['status'] == "failed") ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>From Date</label>
                        <input type="date" name="from_date" class="form-control" value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>To Date</label>
                        <input type="date" name="to_date" class="form-control" value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>">
                    </div>
                    <div class="form-group" style="display:flex; gap:10px">
                        <button type="submit" class="btn btn-primary" style="flex:1">Filter</button>
                        <a href="index.php" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
            
            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Receipt No</th>
                            <th>Donor Details</th>
                            <th>Amount</th>
                            <th>Purpose</th>
                            <th>Transaction ID</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <span style="font-family:monospace; font-weight:700; color:var(--text)"><?php echo htmlspecialchars($row['receipt_no']); ?></span>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <span class="user-name"><?php echo htmlspecialchars($row['full_name']); ?></span>
                                            <span class="user-email"><?php echo htmlspecialchars($row['email']); ?></span>
                                            <span style="font-size:12px;color:var(--muted)"><?php echo htmlspecialchars($row['mobile']); ?></span>
                                            <?php if($row['pan_card']): ?>
                                                <span style="font-size:11px;color:var(--text); background:#f1f5f9; padding:2px 4px; border-radius:4px; width:fit-content; margin-top:2px">PAN: <?php echo htmlspecialchars($row['pan_card']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="font-weight:700; color:var(--text)">
                                        Rs. <?php echo number_format($row['amount'], 2); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                                    <td style="font-size:12px; font-family:monospace;"><?php echo htmlspecialchars($row['payment_id']); ?></td>
                                    <td>
                                        <?php 
                                            $status = strtolower($row['status']);
                                            $badgeClass = 'badge-pending';
                                            if($status == 'success') $badgeClass = 'badge-success';
                                            if($status == 'failed') $badgeClass = 'badge-failed';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($status); ?></span>
                                    </td>
                                    <td style="font-size:13px;color:var(--muted);"><?php echo date("d M Y, h:i A", strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <?php if(strtolower($row['status']) == 'success'): ?>
                                            <a href="../../pages/donate-now/download-receipt.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-secondary btn-sm" title="Download Receipt">
                                                Download PDF
                                            </a>
                                        <?php else: ?>
                                            <span style="color:var(--muted); font-size:12px;">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--muted)">No donations found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php 
                    $query_params = $_GET;
                    unset($query_params['page']);
                    $query_string = http_build_query($query_params);
                    $query_string = $query_string ? '&' . $query_string : '';
                ?>
                
                <?php if($page > 1): ?>
                    <a href="?page=<?php echo $page-1 . $query_string; ?>" class="page-link">&laquo;</a>
                <?php endif; ?>
                
                <?php for($i=1; $i<=$total_pages; $i++): ?>
                    <a href="?page=<?php echo $i . $query_string; ?>" class="page-link <?php echo $page == $i ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1 . $query_string; ?>" class="page-link">&raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
