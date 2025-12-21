<?php
require_once 'database/db-config.php';
$conn = getDbConnection();

$result = $conn->query("SELECT fees FROM courses LIMIT 1");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "Fees JSON:\n" . $row['fees'] . "\n";
} else {
    echo "No courses found.\n";
}

$conn->close();
?>
