<?php
// online-exam/submit-exam.php
session_start();
require_once __DIR__ . '/../database/db-config.php';
date_default_timezone_set('Asia/Kolkata');

// Prevent HTML errors from breaking JSON
ini_set('display_errors', 0);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

header('Content-Type: application/json');

try {


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
    exit;
}

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session Expired']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['exam_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Data']);
    exit;
}

$conn = getDbConnection();
$student_id = $_SESSION['student_id'];
$exam_id = intval($input['exam_id']);
$user_answers = $input['answers'] ?? []; // { qId: { selected: 'A', status: '...' } }

// 1. Fetch Exam & Paper Details to Validation
$sql = "SELECT es.*, qp.id as paper_id, qp.total_questions, qp.total_marks, qp.marks_per_question 
        FROM exam_schedules es
        JOIN question_papers qp ON es.session_id = qp.session_id AND es.subject_id = qp.subject_id
        WHERE es.id = $exam_id";

$res = $conn->query($sql);
if ($res->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Exam Not Found']);
    exit;
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
$total_q = $exam['total_questions'];
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
$total_marks = $exam['total_marks'];
$percentage = ($total_marks > 0) ? ($obtained_marks / $total_marks) * 100 : 0;
$status = ($percentage >= 33) ? 'PASS' : 'FAIL'; // 33% passing criteria

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
            $ins_ans->bind_param("iISI", $result_id, $q_id, $selected, $is_correct); // iisi -> i = int, s = string, i = int (bool)
            // Wait, bind_param types: i (int), d (double), s (string), b (blob)
            // 'i', 'i', 's', 'i'
            $ins_ans->bind_param("iisi", $result_id, $q_id, $selected, $is_correct);
            $ins_ans->execute();
        }
    }
    
    echo json_encode(['status' => 'success', 'message' => 'Result Saved', 'redirect' => 'result.php?exam_id='.$exam_id]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $conn->error]);
}

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>
