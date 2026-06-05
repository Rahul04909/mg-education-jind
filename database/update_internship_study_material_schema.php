<?php
require_once __DIR__ . '/db-config.php';
$conn = getDbConnection();

// Create internship_study_material table
$sql = "CREATE TABLE IF NOT EXISTS internship_study_material (
    id INT AUTO_INCREMENT PRIMARY KEY,
    internship_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'internship_study_material' checked/created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
    error_log("Error creating table internship_study_material: " . $conn->error);
}
?>
