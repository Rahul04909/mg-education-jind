<?php
require_once __DIR__ . '/db-config.php';
$conn = getDbConnection();

$sql = "CREATE TABLE IF NOT EXISTS volunteers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    dob DATE,
    gender VARCHAR(20),
    
    pincode VARCHAR(10),
    country VARCHAR(50),
    state VARCHAR(50),
    city VARCHAR(50),
    address TEXT,
    
    role VARCHAR(100),
    hours_per_week INT,
    weekdays TEXT, -- JSON or Comma separated
    preferred_mode VARCHAR(50),
    
    aadhar_no VARCHAR(20),
    aadhar_file VARCHAR(255),
    photo_file VARCHAR(255),
    resume_file VARCHAR(255),
    
    is_student BOOLEAN DEFAULT 0,
    student_id_no VARCHAR(50),
    student_id_file VARCHAR(255),
    
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'volunteers' created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
