<?php
require_once __DIR__ . '/db-config.php';

$conn = getDbConnection();

// Create student_assignments table
$sql_student_assignments = "CREATE TABLE IF NOT EXISTS student_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    submission_file VARCHAR(255) NOT NULL,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    marks_obtained INT DEFAULT NULL,
    status ENUM('SUBMITTED', 'GRADED') DEFAULT 'SUBMITTED',
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES admissions(id) ON DELETE CASCADE
)";

if ($conn->query($sql_student_assignments) === TRUE) {
    // echo "Table student_assignments created successfully or already exists.<br>";
} else {
    error_log("Error creating table student_assignments: " . $conn->error);
}
?>
