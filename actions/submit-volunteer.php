<?php
require_once __DIR__ . '/../database/db-config.php';

header('Content-Type: application/json');

function uploadFile($file, $sub_dir) {
    if (!isset($file['name']) || $file['error'] != 0) return null;
    $target_dir = "../assets/uploads/volunteers/" . $sub_dir . "/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . "_" . time() . "." . $ext;
    $target_file = $target_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return "assets/uploads/volunteers/" . $sub_dir . "/" . $filename;
    }
    return null;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = getDbConnection();

    // Inputs
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $mobile = mysqli_real_escape_string($conn, trim($_POST['mobile']));
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $hours_per_week = intval($_POST['hours_per_week']);
    $weekdays = isset($_POST['weekdays']) ? json_encode($_POST['weekdays']) : '[]';
    $preferred_mode = mysqli_real_escape_string($conn, $_POST['preferred_mode']);
    
    $aadhar_no = mysqli_real_escape_string($conn, $_POST['aadhar_no']);
    
    $is_student = isset($_POST['is_student']) ? 1 : 0;
    $student_id_no = $is_student ? mysqli_real_escape_string($conn, $_POST['student_id_no']) : NULL;
    
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // Uploads
    $photo_path = uploadFile($_FILES['photo_file'], 'photos');
    $resume_path = uploadFile($_FILES['resume_file'], 'resumes');
    $aadhar_path = uploadFile($_FILES['aadhar_file'], 'documents');
    $student_id_path = $is_student ? uploadFile($_FILES['student_id_file'], 'documents') : NULL;

    // Validation (Basic)
    if (empty($full_name) || empty($email) || empty($mobile)) {
        echo json_encode(['status' => 'error', 'message' => 'Required fields missing.']);
        exit;
    }

    $weekdays_esc = mysqli_real_escape_string($conn, $weekdays);

    $sql = "INSERT INTO volunteers (
        full_name, email, mobile, dob, gender,
        pincode, country, state, city, address,
        role, hours_per_week, weekdays, preferred_mode,
        aadhar_no, aadhar_file, photo_file, resume_file,
        is_student, student_id_no, student_id_file,
        message
    ) VALUES (
        '$full_name', '$email', '$mobile', '$dob', '$gender',
        '$pincode', '$country', '$state', '$city', '$address',
        '$role', $hours_per_week, '$weekdays_esc', '$preferred_mode',
        '$aadhar_no', '$aadhar_path', '$photo_path', '$resume_path',
        $is_student, '$student_id_no', '$student_id_path',
        '$message'
    )";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success', 'message' => 'Registration successful! We will contact you soon.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $conn->error]);
    }

    $conn->close();
}
?>
