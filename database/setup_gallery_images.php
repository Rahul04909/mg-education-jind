<?php
require_once __DIR__ . '/db-config.php';

$conn = getDbConnection();

// Create gallery_images table
$sql = "CREATE TABLE IF NOT EXISTS gallery_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title VARCHAR(255) NULL,
    image_path VARCHAR(255) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES gallery_categories(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'gallery_images' created successfully (or already exists).\n";
} else {
    echo "Error creating table 'gallery_images': " . $conn->error . "\n";
}

$conn->close();
?>
