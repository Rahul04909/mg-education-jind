<?php
// online-exam/install_db.php
// Standalone script to create missing exam tables

require_once __DIR__ . '/../database/db-config.php';
$conn = getDbConnection();

echo "<h2>Checking Database Tables...</h2>";

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
    echo "<div style='color:green'>✔ Table 'exam_results' created or already exists.</div>";
} else {
    echo "<div style='color:red'>✘ Error creating 'exam_results': " . $conn->error . "</div>";
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
    echo "<div style='color:green'>✔ Table 'student_answers' created or already exists.</div>";
} else {
    echo "<div style='color:red'>✘ Error creating 'student_answers': " . $conn->error . "</div>";
}

$conn->close();
echo "<br><b>Done. You can now try submitting the exam.</b>";
?>
