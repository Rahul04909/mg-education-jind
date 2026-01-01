<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

if (!isset($_SESSION['student_id']) || !isset($_GET['exam_id'])) {
    header("Location: index.php");
    exit;
}

$conn = getDbConnection();
$student_id = $_SESSION['student_id'];
$exam_id = intval($_GET['exam_id']);

// Fetch Result
$sql = "SELECT er.*, s.name as subject_name 
        FROM exam_results er
        JOIN exam_schedules es ON er.exam_schedule_id = es.id
        JOIN subjects s ON es.subject_id = s.id
        WHERE er.student_id = $student_id AND er.exam_schedule_id = $exam_id
        ORDER BY er.id DESC LIMIT 1";

$res = $conn->query($sql);

if ($res->num_rows == 0) {
    // Result not found? Maybe not submitted yet.
    header("Location: index.php");
    exit;
}

$result = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Result - <?php echo htmlspecialchars($result['subject_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --primary: #6366f1; --bg: #f8fafc; --text: #0f172a; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text); display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        
        .result-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px -5px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        
        .score-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: #f0f9ff;
            color: var(--primary);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            border: 5px solid var(--primary);
            position: relative;
        }
        .score-val { font-size: 36px; font-weight: 700; line-height: 1; }
        .score-total { font-size: 14px; color: #64748b; font-weight: 500; }
        
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 50px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .pass { background: #dcfce7; color: #15803d; }
        .fail { background: #fee2e2; color: #b91c1c; }
        
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; text-align: left; margin-bottom: 30px; }
        .stat-item { padding: 15px; background: #f8fafc; border-radius: 10px; }
        .stat-label { font-size: 12px; color: #64748b; margin-bottom: 4px; }
        .stat-num { font-size: 18px; font-weight: 700; }
        
        .btn-home {
            display: block;
            width: 100%;
            padding: 15px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: 0.2s;
        }
        .btn-home:hover { background: #4f46e5; }
    </style>
</head>
<body>

    <div class="result-card">
        <h2 style="margin-bottom: 10px;">Exam Result</h2>
        <p style="color: #64748b; margin-bottom: 30px;"><?php echo htmlspecialchars($result['subject_name']); ?></p>
        
        <div class="score-circle">
            <span class="score-val"><?php echo floatval($result['percentage']); ?>%</span>
            <span class="score-total">Percentage</span>
        </div>
        
        <span class="status-badge <?php echo strtolower($result['status']); ?>">
            <?php echo $result['status']; ?>
        </span>
        
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-label">Total Questions</div>
                <div class="stat-num"><?php echo $result['total_questions']; ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Correct Answers</div>
                <div class="stat-num" style="color:#15803d"><?php echo $result['correct_answers']; ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Wrong Answers</div>
                <div class="stat-num" style="color:#b91c1c"><?php echo $result['wrong_answers']; ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Score</div>
                <div class="stat-num"><?php echo floatval($result['obtained_marks']); ?> / <?php echo $result['total_marks']; ?></div>
            </div>
        </div>
        
        <a href="index.php" class="btn-home">Back to Dashboard</a>
    </div>

</body>
</html>
