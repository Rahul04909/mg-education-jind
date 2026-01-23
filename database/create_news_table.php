<?php
require_once __DIR__ . '/db-config.php';

function createNewsTable() {
    $conn = getDbConnection();

    $sql = "CREATE TABLE IF NOT EXISTS news (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        url VARCHAR(255) DEFAULT '#',
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table 'news' created successfully or already exists.";
    } else {
        echo "Error creating table: " . $conn->error;
    }

    $conn->close();
}

createNewsTable();
?>
