<?php
require_once __DIR__ . '/../database/db-config.php';
require_once __DIR__ . '/../database/update_admission_schema.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Razorpay\Api\Api;

header('Content-Type: application/json');

$conn = getDbConnection();

// Helpers
function uploadFile($file, $dir) {
    if (!isset($file['name']) || $file['error'] != 0) return null;
    $target_dir = "../assets/uploads/students/" . $dir . "/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = AuthGenEnroll() . "_" . uniqid() . "." . $ext; // Use tmp enroll prefix
    $target_file = $target_dir . $filename;
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return "assets/uploads/students/" . $dir . "/" . $filename;
    }
    return null;
}

function AuthGenEnroll() {
    return "MG" . date("Y") . rand(1000,9999); // Temp generator
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Verify Payment Signature (Critical)
    // Fetch Keys
    $k_sql = "SELECT * FROM razorpay_settings WHERE is_active = 1 LIMIT 1";
    $k_res = $conn->query($k_sql);
    if ($k_res->num_rows == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Payment Gateway Error']); exit;
    }
    $keys = $k_res->fetch_assoc();
    $api = new Api($keys['razorpay_key_id'], $keys['razorpay_key_secret']);
    
    $payment_id = $_POST['razorpay_payment_id'];
    $order_id = $_POST['razorpay_order_id'];
    $signature = $_POST['razorpay_signature'];
    
    $success = true;
    try {
        $attributes = [
            'razorpay_order_id' => $order_id,
            'razorpay_payment_id' => $payment_id,
            'razorpay_signature' => $signature
        ];
        $api->utility->verifyPaymentSignature($attributes);
    } catch(Exception $e) {
        $success = false;
        echo json_encode(['status' => 'error', 'message' => 'Payment Verification Failed']); exit;
    }

    // Generate Enrollment Number: MGYYYYXXXX
    $year = date("Y");
    $last_sql = "SELECT enrollment_no FROM admissions WHERE enrollment_no LIKE 'MG{$year}%' ORDER BY id DESC LIMIT 1";
    $last_res = $conn->query($last_sql);
    $seq = 1;
    if ($last_res->num_rows > 0) {
        $last_row = $last_res->fetch_assoc();
        $last_seq = intval(substr($last_row['enrollment_no'], -4));
        $seq = $last_seq + 1;
    }
    $enrollment_no = "MG" . $year . str_pad($seq, 4, "0", STR_PAD_LEFT);

    // Inputs
    $course_id = intval($_POST['course_id']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $father_name = mysqli_real_escape_string($conn, $_POST['father_name']);
    $mother_name = mysqli_real_escape_string($conn, $_POST['mother_name']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $admission_mode = isset($_POST['admission_mode']) ? mysqli_real_escape_string($conn, $_POST['admission_mode']) : 'Online';
    
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $alt_mobile = mysqli_real_escape_string($conn, $_POST['alt_mobile']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    $highest_qual = mysqli_real_escape_string($conn, $_POST['highest_qual']);
    $school_name = mysqli_real_escape_string($conn, $_POST['school_name']);
    $board = mysqli_real_escape_string($conn, $_POST['board']);
    $passing_year = intval($_POST['passing_year']);
    $percentage = mysqli_real_escape_string($conn, $_POST['percentage']);
    
    $computer_knowledge = mysqli_real_escape_string($conn, $_POST['computer_knowledge']);
    $typing_speed = mysqli_real_escape_string($conn, $_POST['typing_speed']);
    
    $prev_course_done = isset($_POST['prev_course_done']) && $_POST['prev_course_done'] == 'yes' ? 1 : 0;
    $prev_enroll_no = mysqli_real_escape_string($conn, $_POST['prev_enroll_no']);
    $prev_course_name = mysqli_real_escape_string($conn, $_POST['prev_course_name']);
    $prev_course_session = mysqli_real_escape_string($conn, $_POST['prev_course_session']);
    
    $aadhar_no = mysqli_real_escape_string($conn, $_POST['aadhar_no']);
    $course_fee = floatval($_POST['course_fee']);
    
    // Uploads
    $photo_path = uploadFile($_FILES['student_photo'], 'photos');
    $sign_path = uploadFile($_FILES['student_sign'], 'signatures');
    $aadhar_path = uploadFile($_FILES['aadhar_file'], 'documents');
    $cert_path = uploadFile($_FILES['edu_cert_file'], 'documents');
    
    $sql = "INSERT INTO admissions (
        enrollment_no, course_id, full_name, father_name, mother_name, dob, category, admission_mode,
        student_photo, student_sign, mobile, alt_mobile, email,
        pincode, country, state, city, address,
        highest_qual, school_name, board_university, passing_year, percentage,
        computer_knowledge, typing_speed, prev_course_done, prev_enroll_no, prev_course_name, prev_course_session,
        aadhar_no, aadhar_file, edu_cert_file,
        course_fee, payment_status, razorpay_payment_id, razorpay_order_id
    ) VALUES (
        '$enrollment_no', $course_id, '$full_name', '$father_name', '$mother_name', '$dob', '$category', '$admission_mode',
        '$photo_path', '$sign_path', '$mobile', '$alt_mobile', '$email',
        '$pincode', '$country', '$state', '$city', '$address',
        '$highest_qual', '$school_name', '$board', $passing_year, '$percentage',
        '$computer_knowledge', '$typing_speed', $prev_course_done, '$prev_enroll_no', '$prev_course_name', '$prev_course_session',
        '$aadhar_no', '$aadhar_path', '$cert_path',
        $course_fee, 'success', '$payment_id', '$order_id'
    )";
    
    if ($conn->query($sql) === TRUE) {
        
        // Send Email
        // Fetch SMTP
        $smtp_sql = "SELECT * FROM smtp_settings WHERE id = 1 AND is_active = 1";
        $smtp_res = $conn->query($smtp_sql);
        if ($smtp_res->num_rows > 0) {
            $smtp = $smtp_res->fetch_assoc();
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = $smtp['smtp_host'];
                $mail->SMTPAuth = true;
                $mail->Username = $smtp['smtp_username'];
                $mail->Password = $smtp['smtp_password'];
                $mail->SMTPSecure = $smtp['smtp_encryption'];
                $mail->Port = $smtp['smtp_port'];

                $mail->setFrom($smtp['from_email'], $smtp['from_name']);
                $mail->addAddress($email, $full_name);

                $mail->isHTML(true);
                $mail->Subject = "Admission Confirmed - MG Skills";
                $mail->Body = "
                    <h2>Admission Successful!</h2>
                    <p>Dear $full_name,</p>
                    <p>Your admission has been confirmed. Welcome to MG Education & Social Development Organization.</p>
                    <p><strong>Enrollment Number:</strong> $enrollment_no<br>
                    <strong>Payment ID:</strong> $payment_id<br>
                    <strong>Fees Paid:</strong> ₹$course_fee</p>
                    <p>Please keep this enrollment number for future reference.</p>
                    <br>
                    <p>Best Regards,<br>MG Skills Team</p>
                ";
                $mail->send();
            } catch (Exception $e) {}
        }
        
        echo json_encode(['status' => 'success', 'enrollment_no' => $enrollment_no]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $conn->error]);
    }
}
$conn->close();
?>
