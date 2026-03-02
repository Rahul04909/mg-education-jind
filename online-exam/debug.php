<?php

$url = "https://mgedu.in/online-exam/attempt.php?exam_id=5";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Code: $httpCode\n";

echo "Full Response:\n";
echo $response;

curl_close($ch);