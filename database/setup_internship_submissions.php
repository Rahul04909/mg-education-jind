<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db-config.php';
$conn = getDbConnection();

$sql = "CREATE TABLE IF NOT EXISTS internship_submissions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT(11) UNSIGNED NOT NULL,
    student_id INT(11) NOT NULL,
    submission_file VARCHAR(255) NOT NULL,
    comments TEXT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Submitted', 'Graded', 'Resubmit') DEFAULT 'Submitted',
    marks INT(11) NULL,
    feedback TEXT NULL,
    FOREIGN KEY (assignment_id) REFERENCES internship_assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES internship_enrollments(id) ON DELETE CASCADE,
    INDEX (assignment_id),
    INDEX (student_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table internship_submissions created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
