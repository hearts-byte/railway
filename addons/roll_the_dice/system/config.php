<?php

// Roll the dice
// Custom plugin by JOOJ
// for Cody chat


$load_addons = 'roll_the_dice';
require_once('../../../system/config_addons.php');

if(!boomAllow(10)){
	die();
}
?>
<style>
</style>
<?php echo elementTitle($addons['addons'], 'loadLob(\'admin/setting_addons.php\');'); ?>

<div class="page_full">
	<div>
		<div class="tab_menu">
			<ul>
				<li class="tab_menu_item tab_selected" data="adnoy" data-z="dice_game_setting"><i class="fa fa-cogs"></i> <?php echo $lang['settings']; ?></li>
				<li class="tab_menu_item" data="adnoy" data-z="dice_game_help"><i class="fa fa-question-circle"></i> <?php echo $lang['help']; ?></li>
			</ul>
		</div>
	</div>
	<div class="page_element">
		<div class="tpad15">
			<div id="adnoy">
				<div id="dice_game_help" class="hide_zone tab_zone no_rtl">
					<div class="docu_box">
						<div class="docu_head border_bottom sub_list">
							Installation
						</div>
						<div class="docu_content">
							<div class="docu_description">
								Save your setings and then you are ready to play!
							</div>
						</div>
					</div>
					<div class="docu_box">
						<div class="docu_head border_bottom sub_list">
							How to play
						</div>
						<div class="docu_content">
							<div class="docu_description">
								<p>
								Choose a user &gt; <img src="addons/roll_the_dice/files/images/dice.png" height="13" width="13"> Roll the dice
								<br><br>
								The user who gets the highest number wins the round.
								</p>		
							</div>
						</div>
					</div>
				</div>
				<div id="dice_game_setting" class="tab_zone">
					<div class="setting_element ">
						<p class="label"><?php echo $lang['status']; ?></p>
						<select id="set_dice_game_status">
							<?php echo onOff($addons['custom2']); ?>
						</select>
					</div>
					<div class="setting_element ">
						<p class="label"><?php echo $lang['show_scores']; ?></p>
						<select id="set_show_scores">
							<?php echo onOff($addons['custom3']); ?>
						</select>
					</div>
					<div class="setting_element ">
						<p class="label"><?php echo $lang['room']; ?></p>
						<select id="set_dice_game_room">
							<?php echo roomSelect($addons['custom1']); ?>
						</select>
					</div>
					<button id="save_dice_game" onclick="saveRollTheDice();" type="button" class="tmargin10 reg_button theme_btn"><i class="fa fa-floppy-o"></i> <?php echo $lang['save']; ?></button>
					<button id="reset_dice_game" onclick="resetScore();" type="button" class="tmargin10 reg_button default_btn"><i class="fa fa-eraser"></i> <?php echo $lang['reset_score']; ?></button>
				</div>
			</div>
		</div>
		<div class="config_section">
			<script data-cfasync="false">
			saveRollTheDice = function(){
				$.post('addons/roll_the_dice/system/action.php', {
					save: 1,
					status: $('#set_dice_game_status').val(),
					room: $('#set_dice_game_room').val(),
					show_scores: $('#set_show_scores').val(),
					token: utk,
					}, function(response) {
						if(response == 5){
							callSaved(system.saved, 1);
						}
						else{
							callSaved(system.error, 3);
						}
				});	
			}
			resetScore = function(){
				$.post('addons/roll_the_dice/system/reset_score.php', {
					token: utk,
					}, function(response) {
						showModal(response);
				});	
			}
			confirmRollTheDiceReset = function(){
				$.post('addons/roll_the_dice/system/action.php', {
					reset_score: 1,
					token: utk,
					}, function(response) {
						if(response == 1){
							callSaved(system.actionComplete, 1);
							hideModal();
						}
						else {
							callSaved(system.error, 3);
							hideModal();
						}
				});
			}
			</script>
		</div>
	</div>
</div>
