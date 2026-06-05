<?php
require_once __DIR__ . '/db-config.php';

$conn = getDbConnection();

// Create internship_syllabus table
$sql_syllabus = "CREATE TABLE IF NOT EXISTS internship_syllabus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    internship_id INT NOT NULL,
    unit_title VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE
)";

if ($conn->query($sql_syllabus) === TRUE) {
    // Table created successfully or already exists
} else {
    error_log("Error creating table internship_syllabus: " . $conn->error);
}
?>
