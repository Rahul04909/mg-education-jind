<?php
require_once __DIR__ . '/../../database/db-config.php';
require_once __DIR__ . '/../../database/update_admission_schema.php';

$conn = getDbConnection();

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Fetch Students
$sql = "SELECT a.*, c.title as course_title 
        FROM admissions a 
        LEFT JOIN courses c ON a.course_id = c.id 
        ORDER BY a.created_at DESC 
        LIMIT $offset, $limit";
$result = $conn->query($sql);

// Total Count
$count_sql = "SELECT COUNT(*) as total FROM admissions";
$total_result = $conn->query($count_sql);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - MG Skills</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e;--info:#3b82f6}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:20px;transition:margin-left .25s ease}
        body.sidebar-collapsed .admin-content{margin-left:88px}
        .admin-wrap{max-width:1200px;margin:0 auto}
        
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px}
        .page-title{font-size:24px;font-weight:800;color:var(--text)}
        .btn{padding:10px 20px;border-radius:8px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:8px;font-size:14px;border:none;cursor:pointer}
        .btn-primary{background:var(--indigo);color:#fff}
        .btn-sm{padding:6px 12px;font-size:12px;border-radius:6px}
        .btn-outline{border:1px solid var(--line);background:#fff;color:var(--text)}
        
        .table-card{background:#fff;border:1px solid var(--line);border-radius:12px;overflow:hidden;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1)}
        table{width:100%;border-collapse:collapse;font-size:14px}
        th{background:#f8fafc;padding:12px 16px;text-align:left;font-weight:600;color:var(--muted);border-bottom:1px solid var(--line)}
        td{padding:12px 16px;border-bottom:1px solid var(--line);vertical-align:middle}
        tr:last-child td{border-bottom:none}
        
        .badge{padding:4px 8px;border-radius:99px;font-size:12px;font-weight:600}
        .badge-success{background:#d1fae5;color:#065f46}
        .badge-warning{background:#fef3c7;color:#92400e}
        
        .actions{display:flex;gap:8px}
        .pagination{display:flex;justify-content:center;gap:8px;margin-top:20px}
        .page-link{width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:6px;background:#fff;border:1px solid var(--line);color:var(--text);text-decoration:none}
        .page-link.active{background:var(--indigo);color:#fff;border-color:var(--indigo)}
    </style>
</head>
<body>
    <?php include __DIR__ . "/../sidebar.php"; ?>
    
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Students</h1>
                    <p style="color:var(--muted)">Manage all student admissions</p>
                </div>
                <a href="add-student.php" class="btn btn-primary">+ Add New Student</a>
            </div>
            
            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Enrollment No</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Mobile</th>
                            <th>Mode</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['enrollment_no']); ?></td>
                                    <td>
                                        <div style="font-weight:600"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                        <div style="font-size:12px;color:var(--muted)"><?php echo htmlspecialchars($row['email']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['course_title'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                                    <td><span class="badge badge-warning"><?php echo htmlspecialchars($row['admission_mode'] ?? 'Online'); ?></span></td>
                                    <td>
                                        <span class="badge <?php echo $row['payment_status'] == 'success' ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo ucfirst($row['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <a href="view-student.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline">View</a>
                                        <a href="edit-student.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align:center;padding:30px">No students found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for($i=1; $i<=$total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $page == $i ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
