<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$conn = getDbConnection();

// 1. Fetch Student Details
$student_sql = "SELECT s.*, c.title as course_name 
                FROM admissions s
                LEFT JOIN courses c ON s.course_id = c.id
                WHERE s.id = $student_id";
$s_res = $conn->query($student_sql);
$student = $s_res->fetch_assoc();

if (!$student) {
    die("Student not found.");
}

// 2. Center Logic
$center_name = "MG SKILLS & SOCIAL DEVELOPMENT ORGANIZATION";
if (!empty($student['center_id'])) {
    $cid = $student['center_id'];
    $center_res = $conn->query("SELECT center_name, address FROM centers WHERE id = $cid");
    if($center_res && $center_res->num_rows > 0){
        $c_data = $center_res->fetch_assoc();
        $center_name = $c_data['center_name'];
        // Optional: Append address if needed, user just said Center Name
    }
}

// 3. Fetch Exam Schedules
$session_id = $student['session_id'] ?? 0;
$exams = [];
if ($session_id > 0) {
    $sql = "SELECT es.*, s.name as subject_name 
            FROM exam_schedules es 
            JOIN subjects s ON es.subject_id = s.id 
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
    <title>Hall Ticket - Preview</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root{--primary:#6366f1;--secondary:#0f172a;--bg:#f8fafc;--white:#fff;--border:#e2e8f0;}
        body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--secondary);display:flex;min-height:100vh}
        
        .main{margin-left:260px;flex:1;padding:30px}
        .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px}
        
        .hall-ticket-container {
            width: 210mm; /* A4 Width */
            min-height: 297mm; /* A4 Height */
            margin: 0 auto;
            background: #fff;
            position: relative;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            /* Using the background image as specified */
            background-image: url('hall-ticket/background-hall-ticket.png'); 
            background-size: cover; /* or 100% 100% to force fit */
            background-position: center;
            background-repeat: no-repeat;
            margin-top: 120px;
        }

        /* If background is meant to be a border/frame, we might need padding inside */
        .ht-content {
            padding: 40px;
            position: relative;
            z-index: 2;
        }

        .ht-header {
            text-align: center;
            margin-bottom: 30px;
            margin-top: 100px; /* Adjust based on logo space in background */
        }
        .ht-header h1 {
            font-size: 28px;
            text-transform: uppercase;
            color: #b91c1c; /* Dark Red often looks pro on certs */
            margin-bottom: 5px;
            font-weight: 800;
        }
        .ht-header h2 {
            font-size: 16px;
            color: #333;
            font-weight: 600;
        }
        
        .ht-title-strip {
            background: #b91c1c;
            color: white;
            text-align: center;
            padding: 8px;
            font-size: 20px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 25px;
            border-radius: 4px;
        }

        .ht-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 8px 12px;
            border: 1px solid #000;
            font-size: 14px;
        }
        .info-label {
            font-weight: 700;
            width: 160px;
            background: #fff5f5;
        }
        .photo-box {
            border: 2px solid #000;
            height: 180px;
            width: 150px;
            margin: 0 auto;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        .student-photo {
            flex: 1;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .sign-box {
            height: 40px;
            border-top: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sign-img {
            max-height: 35px;
            max-width: 100%;
        }

        .exam-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .exam-table th {
            background: #333;
            color: #fff;
            padding: 10px;
            font-size: 14px;
            text-transform: uppercase;
            border: 1px solid #000;
        }
        .exam-table td {
            padding: 10px;
            border: 1px solid #000;
            font-size: 13px;
            text-align: center;
        }

        .footer-section {
            margin-top: 50px;
            display: flex;
            justify-content: flex-end;
            padding-right: 40px;
        }
        .auth-sign {
            text-align: center;
        }
        .auth-sign p {
            font-weight: 700;
            margin-top: 5px;
            border-top: 1px solid #000;
            padding-top: 5px;
            width: 200px;
        }
        
        .download-btn-container {
            text-align: right;
            margin-bottom: 20px;
        }
        .btn-download {
            background: #22c55e;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-download:hover {background: #16a34a;}

        @media(max-width:1024px){
            .main{margin-left:0; padding:10px;}
            .hall-ticket-container { width: 100%; height: auto; background-size: contain; }
            .sidebar {display:none;}
        }
        
        /* Print Hide */
        @media print {
            .sidebar, .header, .download-btn-container {display: none;}
            .main {margin: 0; padding: 0;}
            .hall-ticket-container {box-shadow: none; margin: 0; width: 100%;}
        }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <main class="main">
        <div class="header">
            <h1 class="page-title" style="font-size:24px; font-weight:700;">Hall Ticket</h1>
            <div class="download-btn-container">
                <a href="download-hall-ticket.php" class="btn-download" target="_blank">
                    <i data-lucide="download"></i> Download PDF
                </a>
            </div>
        </div>

        <div class="hall-ticket-container">
            <div class="ht-content">
                
                <div class="ht-header">
                     <!-- Assuming Logo is part of background or we can add it -->
                     <!-- <img src="../../assets/images/logo.png" style="height: 80px;"> -->
                     <h1><?php echo htmlspecialchars($center_name); ?></h1>
                     <h2>(An ISO 9001:2015 Certified Organization)</h2>
                </div>

                <div class="ht-title-strip">HALL TICKET</div>

                <div class="ht-grid">
                    <div>
                        <table class="info-table">
                            <tr>
                                <td class="info-label">Student Name</td>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Enrollment No</td>
                                <td><?php echo htmlspecialchars($student['enrollment_no'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Course Name</td>
                                <td><?php echo htmlspecialchars($student['course_name'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Father's Name</td>
                                <td><?php echo htmlspecialchars($student['father_name']); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Mother's Name</td>
                                <td><?php echo htmlspecialchars($student['mother_name']); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Date of Birth</td>
                                <td><?php echo htmlspecialchars($student['dob']); ?></td>
                            </tr>
                             <tr>
                                <td class="info-label">Mobile No</td>
                                <td><?php echo htmlspecialchars($student['mobile']); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div>
                         <div class="photo-box">
                             <?php if(!empty($student['photo'])): ?>
                                <img src="../../uploads/<?php echo $student['photo']; ?>" class="student-photo" alt="Photo">
                             <?php else: ?>
                                <div style="display:flex;align-items:center;justify-content:center;height:100%;color:#ccc;">No Photo</div>
                             <?php endif; ?>
                             
                             <div class="sign-box">
                                 <?php if(!empty($student['signature'])): ?>
                                    <img src="../../uploads/<?php echo $student['signature']; ?>" class="sign-img" alt="Sign">
                                 <?php else: ?>
                                    <span style="font-size:10px; color:#ccc;">Sign</span>
                                 <?php endif; ?>
                             </div>
                         </div>
                    </div>
                </div>

                <!-- Exam Schedule -->
                <h3 style="margin-top:20px; font-weight:700; text-transform:uppercase; border-bottom:2px solid #000; padding-bottom:5px;">Exam Schedule</h3>
                <table class="exam-table">
                    <thead>
                        <tr>
                            <th style="width:50px;">Sr. No</th>
                            <th>Subject Name</th>
                            <th>Exam Date</th>
                            <th>Timing</th>
                            <th>Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($exams)): ?>
                            <tr><td colspan="5">No Exam Schedule Available</td></tr>
                        <?php else: ?>
                            <?php foreach($exams as $i => $ex): 
                                $start = date('h:i A', strtotime($ex['start_time']));
                                $end = date('h:i A', strtotime($ex['start_time']) + ($ex['duration_minutes']*60));
                            ?>
                            <tr>
                                <td><?php echo $i+1; ?></td>
                                <td><?php echo htmlspecialchars($ex['subject_name']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($ex['exam_date'])); ?></td>
                                <td><?php echo $start . ' - ' . $end; ?></td>
                                <td><?php echo $ex['duration_minutes']; ?> Mins</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="footer-section">
                    <div class="auth-sign">
                        <!-- Space for Signature Image -->
                         <br><br><br>
                        <p>Authorized Signatory</p>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
<?php $conn->close(); ?>
