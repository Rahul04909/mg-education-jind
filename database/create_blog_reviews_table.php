<?php
require_once __DIR__ . '/../database/db-config.php';
$conn = getDbConnection();

$sql = "CREATE TABLE IF NOT EXISTS blog_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    rating INT NOT NULL,
    review TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'blog_reviews' created successfully.";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
