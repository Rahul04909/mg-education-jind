<?php
require_once __DIR__ . '/../database/db-config.php';
$conn = getDbConnection();

// Add response_note column if not exists
$sql = "SHOW COLUMNS FROM quick_enquiries LIKE 'response_note'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $alter_sql = "ALTER TABLE quick_enquiries ADD COLUMN response_note TEXT AFTER message";
    if ($conn->query($alter_sql) === TRUE) {
        echo "Column 'response_note' added successfully.<br>";
    } else {
        echo "Error adding column: " . $conn->error . "<br>";
    }
} else {
    echo "Column 'response_note' already exists.<br>";
}

$conn->close();
?>
