<?php
require_once __DIR__ . '/db-config.php';

function createUniversitiesTable() {
    $conn = getDbConnection();

    $sql = "CREATE TABLE IF NOT EXISTS universities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        logo_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table 'universities' created successfully or already exists.";
    } else {
        echo "Error creating table: " . $conn->error;
    }

    $conn->close();
}

createUniversitiesTable();
?>
