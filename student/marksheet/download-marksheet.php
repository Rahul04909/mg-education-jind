<?php
session_start();
require_once __DIR__ . '/../../database/db-config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

// Enable Error Reporting for Debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Dompdf\Dompdf;
use Dompdf\Options;

// QR Code Imports
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

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
                sub.name as subject_name, sub.id as subject_id, sub.theory_marks, sub.assignment_marks, c.title as course_name, cs.session_name,
                es.exam_date
         FROM exam_results er
         JOIN admissions s ON er.student_id = s.id
         LEFT JOIN courses c ON s.course_id = c.id
         LEFT JOIN course_sessions cs ON s.session_id = cs.id
         JOIN exam_schedules es ON er.exam_schedule_id = es.id
         JOIN subjects sub ON es.subject_id = sub.id
         WHERE er.student_id = $student_id AND er.exam_schedule_id = $exam_id";

$res = $conn->query($sql);
if ($res->num_rows == 0) {
    die("Result not found.");
}

$data = $res->fetch_assoc();

// Fetch Assignment (Internal) Marks
$subject_id = $data['subject_id'];
$internal_sql = "SELECT SUM(marks_obtained) as total FROM student_assignments sa 
                 JOIN assignments a ON sa.assignment_id = a.id 
                 WHERE sa.student_id = $student_id AND a.subject_id = $subject_id AND sa.status = 'GRADED'";
$internal_res = $conn->query($internal_sql);
$internal_marks = ($internal_res->num_rows > 0) ? $internal_res->fetch_assoc()['total'] : 0;
$internal_marks = $internal_marks ? $internal_marks : 0; 
$internal_max = $data['assignment_marks']; // From subjects table

$theory_obtained = $data['obtained_marks'];
$theory_max = $data['theory_marks']; // From subjects table

// Calculate Totals
$grand_total_obtained = $internal_marks + $theory_obtained;
$grand_total_max = $theory_max + $internal_max;

// Recalculate Percentage
$percentage = ($grand_total_max > 0) ? round(($grand_total_obtained / $grand_total_max) * 100, 2) : 0;

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

$grade = getGrade($percentage);
$dob_formatted = date('d/m/Y', strtotime($data['dob']));

// Image Helper
function get_image_base64($path) {
    if (!file_exists($path)) {
        return '';
    }
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $img_content = file_get_contents($path);
    if ($img_content === false) return '';
    return 'data:image/' . $type . ';base64,' . base64_encode($img_content);
}

// Prepare Images
$base_dir = __DIR__;
$bg_image_path = $base_dir . '/mg-background.png';
$sign_image_path = $base_dir . '/mg-sign.png';

// Student photo is in root/assets/uploads... so we go up 2 levels from student/marksheet to get to root
$root_dir = dirname(dirname($base_dir)); 
$photo_path = (!empty($data['student_photo'])) ? $root_dir . '/' . $data['student_photo'] : $root_dir . '/assets/images/avatar-placeholder.png';

$bg_src = get_image_base64($bg_image_path);
$sign_src = get_image_base64($sign_image_path);
$photo_src = get_image_base64($photo_path);

// Determine Pass/Fail Color based on Grade or existing status? 
// Re-evaluating status based on grade
$status_result = ($grade == 'FAIL') ? 'FAIL' : 'PASS';
$status_color = ($status_result == 'PASS') ? 'green' : 'red';

$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        @page { margin: 0px; }
        body { margin: 0px; font-family: "Times New Roman", serif; }
        
        .bg-image {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .content {
            padding: 0px 40px; /* Standardize padding */
            width: 100%;
            box-sizing: border-box;
            position: relative; 
            z-index: 10;
        }

        .header-spacer { height: 290px; } /* Fine-tuned based on 300px feedback */
        
        /* Unified Container for Aligned Content */
        .aligned-container {
            margin-left: 20px; 
            width: 86%; /* Reduced from 95% to prevent cutoff */
        }

        .student-info-table { border-collapse: collapse; width: 100%; font-size: 14px; font-weight: bold; }
        .student-info-table td { padding: 4px 0; vertical-align: top; }
        .info-label { width: 140px; color: #334155; }
        .info-colon { width: 20px; text-align: center; } 
        .info-val { color: #000; text-transform: uppercase; }

        .photo-box {
            width: 110px;
            height: 130px;
            border: 2px solid #000;
            padding: 3px;
            margin-left: auto; 
        }
        .photo-img { width: 100%; height: 100%; display: block; object-fit: cover; }

        .marks-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
            border: 2px solid #000; 
        }
        .marks-table th, .marks-table td { border: 1px solid #000; padding: 10px 5px; text-align: center; font-size: 13px; }
        .marks-table th { background-color: #fff9c4; font-weight: bold; }
        .marks-table td { font-weight: bold; }

        .summary { margin-top: 15px; font-size: 12px; font-weight: bold; }
        
        .footer { margin-top: 50px; text-align: right; padding-right: 30px; }
    </style>
</head>
<body>
    <img src="' . $bg_src . '" class="bg-image">

    <div class="content">
        <div class="header-spacer"></div>

        <div class="aligned-container">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 75%; vertical-align: top;">
                        <table class="student-info-table">
                            <tr>
                                <td class="info-label">Student\'s Name</td>
                                <td class="info-colon">:</td>
                                <td class="info-val">' . htmlspecialchars($data['full_name']) . '</td>
                            </tr>
                            <tr>
                                <td class="info-label">Father\'s Name</td>
                                <td class="info-colon">:</td>
                                <td class="info-val">' . htmlspecialchars($data['father_name']) . '</td>
                            </tr>
                            <tr>
                                <td class="info-label">Mother\'s Name</td>
                                <td class="info-colon">:</td>
                                <td class="info-val">' . htmlspecialchars($data['mother_name']) . '</td>
                            </tr>
                            <tr>
                                <td class="info-label">Class/Course</td>
                                <td class="info-colon">:</td>
                                <td class="info-val">' . htmlspecialchars($data['course_name']) . '</td>
                            </tr>
                            <tr>
                                <td class="info-label">Session</td>
                                <td class="info-colon">:</td>
                                <td class="info-val">' . htmlspecialchars($data['session_name']) . '</td>
                            </tr>
                            <tr>
                                <td class="info-label">Enrollment No.</td>
                                <td class="info-colon">:</td>
                                <td class="info-val">' . htmlspecialchars($data['enrollment_no']) . '</td>
                            </tr>
                            <tr>
                                <td class="info-label">DOB</td>
                                <td class="info-colon">:</td>
                                <td class="info-val">' . $dob_formatted . '</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 25%; vertical-align: top;">
                        <div class="photo-box">
                            <img src="' . $photo_src . '" class="photo-img">
                        </div>
                    </td>
                </tr>
            </table>

            <table class="marks-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 10%;">SR. NO.</th>
                    <th rowspan="2" style="width: 35%;">SUBJECT</th>
                    <th colspan="5">ASSESSMENT OF ACADEMIC AREAS</th>
                    <th rowspan="2">ANNUAL RESULT</th>
                </tr>
                <tr>
                    <th>TOTAL MARKS</th>
                    <th>INTERNAL</th>
                    <th>THEORY</th>
                    <th>OBTAINED</th>
                    <th>GRADE</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td style="text-align: left; padding-left: 10px;">'.htmlspecialchars($data['subject_name']).'</td>
                    <td>'.$grand_total_max.'</td>
                    <td>'.$internal_marks.'</td>
                    <td>'.$theory_obtained.'</td>
                    <td>'.$grand_total_obtained.'</td>
                    <td>'.$grade.'</td>
                    <td>'.$status_result.'</td>
                </tr>';
                
                // Filler rows
                for($i=0; $i<4; $i++) {
                    $html .= '<tr>
                        <td>'.($i+2).'</td>
                        <td style="height: 25px;">-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>';
                }

// Output PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Build QR Data
$qr_content = "Name: " . $data['full_name'] . "\n";
$qr_content .= "Enrollment No: " . $data['enrollment_no'] . "\n";
$qr_content .= "Course: " . $data['course_name'] . "\n";
$qr_content .= "Session: " . $data['session_name'] . "\n";
$qr_content .= "Total Marks: " . $grand_total_obtained . "/" . $grand_total_max . "\n";
$qr_content .= "Result: " . $status_result;

$qr_result = (new Builder(
    writer: new PngWriter(),
    writerOptions: [],
    validateResult: false,
    data: $qr_content,
    encoding: new Encoding('UTF-8'),
    errorCorrectionLevel: ErrorCorrectionLevel::High,
    size: 100,
    margin: 0
))->build();

$qr_data_uri = $qr_result->getDataUri();

$html .= '       <tr style="background-color: none">
                    <td colspan="2" style="text-align: right; padding-right: 10px; font-weight: bold;">GRAND TOTAL</td>
                    <td>'.$grand_total_max.'</td>
                    <td>'.$internal_marks.'</td>
                    <td>'.$theory_obtained.'</td>
                    <td>'.$grand_total_obtained.'</td>
                    <td>-</td>
                    <td style="color: '.$status_color.';">'.$status_result.'</td>
                </tr>
            </tbody>
        </table>

        <div class="summary">
            PERCENTAGE: <span style="margin-right: 30px;">'.$percentage.'%</span>
            RESULT: <span>'.$status_result.'</span>
        </div>

        <div class="footer">
            <table style="width: 100%; border: none; margin-top: 30px;">
                <tr>
                    <td style="width: 50%; text-align: center; vertical-align: bottom;">
                        <img src="' . $qr_data_uri . '" style="width: 90px; height: 90px;">
                        <div style="font-size: 10px; margin-top: 5px; font-weight: bold;">Scan to Verify</div>
                    </td>
                    <td style="width: 50%; text-align: right; vertical-align: bottom;">
                        <div style="display: inline-block; text-align: center;">
                            <img src="' . $sign_src . '" style="height: 50px; display: block; margin: 0 auto;">
                            <div style="border-top: 1px solid #000; margin-top: 5px; font-weight: bold; font-size: 12px; padding-top: 2px;">AUTHORIZED SIGNATORY</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        </div> <!-- End aligned-container -->
    </div>
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Marksheet_" . $data['enrollment_no'] . ".pdf", ["Attachment" => 1]);
?>
