<?php
session_start();
require_once __DIR__ . '/../../database/db-config.php';

if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}

$center_id = $_SESSION['center_id'];
$conn = getDbConnection();

// Fetch Students for this center
$sql = "SELECT a.*, c.title as course_name 
        FROM admissions a 
        LEFT JOIN courses c ON a.course_id = c.id 
        WHERE a.center_id = $center_id AND a.added_by = 'center' 
        ORDER BY a.id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Center Dashboard</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e;}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit',sans-serif;background:#f8fafc;color:var(--text)}
        .admin-content{margin-left:260px;padding:30px;transition:margin-left .25s ease}
        .page-header{margin-bottom:30px;display:flex;justify-content:space-between;align-items:center}
        .page-title{font-size:24px;font-weight:700}
        .btn{padding:10px 20px;background:var(--indigo);color:#fff;text-decoration:none;border-radius:8px;font-weight:600}
        .card{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.05);overflow:hidden}
        table{width:100%;border-collapse:collapse}
        th,td{padding:15px;text-align:left;border-bottom:1px solid var(--line)}
        th{background:#f1f5f9;font-weight:600;color:var(--muted)}
        .btn-sm{padding:6px 12px;font-size:12px;}
    </style>
</head>
<body>
    <?php include __DIR__ . '/../sidebar.php'; ?>
    <main class="admin-content">
        <div class="page-header">
            <h1 class="page-title">Center Students</h1>
            <a href="add-student.php" class="btn">+ Add New Student</a>
        </div>
        
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Enrollment No</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Mobile</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['enrollment_no']); ?></td>
                                <td>
                                    <div style="font-weight:600"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                    <small style="color:var(--muted)"><?php echo htmlspecialchars($row['father_name']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <a href="edit-student.php?id=<?php echo $row['id']; ?>" class="btn btn-sm" style="background:#3b82f6">Edit</a>
                                    <!-- <a href="view-student.php?id=<?php echo $row['id']; ?>" class="btn btn-sm" style="background:#64748b">View</a> -->
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;">No students found enrolled by this center.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
