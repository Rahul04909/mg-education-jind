<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$conn = getDbConnection();

// 1. Fetch Student's Session ID
$student_sql = "SELECT session_id FROM admissions WHERE id = $student_id";
$stu_res = $conn->query($student_sql);
$student_data = $stu_res->fetch_assoc();
$session_id = $student_data['session_id'] ?? 0;

// 2. Fetch Exam Schedules for this Session
$exams = [];
if ($session_id > 0) {
    $sql = "SELECT es.*, s.name as subject_name, s.code as subject_code, c.title as course_name 
            FROM exam_schedules es 
            JOIN subjects s ON es.subject_id = s.id 
            JOIN courses c ON s.course_id = c.id 
            WHERE es.session_id = $session_id 
            ORDER BY es.exam_date ASC, es.start_time ASC";
            
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $exams[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Date Sheet - MG Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root{--primary:#6366f1;--secondary:#0f172a;--bg:#f8fafc;--white:#fff;--border:#e2e8f0;--success:#22c55e;}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--secondary);display:flex;min-height:100vh}
        
        .main{margin-left:260px;flex:1;padding:30px}
        .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px}
        .page-title{font-size:24px;font-weight:700;}
        
        .content-card{background:var(--white);border-radius:16px;border:1px solid var(--border);padding:24px;margin-bottom:24px; overflow:hidden;}
        
        /* Table Styles */
        .table-responsive {overflow-x: auto;}
        .table {width: 100%; border-collapse: collapse;}
        .table th {text-align: left; padding: 16px; background: #f1f5f9; font-size: 13px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;}
        .table td {padding: 16px; border-bottom: 1px solid var(--border); font-size: 14px; vertical-align: middle;}
        .table tr:last-child td {border-bottom: none;}
        
        .date-badge {background: #eff6ff; color: var(--primary); padding: 6px 12px; border-radius: 8px; font-weight: 500; display: inline-block;}
        .time-badge {color: #64748b; font-size: 13px; display: flex; align-items: center; gap: 6px;}

        @media(max-width:1024px){
            .sidebar{display:none}
            .main{margin-left:0}
        }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <main class="main">
        <div class="header">
            <div>
                <h1 class="page-title">Exam Date Sheet</h1>
                <p style="color:#64748b; margin-top:5px;">Schedule for your current session exams</p>
            </div>
        </div>

        <div class="content-card">
            <?php if (empty($exams)): ?>
                <div style="text-align:center; padding:40px; color:#64748b;">
                    <div style="margin-bottom:15px; background:#f1f5f9; width:60px; height:60px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center;">
                        <i data-lucide="calendar-off" size="24" color="#94a3b8"></i>
                    </div>
                    <h3>No Exams Scheduled</h3>
                    <p>There are no exams scheduled for your current session yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Exam Date</th>
                                <th>Subject</th>
                                <th>Timing</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($exams as $exam): 
                                $start_time = strtotime($exam['start_time']);
                                $duration = $exam['duration_minutes'];
                                $end_time = $start_time + ($duration * 60);
                            ?>
                            <tr>
                                <td>
                                    <div class="date-badge">
                                        <?php echo date('d M, Y', strtotime($exam['exam_date'])); ?>
                                    </div>
                                    <div style="font-size:12px; color:#64748b; margin-top:4px;">
                                        <?php echo date('l', strtotime($exam['exam_date'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight:600; color:var(--secondary);">
                                        <?php echo htmlspecialchars($exam['subject_name']); ?>
                                    </div>
                                    <div style="font-size:12px; color:#64748b;">
                                        <?php echo htmlspecialchars($exam['subject_code']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="time-badge">
                                        <i data-lucide="clock" size="14"></i>
                                        <?php echo date('h:i A', $start_time); ?> - <?php echo date('h:i A', $end_time); ?>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-weight:500;"><?php echo $duration; ?> Mins</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
<?php $conn->close(); ?>
