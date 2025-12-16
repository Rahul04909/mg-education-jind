<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

// Fetch Courses
$sql = "SELECT c.*, cat.name as category_name 
        FROM courses c 
        LEFT JOIN course_categories cat ON c.category_id = cat.id 
        ORDER BY c.created_at DESC";
$result = $conn->query($sql);

include __DIR__ . "/../sidebar.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - MG Education</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:20px}
        .page-header{margin-bottom:30px; display: flex; justify-content: space-between; align-items: center;}
        .page-title{font-size:32px;font-weight:800;color:var(--text);margin-bottom:8px;}
        .btn{padding:10px 20px;border-radius:10px;text-decoration:none;font-weight:700;display:inline-flex;align-items:center;gap:8px;font-size:14px;transition:0.2s;}
        .btn-primary{background:var(--indigo);color:#fff;}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 5px 15px rgba(111,117,255,0.3);}
        .card{background:#fff;border:1px solid var(--line);border-radius:18px;overflow:hidden;}
        .table{width:100%;border-collapse:collapse;}
        .table th{text-align:left;padding:16px;background:#f8fafc;font-size:13px;font-weight:700;border-bottom:1px solid var(--line);}
        .table td{padding:16px;border-bottom:1px solid var(--line);font-size:14px;}
        .badge{padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700;}
        .badge.active{background:#d1fae5;color:#065f46;}
        .badge.inactive{background:#fee2e2;color:#991b1b;}
    </style>
</head>
<body>
    <main class="admin-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Courses</h1>
                <p style="color:var(--muted)">Manage your courses and categories</p>
            </div>
            <div style="display:flex; gap:10px">
                <a href="add-category.php" class="btn btn-primary" style="background:#fff; color:var(--indigo); border:1px solid var(--indigo)">Add Category</a>
                <a href="add-course.php" class="btn btn-primary">+ Add Course</a>
            </div>
        </div>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Fees</th>
                        <th>Created</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): 
                            $fees = json_decode($row['fees'], true);
                            $fee_display = $fees['amount'] ? 'INR ' . number_format($fees['amount']) : '-';
                        ?>
                        <tr>
                            <td>
                                <div style="font-weight:700"><?php echo htmlspecialchars($row['title']); ?></div>
                                <div style="font-size:12px;color:var(--muted)"><?php echo htmlspecialchars($row['slug']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td><?php echo $fee_display; ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <span class="badge <?php echo $row['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <!-- Edit/Delete placeholders -->
                                <a href="edit-course.php?id=<?php echo $row['id']; ?>" style="color:var(--indigo);font-weight:600;text-decoration:none">Edit</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted)">No courses found. Add your first course!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
