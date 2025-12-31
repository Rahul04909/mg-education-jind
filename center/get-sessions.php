<?php
require_once __DIR__ . '/../database/db-config.php';

header('Content-Type: application/json');

if (!isset($_GET['course_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Course ID required']);
    exit;
}

$course_id = intval($_GET['course_id']);
$conn = getDbConnection();

$sql = "SELECT id, session_name FROM course_sessions WHERE course_id = $course_id AND is_active = 1 ORDER BY id DESC";
$result = $conn->query($sql);

$sessions = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $sessions[] = $row;
    }
}

echo json_encode(['status' => 'success', 'data' => $sessions]);
$conn->close();
?>
