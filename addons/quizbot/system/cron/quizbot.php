<?php
$load_addons = 'quizbot';
require(__DIR__ . '/../../../../system/config_cron.php');

$time = time();
$mode = 1;
$zone = 'quiz';

if($addons['custom2'] == 0){
	die();
}

$file = quizFile();

if($file == $addons['custom5']){
	$mode = 2;
	$zone = 'scramble';
}

$lines = file(__DIR__ . '/../' . $zone . '/' . $file);
$line_count = count($lines);
$random = rand(1,$line_count - 1);
$read = $lines[$random];

if($mode == 2){
	$question = escape(shuffleIt(trim($read)));
	$answer = escape($read);
}
else {
	$qline = explode('*',$read);
	$question = escape(trim($qline[0]));
	$answer = escape(trim($qline[1]));
}
if($question == '' || $answer == ''){
	die();
}
	
$quiz = updateQuiz($question, $answer);
if(empty($quiz)){
	die();
}

if(!playerOn()){
	die();
}
if($mode == 2){
	postScramble();
}
else {
	postQuestion();
}

mysqli_close($mysqli);

$pass = 0;
while($pass < 11){
	usleep(5250000);
	$mysqli = @new mysqli(BOOM_DHOST, BOOM_DUSER, BOOM_DPASS, BOOM_DNAME);
	$check_answer = $mysqli->query("SELECT * FROM boom_chat WHERE post_message COLLATE UTF8MB4_UNICODE_CI LIKE '%{$quiz['answer']}%' AND post_date > '$time' ORDER BY post_date ASC LIMIT 1");
	
	if($check_answer->num_rows > 0){
		$winner_details = $check_answer->fetch_array(MYSQLI_BOTH);
		$user = userDetails($winner_details['user_id']);
		postWinner($user);
		die();
	}
	else {
		if($pass == 5){
			postHint();
			$pass++;
			mysqli_close($mysqli);
		}
		else if($pass == 10){
			postFail();
			die();
		}
		else {
			$pass++;
			mysqli_close($mysqli);
		}
	}
}
?>