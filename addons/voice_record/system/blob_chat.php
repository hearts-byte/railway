<?php
$load_addons = 'voice_record';
require(__DIR__ . '/../../../system/config_addons.php');

if(mainBlocked()){
	boomCode(0);
}

function runMainRecorder(){
	global $mysqli, $data, $lang;

	if(!mainVoiceRecord()){
		return boomCode(0);
	}
	if (!isset($_FILES["file"])){
		return boomCode(1);
	}
	ini_set('memory_limit','128M');
	$info = pathinfo($_FILES["file"]["name"]);
	$extension = 'mp3';
	$origin = escape(filterOrigin($info['filename']) . '.' . $extension);
	if ( fileError() ){
		return boomCode(1);
	}
	else if (isMusic($extension)){
		$file_name = encodeFile($extension);
		boomMoveFile('upload/chat/' . $file_name);
		$myfile = 'upload/chat/' . $file_name;
		$content =  uploadProcess('voice', $myfile);
		$logs = userPostChat($content, array('file'=> $file_name, 'filetype'=> 'music'));
		return boomCode(5, array('logs'=> $logs));
	}
	else {
		return boomCode(1);
	}
}
function runPrivateRecorder(){
	global $mysqli, $data, $lang;
	
	$target = escape($_POST['target'], true);
	
	$user = userRelationDetails($target);
	if(!canSendPrivate($user)){
		return boomCode(99);
	}
	
	if(!privateVoiceRecord()){
		return boomCode(0);
	}
	if(!isset($_POST['target'])){
		return boomCode(0);
	}
	if(!isset($_FILES["file"])){
		return boomCode(1);
	}

	$info = pathinfo($_FILES["file"]["name"]);
	$extension = 'mp3';
	$origin = escape(filterOrigin($info['filename']) . '.' . $extension);
	if ( fileError() ){
		return boomCode(1);
	}
	$file_name = encodeFile($extension);	
	if (isMusic($extension)){
		boomMoveFile('upload/private/' . $file_name);
		$myfile = 'upload/private/' . $file_name;
		$content =  uploadProcess('voice', $myfile);
		$logs = userPostPrivate($user, $content, array('file'=> $file_name, 'filetype'=> 'music'));
		return boomCode(5, array('logs'=> $logs));
	}
	else {
		return boomCode(1);
	}
}
if(isset($_POST['private'])){
	echo runPrivateRecorder();
	die();
}
else {
	echo runMainRecorder();
	die();
}
?>