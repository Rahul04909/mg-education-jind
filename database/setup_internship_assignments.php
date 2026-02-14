<?php
require_once 'db-config.php';
$conn = getDbConnection();

$sql = "CREATE TABLE IF NOT EXISTS internship_assignments (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    internship_id INT(11) UNSIGNED NOT NULL,
    session_id INT(11) UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    assignment_file VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES internship_sessions(id) ON DELETE SET NULL,
    INDEX (internship_id),
    INDEX (session_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table internship_assignments created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
