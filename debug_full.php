<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/database/db-config.php';

$conn = getDbConnection();

echo "<h1>Diagnostic Report</h1>";

// 1. Check Courses
echo "<h2>1. Active Courses</h2>";
$c_res = $conn->query("SELECT id, title FROM courses WHERE is_active = 1");
$courses = [];
if ($c_res->num_rows > 0) {
    echo "Found " . $c_res->num_rows . " active courses.<br>";
    while($row = $c_res->fetch_assoc()) {
        $courses[$row['id']] = $row['title'];
        echo "ID: " . $row['id'] . " - " . $row['title'] . "<br>";
    }
} else {
    echo "<strong style='color:red'>No active courses found.</strong><br>";
}

// 2. Check Sessions
echo "<h2>2. Course Sessions</h2>";
$s_res = $conn->query("SELECT * FROM course_sessions");
if ($s_res->num_rows > 0) {
    echo "Found " . $s_res->num_rows . " sessions total.<br>";
    echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Course ID</th><th>Name</th><th>Is Active</th></tr>";
    while($row = $s_res->fetch_assoc()) {
        $course_name = isset($courses[$row['course_id']]) ? $courses[$row['course_id']] : "Unknown Course (ID: {$row['course_id']})";
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['course_id']} ({$course_name})</td>";
        echo "<td>{$row['session_name']}</td>";
        echo "<td>{$row['is_active']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<strong style='color:red'>No sessions found in 'course_sessions' table.</strong><br>";
}

// 3. Simulate Frontend Data
echo "<h2>3. Frontend Data JSON</h2>";
$input_sessions = [];
$s_sql = "SELECT id, course_id, session_name FROM course_sessions WHERE is_active = 1 ORDER BY id DESC";
$s_res2 = $conn->query($s_sql);
while($row = $s_res2->fetch_assoc()) {
    $input_sessions[$row['course_id']][] = $row;
}

$json = json_encode($input_sessions);
echo "<strong>JSON Output:</strong><br>";
echo "<textarea style='width:100%; height: 100px;'>$json</textarea>";

echo "<h2>4. JS Check</h2>";
echo "If you select a Course ID from Section 1, look for it in the JSON keys in Section 3.";

$conn->close();
?>
