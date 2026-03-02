<?php
/**
 * Online Exam Load Testing Script
 * This script simulates multiple concurrent users starting and submitting an exam.
 * 
 * Usage: php load-test.php --users=10 --exam_id=1
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration
$options = getopt("", ["users:", "exam_id:"]);
$conc_users = isset($options['users']) ? intval($options['users']) : 5;
$exam_id = isset($options['exam_id']) ? intval($options['exam_id']) : null;

if (!$exam_id) {
    echo "Error: --exam_id is required.\n";
    // Attempt auto-discovery if not provided
    require_once __DIR__ . '/../database/db-config.php';
    $conn = getDbConnection();
    $res = $conn->query("SELECT id FROM exam_schedules LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        $exam_id = $row['id'];
        echo "Auto-discovered exam_id: $exam_id\n";
    } else {
        die("Could not find any exam schedules in DB.\n");
    }
}

// Prepare simulation data
// We need valid student IDs. We'll pick a few from the DB.
require_once __DIR__ . '/../database/db-config.php';
$conn = getDbConnection();
$student_res = $conn->query("SELECT id FROM admissions LIMIT $conc_users");
$student_ids = [];
while ($row = $student_res->fetch_assoc()) {
    $student_ids[] = $row['id'];
}

if (count($student_ids) < $conc_users) {
    echo "Warning: Only found " . count($student_ids) . " students. Reducing concurrency.\n";
    $conc_users = count($student_ids);
}

echo "Starting Load Test with $conc_users concurrent users for Exam ID $exam_id...\n";
echo "--------------------------------------------------------------------------\n";

$mh = curl_multi_init();
$curls = [];

// Base URL detection (assuming local wamp setup)
$base_url = "http://localhost/mg-skill/online-exam/";

// Step 1: Simulate "Start Exam" (READ Load)
foreach ($student_ids as $sid) {
    $ch = curl_init();
    // We can't easily simulate sessions via CURL without cookie handling, 
    // but we can mock the session check in a test-version of the file or 
    // just hit it and measure response time of the query logic.
    // For a real load test, we'd need to log in each user.
    // To keep it simple and effective, we hit the script directly if it allowed it, 
    // but start-exam.php checks $_SESSION.
    
    // Instead, let's measure the core logic by hitting a "benchmark" wrapper if needed, 
    // but for now let's hit the URL and see the redirect/auth overhead too.
    curl_setopt($ch, CURLOPT_URL, $base_url . "start-exam.php?exam_id=" . $exam_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $curls[$sid] = $ch;
    curl_multi_add_handle($mh, $ch);
}

$start = microtime(true);
$active = null;
do {
    $mrc = curl_multi_exec($mh, $active);
} while ($mrc == CURLM_CALL_MULTI_PERFORM || $active);

while ($active && $mrc == CURLM_OK) {
    if (curl_multi_select($mh) != -1) {
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    }
}

$end = microtime(true);
echo "Phase 1 (Start Exam) Results:\n";
echo "Total Time: " . round($end - $start, 4) . "s\n";
echo "Avg Time per User: " . round(($end - $start) / $conc_users, 4) . "s\n\n";

// Cleanup Phase 1
foreach ($curls as $ch) {
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}

// Step 2: Simulate "Submit Exam" (WRITE Load - The real bottleneck)
echo "Phase 2 (Submit Exam) Results (Simulating 50 questions per user):\n";
$mh = curl_multi_init();
$curls = [];

foreach ($student_ids as $sid) {
    $ch = curl_init();
    $answers = [];
    for ($i = 1; $i <= 50; $i++) {
        $answers[$i] = ['selected' => 'A', 'status' => 'answered'];
    }
    
    $payload = [
        'exam_id' => $exam_id,
        'answers' => $answers
    ];
    
    curl_setopt($ch, CURLOPT_URL, $base_url . "submit-exam.php");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Mock user ID via header if we were to modify submit-exam.php for testing
    // For now, these will likely fail auth, but we want to measure the "Arrival" and "Parsing" load.
    // TO ACTUALLY TEST DB LOAD: I will create a 'submit-bench.php' which skips auth for load testing.
    
    $curls[$sid] = $ch;
    curl_multi_add_handle($mh, $ch);
}

$start = microtime(true);
$active = null;
do {
    $mrc = curl_multi_exec($mh, $active);
} while ($mrc == CURLM_CALL_MULTI_PERFORM || $active);

while ($active && $mrc == CURLM_OK) {
    if (curl_multi_select($mh) != -1) {
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    }
}
$end = microtime(true);

echo "Total Time: " . round($end - $start, 4) . "s\n";
echo "Avg Time per User: " . round(($end - $start) / $conc_users, 4) . "s\n";
echo "--------------------------------------------------------------------------\n";

curl_multi_close($mh);
?>
