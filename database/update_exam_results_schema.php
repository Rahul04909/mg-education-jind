<?php
require_once __DIR__ . '/db-config.php';
$conn = getDbConnection();

// 1. Create exam_results table
$sql_results = "CREATE TABLE IF NOT EXISTS exam_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_schedule_id INT NOT NULL,
    student_id INT NOT NULL,
    total_questions INT NOT NULL,
    correct_answers INT NOT NULL,
    wrong_answers INT NOT NULL,
    total_marks INT NOT NULL,
    obtained_marks DECIMAL(10,2) NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    status ENUM('PASS', 'FAIL') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_schedule_id) REFERENCES exam_schedules(id),
    FOREIGN KEY (student_id) REFERENCES admissions(id)
)";

if ($conn->query($sql_results) === TRUE) {
    if (!defined('SILENT_UPDATE')) echo "Table 'exam_results' created/checked.\n";
} else {
    error_log("Error creating 'exam_results': " . $conn->error);
    if (!defined('SILENT_UPDATE')) echo "Error creating 'exam_results': " . $conn->error . "\n";
}

// 2. Create student_answers table
$sql_answers = "CREATE TABLE IF NOT EXISTS student_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_result_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option ENUM('A', 'B', 'C', 'D') NOT NULL,
    is_correct BOOLEAN NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_result_id) REFERENCES exam_results(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id)
)";

if ($conn->query($sql_answers) === TRUE) {
    if (!defined('SILENT_UPDATE')) echo "Table 'student_answers' created/checked.\n";
} else {
    error_log("Error creating 'student_answers': " . $conn->error);
    if (!defined('SILENT_UPDATE')) echo "Error creating 'student_answers': " . $conn->error . "\n";
}

$conn->close();
?>
