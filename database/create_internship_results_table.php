<?php
// database/create_internship_results_table.php

require_once __DIR__ . '/db-config.php';
$conn = getDbConnection();

echo "<h2>Setting up Internship Exam Tables...</h2>";

// 1. Create internship_results table
$sql_results = "CREATE TABLE IF NOT EXISTS internship_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    internship_paper_id INT NOT NULL,
    student_id INT NOT NULL,
    total_questions INT NOT NULL,
    correct_answers INT NOT NULL,
    wrong_answers INT NOT NULL,
    total_marks INT NOT NULL,
    obtained_marks DECIMAL(10,2) NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    status ENUM('PASS', 'FAIL') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (internship_paper_id) REFERENCES internship_question_papers(id),
    FOREIGN KEY (student_id) REFERENCES internship_enrollments(id)
)";

if ($conn->query($sql_results) === TRUE) {
    echo "<div style='color:green'>✔ Table 'internship_results' created or already exists.</div>";
} else {
    echo "<div style='color:red'>✘ Error creating 'internship_results': " . $conn->error . "</div>";
}

// 2. Create internship_student_answers table
$sql_answers = "CREATE TABLE IF NOT EXISTS internship_student_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    result_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option ENUM('A', 'B', 'C', 'D') NOT NULL,
    is_correct BOOLEAN NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (result_id) REFERENCES internship_results(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES internship_questions(id)
)";

if ($conn->query($sql_answers) === TRUE) {
    echo "<div style='color:green'>✔ Table 'internship_student_answers' created or already exists.</div>";
} else {
    echo "<div style='color:red'>✘ Error creating 'internship_student_answers': " . $conn->error . "</div>";
}

$conn->close();
echo "<br><b>Done. Tables are ready.</b>";
?>
