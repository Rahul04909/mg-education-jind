<?php
require_once __DIR__ . '/../database/db-config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = getDbConnection();

    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $mobile = mysqli_real_escape_string($conn, trim($_POST['mobile']));
    $schedule_from = mysqli_real_escape_string($conn, trim($_POST['schedule_from']));
    $schedule_to = mysqli_real_escape_string($conn, trim($_POST['schedule_to']));
    $page_url = mysqli_real_escape_string($conn, trim($_POST['page_url']));
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : NULL;

    if (empty($full_name) || empty($mobile)) {
        echo json_encode(['status' => 'error', 'message' => 'Name and Mobile are required.']);
        exit;
    }

    $c_id_val = $course_id ? $course_id : "NULL";

    $sql = "INSERT INTO callback_requests (full_name, email, mobile, schedule_from, schedule_to, course_id, page_url) 
            VALUES ('$full_name', '$email', '$mobile', '$schedule_from', '$schedule_to', $c_id_val, '$page_url')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success', 'message' => 'Request submitted successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $conn->error]);
    }

    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
}
?>
