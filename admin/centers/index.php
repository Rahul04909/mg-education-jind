<?php
require_once __DIR__ . '/../../database/db-config.php';

$conn = getDbConnection();
$sql = "SELECT * FROM centers ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Centers - MG Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--primary:#4f46e5;--primary-dark:#3730a3;--primary-soft:#eef2ff;--text-main:#0f172a;--text-light:#64748b;--bg-body:#f1f5f9;--surface:#ffffff;--border:#e2e8f0;--success:#22c55e;--danger:#ef4444;--warning:#f59e0b;}
        body{font-family:'Outfit',sans-serif;background-color:var(--bg-body);margin:0;padding:0;color:var(--text-main)}
        .admin-content{margin-left:260px;min-height:100vh;padding:32px;transition:margin-left .3s cubic-bezier(0.4,0,0.2,1)}
        body.sidebar-collapsed .admin-content{margin-left:80px}
        @media (max-width:900px){.admin-content{margin-left:80px;padding:20px}}
        
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:32px}
        .page-title h1{font-size:24px;font-weight:700;margin:0;color:var(--text-main)}
        .breadcrumb{font-size:14px;color:var(--text-light);margin-top:4px}
        
        .btn{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:10px;font-weight:600;font-size:14px;border:none;cursor:pointer;transition:all .2s;text-decoration:none}
        .btn-primary{background:var(--primary);color:#fff}
        .btn-primary:hover{background:var(--primary-dark)}
        
        .card{background:var(--surface);border-radius:16px;border:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,0.1);overflow:hidden}
        
        table{width:100%;border-collapse:collapse}
        th{text-align:left;font-size:12px;font-weight:600;color:var(--text-light);text-transform:uppercase;padding:16px 24px;border-bottom:1px solid var(--border);background:#f8fafc}
        td{padding:16px 24px;font-size:14px;color:var(--text-main);border-bottom:1px solid #f1f5f9}
        tr:last-child td{border-bottom:none}
        tr:hover td{background:#f8fafc}
        
        .center-info{display:flex;align-items:center;gap:12px}
        .center-logo{width:40px;height:40px;border-radius:8px;object-fit:cover;background:#e2e8f0}
        .center-details{display:flex;flex-direction:column}
        .center-name{font-weight:600;color:var(--text-main)}
        .center-loc{font-size:12px;color:var(--text-light)}
        
        .badge{padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700}
        .badge.active{background:#dcfce7;color:#16a34a}
        .badge.inactive{background:#fee2e2;color:#dc2626}
        
        .action-btns{display:flex;gap:8px}
        .btn-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border);background:#fff;color:var(--text-light);transition:all .2s;text-decoration:none}
        .btn-icon:hover{border-color:var(--primary);color:var(--primary)}
        
        .empty-state{padding:48px;text-align:center;color:var(--text-light)}
    </style>
</head>
<body>
    <?php include __DIR__ . '/../sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="page-header">
            <div>
                <div class="page-title"><h1>Centers Directory</h1></div>
                <div class="breadcrumb">Dashboard › Centers</div>
            </div>
            <a href="add-center.php" class="btn btn-primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Add New Center
            </a>
        </div>

        <div class="card">
            <div style="overflow-x:auto">
                <table>
                    <thead>
                        <tr>
                            <th>Center</th>
                            <th>Contact</th>
                            <th>Infrastructure</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="center-info">
                                            <img src="<?php echo !empty($row['center_logo']) ? '../../'.$row['center_logo'] : '../../assets/images/placeholder-logo.png'; ?>" class="center-logo" onerror="this.src='https://via.placeholder.com/40'">
                                            <div class="center-details">
                                                <span class="center-name"><?php echo htmlspecialchars($row['center_name']); ?></span>
                                                <span class="center-loc"><?php echo htmlspecialchars($row['city'] . ', ' . $row['state']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight:500"><?php echo htmlspecialchars($row['owner_name']); ?></div>
                                        <div style="font-size:12px;color:var(--text-light)"><?php echo htmlspecialchars($row['mobile']); ?></div>
                                    </td>
                                    <td>
                                        <div style="font-size:13px">
                                            <?php echo $row['num_computers']; ?> Computers<br>
                                            <span style="color:var(--text-light)"><?php echo $row['lab_type']; ?> Lab</span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($row['is_active']): ?>
                                            <span class="badge active">Active</span>
                                        <?php else: ?>
                                            <span class="badge inactive">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color:var(--text-light);font-size:13px">
                                        <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="view-center.php?id=<?php echo $row['id']; ?>" class="btn-icon" title="View Details">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                            </a>
                                            <a href="edit-center.php?id=<?php echo $row['id']; ?>" class="btn-icon" title="Edit Center">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                            </a>
                                            <a href="#" class="btn-icon" style="color:var(--danger);border-color:#fee2e2;background:#fef2f2" onclick="return confirm('Are you sure?')" title="Delete Center">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <h3>No Centers Found</h3>
                                        <p>Get started by adding a new training center.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
