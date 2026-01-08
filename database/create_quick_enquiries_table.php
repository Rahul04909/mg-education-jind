<?php
require_once __DIR__ . '/db-config.php';
$conn = getDbConnection();

// Create Table
$sql = "CREATE TABLE IF NOT EXISTS quick_enquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    message TEXT,
    course_source VARCHAR(50) DEFAULT 'listing',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'contacted', 'archived') DEFAULT 'pending'
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'quick_enquiries' created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

$conn->close();
?>
