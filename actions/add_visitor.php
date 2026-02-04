<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/db-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$purpose = $_POST['purpose'] ?? '';
$meet_whom = $_POST['meet_whom'] ?? '';
$email = $_POST['email'] ?? '';

if (empty($name) || empty($phone) || empty($purpose)) {
    echo json_encode(['success' => false, 'message' => 'Name, Phone and Purpose are required']);
    exit;
}

$conn = getDbConnection();

$stmt = $conn->prepare("INSERT INTO visitors (name, phone, email, purpose, meet_whom) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $phone, $email, $purpose, $meet_whom);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Visitor added successfully', 'id' => $conn->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
