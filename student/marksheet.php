<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$conn = getDbConnection();

$error = '';
$verified = false;
$results = [];

// Fetch correct DOB for comparison
$s_sql = "SELECT dob, full_name, enrollment_no FROM admissions WHERE id = $student_id";
$s_res = $conn->query($s_sql);
$student_data = $s_res->fetch_assoc();
$correct_dob = $student_data['dob'];

// Handle DOB Verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_dob'])) {
    $input_dob = $_POST['dob'];
    if ($input_dob === $correct_dob) {
        $verified = true;
        // Fetch all exam results for this student
        $r_sql = "SELECT er.*, sub.name as subject_name, sub.theory_marks, sub.assignment_marks, es.exam_date 
                  FROM exam_results er
                  JOIN exam_schedules es ON er.exam_schedule_id = es.id
                  JOIN subjects sub ON es.subject_id = sub.id
                  WHERE er.student_id = $student_id
                  ORDER BY es.exam_date DESC";
        $r_res = $conn->query($r_sql);
        while($row = $r_res->fetch_assoc()) {
            $results[] = $row;
        }
    } else {
        $error = "Incorrect Date of Birth. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Marksheet - MG Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --primary: #a855f7;
            --primary-light: #f3e8ff;
            --secondary: #0f172a;
            --bg: #f8fafc;
            --white: #ffffff;
            --border: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --danger: #ef4444;
            --sidebar-w: 260px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text-main); }
        .main-container { margin-left: var(--sidebar-w); min-height: 100vh; padding: 40px; }

        .header { margin-bottom: 30px; }
        .header h1 { font-size: 28px; font-weight: 700; color: var(--secondary); }
        .header p { color: var(--text-muted); margin-top: 4px; }

        /* Verification Card */
        .verification-card {
            max-width: 500px;
            background: var(--white);
            padding: 40px;
            border-radius: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
            margin-top: 20px;
        }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 14px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; }
        .form-input { 
            width: 100%; 
            padding: 12px 16px; 
            border: 1px solid var(--border); 
            border-radius: 12px; 
            font-family: inherit; 
            font-size: 16px;
            transition: border-color 0.2s;
        }
        .form-input:focus { outline: none; border-color: var(--primary); }
        
        .verify-btn {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .verify-btn:active { transform: scale(0.98); }

        .error-msg {
            background: #fef2f2;
            color: var(--danger);
            padding: 12px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
            border: 1px solid #fee2e2;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Result List */
        .result-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
            margin-top: 20px;
        }
        .result-card {
            background: var(--white);
            border-radius: 20px;
            border: 1px solid var(--border);
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            transition: transform 0.3s;
        }
        .result-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        
        .res-type {
            font-size: 12px;
            font-weight: 700;
            color: var(--primary);
            background: var(--primary-light);
            padding: 4px 10px;
            border-radius: 20px;
            align-self: flex-start;
        }
        .res-title { font-size: 18px; font-weight: 700; color: var(--secondary); }
        .res-meta { font-size: 14px; color: var(--text-muted); display: flex; align-items: center; gap: 6px; }
        
        .download-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--secondary);
            color: white;
            padding: 12px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            margin-top: auto;
            transition: background 0.2s;
        }
        .download-btn:hover { background: #1e293b; }

        @media (max-width: 768px) {
            .main-container { margin-left: 0; padding: 20px; }
            .verification-card { padding: 25px; }
        }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <main class="main-container">
        <div class="header">
            <h1 style="display: flex; align-items:center; gap:12px;">
                <i data-lucide="file-text" style="color:var(--primary)"></i> Download Marksheet
            </h1>
            <p>Verify your identity to access and download your examination records.</p>
        </div>

        <?php if (!$verified): ?>
            <div class="verification-card">
                <div style="text-align: center; margin-bottom: 30px;">
                    <div style="width: 60px; height: 60px; background: var(--primary-light); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                        <i data-lucide="lock" size="30"></i>
                    </div>
                    <h2 style="font-size: 20px; margin-bottom: 5px;">Identity Verification</h2>
                    <p style="font-size: 14px; color: var(--text-muted);">Enter your Date of Birth as per records</p>
                </div>

                <?php if ($error): ?>
                    <div class="error-msg">
                        <i data-lucide="alert-circle" size="18"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="dob" class="form-input" required>
                    </div>
                    <button type="submit" name="verify_dob" class="verify-btn">Verify & Proceed</button>
                </form>
            </div>
        <?php else: ?>
            <div class="success-header" style="margin-bottom: 20px;">
                <p style="color:var(--success); font-weight:600; display:flex; align-items:center; gap:6px;">
                    <i data-lucide="check-circle" size="18"></i> Identity Verified Successfully
                </p>
            </div>

            <?php if (empty($results)): ?>
                <div style="background:white; padding:40px; border-radius:24px; text-align:center; border:1px solid var(--border);">
                    <i data-lucide="inbox" size="48" style="color:var(--text-muted); margin-bottom:15px;"></i>
                    <h3 style="color:var(--secondary)">No Marksheets Found</h3>
                    <p style="color:var(--text-muted)">Your examination results are not yet available for download.</p>
                </div>
            <?php else: ?>
                <div class="result-grid">
                    <?php foreach ($results as $res): ?>
                        <div class="result-card">
                            <span class="res-type">Examination Result</span>
                            <h3 class="res-title"><?php echo htmlspecialchars($res['subject_name']); ?></h3>
                            <div class="res-meta">
                                <i data-lucide="calendar" size="16"></i>
                                Exam Date: <?php echo date('d M, Y', strtotime($res['exam_date'])); ?>
                            </div>
                            <div class="res-meta">
                                <i data-lucide="award" size="16"></i>
                                Marks: <?php echo $res['obtained_marks']; ?> / <?php echo ($res['theory_marks'] + $res['assignment_marks']); ?>
                            </div>
                            <a href="marksheet/download-marksheet.php?exam_id=<?php echo $res['exam_schedule_id']; ?>" class="download-btn">
                                <i data-lucide="download" size="18"></i> Download PDF
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
