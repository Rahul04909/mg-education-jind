<?php
session_start();
require_once __DIR__ . '/../../database/db-config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

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
               sub.name as subject_name, c.title as course_name, cs.session_name,
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
$bg_image_path = $base_dir . '/legal-marksheet-background.png';
$sign_image_path = $base_dir . '/mg-sign.png';

// Student photo is in root/assets/uploads... so we go up 2 levels from student/marksheet to get to root
$root_dir = dirname(dirname($base_dir)); 
$photo_path = (!empty($data['student_photo'])) ? $root_dir . '/' . $data['student_photo'] : $root_dir . '/assets/images/avatar-placeholder.png';

$bg_src = get_image_base64($bg_image_path);
$sign_src = get_image_base64($sign_image_path);
$photo_src = get_image_base64($photo_path);

// Determine Pass/Fail Color
$status_color = ($data['status'] == 'PASS') ? 'green' : 'red';

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
            padding: 0mm 20mm 15mm -20mm; /* Increased side padding */
            width: 100%;
            box-sizing: border-box;
            position: relative; 
            z-index: 10;
        }

        .header-spacer { height: 300px; } /* Increased height to move content down */
        
        .student-info { width: 800px; margin-left: 40px; margin-bottom: 20px; }
        .info-table { border-collapse: collapse; width: 100%; font-size: 14px; font-weight: bold; }
        .info-table td { padding: 4px 0; vertical-align: top; }
        .info-label { width: 130px; color: #334155; }
        .info-val { color: #000; text-transform: uppercase; }

        .photo-box {
            width: 100px;
            height: 120px;
            border: 2px solid #000;
            padding: 3px;
            float: right;
        }
        .photo-img { width: 100%; height: 100%; display: block; }

        .marks-table { width: 800px; border-collapse: collapse; margin-left: 40px; margin-top: 10px; border: 2px solid #000; }
        .marks-table th, .marks-table td { border: 1px solid #000; padding: 8px; text-align: center; font-size: 12px; }
        .marks-table th { background-color: #fef9c3; font-weight: bold; }
        .marks-table td { font-weight: bold; }

        .summary { margin-top: 15px; font-size: 12px; font-weight: bold; }
        
        .footer { margin-top: 50px; text-align: right; padding-right: 30px; }
    </style>
</head>
<body>
    <img src="' . $bg_src . '" class="bg-image">

    <div class="content">
        <div class="header-spacer"></div>

        <table style="width: 100%;">
            <tr>
                <td style="width: 75%;">
                    <table class="info-table">
                        <tr><td class="info-label">Student\'s Name</td><td>: <span class="info-val">'.htmlspecialchars($data['full_name']).'</span></td></tr>
                        <tr><td class="info-label">Father\'s Name</td><td>: <span class="info-val">'.htmlspecialchars($data['father_name']).'</span></td></tr>
                        <tr><td class="info-label">Mother\'s Name</td><td>: <span class="info-val">'.htmlspecialchars($data['mother_name']).'</span></td></tr>
                        <tr><td class="info-label">Class/Course</td><td>: <span class="info-val">'.htmlspecialchars($data['course_name']).'</span></td></tr>
                        <tr><td class="info-label">Session</td><td>: <span class="info-val">'.htmlspecialchars($data['session_name']).'</span></td></tr>
                        <tr><td class="info-label">Enrollment No.</td><td>: <span class="info-val">'.htmlspecialchars($data['enrollment_no']).'</span></td></tr>
                        <tr><td class="info-label">DOB</td><td>: <span class="info-val">'.$dob_formatted.'</span></td></tr>
                    </table>
                </td>
                <td style="width: 25%; vertical-align: top;">
                    <div class="photo-box">
                        <img src="'.$photo_src.'" class="photo-img">
                    </div>
                </td>
            </tr>
        </table>

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
                <tr>
                    <td style="text-align: left; padding-left: 10px;">'.htmlspecialchars($data['subject_name']).'</td>
                    <td>'.$data['total_marks'].'</td>
                    <td>'.$data['obtained_marks'].'</td>
                    <td>'.$grade.'</td>
                    <td>'.($grade === 'FAIL' ? 'FAIL' : 'PASS').'</td>
                </tr>';
                
                // Filler rows
                for($i=0; $i<4; $i++) {
                    $html .= '<tr>
                        <td style="height: 25px;">-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>';
                }

$html .= '       <tr style="background-color: #f0f9ff;">
                    <td style="text-align: right; padding-right: 10px; font-weight: bold;">GRAND TOTAL</td>
                    <td>'.$data['total_marks'].'</td>
                    <td>'.$data['obtained_marks'].'</td>
                    <td>-</td>
                    <td style="color: '.$status_color.';">'.$data['status'].'</td>
                </tr>
            </tbody>
        </table>

        <div class="summary">
            PERCENTAGE: <span style="margin-right: 30px;">'.$data['percentage'].'%</span>
            RESULT: <span>'.$data['status'].'</span>
        </div>

        <div class="footer">
            <div style="text-align: center; display: inline-block;">
                <img src="'.$sign_src.'" style="height: 50px; display: block; margin: 0 auto;">
                <div style="border-top: 1px solid #000; margin-top: 5px; font-weight: bold; font-size: 12px; padding-top: 2px;">AUTHORIZED SIGNATORY</div>
            </div>
        </div>

    </div>
</body>
</html>';

// Output PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Marksheet_" . $data['enrollment_no'] . ".pdf", ["Attachment" => 1]);
?>
