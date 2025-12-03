<?php
header('Content-Type: application/json');
$input=file_get_contents('php://input');
$payload=$input?json_decode($input,true):[];
$message=isset($payload['message'])?trim($payload['message']):'';
$history=isset($payload['history'])&&is_array($payload['history'])?$payload['history']:[];
$model=isset($payload['model'])?trim($payload['model']):'anthropic/claude-3-haiku';
if($message===''){http_response_code(400);echo json_encode(['error'=>'empty']);exit;}
$key=getenv('OPENROUTER_API_KEY');
if(!$key){$keys=@file_get_contents(__DIR__.'/../../api-keys');if($keys){if(preg_match('/sk-or-[a-zA-Z0-9-]+/',$keys,$m)){$key=$m[0];}}}
if(!$key){http_response_code(500);echo json_encode(['error'=>'no_key']);exit;}
$msgs=[];
$msgs[]=['role'=>'system','content'=>'You are MG AI, a helpful assistant for MG Skill. Be concise, friendly, and practical.'];
foreach($history as $h){if(isset($h['q'])){$msgs[]=['role'=>'user','content'=>$h['q']];}if(isset($h['a'])){$msgs[]=['role'=>'assistant','content'=>$h['a']];}}
$msgs[]=['role'=>'user','content'=>$message];
$data=[
  'model'=>$model,
  'messages'=>$msgs,
  'temperature'=>0.7
];
$scheme=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';
$referer=$scheme.'://'.(isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'localhost');
$ch=curl_init('https://openrouter.ai/api/v1/chat/completions');
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_POST,true);
curl_setopt($ch,CURLOPT_HTTPHEADER,[
  'Authorization: Bearer '.$key,
  'Content-Type: application/json',
  'Accept: application/json',
  'HTTP-Referer: '.$referer,
  'Referer: '.$referer,
  'X-Title: MG Skill'
]);
curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($data));
curl_setopt($ch,CURLOPT_TIMEOUT,20);
if(strpos($referer,'localhost')!==false||strpos($referer,'127.0.0.1')!==false){curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);}
$resp=curl_exec($ch);
$err=curl_errno($ch)?curl_error($ch):'';
$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
curl_close($ch);
if($code>=200&&$code<300&&$resp){$j=json_decode($resp,true);$out='';
  if(isset($j['choices'][0]['message']['content'])){$out=$j['choices'][0]['message']['content'];}
  if($out===''){$out='(No response from model)';}
  echo json_encode(['reply'=>$out]);
}else{
  http_response_code($code ?: 500);
  $body=json_decode($resp,true);
  $msg=$err;
  if(is_array($body)){
    if(isset($body['error']['message'])){$msg=$body['error']['message'];}
    elseif(isset($body['message'])){$msg=$body['message'];}
  }
  echo json_encode(['error'=>'upstream','status'=>$code,'detail'=>$msg]);
}
?>
