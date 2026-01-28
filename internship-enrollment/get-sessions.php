<?php
require_once __DIR__ . '/../database/db-config.php';
$conn = getDbConnection();

$internship_id = isset($_GET['internship_id']) ? intval($_GET['internship_id']) : 0;

if($internship_id > 0) {
    $sessions = [];
    $sql = "SELECT id, session_name FROM internship_sessions WHERE internship_id = $internship_id AND is_active = 1 ORDER BY id DESC";
    $result = $conn->query($sql);
    
    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $sessions[] = $row;
        }
    }
    
    echo json_encode(['status' => 'success', 'data' => $sessions]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Internship ID']);
}

$conn->close();
?>
