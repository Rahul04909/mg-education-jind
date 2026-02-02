<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

if (!isset($_SESSION['student_id']) || !isset($_GET['paper_id'])) {
    header("Location: index.php");
    exit;
}

$conn = getDbConnection();
$student_id = $_SESSION['student_id'];
$paper_id = intval($_GET['paper_id']);

// Fetch Result
$sql = "SELECT er.*, i.title as internship_title
        FROM internship_results er
        JOIN internship_question_papers iqp ON er.internship_paper_id = iqp.id
        JOIN internships i ON iqp.internship_id = i.id
        WHERE er.student_id = $student_id AND er.internship_paper_id = $paper_id
        ORDER BY er.id DESC LIMIT 1";

$res = $conn->query($sql);

if ($res && $res->num_rows == 0) {
    header("Location: index.php");
    exit;
}

$result = $res->fetch_assoc();
$percentage = floatval($result['percentage']);
$status = $result['status'];
$is_pass = ($status === 'PASS');
$color = $is_pass ? '#22c55e' : '#ef4444';
$bg_color = $is_pass ? '#f0fdf4' : '#fef2f2';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result - <?php echo htmlspecialchars($result['internship_title']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --primary: #6366f1; --dark: #0f172a; --slate: #64748b; --success: #22c55e; --danger: #ef4444; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Outfit', sans-serif; height: 100vh; overflow: hidden; display: flex; background: #f8fafc; }

        /* Left Split - Visual Score */
        .score-panel {
            width: 40%;
            background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 40px;
            box-shadow: 10px 0 30px rgba(0,0,0,0.1);
            z-index: 10;
        }

        .score-circle-outer {
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 40px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            position: relative;
        }

        .score-circle-inner {
            width: 240px;
            height: 240px;
            background: white;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--dark);
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        }

        .percentage-val { font-size: 64px; font-weight: 800; line-height: 1; letter-spacing: -2px; color: var(--primary); }
        .percentage-label { font-size: 16px; font-weight: 600; color: var(--slate); text-transform: uppercase; margin-top: 5px; }

        .result-badges { display: flex; gap: 15px; }
        .badge { padding: 8px 20px; border-radius: 50px; background: rgba(255,255,255,0.2); font-weight: 600; font-size: 14px; backdrop-filter: blur(5px); border: 1px solid rgba(255,255,255,0.2); }

        /* Right Split - Details */
        .details-panel {
            flex: 1;
            padding: 60px 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
        }

        .exam-header { margin-bottom: 40px; }
        .subject-badge { background: #e0e7ff; color: var(--primary); padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 15px; display: inline-block; }
        .exam-title { font-size: 32px; font-weight: 800; color: var(--dark); margin-bottom: 5px; }
        .exam-date { color: var(--slate); font-size: 15px; }

        .status-box {
            padding: 25px;
            border-radius: 16px;
            background: <?php echo $bg_color; ?>;
            border: 1px solid <?php echo $color; ?>;
            color: <?php echo $color; ?>;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .status-icon { width: 50px; height: 50px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .status-text h3 { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
        .status-text p { font-size: 14px; opacity: 0.9; }

        .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; transition: 0.2s; }
        .stat-card:hover { border-color: var(--primary); transform: translateY(-2px); box-shadow: 0 10px 30px -5px rgba(0,0,0,0.05); }
        
        .stat-val { font-size: 24px; font-weight: 700; color: var(--dark); margin-bottom: 5px; }
        .stat-label { font-size: 13px; color: var(--slate); font-weight: 500; display: flex; align-items: center; gap: 6px; }

        .actions { display: flex; gap: 15px; }
        .btn { padding: 14px 28px; border-radius: 10px; font-weight: 600; text-decoration: none; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; font-size: 15px; border: none; cursor: pointer; }
        .btn-primary { background: var(--dark); color: white; }
        .btn-primary:hover { background: #334155; }
        .btn-outline { background: white; border: 1px solid #cbd5e1; color: var(--slate); }
        .btn-outline:hover { border-color: var(--dark); color: var(--dark); }

        @media (max-width: 1024px) {
            body { flex-direction: column; overflow: auto; height: auto; }
            .score-panel { width: 100%; padding: 60px 20px; border-radius: 0 0 30px 30px; }
            .details-panel { padding: 40px 20px; }
        }
    </style>
</head>
<body>

    <!-- Left Panel: Score -->
    <div class="score-panel">
        <div class="score-circle-outer">
            <div class="score-circle-inner">
                <span class="percentage-val"><?php echo $percentage; ?>%</span>
                <span class="percentage-label">Total Score</span>
            </div>
            
            <!-- Decorative Orbit -->
            <div style="position:absolute; width: 100%; height: 100%; border: 2px dashed rgba(255,255,255,0.3); border-radius: 50%; animation: spin 20s linear infinite;"></div>
        </div>

        <div class="result-badges">
            <div class="badge">
                <i data-lucide="check-circle" style="width:14px; vertical-align:middle; margin-right:5px;"></i>
                <?php echo $result['correct_answers']; ?> Correct
            </div>
            <div class="badge">
                <i data-lucide="clock" style="width:14px; vertical-align:middle; margin-right:5px;"></i>
                Final Result
            </div>
        </div>
    </div>

    <!-- Right Panel: Details -->
    <div class="details-panel">
        <div class="exam-header">
            <span class="subject-badge"><?php echo htmlspecialchars($result['internship_title']); ?></span>
            <h1 class="exam-title">Exam Overview</h1>
            <p class="exam-date">Completed on <?php echo date('d M, Y \a\t h:i A', strtotime($result['created_at'])); ?></p>
        </div>

        <div class="status-box">
            <div class="status-icon">
                <?php if($is_pass): ?>
                    <i data-lucide="trophy" color="<?php echo $color; ?>"></i>
                <?php else: ?>
                    <i data-lucide="alert-circle" color="<?php echo $color; ?>"></i>
                <?php endif; ?>
            </div>
            <div class="status-text">
                <h3><?php echo $is_pass ? 'Congratulations!' : 'Need Improvement'; ?></h3>
                <p>You have <strong><?php echo $status; ?>ED</strong> this exam with <?php echo $percentage; ?>% marks.</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-val"><?php echo floatval($result['obtained_marks']); ?> / <?php echo $result['total_marks']; ?></div>
                <div class="stat-label"><i data-lucide="target" width="14"></i> Marks Obtained</div>
            </div>
            <div class="stat-card">
                <div class="stat-val"><?php echo $result['total_questions']; ?></div>
                <div class="stat-label"><i data-lucide="help-circle" width="14"></i> Total Questions</div>
            </div>
            <div class="stat-card">
                <div class="stat-val" style="color:#22c55e"><?php echo $result['correct_answers']; ?></div>
                <div class="stat-label"><i data-lucide="check" width="14"></i> Correct Answers</div>
            </div>
            <div class="stat-card">
                <div class="stat-val" style="color:#ef4444"><?php echo $result['wrong_answers']; ?></div>
                <div class="stat-label"><i data-lucide="x" width="14"></i> Wrong Answers</div>
            </div>
        </div>

        <div class="actions">
            <a href="index.php" class="btn btn-primary">
                <i data-lucide="layout-dashboard" width="18"></i> Return to Dashboard
            </a>
            <button class="btn btn-outline" onclick="window.print()">
                <i data-lucide="printer" width="18"></i> Print Result
            </button>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
    <style>
        @keyframes spin { 100% { transform: rotate(360deg); } }
        @media print {
            .score-panel { width: 100%; height: auto; padding: 20px; }
            .details-panel { overflow: visible; }
            .actions { display: none; }
        }
    </style>
</body>
</html>
