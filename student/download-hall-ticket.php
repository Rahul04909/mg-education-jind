<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

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

if (!$student) die("Student not found.");

// 2. Center Logic
$center_name = "MG SKILLS & SOCIAL DEVELOPMENT ORGANIZATION";
if (!empty($student['center_id'])) {
    $cid = $student['center_id'];
    $center_res = $conn->query("SELECT center_name FROM centers WHERE id = $cid");
    if($center_res && $center_res->num_rows > 0){
        $c_data = $center_res->fetch_assoc();
        $center_name = $c_data['center_name'];
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

// 4. Prepare Images (Absolute Paths for DOMPDF)
// Use __DIR__ to get the exact path, replacing backslashes for consistency
$base_dir = str_replace('\\', '/', __DIR__); 

$bg_image = $base_dir . '/hall-ticket/background-hall-ticket.png';
// DB stores "assets/uploads/..." -> So we just need Root + DB Value
// Base dir is ".../student", so Root is ".../student/../" which is ".../"
// Actually better: dirname($base_dir) gives root
$root_dir = dirname($base_dir);
$photo_path = $root_dir . '/' . $student['student_photo']; 
$sign_path = $root_dir . '/' . $student['student_sign'];

// Helper to encode image to Base64 (Most reliable for DOMPDF)
function get_image_base64($path) {
    if (!file_exists($path)) {
        return ''; // File missing
    }
    
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    
    if ($data === false) {
        return ''; // Read failed
    }

    return 'data:image/' . $type . ';base64,' . base64_encode($data);
}

$bg_src = get_image_base64($bg_image);
$photo_src = get_image_base64($photo_path);
$sign_src = get_image_base64($sign_path);

// Prepare Photo HTML
$photo_html = '<br>No Photo<br>';
if (!empty($photo_src)) {
    $photo_html = '<img src="' . $photo_src . '" class="photo-img">';
}

// Prepare Signature HTML
$sign_html = 'Sign';
if (!empty($sign_src)) {
    $sign_html = '<img src="' . $sign_src . '" class="sign-img">';
}

// 5. Generate HTML
$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        @page { margin: 0px; }
        body { margin: 0px; font-family: sans-serif; }
        
        .bg-image {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .content { padding: 40px; position: relative; z-index: 10; }
        
        .header { text-align: center; margin-top: 250px; margin-bottom: 20px; }
        .header h1 { color: #b91c1c; font-size: 24px; text-transform: uppercase; margin: 0; }
        .header h2 { font-size: 14px; margin: 5px 0 0 0; color: #333; }

        .title-strip {
            background-color: #b91c1c; color: #fff; text-align: center;
            padding: 5px; font-weight: bold; font-size: 18px;
            margin-bottom: 20px; text-transform: uppercase;
        }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { border: 1px solid #000; padding: 6px 10px; font-size: 12px; }
        .label { font-weight: bold; width: 140px; background-color: #fce4e4; }

        .photo-box {
            width: 120px; height: 150px; border: 2px solid #000;
            margin-left: auto; text-align: center; position: relative;
            overflow: hidden; /* Ensure image does not overflow */
        }
        /* DOMPDF does not support object-fit. We use width/height constraints. */
        .photo-img { width: 100%; height: 120px; display: block; } 
        
        .sign-box { height: 30px; border-top: 1px solid #000; }
        .sign-img { max-height: 25px; max-width: 100%; margin-top: 2px; }

        .exam-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .exam-table th { background-color: #333; color: #fff; border: 1px solid #000; padding: 8px; font-size: 12px; }
        .exam-table td { border: 1px solid #000; padding: 8px; font-size: 11px; text-align: center; }

        .footer { margin-top: 60px; text-align: right; padding-right: 30px; }
        .auth-sign p { border-top: 1px solid #000; display: inline-block; padding-top: 5px; font-weight: bold; font-size: 12px; }
    </style>
</head>
<body>
    <img src="' . $bg_src . '" class="bg-image">
    
    <div class="content">
        <div class="header">
            <h1>'.$center_name.'</h1>
                <h2>(An ISO 9001:2015 Certified Organization)</h2>
            </div>
            
            <div class="title-strip">Hall Ticket</div>

            <table style="width: 100%;">
                <tr>
                    <td style="width: 70%; vertical-align: top;">
                        <table class="info-table">
                            <tr><td class="label">Student Name</td><td>'.htmlspecialchars($student['full_name']).'</td></tr>
                            <tr><td class="label">Enrollment No</td><td>'.htmlspecialchars($student['enrollment_no']).'</td></tr>
                             <tr><td class="label">Course Name</td><td>'.htmlspecialchars($student['course_name']).'</td></tr>
                            <tr><td class="label">Father\'s Name</td><td>'.htmlspecialchars($student['father_name']).'</td></tr>
                            <tr><td class="label">Mother\'s Name</td><td>'.htmlspecialchars($student['mother_name']).'</td></tr>
                            <tr><td class="label">Date of Birth</td><td>'.htmlspecialchars($student['dob']).'</td></tr>
                            <tr><td class="label">Mobile No</td><td>'.htmlspecialchars($student['mobile']).'</td></tr>
                        </table>
                    </td>
                    <td style="width: 30%; vertical-align: top; padding-left: 10px;">
                        <div class="photo-box">
                            ' . $photo_html . '
                            <div class="sign-box">
                                ' . $sign_html . '
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <h3 style="font-size: 14px; border-bottom: 2px solid #000; padding-bottom: 5px; margin-top: 10px;">EXAM SCHEDULE</h3>
            <table class="exam-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">Sr. No</th>
                        <th>Subject Name</th>
                        <th>Exam Date</th>
                        <th>Timing</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>';
                
                if (empty($exams)) {
                    $html .= '<tr><td colspan="5">No Exam Schedule Available</td></tr>';
                } else {
                    foreach ($exams as $i => $ex) {
                        $start = date('h:i A', strtotime($ex['start_time']));
                        $end = date('h:i A', strtotime($ex['start_time']) + ($ex['duration_minutes']*60));
                        $html .= '<tr>
                            <td>'.($i+1).'</td>
                            <td>'.htmlspecialchars($ex['subject_name']).'</td>
                            <td>'.date('d-m-Y', strtotime($ex['exam_date'])).'</td>
                            <td>'.$start.' - '.$end.'</td>
                            <td>'.$ex['duration_minutes'].' Mins</td>
                        </tr>';
                    }
                }

$html .= '      </tbody>
            </table>

            <div class="footer">
                <div class="auth-sign">
                    <br><br>
                    <p>Authorized Signatory</p>
                </div>
            </div>
    </div>
</body>
</html>';

// 6. Output PDF
$options = new Options();
$options->set('isRemoteEnabled', true); // Needed for images if using URLs, but we utilize base64/absolute for safety
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Hall_Ticket_" . $student['enrollment_no'] . ".pdf", ["Attachment" => 0]);
?>
