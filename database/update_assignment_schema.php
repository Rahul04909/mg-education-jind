<?php
require_once __DIR__ . '/db-config.php';

$conn = getDbConnection();

// Create assignments table
$sql_assignments = "CREATE TABLE IF NOT EXISTS assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    session_id INT NOT NULL DEFAULT 0,
    title VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    last_date DATE NOT NULL,
    pdf_file VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
)";

if ($conn->query($sql_assignments) === TRUE) {
    // echo "Table assignments created successfully or already exists.<br>";
} else {
    error_log("Error creating table assignments: " . $conn->error);
}
?>
