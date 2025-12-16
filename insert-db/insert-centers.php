<?php
require_once __DIR__ . '/../database/db-config.php';

$conn = getDbConnection();

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create centers table
$sql = "CREATE TABLE IF NOT EXISTS centers (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Basic Details
    center_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    owner_name VARCHAR(255) NOT NULL,
    
    -- Location Details
    country VARCHAR(100) DEFAULT 'India',
    state VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    pincode VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    
    -- Infrastructure
    num_classrooms INT DEFAULT 0,
    num_computers INT DEFAULT 0,
    has_internet BOOLEAN DEFAULT 0,
    has_power_backup BOOLEAN DEFAULT 0,
    lab_type ENUM('Basic', 'Advanced') DEFAULT 'Basic',
    working_hours_from TIME,
    working_hours_to TIME,
    total_staff INT DEFAULT 0,
    weekend_off JSON,
    
    -- Legal & Docs
    legal_documents JSON,
    
    -- Franchise & Royalty
    franchise_fee DECIMAL(10, 2) DEFAULT 0.00,
    royalty_percentage DECIMAL(5, 2) DEFAULT 0.00,
    
    -- Media
    social_links JSON,
    center_logo VARCHAR(255),
    owner_image VARCHAR(255),
    authorized_signatory VARCHAR(255),
    digital_stamp VARCHAR(255),
    
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'centers' created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

$conn->close();
?>
