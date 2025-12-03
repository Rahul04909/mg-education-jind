<?php
$text='';
if($_SERVER['REQUEST_METHOD']==='POST'){$text=isset($_POST['text'])?$_POST['text']:'';}else{$text=isset($_GET['text'])?$_GET['text']:'';}
$voice=isset($_GET['voice'])?$_GET['voice']:'21m00Tcm4TlvDq8ikWAM';
if(trim($text)===''){http_response_code(400);exit;}
$key=getenv('ELEVENLABS_API_KEY');
if(!$key){$keys=@file_get_contents(__DIR__.'/../../api-keys');if($keys){if(preg_match('/sk_[a-zA-Z0-9]+/',$keys,$m)){$key=$m[0];}}}
if(!$key){http_response_code(500);exit;}
$url='https://api.elevenlabs.io/v1/text-to-speech/'.$voice;
$payload=json_encode(['text'=>$text,'model_id'=>'eleven_monolingual_v1','voice_settings'=>['stability'=>0.4,'similarity_boost'=>0.7]]);
$scheme=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';
$ch=curl_init($url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_POST,true);
curl_setopt($ch,CURLOPT_HTTPHEADER,[
  'Content-Type: application/json',
  'Accept: audio/mpeg',
  'xi-api-key: '.$key
]);
curl_setopt($ch,CURLOPT_POSTFIELDS,$payload);
$referer=$scheme.'://'.(isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'localhost');
curl_setopt($ch,CURLOPT_TIMEOUT,20);
if(strpos($referer,'localhost')!==false||strpos($referer,'127.0.0.1')!==false){curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);} 
$resp=curl_exec($ch);
$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
curl_close($ch);
if($code>=200&&$code<300&&$resp){header('Content-Type: audio/mpeg');echo $resp;}else{http_response_code(500);} 
?>
