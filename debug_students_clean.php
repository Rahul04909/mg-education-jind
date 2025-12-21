<?php
require_once 'database/db-config.php';
$conn = getDbConnection();

echo "--- Student Credentials ---\n";
$sql = "SELECT id, enrollment_no, length(enrollment_no) as len, full_name FROM admissions";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . "\n";
        echo "Enrollment: '" . $row['enrollment_no'] . "' (Length: " . $row['len'] . ")\n";
        echo "Name: " . $row['full_name'] . "\n";
        echo "-------------------\n";
    }
} else {
    echo "No students found.\n";
}
$conn->close();
?>
