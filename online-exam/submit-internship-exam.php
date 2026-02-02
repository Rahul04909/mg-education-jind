<?php
ob_start();
session_start();
require_once __DIR__ . '/../database/db-config.php';

function sendJson($data, $code = 200) {
    if (ob_get_length()) ob_clean(); 
    http_response_code($code);
    echo json_encode($data);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJson(['status' => 'error', 'message' => 'Invalid Request Method']);
    }

    if (!isset($_SESSION['student_id'])) {
        sendJson(['status' => 'error', 'message' => 'Session Expired']);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['exam_id'])) {
        sendJson(['status' => 'error', 'message' => 'Invalid Data']);
    }

    $conn = getDbConnection();
    $student_id = $_SESSION['student_id'];
    $exam_id = intval($input['exam_id']); // internship_paper_id
    $user_answers = $input['answers'] ?? [];

    // 1. Fetch Paper Details
    // Using aliases to match logic
    $sql = "SELECT qp.*, qp.id as paper_id 
            FROM internship_question_papers qp
            WHERE qp.id = $exam_id";

    $res = $conn->query($sql);
    if ($res->num_rows == 0) {
        sendJson(['status' => 'error', 'message' => 'Exam Not Found']);
    }
    $exam = $res->fetch_assoc();
    
    // Determine marks per question
    // If not set in DB, calculate dynamically
    $marks_per_q = $exam['marks_per_question'];
    if($marks_per_q <= 0 && $exam['total_questions'] > 0) {
        $marks_per_q = $exam['total_marks'] / $exam['total_questions'];
    }

    // 2. Fetch Correct Answers
    $q_sql = "SELECT id, correct_option FROM internship_questions WHERE internship_paper_id = $exam_id";
    $q_res = $conn->query($q_sql);
    $correct_answers_map = [];
    while ($row = $q_res->fetch_assoc()) {
        $correct_answers_map[$row['id']] = $row['correct_option'];
    }

    // 3. Calculate Score
    $total_q = count($correct_answers_map);
    $correct_count = 0;
    $wrong_count = 0;
    
    foreach ($user_answers as $q_id => $ans_data) {
        if (isset($ans_data['selected']) && $ans_data['selected'] !== null) {
            $selected = $ans_data['selected'];
            if (isset($correct_answers_map[$q_id]) && $correct_answers_map[$q_id] === $selected) {
                $correct_count++;
            } else {
                $wrong_count++;
            }
        }
    }

    $obtained_marks = $correct_count * $marks_per_q;
    $total_marks = $total_q * $marks_per_q;
    
    $status = ($obtained_marks >= $exam['passing_marks']) ? 'Pass' : 'Fail';

    // 4. Save to `internship_results`
    // Check for duplicate submission first to prevent double insert
    $chk = $conn->query("SELECT id FROM internship_results WHERE internship_paper_id = $exam_id AND student_id = $student_id");
    if($chk->num_rows > 0) {
        sendJson(['status' => 'success', 'message' => 'Already Submitted', 'redirect' => 'internship-result.php?exam_id='.$exam_id]);
    }

    $ins_res = $conn->prepare("INSERT INTO internship_results 
        (student_id, internship_paper_id, total_questions, correct_answers, wrong_answers, total_marks, obtained_marks, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $ins_res->bind_param("iiiiidds", 
        $student_id, $exam_id, $total_q, $correct_count, $wrong_count, $total_marks, $obtained_marks, $status
    );

    if ($ins_res->execute()) {
        sendJson(['status' => 'success', 'message' => 'Result Saved']);
    } else {
        sendJson(['status' => 'error', 'message' => 'DB Error: ' . $conn->error]);
    }

} catch (Exception $e) {
    sendJson(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()], 500);
}
?>
