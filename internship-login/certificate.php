<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

if (!isset($_SESSION['student_id']) || !isset($_SESSION['is_internship'])) {
    header("Location: login.php");
    exit;
}

$conn = getDbConnection();
$student_id = $_SESSION['student_id'];

// Fetch Student Details
$sql = "SELECT s.*, i.title as internship_title, sess.session_name 
        FROM internship_enrollments s 
        LEFT JOIN internships i ON s.internship_id = i.id 
        LEFT JOIN internship_sessions sess ON s.session_id = sess.id
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    die("Student not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Internship - <?php echo htmlspecialchars($student['full_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: #f0f2f5; 
            font-family: 'Outfit', sans-serif; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            min-height: 100vh;
            padding: 20px;
        }

        .no-print {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 14px;
            transition: 0.2s;
        }
        .btn:hover { background: #4338ca; }
        .btn-secondary { background: #fff; color: #1e293b; border: 1px solid #e2e8f0; }
        .btn-secondary:hover { background: #f8fafc; }

        /* Certificate Container (A4 Landscape aspect ratio approx) */
        .certificate-container {
            width: 1123px; /* A4 width at 96 DPI approx */
            height: 794px; /* A4 height at 96 DPI approx */
            position: relative;
            background-image: url('assets/new-background.png');
            background-size: cover;
            background-position: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            background-color: white;
            color: #1e293b;
            overflow: hidden;
        }

        /* Content Positioning */
        .enrollment-no {
            position: absolute;
            top: 14%; /* Adjusted based on typical certificate layouts */
            left: 50%;
            transform: translateX(-50%);
            font-size: 16px;
            font-weight: 500;
            color: #475569;
            letter-spacing: 1px;
        }

        .presented-to {
            position: absolute;
            top: 38%;
            left: 50%;
            transform: translateX(-50%);
            font-size: 18px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .student-name {
            position: absolute;
            top: 42%; 
            left: 50%;
            transform: translateX(-50%);
            font-family: 'Great Vibes', cursive;
            font-size: 50px; /* Large script font */
            color: #1e293b;
            text-align: center;
            width: 80%;
            line-height: 1.2;
        }

        .internship-text {
            position: absolute;
            top: 58%;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            width: 70%;
            font-size: 18px;
            line-height: 1.6;
            color: #334155;
        }
        
        .internship-title {
            font-weight: 700;
            color: #0f172a;
            font-size: 22px;
            display: block;
            margin-top: 5px;
        }
        
        .date-section {
            position: absolute;
            bottom: 18%;
            left: 20%;
            text-align: center;
        }
        
        .signature-section {
            position: absolute;
            bottom: 18%;
            right: 20%;
            text-align: center;
        }

        @media print {
            body { 
                background: none; 
                padding: 0; 
                display: block; 
            }
            .no-print { display: none; }
            .certificate-container {
                box-shadow: none;
                width: 100%;
                height: 100vh; /* Force full page */
                page-break-after: always;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            @page {
                size: landscape;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <a href="index.php" class="btn btn-secondary">
            <i data-lucide="arrow-left" width="16"></i> Back to Dashboard
        </a>
        <button onclick="window.print()" class="btn">
            <i data-lucide="download" width="16"></i> Download / Print Certificate
        </button>
    </div>

    <div class="certificate-container">
        <!-- Enrollment Number -->
        <div class="enrollment-no">Enrollment No: <?php echo htmlspecialchars($student['enrollment_no']); ?></div>
        
        <!-- Name -->
        <div class="student-name"><?php echo htmlspecialchars($student['full_name']); ?></div>

        <!-- Internship Details -->
        <div class="internship-text">
            For successfully completing the internship program in
            <span class="internship-title"><?php echo htmlspecialchars($student['internship_title']); ?></span>
        </div>

        <!-- Optional: Date/Signature placeholders if they aren't on the background image -->
        <!-- 
        <div class="date-section">
            <div style="font-weight:600;"><?php echo date('d M Y'); ?></div>
            <div style="font-size:12px; border-top:1px solid #94a3b8; margin-top:5px; padding-top:5px;">Date</div>
        </div>
        -->
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
<?php $conn->close(); ?>
