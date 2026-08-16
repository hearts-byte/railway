<?php

// Roll the dice
// Custom plugin by JOOJ
// for Cody chat

function rollTheDice($user){
	global $data, $mysqli, $lang, $addons;

	$id = $data['user_id'];
	$exist = $mysqli->query("SELECT user_roomid FROM boom_users WHERE user_id = '$user'");
	if($exist->num_rows > 0){
		$userRoom = $exist->fetch_assoc();
		if ($userRoom['user_roomid'] != $data['user_roomid']){
			echo 1;
			die();
		}
	}else{
		die();
	}
	
	$scoreCount = $mysqli->query("SELECT myScore, userScore FROM (SELECT COUNT(*) as myScore FROM boom_dicegame_scores WHERE winner = {$data['user_id']} AND looser = $user) as myScores, (SELECT COUNT(*) as userScore FROM boom_dicegame_scores WHERE winner = $user AND looser = {$data['user_id']}) as userScores");
	if($scoreCount->num_rows > 0){
		$score = $scoreCount->fetch_assoc();
	}
	
	$myDice = rand(1,6);
	$userDice = rand(1,6);
	
	$userDetails = userDetails($user);
	$username = $userDetails['user_name'];
	
	$diceText1 = str_replace('%user%', '<b>' . $data['user_name'] . '</b>', escape($lang['rolled_the_dice']));
	$diceText2 = str_replace('%user%', '<b>' . $username . '</b>', escape($lang['rolled_the_dice']));
	
	$rolls = $diceText1 . ': <img src="addons/roll_the_dice/files/images/' . $myDice . '.png" height="15" width="15"><br>' . $diceText2 . ': <img src="addons/roll_the_dice/files/images/' . $userDice . '.png" height="15" width="15"><br><br>';
	
	$winner = '';
	$looser = '';
	$same = 0;
	if ($myDice > $userDice){
		$result = str_replace('%user%', '<b>' . $data['user_name'] . '</b>', escape($lang['dice_winner'])) . ' <i style="color:goldenrod;" class="i_btm fa fa-trophy"></i>';
		$score['myScore']++;
		$winner = $data['user_id'];
		$looser = $user;
	}else if($myDice < $userDice){
		$result = str_replace('%user%', '<b>' . $username . '</b>', escape($lang['dice_winner'])) . ' <i style="color:goldenrod;" class="i_btm fa fa-trophy"></i>';
		$score['userScore']++;
		$winner = $user;
		$looser = $data['user_id'];
	}else{
		$result = $lang['dice_same_number'];
		$same = 1;
	}
	
	$scores = '';
	if ($addons['custom3'] == 1){
		if ($myScore >= $userScore){
			$scores = '<br><br>' . $lang['score']. ': ' . $score['myScore'] . ' - ' . $score['userScore'] . ' (' . $data['user_name'] . ' - ' . $username . ')';
		}else{
			$scores = '<br><br>' . $lang['score']. ': ' . $score['userScore'] . ' - ' . $score['myScore'] . ' (' . $username . ' - ' . $data['user_name'] . ')';

		}
	}

	if ($same == 0){
		$mysqli->query("INSERT INTO boom_dicegame_scores (winner, looser) VALUES ($winner, $looser)");
	}
	
	$message = $rolls . $result . $scores;
	userPostChat($message);

	return true;
}
?>