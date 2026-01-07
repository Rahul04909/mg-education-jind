<?php
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

// Create Table if not exists (Safety Check)
$create_table_sql = "CREATE TABLE IF NOT EXISTS donation_enquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    purpose VARCHAR(100) NOT NULL,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($create_table_sql);

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Fetch Enquiries
$sql = "SELECT * FROM donation_enquiries ORDER BY created_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();

// Total Count
$count_sql = "SELECT COUNT(*) as total FROM donation_enquiries";
$count_res = $conn->query($count_sql);
$total_rows = $count_res->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Enquiries - MG Skills</title>
    <style>
        :root{--active:#22c55e;--indigo:#4f46e5;--line:#e2e8f0;--text:#1e293b;--muted:#64748b;--bg:#f8fafc}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit',system-ui,sans-serif;background:var(--bg);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:30px;transition:margin-left .3s ease}
        body.sidebar-collapsed .admin-content{margin-left:80px}
        .admin-wrap{max-width:1400px;margin:0 auto}
        
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
        .page-title{font-size:24px;font-weight:700;color:var(--text)}
        
        .table-card{background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.05);border:1px solid var(--line)}
        table{width:100%;border-collapse:collapse;font-size:14px}
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
    </style>
</head>
<body>
    <?php include __DIR__ . "/../sidebar.php"; ?>
    
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Donation Enquiries</h1>
                    <p style="color:var(--muted); font-size:14px; margin-top:4px">View enquiries received from the frontend.</p>
                </div>
            </div>
            
            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Donor Details</th>
                            <th>Purpose</th>
                            <th>Message</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td>
                                        <div class="user-info">
                                            <span class="user-name"><?php echo htmlspecialchars($row['full_name']); ?></span>
                                            <span class="user-email"><?php echo htmlspecialchars($row['email']); ?></span>
                                            <span style="font-size:12px;color:var(--text)"><?php echo htmlspecialchars($row['phone']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                                    <td style="max-width:300px;"><?php echo htmlspecialchars($row['message']); ?></td>
                                    <td style="font-size:13px;color:var(--muted);"><?php echo date("d M Y, h:i A", strtotime($row['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--muted)">No enquiries found yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if($page > 1): ?>
                    <a href="?page=<?php echo $page-1; ?>" class="page-link">&laquo;</a>
                <?php endif; ?>
                
                <?php for($i=1; $i<=$total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $page == $i ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?>" class="page-link">&raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
