<?php
require_once __DIR__ . '/db-config.php';

function createDonationEnquiryTable() {
    $conn = getDbConnection();

    $sql = "CREATE TABLE IF NOT EXISTS donation_enquiries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(100) NOT NULL,
        purpose VARCHAR(100) NOT NULL,
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table 'donation_enquiries' created successfully or already exists.";
    } else {
        echo "Error creating table: " . $conn->error;
    }

    $conn->close();
}

createDonationEnquiryTable();
?>
