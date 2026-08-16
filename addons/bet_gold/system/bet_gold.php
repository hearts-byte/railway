<?php
$load_addons = 'bet_gold';
require('../../../system/config_addons.php');

?>
<div class="setting_element">
    <img style="width: 100%;padding: 0px 53px;height: 168px;margin: 5px 0px;" src="addons/bet_gold/files/icon.png" alt="">
  <p style="text-align:center" class="label">
     Balacne <img style="width:13px;" src="/addons/bet_gold/files/img/gold.svg" alt="">  
        <?php echo $data['user_gold']; ?>
    </p>
    <input id="changegoldme" class="full_input" value="" placeholder="Enter gold bet" type="number" min="1" max="5000"/>
    <button onclick="flipGoldGame(<?php echo $data['user_id']; ?>);" type="button" class="tmargin10 reg_button theme_btn close_modal">Bet</button>
    <button class="reg_button default_btn cancel_modal"><?php echo $lang['cancel']; ?></button>
</div>
