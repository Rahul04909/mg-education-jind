<?php
// internship-exam/submit-exam.php
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
if (!$input || !isset($input['paper_id'])) {
    sendJson(['status' => 'error', 'message' => 'Invalid Data']);
}

$conn = getDbConnection();
$student_id = $_SESSION['student_id'];
$paper_id = intval($input['paper_id']);
$user_answers = $input['answers'] ?? []; // { qId: { selected: 'A', status: '...' } }

// 1. Fetch Paper Details
$sql = "SELECT * FROM internship_question_papers WHERE id = $paper_id";
$res = $conn->query($sql);
if ($res->num_rows == 0) {
    sendJson(['status' => 'error', 'message' => 'Exam Paper Not Found']);
}
$paper = $res->fetch_assoc();
$marks_per_q = $paper['marks_per_question'];

// 2. Fetch Correct Answers from DB
$q_sql = "SELECT id, correct_option FROM internship_questions WHERE paper_id = $paper_id";
$q_res = $conn->query($q_sql);
$correct_answers_map = [];
while ($row = $q_res->fetch_assoc()) {
    $correct_answers_map[$row['id']] = $row['correct_option'];
}

// 3. Calculate Score
$total_q = count($correct_answers_map); 
$correct_count = 0;
$wrong_count = 0;
$attempted_count = 0;

foreach ($user_answers as $q_id => $ans_data) {
    if (isset($ans_data['selected']) && $ans_data['selected'] !== null) {
        $attempted_count++;
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
$percentage = ($total_marks > 0) ? ($obtained_marks / $total_marks) * 100 : 0;
$status = ($obtained_marks >= $paper['passing_marks']) ? 'PASS' : 'FAIL';

// 4. Save to `internship_results`
$ins_res = $conn->prepare("INSERT INTO internship_results (internship_paper_id, student_id, total_questions, correct_answers, wrong_answers, total_marks, obtained_marks, percentage, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$ins_res->bind_param("iiiiiddds", $paper_id, $student_id, $total_q, $correct_count, $wrong_count, $total_marks, $obtained_marks, $percentage, $status);

if ($ins_res->execute()) {
    $result_id = $ins_res->insert_id;
    
    // 5. Save detailed answers
    $ins_ans = $conn->prepare("INSERT INTO internship_student_answers (result_id, question_id, selected_option, is_correct) VALUES (?, ?, ?, ?)");
    
    foreach ($user_answers as $q_id => $ans_data) {
        if (isset($ans_data['selected']) && $ans_data['selected'] !== null) {
            $selected = $ans_data['selected'];
            $is_correct = (isset($correct_answers_map[$q_id]) && $correct_answers_map[$q_id] === $selected) ? 1 : 0;
            $ins_ans->bind_param("iisi", $result_id, $q_id, $selected, $is_correct);
            $ins_ans->execute();
        }
    }
    
    sendJson(['status' => 'success', 'message' => 'Result Saved', 'redirect' => 'result.php?paper_id='.$paper_id]);
} else {
    sendJson(['status' => 'error', 'message' => 'DB Error: ' . $conn->error]);
}

} catch (Exception $e) {
    sendJson(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()], 500);
}
