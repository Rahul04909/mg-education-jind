<?php
require_once __DIR__ . '/database/db-config.php';

$conn = getDbConnection();

// Add columns to admissions table if not exists
$columns = [
    "ADD COLUMN center_id INT(11) NULL AFTER id",
    "ADD COLUMN added_by ENUM('admin', 'center') DEFAULT 'admin' AFTER center_id"
];

foreach ($columns as $col) {
    $sql = "ALTER TABLE admissions " . $col;
    if ($conn->query($sql) === TRUE) {
        echo "Column added successfully: $col <br>";
    } else {
        echo "Error adding column (might already exist): " . $conn->error . "<br>";
    }
}

echo "Database update complete.";
?>
