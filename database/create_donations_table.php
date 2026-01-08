<?php
require_once __DIR__ . '/db-config.php';

function createDonationsTable() {
    $conn = getDbConnection();

    $sql = "CREATE TABLE IF NOT EXISTS donations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        mobile VARCHAR(20) NOT NULL,
        pan_card VARCHAR(20),
        address TEXT,
        amount DECIMAL(10, 2) NOT NULL,
        purpose VARCHAR(255) DEFAULT 'General Donation',
        payment_id VARCHAR(100),
        order_id VARCHAR(100),
        status VARCHAR(50) DEFAULT 'pending',
        receipt_no VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table 'donations' created successfully or already exists.";
    } else {
        echo "Error creating table: " . $conn->error;
    }

    $conn->close();
}

createDonationsTable();
?>
