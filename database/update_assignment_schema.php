<?php
require_once __DIR__ . '/db-config.php';

$conn = getDbConnection();

// Create assignments table
$sql_assignments = "CREATE TABLE IF NOT EXISTS assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    session_id INT NOT NULL DEFAULT 0,
    subject_id INT NOT NULL DEFAULT 0,
    title VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    last_date DATE NOT NULL,
    pdf_file VARCHAR(255) NOT NULL,
    total_marks INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
)";

if ($conn->query($sql_assignments) === TRUE) {
    // Check if subject_id column exists
    $check_col = $conn->query("SHOW COLUMNS FROM assignments LIKE 'subject_id'");
    if ($check_col->num_rows == 0) {
        $conn->query("ALTER TABLE assignments ADD COLUMN subject_id INT NOT NULL DEFAULT 0 AFTER session_id");
    }
    
    // Check if total_marks column exists
    $check_col_marks = $conn->query("SHOW COLUMNS FROM assignments LIKE 'total_marks'");
    if ($check_col_marks->num_rows == 0) {
        $conn->query("ALTER TABLE assignments ADD COLUMN total_marks INT NOT NULL DEFAULT 0 AFTER pdf_file");
    }
} else {
    error_log("Error creating table assignments: " . $conn->error);
}
?>
