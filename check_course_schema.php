<?php
require_once 'database/db-config.php';
$conn = getDbConnection();

echo "Courses Columns:\n";
$columns = $conn->query("SHOW COLUMNS FROM courses");
if ($columns) {
    while ($row = $columns->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\nCourse Categories Columns:\n";
$columns = $conn->query("SHOW COLUMNS FROM course_categories");
if ($columns) {
    while ($row = $columns->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

$conn->close();
?>
