<?php
session_start();
require_once __DIR__ . '/../../database/db-config.php';

if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}

$conn = getDbConnection();
$center_id = $_SESSION['center_id'];

// Fetch Courses with Subject Count and Schedule Existence
// Logic: Get active courses. Check if they have subjects. Check if any subject has a schedule.
$sql = "SELECT c.id, c.title, c.duration_value, c.duration_type,
        (SELECT COUNT(*) FROM subjects s WHERE s.course_id = c.id) as total_subjects,
        (SELECT COUNT(*) FROM exam_schedules es JOIN subjects s ON es.subject_id = s.id WHERE s.course_id = c.id) as total_schedules
        FROM courses c 
        WHERE c.is_active = 1";

$result = $conn->query($sql);

include '../sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Schedules - MG Skills</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit', sans-serif;background:#f8fafc;color:var(--text)}
        
        /* Sidebar Adjustment */
        .admin-content{margin-left:280px;min-height:100vh;padding:30px;transition:margin-left .25s ease}
        body.sidebar-collapsed .admin-content{margin-left:80px}
        
        .page-header{margin-bottom:30px}
        .page-title{font-size:28px;font-weight:700;color:var(--text);margin-bottom:5px}
        
        .card { background: #fff; border: 1px solid var(--line); border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .table { width: 100%; border-collapse: collapse; }
        .table th { text-align: left; padding: 16px; background: #f8fafc; font-size: 13px; font-weight: 700; border-bottom: 1px solid var(--line); color: var(--muted); text-transform: uppercase; }
        .table td { padding: 16px; border-bottom: 1px solid var(--line); font-size: 14px; font-weight: 500; }
        
        .btn { padding: 8px 16px; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-block; font-size: 13px; transition: 0.2s; border: 1px solid transparent; }
        .btn-primary { background: var(--indigo); color: white; }
        .btn-primary:hover { opacity: 0.9; }
        .btn-disabled { background: #e2e8f0; color: #94a3b8; cursor: not-allowed; }
        
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-gray { background: #f1f5f9; color: #475569; }

    </style>
</head>
<body>
    <main class="admin-content">
        <div class="page-header">
            <h1 class="page-title">Exam Schedules</h1>
            <div style="color:var(--muted)">Check exam dates and timings for courses.</div>
        </div>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Duration</th>
                        <th>Total Subjects</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): 
                            $has_schedule = $row['total_schedules'] > 0;
                        ?>
                        <tr>
                            <td>
                                <div style="font-weight:700; font-size:15px;"><?php echo htmlspecialchars($row['title']); ?></div>
                            </td>
                            <td><?php echo $row['duration_value'] . " " . $row['duration_type']; ?></td>
                            <td><?php echo $row['total_subjects']; ?> Subjects</td>
                            <td>
                                <?php if($has_schedule): ?>
                                    <span class="badge badge-green">Schedule Available</span>
                                <?php else: ?>
                                    <span class="badge badge-gray">Not Scheduled</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($has_schedule): ?>
                                    <a href="view-schedule.php?course_id=<?php echo $row['id']; ?>" class="btn btn-primary">View Schedule</a>
                                <?php else: ?>
                                    <button class="btn btn-disabled" disabled>View Schedule</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--muted)">No courses active.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
