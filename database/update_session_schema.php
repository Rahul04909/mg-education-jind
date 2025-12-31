<?php
require_once __DIR__ . '/db-config.php';

function updateSessionSchema() {
    $conn = getDbConnection();
    
    // Check if table exists
    $check_table = $conn->query("SHOW TABLES LIKE 'course_sessions'");
    if ($check_table->num_rows == 0) {
        $sql = "CREATE TABLE course_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_id INT NOT NULL,
            session_name VARCHAR(255) NOT NULL,
            start_month VARCHAR(50) NOT NULL,
            end_month VARCHAR(50) NOT NULL,
            start_year INT NOT NULL,
            end_year INT NOT NULL,
            is_active BOOLEAN DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
        )";
        
        if ($conn->query($sql) === TRUE) {
             if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
                 echo "Success: 'course_sessions' table created successfully.<br>";
             }
        } else {
            echo "Error creating table: " . $conn->error . "<br>";
        }
    } else {
        if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
            echo "Info: 'course_sessions' table already exists.<br>";
        }
    }
    
    $conn->close();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

updateSessionSchema();
?>
