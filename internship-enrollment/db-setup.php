<?php
require_once __DIR__ . '/../database/db-config.php';

function createInternshipEnrollmentTable() {
    $conn = getDbConnection();
    
    $sql = "CREATE TABLE IF NOT EXISTS internship_enrollments (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        enrollment_no VARCHAR(20) UNIQUE,
        internship_id INT(11) NOT NULL,
        session_id INT(11) DEFAULT NULL,
        
        -- Basic Details
        full_name VARCHAR(255) NOT NULL,
        father_name VARCHAR(255) NOT NULL,
        mother_name VARCHAR(255) NOT NULL,
        dob DATE NOT NULL,
        category VARCHAR(50) NOT NULL,
        admission_mode VARCHAR(20) DEFAULT 'Online',
        student_photo VARCHAR(255),
        student_sign VARCHAR(255),
        
        -- Contact
        mobile VARCHAR(15) NOT NULL,
        alt_mobile VARCHAR(15),
        email VARCHAR(255) NOT NULL,
        
        -- Address
        pincode VARCHAR(10) NOT NULL,
        country VARCHAR(100) NOT NULL,
        state VARCHAR(100) NOT NULL,
        city VARCHAR(100) NOT NULL,
        address TEXT NOT NULL,
        
        -- Education
        highest_qual VARCHAR(100),
        school_name VARCHAR(255),
        board_university VARCHAR(255),
        passing_year INT(4),
        percentage VARCHAR(10),
        
        -- Skills
        computer_knowledge VARCHAR(50),
        typing_speed VARCHAR(50),
        prev_course_done TINYINT(1) DEFAULT 0,
        prev_enroll_no VARCHAR(50),
        prev_course_name VARCHAR(255),
        prev_course_session VARCHAR(50),
        
        -- Documents
        aadhar_no VARCHAR(20),
        aadhar_file VARCHAR(255),
        edu_cert_file VARCHAR(255),
        
        -- Fees & Payment
        course_fee DECIMAL(10, 2),
        payment_status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
        razorpay_payment_id VARCHAR(255),
        razorpay_order_id VARCHAR(255),
        
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

        if ($conn->query($sql) === TRUE) {
        // echo "Table 'internship_enrollments' checked/created successfully";
    } else {
        // echo "Error creating table: " . $conn->error;
        error_log("Error creating table: " . $conn->error);
    }
    
    // Add session_id column if not exists
    $check_col = $conn->query("SHOW COLUMNS FROM internship_enrollments LIKE 'session_id'");
    if($check_col->num_rows == 0) {
        $conn->query("ALTER TABLE internship_enrollments ADD COLUMN session_id INT(11) DEFAULT NULL AFTER internship_id");
    }

    // Internship Sessions Table
    $sess_sql = "CREATE TABLE IF NOT EXISTS internship_sessions (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        internship_id INT(11) NOT NULL,
        session_name VARCHAR(100) NOT NULL,
        start_month VARCHAR(20) NOT NULL,
        start_year INT(4) NOT NULL,
        end_month VARCHAR(20) NOT NULL,
        end_year INT(4) NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sess_sql) === TRUE) {
        // Success
    } else {
        error_log("Error creating table internship_sessions: " . $conn->error);
    }
    
    $conn->close();
}

createInternshipEnrollmentTable();
?>
