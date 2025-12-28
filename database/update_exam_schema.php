<?php
require_once __DIR__ . '/db-config.php';

$conn = getDbConnection();

// Create exam_schedules table
$sql_exams = "CREATE TABLE IF NOT EXISTS exam_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    exam_date DATE NOT NULL,
    start_time TIME NOT NULL,
    duration_minutes INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
)";

if ($conn->query($sql_exams) === TRUE) {
    // echo "Table exam_schedules created successfully or already exists.<br>";
} else {
    error_log("Error creating table exam_schedules: " . $conn->error);
}
?>
