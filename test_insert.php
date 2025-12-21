<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'database/db-config.php';

$conn = getDbConnection();

$enrollment_no = "TEST" . time();
$password = password_hash("password", PASSWORD_BCRYPT);
$course_id = 1; // Assuming course ID 1 exists

// Minimal fields based on my understanding
$sql = "INSERT INTO admissions (
    enrollment_no, password, course_id, full_name, father_name, mother_name, dob, category, admission_mode,
    student_photo, student_sign, mobile, alt_mobile, email,
    pincode, country, state, city, address,
    highest_qual, school_name, board_university, passing_year, percentage,
    computer_knowledge, typing_speed, prev_course_done,
    aadhar_no, aadhar_file, edu_cert_file,
    course_fee, payment_status
) VALUES (
    '$enrollment_no', '$password', $course_id, 'Test User', 'Test Father', 'Test Mother', '2000-01-01', 'General', 'Online',
    'path/to/photo.jpg', 'path/to/sign.jpg', '9999999999', '', 'test@example.com',
    '110001', 'India', 'Delhi', 'New Delhi', 'Test Address',
    '12th', 'Test School', 'CBSE', 2018, '90%',
    'Beginner', '30wpm', 0,
    '123456789012', 'path/to/aadhar.jpg', 'path/to/cert.jpg',
    0, 'success'
)";

echo "Attempting INSERT...\n";
if ($conn->query($sql) === TRUE) {
    echo "SUCCESS: Inserted student with Enrollment No: $enrollment_no\n";
} else {
    echo "FAILURE: " . $conn->error . "\n";
}
$conn->close();
?>
