<?php
require_once __DIR__ . '/db-config.php';

$conn = getDbConnection();

// Create internship_question_papers table
$sql_papers = "CREATE TABLE IF NOT EXISTS internship_question_papers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    internship_id INT NOT NULL,
    session_id INT NOT NULL,
    total_questions INT NOT NULL,
    marks_per_question INT NOT NULL,
    total_marks INT NOT NULL,
    passing_marks INT NOT NULL,
    exam_date DATE NOT NULL,
    exam_duration INT NOT NULL COMMENT 'Duration in minutes',
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES internship_sessions(id) ON DELETE CASCADE
)";

$show_output = (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]));

if ($conn->query($sql_papers) === TRUE) {
    if ($show_output) echo "Table internship_question_papers created successfully or already exists.<br>";
} else {
    if ($show_output) echo "Error creating table internship_question_papers: " . $conn->error . "<br>";
}

// Create internship_questions table
$sql_questions = "CREATE TABLE IF NOT EXISTS internship_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paper_id INT NOT NULL,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option ENUM('A', 'B', 'C', 'D') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paper_id) REFERENCES internship_question_papers(id) ON DELETE CASCADE
)";

if ($conn->query($sql_questions) === TRUE) {
    if ($show_output) echo "Table internship_questions created successfully or already exists.<br>";
} else {
    if ($show_output) echo "Error creating table internship_questions: " . $conn->error . "<br>";
}

$conn->close();
?>
