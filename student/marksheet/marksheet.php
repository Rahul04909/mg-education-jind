<?php
session_start();
require_once __DIR__ . '/../../database/db-config.php';

if (!isset($_SESSION['student_id'])) {
    die("Access Denied");
}

$conn = getDbConnection();
$student_id = $_SESSION['student_id'];

// Get Exam ID (Specific or Latest)
$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;

if ($exam_id == 0) {
    // Fetch latest result
    $l_sql = "SELECT exam_schedule_id FROM exam_results WHERE student_id = $student_id ORDER BY id DESC LIMIT 1";
    $l_res = $conn->query($l_sql);
    if ($l_res->num_rows > 0) {
        $exam_id = $l_res->fetch_assoc()['exam_schedule_id'];
    } else {
        die("No results found.");
    }
}

// Fetch Student & Result Details
$sql = "SELECT er.*, 
               s.full_name, s.father_name, s.mother_name, s.enrollment_no, s.dob, s.student_photo,
               sub.name as subject_name, c.title as course_name,
               es.exam_date
        FROM exam_results er
        JOIN admissions s ON er.student_id = s.id
        LEFT JOIN courses c ON s.course_id = c.id
        JOIN exam_schedules es ON er.exam_schedule_id = es.id
        JOIN subjects sub ON es.subject_id = sub.id
        WHERE er.student_id = $student_id AND er.exam_schedule_id = $exam_id";

$res = $conn->query($sql);
if ($res->num_rows == 0) {
    die("Result not found.");
}

$data = $res->fetch_assoc();

// Grade Calculation Helper
function getGrade($percentage) {
    if ($percentage >= 85) return 'A+';
    if ($percentage >= 75) return 'A';
    if ($percentage >= 65) return 'B+';
    if ($percentage >= 55) return 'B';
    if ($percentage >= 45) return 'C';
    if ($percentage >= 33) return 'D';
    return 'FAIL';
}

$grade = getGrade($data['percentage']);
$dob_formatted = date('d/m/Y', strtotime($data['dob']));
$exam_date_fmt = date('Y', strtotime($data['exam_date']));

// Number to Words (Simplified for demo)
$f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
$total_words = strtoupper($f->format($data['obtained_marks']));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marksheet - <?php echo htmlspecialchars($data['full_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Times+New+Roman:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; background: #e2e8f0; font-family: 'Times New Roman', serif; }
        
        .page-container {
            width: 210mm;
            height: 297mm;
            margin: 20px auto;
            background: white url('legal-marksheet-background.png') no-repeat center center;
            background-size: 100% 100%;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            padding: 40mm 15mm 15mm 15mm; /* Adjust padding based on background design */
        }
        
        /* Overlay Content */
        .header { text-align: center; margin-bottom: 30px; }
        .school-name { font-size: 32px; font-weight: 700; color: #dc2626; text-transform: uppercase; margin-bottom: 5px; }
        .address { font-size: 14px; color: #1e293b; margin-bottom: 20px; line-height: 1.4; }
        .address { font-size: 14px; color: #1e293b; margin-bottom: 20px; line-height: 1.4; }

        .student-info { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .info-table { border-collapse: collapse; width: 70%; font-size: 16px; font-weight: 700; }
        .info-table td { padding: 5px 0; vertical-align: top; }
        .info-label { width: 140px; color: #334155; }
        .info-val { color: #000; text-transform: uppercase; }

        .photo-box { width: 120px; height: 140px; border: 2px solid #000; padding: 5px; }
        .photo-box img { width: 100%; height: 100%; object-fit: cover; }

        .marks-table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 2px solid #000; }
        .marks-table th, .marks-table td { border: 1px solid #000; padding: 8px; text-align: center; font-size: 14px; }
        .marks-table th { background: #fef9c3; font-weight: 700; }
        .marks-table td { font-weight: 600; }
        
        .footer { margin-top: 50px; display: flex; justify-content: space-between; align-items: flex-end; padding: 0 20px; }
        .sign-box { text-align: center; }
        .sign-box img { height: 50px; display: block; margin: 0 auto; }
        .sign-label { font-weight: 700; margin-top: 5px; border-top: 1px solid #000; padding-top: 5px; width: 200px; display: inline-block; }

        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #2563eb;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-family: sans-serif;
            font-weight: 600;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
            cursor: pointer;
            z-index: 100;
        }

        @media print {
            body { background: white; margin: 0; }
            .page-container { margin: 0; box-shadow: none; width: 100%; height: 100vh; page-break-after: always; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

    <div class="page-container">
        <!-- Assuming background image has header, adjusting spacing -->
        <!-- If strict overlay needed on blank background, un-comment header -->
        <div class="header" style="margin-top: 100px;"> <!-- Spacer for pre-printed header in bg -->
            <!-- 
            <div class="school-name">MG Education & Skill Development</div>
            <div class="address">Navjeevan Colony, Behind Chhola Dussehra Ground<br>Bhopal (462010), M.P.</div>
            -->
        </div>

        <div class="student-info">
            <table class="info-table">
                <tr>
                    <td class="info-label">Student's Name</td>
                    <td>: <span class="info-val"><?php echo htmlspecialchars($data['full_name']); ?></span></td>
                </tr>
                <tr>
                    <td class="info-label">Father's Name</td>
                    <td>: <span class="info-val"><?php echo htmlspecialchars($data['father_name']); ?></span></td>
                </tr>
                <tr>
                    <td class="info-label">Mother's Name</td>
                    <td>: <span class="info-val"><?php echo htmlspecialchars($data['mother_name']); ?></span></td>
                </tr>
                <tr>
                    <td class="info-label">Class/Course</td>
                    <td>: <span class="info-val"><?php echo htmlspecialchars($data['course_name']); ?></span></td>
                </tr>
                <tr>
                    <td class="info-label">Enrollment No.</td>
                    <td>: <span class="info-val"><?php echo htmlspecialchars($data['enrollment_no']); ?></span></td>
                </tr>
                <tr>
                    <td class="info-label">DOB</td>
                    <td>: <span class="info-val"><?php echo $dob_formatted; ?></span></td>
                </tr>
            </table>

            <div class="photo-box">
                <?php 
                    $photo = (!empty($data['student_photo']) && file_exists("../../" . $data['student_photo'])) ? "../../" . $data['student_photo'] : "../../assets/images/avatar-placeholder.png";
                ?>
                <img src="<?php echo $photo; ?>" alt="Student Photo">
            </div>
        </div>

        <table class="marks-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 40%;">SUBJECT</th>
                    <th colspan="3">ASSESSMENT OF ACADEMIC AREAS</th>
                    <th rowspan="2">ANNUAL RESULT</th>
                </tr>
                <tr>
                    <th>TOTAL MARKS</th>
                    <th>OBTAINED MARKS</th>
                    <th>GRADE</th>
                </tr>
            </thead>
            <tbody>
                <!-- Single Subject Exam - Row 1 -->
                <tr>
                    <td style="text-align: left; padding-left: 15px;"><?php echo htmlspecialchars($data['subject_name']); ?></td>
                    <td><?php echo $data['total_marks']; ?></td>
                    <td><?php echo $data['obtained_marks']; ?></td>
                    <td><?php echo $grade; ?></td>
                    <td style="font-weight: 700;"><?php echo $grade === 'FAIL' ? 'FAIL' : 'PASS'; ?></td>
                </tr>
                <!-- Empty rows for look -->
                <?php for($i=0; $i<4; $i++): ?>
                <tr>
                    <td style="height: 30px;">-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
                <?php endfor; ?>
                
                <tr style="background: #f0f9ff;">
                    <td style="text-align: right; padding-right: 15px; font-weight: 700;">GRAND TOTAL</td>
                    <td><?php echo $data['total_marks']; ?></td>
                    <td><?php echo $data['obtained_marks']; ?></td>
                    <td>-</td>
                    <td style="color: <?php echo ($data['status']=='PASS') ? 'green' : 'red'; ?>"><?php echo $data['status']; ?></td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 15px; font-size: 14px; font-weight: 700;">
            PERCENTAGE: <span style="margin-right: 30px;"><?php echo $data['percentage']; ?>%</span>
            RESULT: <span><?php echo $data['status']; ?></span>
        </div>

        <div class="footer" style="justify-content: flex-end; padding-right: 50px;">
            <div class="sign-box">
                <img src="mg-sign.png" alt="Authorized Signatory" style="height: 60px; display: block; margin: 0 auto;"> 
                <div class="sign-label">AUTHORIZED SIGNATORY</div>
            </div>
        </div>

    </div>

    <button class="print-btn" onclick="window.print()">Download Marksheet</button>

</body>
</html>
