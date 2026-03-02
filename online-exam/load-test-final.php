<?php
/**
 * Professional Load Test Tool for MG Skills Exam Portal
 * Measures throughput and latency for Start and Submit phases.
 */

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

// Configuration
$config = [
    'base_url' => 'http://localhost/mg-skill/online-exam/',
    'users'    => 20, // Default concurrent users
    'exam_id'  => 1,
    'questions'=> 50  // Simulated questions per student
];

// Handle CLI args
$options = getopt("u:e:q:", ["users:", "exam_id:", "questions:"]);
if (isset($options['u']) || isset($options['users'])) $config['users'] = intval($options['u'] ?: $options['users']);
if (isset($options['e']) || isset($options['exam_id'])) $config['exam_id'] = intval($options['e'] ?: $options['exam_id']);
if (isset($options['q']) || isset($options['questions'])) $config['questions'] = intval($options['q'] ?: $options['questions']);

echo "==========================================================\n";
echo " MG SKILLS ONLINE-EXAM LOAD TESTER\n";
echo "==========================================================\n";
echo "Target URL  : " . $config['base_url'] . "\n";
echo "Users       : " . $config['users'] . "\n";
echo "Exam ID     : " . $config['exam_id'] . "\n";
echo "Questions   : " . $config['questions'] . " per user\n";
echo "==========================================================\n\n";

function runPhase($name, $count, $callback) {
    echo "Starting Phase: $name ($count concurrent requests)...\n";
    $mh = curl_multi_init();
    $handles = [];
    $results = [];
    
    for ($i = 0; $i < $count; $i++) {
        $ch = $callback($i);
        $handles[$i] = $ch;
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
    $total_time = $end - $start;

    $success = 0;
    $errors = 0;
    $latencies = [];
    
    foreach ($handles as $ch) {
        $info = curl_getinfo($ch);
        $res = curl_multi_getcontent($ch);
        if ($info['http_code'] >= 200 && $info['http_code'] < 300) {
            $success++;
        } else {
            $errors++;
        }
        $latencies[] = $info['total_time'];
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }
    curl_multi_close($mh);

    $avg_lat = array_sum($latencies) / count($latencies);
    
    echo "Done.\n";
    echo "  Total Time : " . round($total_time, 4) . "s\n";
    echo "  Success    : $success\n";
    echo "  Errors     : $errors\n";
    echo "  Avg Latency: " . round($avg_lat, 4) . "s\n";
    echo "  Throughput : " . round($count / $total_time, 2) . " req/s\n\n";
    
    return [
        'total' => $total_time,
        'avg' => $avg_lat,
        'throughput' => $count / $total_time
    ];
}

// PHASE 1: START EXAM (Read Load)
$p1 = runPhase("FETCH QUESTIONS (READ)", $config['users'], function($i) use ($config) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $config['base_url'] . "start-exam.php?exam_id=" . $config['exam_id']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow to login if needed
    return $ch;
});

// PHASE 2: SUBMIT EXAM (Write Load) - Using benchmark wrapper to measure DB impact
$p2 = runPhase("SUBMIT EXAM (WRITE)", $config['users'], function($i) use ($config) {
    $ch = curl_init();
    $answers = [];
    for ($q = 1; $q <= $config['questions']; $q++) {
        $answers[$q] = ['selected' => 'A'];
    }
    $payload = json_encode([
        'exam_id' => $config['exam_id'],
        'answers' => $answers
    ]);
    
    // Using submit-bench.php which we created to measure DB impact without cookie management
    curl_setopt($ch, CURLOPT_URL, $config['base_url'] . "submit-bench.php?test_student_id=" . ($i + 1));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    return $ch;
});

echo "==========================================================\n";
echo " SUMMARY & ESTIMATES\n";
echo "==========================================================\n";
$max_per_sec = $p2['throughput'];
echo "Current Capacity (Writes): " . round($max_per_sec, 2) . " users/sec\n";
echo "Estimated Concurrent Capacity (30s window): " . round($max_per_sec * 30) . " users\n";

if ($p2['avg'] > 2.0) {
    echo "CRITICAL: High Write Latency detected! (" . round($p2['avg'], 2) . "s)\n";
    echo "Recommendation: Use Bulk Inserts and DB Transactions.\n";
} else {
    echo "Status: Server is handling the current load well.\n";
}
echo "==========================================================\n";
?>
