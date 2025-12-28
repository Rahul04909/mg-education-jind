<?php
require_once __DIR__ . '/db-config.php';

$conn = getDbConnection();

// Create subjects table
$sql_subjects = "CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(100) DEFAULT NULL,
    theory_marks INT DEFAULT 0,
    assignment_marks INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
)";

if ($conn->query($sql_subjects) === TRUE) {
    // echo "Table subjects created successfully or already exists.<br>";
} else {
    error_log("Error creating table subjects: " . $conn->error);
}

// Create syllabus table
$sql_syllabus = "CREATE TABLE IF NOT EXISTS syllabus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    unit_title VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
)";

if ($conn->query($sql_syllabus) === TRUE) {
    // echo "Table syllabus created successfully or already exists.<br>";
} else {
    error_log("Error creating table syllabus: " . $conn->error);
}

// We don't close the connection here if we want to use it in the calling script, 
// but usually schema updates are standalone or required once.
// $conn->close();
?>
