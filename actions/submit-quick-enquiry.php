<?php
require_once __DIR__ . '/../database/db-config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $conn = getDbConnection();

    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid data.']);
        exit;
    }

    $name = mysqli_real_escape_string($conn, trim($data['name']));
    $phone = mysqli_real_escape_string($conn, trim($data['phone']));
    $message = mysqli_real_escape_string($conn, trim($data['message']));
    $source = mysqli_real_escape_string($conn, trim($data['course_source'] ?? 'listing'));

    if (empty($name) || empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'Name and Phone are required.']);
        exit;
    }

    $sql = "INSERT INTO quick_enquiries (name, phone, message, course_source) VALUES ('$name', '$phone', '$message', '$source')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Enquiry submitted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
