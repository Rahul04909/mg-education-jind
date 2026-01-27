<?php
require_once __DIR__ . '/db-config.php';

$conn = getDbConnection();

// Create internships table
$sql_internships = "CREATE TABLE IF NOT EXISTS internships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    fees JSON,
    duration_value INT,
    duration_type VARCHAR(50),
    featured_image VARCHAR(255),
    meta_title VARCHAR(255),
    meta_desc TEXT,
    meta_keywords TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql_internships) === TRUE) {
    echo "Table 'internships' created successfully (or already exists).\n";
} else {
    echo "Error creating table 'internships': " . $conn->error . "\n";
}

// Create internship_reviews table
$sql_reviews = "CREATE TABLE IF NOT EXISTS internship_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    internship_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    status TINYINT(1) DEFAULT 0, -- 0: Pending, 1: Approved
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE
)";

if ($conn->query($sql_reviews) === TRUE) {
    echo "Table 'internship_reviews' created successfully (or already exists).\n";
} else {
    echo "Error creating table 'internship_reviews': " . $conn->error . "\n";
}

$conn->close();
?>
