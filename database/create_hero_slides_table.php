<?php
require_once __DIR__ . '/db-config.php';

function createHeroSlidesTable() {
    $conn = getDbConnection();

    $sql = "CREATE TABLE IF NOT EXISTS hero_slides (
        id INT AUTO_INCREMENT PRIMARY KEY,
        image_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table 'hero_slides' created successfully or already exists.";
    } else {
        echo "Error creating table: " . $conn->error;
    }

    $conn->close();
}

createHeroSlidesTable();
?>
