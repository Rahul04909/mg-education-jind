<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/db-config.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($data['id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$id = $data['id'];
$status = $data['status'];

if ($status !== 'checked_out') {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

$conn = getDbConnection();

// Update status and set check_out_time
$stmt = $conn->prepare("UPDATE visitors SET status = ?, check_out_time = NOW() WHERE id = ?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
