<?php
require_once 'db-config.php';
$conn = getDbConnection();

$sql = "CREATE TABLE IF NOT EXISTS student_transactions (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enrollment_no VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_mode VARCHAR(50) NOT NULL,
    status VARCHAR(20) DEFAULT 'success',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    remarks TEXT,
    INDEX (enrollment_no)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table student_transactions created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
