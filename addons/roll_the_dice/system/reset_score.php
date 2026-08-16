<?php

// Roll the dice
// Custom plugin by JOOJ
// for Cody chat

$load_addons = 'roll_the_dice';
require_once('../../../system/config_addons.php');
?>

<div class="pad20">
	<div class="centered_element">
		<p class="bpad15"><?php echo $lang['want_reset_game']; ?></p>
	</div>
	<div class="centered_element">
		<button onclick="confirmRollTheDiceReset();" class="reg_button theme_btn"><i class="fa fa-check"></i> <?php echo $lang['yes']; ?></button>
		<button class="reg_button cancel_modal default_btn"><?php echo $lang['cancel']; ?></button>
	</div>
</div>

