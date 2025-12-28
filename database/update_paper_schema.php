<?php
require_once __DIR__ . '/db-config.php';

$conn = getDbConnection();

// Create question_papers table
$sql_papers = "CREATE TABLE IF NOT EXISTS question_papers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    total_questions INT NOT NULL,
    marks_per_question INT NOT NULL,
    total_marks INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
)";

if ($conn->query($sql_papers) === TRUE) {
    // echo "Table question_papers created successfully or already exists.<br>";
} else {
    error_log("Error creating table question_papers: " . $conn->error);
}

// Create questions table
$sql_questions = "CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paper_id INT NOT NULL,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option ENUM('A', 'B', 'C', 'D') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paper_id) REFERENCES question_papers(id) ON DELETE CASCADE
)";

if ($conn->query($sql_questions) === TRUE) {
    // echo "Table questions created successfully or already exists.<br>";
} else {
    error_log("Error creating table questions: " . $conn->error);
}
?>
