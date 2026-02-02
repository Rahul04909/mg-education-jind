<?php
require_once __DIR__ . '/db-config.php';

$conn = getDbConnection();

$sql = "CREATE TABLE IF NOT EXISTS internship_results (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    internship_paper_id INT(11) NOT NULL,
    total_questions INT(11) NOT NULL,
    correct_answers INT(11) NOT NULL,
    wrong_answers INT(11) NOT NULL,
    obtained_marks DECIMAL(10,2) NOT NULL,
    total_marks DECIMAL(10,2) NOT NULL,
    status ENUM('Pass', 'Fail') NOT NULL DEFAULT 'Fail',
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES internship_enrollments(id) ON DELETE CASCADE,
    FOREIGN KEY (internship_paper_id) REFERENCES internship_question_papers(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'internship_results' created successfully.";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
