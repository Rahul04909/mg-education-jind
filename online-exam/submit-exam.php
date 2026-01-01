<?php
// online-exam/submit-exam.php
ob_start(); // Start output buffering immediately
session_start();
require_once __DIR__ . '/../database/db-config.php';
// Helper to send JSON and exit cleanly
function sendJson($data, $code = 200) {
    // Clear buffer if active
    if (ob_get_length()) ob_clean(); 
    
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// Catch Fatal Errors (500s)
function fatalErrorHandler() {
    $error = error_get_last();
    if ($error !== NULL && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR || $error['type'] === E_COMPILE_ERROR)) {
        sendJson(['status' => 'error', 'message' => 'Fatal Error: ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line']], 500);
    }
}
register_shutdown_function('fatalErrorHandler');

// Ensure schema exists on live server (Disabled for stability - Run manually if needed)
// define('SILENT_UPDATE', true);
// require_once __DIR__ . '/../database/update_exam_results_schema.php';

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
$exam_id = intval($input['exam_id']);
$user_answers = $input['answers'] ?? []; // { qId: { selected: 'A', status: '...' } }

// 1. Fetch Exam & Paper Details to Validation
$sql = "SELECT es.*, qp.id as paper_id, qp.total_questions, qp.total_marks, qp.marks_per_question, s.passing_marks 
        FROM exam_schedules es
        JOIN question_papers qp ON es.session_id = qp.session_id AND es.subject_id = qp.subject_id
        JOIN subjects s ON es.subject_id = s.id
        WHERE es.id = $exam_id";

$res = $conn->query($sql);
if ($res->num_rows == 0) {
    sendJson(['status' => 'error', 'message' => 'Exam Not Found']);
}
$exam = $res->fetch_assoc();
$paper_id = $exam['paper_id'];
$marks_per_q = $exam['marks_per_question'];

// 2. Fetch Correct Answers from DB
$q_sql = "SELECT id, correct_option FROM questions WHERE paper_id = $paper_id";
$q_res = $conn->query($q_sql);
$correct_answers_map = [];
while ($row = $q_res->fetch_assoc()) {
    $correct_answers_map[$row['id']] = $row['correct_option'];
}

// 3. Calculate Score
$total_q = count($correct_answers_map); // Use actual question count
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
$total_marks = $total_q * $marks_per_q; // Recalculate based on actual questions
$percentage = ($total_marks > 0) ? ($obtained_marks / $total_marks) * 100 : 0;
// Use dynamic passing marks
$status = ($obtained_marks >= $exam['passing_marks']) ? 'PASS' : 'FAIL';

// 4. Save to `exam_results`
$ins_res = $conn->prepare("INSERT INTO exam_results (exam_schedule_id, student_id, total_questions, correct_answers, wrong_answers, total_marks, obtained_marks, percentage, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$ins_res->bind_param("iiiiiddds", $exam_id, $student_id, $total_q, $correct_count, $wrong_count, $total_marks, $obtained_marks, $percentage, $status);

if ($ins_res->execute()) {
    $result_id = $ins_res->insert_id;
    
    // 5. Save detailed answers
    $ins_ans = $conn->prepare("INSERT INTO student_answers (exam_result_id, question_id, selected_option, is_correct) VALUES (?, ?, ?, ?)");
    
    foreach ($user_answers as $q_id => $ans_data) {
        if (isset($ans_data['selected']) && $ans_data['selected'] !== null) {
            $selected = $ans_data['selected'];
            $is_correct = (isset($correct_answers_map[$q_id]) && $correct_answers_map[$q_id] === $selected) ? 1 : 0;
            $ins_ans->bind_param("iisi", $result_id, $q_id, $selected, $is_correct);
            $ins_ans->execute();
        }
    }
    
    sendJson(['status' => 'success', 'message' => 'Result Saved', 'redirect' => 'result.php?exam_id='.$exam_id]);
} else {
    sendJson(['status' => 'error', 'message' => 'DB Error: ' . $conn->error]);
}

} catch (Exception $e) {
    sendJson(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()], 500);
}
// END OF FILE - No closing tag to avoid whitespace
