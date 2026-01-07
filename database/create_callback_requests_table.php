<?php
require_once __DIR__ . '/db-config.php';
$conn = getDbConnection();

$sql = "CREATE TABLE IF NOT EXISTS callback_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    schedule_from TIME,
    schedule_to TIME,
    course_id INT,
    page_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'callback_requests' created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
