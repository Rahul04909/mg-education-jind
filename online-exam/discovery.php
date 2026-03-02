<?php
require_once __DIR__ . '/../database/db-config.php';
$conn = getDbConnection();

// Get a valid exam schedule
$sql_exam = "SELECT es.id, es.session_id, es.subject_id FROM exam_schedules es LIMIT 1";
$res_exam = $conn->query($sql_exam);
$exam = $res_exam->fetch_assoc();

// Get student IDs in the same session
$students = [];
if ($exam) {
    $session_id = $exam['session_id'];
    $sql_students = "SELECT id FROM admissions WHERE session_id = $session_id LIMIT 10";
    $res_students = $conn->query($sql_students);
    while($row = $res_students->fetch_assoc()) {
        $students[] = $row['id'];
    }
}

echo json_encode([
    'exam' => $exam,
    'students' => $students
]);
?>
