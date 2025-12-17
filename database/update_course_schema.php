<?php
require_once __DIR__ . '/db-config.php';

function updateCourseSchema() {
    $conn = getDbConnection();
    
    // Check if columns exist
    $check_col = $conn->query("SHOW COLUMNS FROM courses LIKE 'duration_value'");
    if ($check_col->num_rows == 0) {
        $sql = "ALTER TABLE courses ADD COLUMN duration_value INT DEFAULT 0, ADD COLUMN duration_type VARCHAR(20) DEFAULT 'Months'";
        if ($conn->query($sql) === TRUE) {
            // echo "Schema updated: Duration columns added successfully.<br>";
        } else {
            echo "Error updating schema: " . $conn->error . "<br>";
        }
    }
    
    $conn->close();
}

updateCourseSchema();
?>
