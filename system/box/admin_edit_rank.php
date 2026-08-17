<?php
require('../config_session.php');
if(!isset($_POST['target']) || !boomAllow($setting['can_rank'])){
	die();
}
$target = escape($_POST['target'], true);
$user = userDetails($target);
if(!canRankUser($user)){
	echo 0;
	die();
}
?>
<div class="modal_content">
	<p class="label"><?php echo $lang['user_rank']; ?></p>
	<select id="profile_rank" onchange="changeRank(this, <?php echo $user['user_id']; ?>);">
		<?php echo changeRank($user); ?>
	</select>
	<?php if(useLevel()){ ?>
	<div class="setting_element">
		<p class="label"><?php echo $lang['user_level']; ?>: <?php echo $user['user_level']; ?></p>
		<button type="button" class="reg_button theme_btn" onclick="levelUpUser(<?php echo $user['user_id']; ?>);"><?php echo $lang['level_up']; ?></button>
	</div>
	<?php } ?>
</div>