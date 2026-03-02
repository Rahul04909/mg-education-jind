<?php

$url = "https://mgedu.in/online-exam/start-exam.php?exam_id=5";

$totalRequests = 200; // kitne users simulate karne hain
$success = 0;
$fail = 0;

$startTime = microtime(true);

for ($i = 0; $i < $totalRequests; $i++) {

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode == 200) {
        $success++;
    } else {
        $fail++;
    }

    curl_close($ch);
}

$endTime = microtime(true);
$totalTime = $endTime - $startTime;

echo "Total Requests: $totalRequests\n";
echo "Successful: $success\n";
echo "Failed: $fail\n";
echo "Total Time: " . round($totalTime, 2) . " sec\n";
echo "Requests Per Second: " . round($totalRequests / $totalTime, 2) . "\n";

?>