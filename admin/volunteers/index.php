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
    $where_clauses[] = "(full_name LIKE ? OR email LIKE ? OR mobile LIKE ? OR volunteer_id LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $types .= "ssss";
}

// Role Filter
if (!empty($_GET['role'])) {
    $where_clauses[] = "role = ?";
    $params[] = $_GET['role'];
    $types .= "s";
}

$where_sql = implode(" AND ", $where_clauses);

// Fetch Volunteers
$sql = "SELECT * FROM volunteers WHERE $where_sql ORDER BY created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Total Count
$count_sql = "SELECT COUNT(*) as total FROM volunteers WHERE $where_sql";
$count_stmt = $conn->prepare($count_sql);

// Remove limit/offset params for count
$count_types = substr($types, 0, -2);
$count_params = array_slice($params, 0, -2);

if ($count_params) {
    $count_stmt->bind_param($count_types, ...$count_params);
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
    <title>Volunteers - MG Skills</title>
    <style>
        :root{--active:#22c55e;--indigo:#4f46e5;--line:#e2e8f0;--text:#1e293b;--muted:#64748b;--bg:#f8fafc;--red:#ef4444;--blue:#3b82f6;}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit',system-ui,sans-serif;background:var(--bg);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:30px;transition:margin-left .3s ease}
        body.sidebar-collapsed .admin-content{margin-left:80px}
        
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
        .page-title{font-size:24px;font-weight:700;color:var(--text)}
        
        .btn { padding: 10px 20px; border-radius: 8px; font-weight: 600; text-decoration: none; cursor: pointer; border: none; font-size: 14px; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: var(--indigo); color: white; }
        .btn-primary:hover { opacity: 0.9; }
        
        .search-box { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 12px; border: 1px solid var(--line); margin-bottom: 20px; flex-wrap: wrap; }
        .form-control { padding: 10px; border: 1px solid var(--line); border-radius: 8px; font-size: 14px; min-width: 200px; }
        
        .table-card{background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.05);border:1px solid var(--line)}
        table{width:100%;border-collapse:collapse;font-size:14px}
        th{background:#f8fafc;padding:12px 20px;text-align:left;font-weight:600;color:var(--muted);border-bottom:1px solid var(--line);font-size:12px;text-transform:uppercase;letter-spacing:0.5px; white-space: nowrap;}
        td{padding:14px 20px;border-bottom:1px solid var(--line);vertical-align:middle; white-space: nowrap;}
        tr:last-child td{border-bottom:none}
        tr:hover td{background:#f8fafc}
        
        .user-info{display:flex;flex-direction:column}
        .user-name{font-weight:600;color:var(--text)}
        .user-email{font-size:12px;color:var(--muted)}
        .user-id { font-size: 11px; color: var(--indigo); font-weight: 700; margin-bottom: 2px; }
        
        .badge{display:inline-flex;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#e2e8f0;color:#475569}
        .badge.blue { background: #dbeafe; color: #1e40af; }
        
        .action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; color: var(--muted); transition: all 0.2s; border: 1px solid transparent; }
        .action-btn:hover { background: #f1f5f9; border-color: var(--line); color: var(--text); }
        .action-btn.view:hover { color: var(--blue); background: #eff6ff; border-color: #bfdbfe; }
        .action-btn.edit:hover { color: var(--indigo); background: #eef2ff; border-color: #c7d2fe; }
        .action-btn.delete:hover { color: var(--red); background: #fef2f2; border-color: #fecaca; }
        
        .pagination{display:flex;justify-content:center;gap:6px;margin-top:24px}
        .page-link{width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:8px;background:#fff;border:1px solid var(--line);color:var(--text);text-decoration:none;font-weight:500;transition:all 0.2s}
        .page-link:hover{border-color:var(--indigo);color:var(--indigo)}
        .page-link.active{background:var(--indigo);color:#fff;border-color:var(--indigo)}
    </style>
</head>
<body class="<?php echo isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] == 'true' ? 'sidebar-collapsed' : ''; ?>">

    <?php include '../../admin/sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-wrap">
            
            <div class="page-header">
                <h1 class="page-title">Volunteers</h1>
                <a href="add-volunteer.php" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                    Add Volunteer
                </a>
            </div>

            <!-- Search & Filters -->
            <form method="GET" class="search-box">
                <input type="text" name="search" class="form-control" placeholder="Search by Name, ID, Mobile..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <select name="role" class="form-control">
                    <option value="">All Roles</option>
                    <option value="Teaching (Online/Offline)" <?php echo (isset($_GET['role']) && $_GET['role'] == 'Teaching (Online/Offline)') ? 'selected' : ''; ?>>Teaching</option>
                    <option value="Blood Donation" <?php echo (isset($_GET['role']) && $_GET['role'] == 'Blood Donation') ? 'selected' : ''; ?>>Blood Donation</option>
                    <option value="Social Media / Marketing" <?php echo (isset($_GET['role']) && $_GET['role'] == 'Social Media / Marketing') ? 'selected' : ''; ?>>Social Media</option>
                    <!-- Add more as needed -->
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="index.php" class="btn" style="background:#f1f5f9;color:#64748b">Reset</a>
            </form>

            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Volunteer</th>
                            <th>Mobile</th>
                            <th>Role</th>
                            <th>Location</th>
                            <th>Join Date</th>
                            <th style="text-align:right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <span class="user-id">#<?php echo htmlspecialchars($row['volunteer_id'] ?? 'N/A'); ?></span>
                                            <span class="user-name"><?php echo htmlspecialchars($row['full_name']); ?></span>
                                            <span class="user-email"><?php echo htmlspecialchars($row['email']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                                    <td>
                                        <span class="badge blue"><?php echo htmlspecialchars($row['role']); ?></span>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <span style="font-size:13px"><?php echo htmlspecialchars($row['city']); ?></span>
                                            <span style="font-size:11px;color:var(--muted)"><?php echo htmlspecialchars($row['state']); ?></span>
                                        </div>
                                    </td>
                                    <td style="font-size:13px;color:var(--muted);"><?php echo date("d M Y", strtotime($row['created_at'])); ?></td>
                                    <td style="text-align:right">
                                        <a href="view-volunteer.php?id=<?php echo $row['id']; ?>" class="action-btn view" title="View">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <a href="edit-volunteer.php?id=<?php echo $row['id']; ?>" class="action-btn edit" title="Edit">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <button onclick="confirmDelete(<?php echo $row['id']; ?>)" class="action-btn delete" title="Delete">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted)">No volunteers found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <!-- Pagination Logic (Simplified for brevity, copying from callback-requests) -->
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
    </div>

    <script>
        function confirmDelete(id) {
            if(confirm('Are you sure you want to delete this volunteer?')) {
                // In a real app, use fetch to delete
                window.location.href = 'delete-volunteer.php?id=' + id;
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
