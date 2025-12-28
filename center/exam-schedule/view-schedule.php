<?php
session_start();
require_once __DIR__ . '/../../database/db-config.php';

if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}

$conn = getDbConnection();
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Fetch Course Info
$course_res = $conn->query("SELECT title FROM courses WHERE id = $course_id");
if($course_res->num_rows == 0) {
    die("Course not found.");
}
$course = $course_res->fetch_assoc();

// Fetch Schedules with Marks
$sql = "SELECT es.*, s.name as subject_name, s.code, s.theory_marks, s.assignment_marks 
        FROM exam_schedules es
        JOIN subjects s ON es.subject_id = s.id
        WHERE s.course_id = $course_id
        ORDER BY es.exam_date ASC, es.start_time ASC";

$result = $conn->query($sql);

include '../sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Schedule - <?php echo htmlspecialchars($course['title']); ?></title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit', sans-serif;background:#f8fafc;color:var(--text)}
        
        .admin-content{margin-left:280px;min-height:100vh;padding:30px;transition:margin-left .25s ease}
        body.sidebar-collapsed .admin-content{margin-left:80px}
        
        .page-header{margin-bottom:30px}
        .breadcrumb { font-size: 14px; margin-bottom: 10px; color: var(--muted); }
        .breadcrumb a { color: var(--indigo); text-decoration: none; }
        .page-title{font-size:28px;font-weight:700;color:var(--text);}
        
        .card { background: #fff; border: 1px solid var(--line); border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table { width: 100%; border-collapse: collapse; min-width: 900px; /* Ensure it doesn't crush */ }
        .table th { text-align: left; padding: 12px; background: #f8fafc; font-size: 13px; font-weight: 700; border-bottom: 1px solid var(--line); color: var(--muted); text-transform: uppercase; white-space:nowrap; }
        .table td { padding: 12px; border-bottom: 1px solid var(--line); font-size: 14px; font-weight: 500; vertical-align:middle; }
        
        /* Specific Column Widths */
        .w-50 { width: 50px; }
    </style>
</head>
<body>
    <main class="admin-content">
        <div class="page-header">
            <div class="breadcrumb">
                <a href="exam-schedule.php">Exam Schedules</a> › View
            </div>
            <h1 class="page-title"><?php echo htmlspecialchars($course['title']); ?></h1>
            <div style="color:var(--muted)">Exam Time Table & Marks Distribution</div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table">
                <thead>
                    <tr>
                        <th class="w-50">Sr No.</th>
                        <th>Subject Name</th>
                        <th>Exam Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Duration</th>
                        <th>Theory Marks</th>
                        <th>Assignment Marks</th>
                        <th>Grand Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): 
                        $sr = 1;
                        while($row = $result->fetch_assoc()):
                            $start_time = strtotime($row['start_time']);
                            $duration_mins = $row['duration_minutes'];
                            $end_time = $start_time + ($duration_mins * 60);
                            $total_marks = $row['theory_marks'] + $row['assignment_marks'];
                    ?>
                    <tr>
                        <td><?php echo $sr++; ?></td>
                        <td>
                            <div style="font-weight:700"><?php echo htmlspecialchars($row['subject_name']); ?></div>
                            <?php if(!empty($row['code'])): ?>
                            <div style="font-size:12px; color:var(--muted)"><?php echo htmlspecialchars($row['code']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d M, Y', strtotime($row['exam_date'])); ?></td>
                        <td><?php echo date('h:i A', $start_time); ?></td>
                        <td><?php echo date('h:i A', $end_time); ?></td>
                        <td><?php echo $duration_mins; ?> Mins</td>
                        <td style="text-align:center"><?php echo $row['theory_marks']; ?></td>
                        <td style="text-align:center"><?php echo $row['assignment_marks']; ?></td>
                        <td style="font-weight:700; color:var(--indigo)"><?php echo $total_marks; ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="9" style="text-align:center; padding:30px; color:var(--muted)">No schedule found for this course.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
