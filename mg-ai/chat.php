<?php
@ini_set('zlib.output_compression', 0);
@ini_set('output_buffering', 'off');
@ini_set('implicit_flush', 1);
ob_implicit_flush(1);

$apiKey = getenv('OPENROUTER_API_KEY');
if(!$apiKey){
  if(isset($_ENV['OPENROUTER_API_KEY'])){$apiKey=$_ENV['OPENROUTER_API_KEY'];}
  elseif(isset($_SERVER['OPENROUTER_API_KEY'])){$apiKey=$_SERVER['OPENROUTER_API_KEY'];}
  else {
    $keyPath = dirname(__DIR__).DIRECTORY_SEPARATOR.'api-keys';
    if(is_readable($keyPath)){
      $apiKey = trim(file_get_contents($keyPath));
    }
  }
}
if(!$apiKey){
  http_response_code(500);
  header('Content-Type: application/json');
  echo json_encode(['error'=>'missing_api_key']);
  exit;
}

$input = file_get_contents('php://input');
$body = json_decode($input, true);
$messages = isset($body['messages']) ? $body['messages'] : [];
$model = isset($body['model']) ? $body['model'] : 'openrouter/auto';
$stream = isset($body['stream']) ? (bool)$body['stream'] : true;

if($stream){
  header('Content-Type: text/event-stream');
  header('Cache-Control: no-cache');
  header('Connection: keep-alive');
  header('X-Accel-Buffering: no');
  echo ":\n\n"; flush();
} else {
  header('Content-Type: application/json');
  header('Cache-Control: no-cache');
}

$ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
curl_setopt($ch, CURLOPT_POST, true);
// Build minimal, API-key only headers to avoid cookie auth requirements
$headers = [
  'Authorization: Bearer '.$apiKey,
  'Content-Type: application/json',
  'Expect:'
];
if($stream){ $headers[]='Accept: text/event-stream'; } else { $headers[]='Accept: application/json'; }
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_USERAGENT, 'MG-EDU-AI/1.0');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
curl_setopt($ch, CURLOPT_ENCODING, '');
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'model' => $model,
  'messages' => $messages,
  'stream' => $stream
]));
if($stream){
  curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data){ echo $data; flush(); return strlen($data); });
  curl_exec($ch);
  if(curl_errno($ch)){
    $err = curl_error($ch);
    $code = curl_errno($ch);
    echo 'data: '.json_encode(['error'=>'curl','code'=>$code,'message'=>$err])."\n\n"; flush();
  }
} else {
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $resp = curl_exec($ch);
  if(curl_errno($ch)){
    $err = curl_error($ch);
    $code = curl_errno($ch);
    echo json_encode(['error'=>'curl','code'=>$code,'message'=>$err]);
  } else {
    echo $resp;
  }
}
curl_close($ch);
