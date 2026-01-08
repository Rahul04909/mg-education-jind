<?php
require_once __DIR__ . '/../database/db-config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $conn = getDbConnection();

    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid data.']);
        exit;
    }

    $id = intval($data['id']);
    $status = mysqli_real_escape_string($conn, $data['status']);
    $note = mysqli_real_escape_string($conn, $data['response_note']);

    // Update query
    $sql = "UPDATE quick_enquiries SET status = '$status', response_note = '$note' WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
