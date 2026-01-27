<?php
require_once __DIR__ . '/db-config.php';

$conn = getDbConnection();

// Create gallery_categories table
$sql = "CREATE TABLE IF NOT EXISTS gallery_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'gallery_categories' created successfully (or already exists).\n";
} else {
    echo "Error creating table 'gallery_categories': " . $conn->error . "\n";
}

$conn->close();
?>
