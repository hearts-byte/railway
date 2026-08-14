<?php
include(addonsLang('quizbot'));
?>
<script data-cfasync="false">
var quizbotVersion = <?php echo $setting['version']; ?>;

quizLeaderboard = function(){
	$.ajax({
		url: "addons/quizbot/system/leaderboard.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
		},
		beforeSend: function(){
			prepareLeft(340);
		},
		success: function(response){
			showLeftPanel(response.content, 340, response.title);
		},
		error: function(){
			callError(system.error);
		}
	});
}

$(document).ready(function(){
	if(quizbotVersion > 6){
		appLeadMenu('addons/quizbot/files/top_quiz.svg', '<?php echo $lang['quiz_leaderboard']; ?>', 'quizLeaderboard();');
	}
	else {
		appLeftMenu('question-circle', '<?php echo $lang['quiz_leaderboard']; ?>', 'quizLeaderboard();');
	}	
	boomAddCss('addons/quizbot/files/quizbot.css');
});
</script>