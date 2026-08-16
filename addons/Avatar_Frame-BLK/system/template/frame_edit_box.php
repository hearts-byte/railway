<div class="pad15">
	<div class="modal_content">
    <div class="centered_element">
			<div class="tpad20">
				<img class="gift_selected" id="gift_selected" src="addons/<?php echo BOOM_ADDONS; ?>/files/frames/<?php echo $boom['tumb']; ?>">
			</div>
			<div class="setting_element ">
				<p class="label"><?php echo $lang['limit_feature']; ?></p>
				<select id="set_rank">
					<?php echo listRank($boom['rank'], 1); ?>
				</select>
			</div>
			<div class="setting_element ">
				<p class="label"><?php echo $lang['payment_method']; ?></p>
				<select id="set_method">
					<option value="1" <?php echo selCurrent($boom['method'], 1); ?>><?php echo $lang['gold']; ?></option>
					<option value="2" <?php echo selCurrent($boom['method'], 2); ?>><?php echo $lang['ruby']; ?></option>
				</select>
			</div>
			<div class="setting_element ">
				<p class="label"><?php echo $lang['amount_required']; ?></p>
                <input id="set_price" class="full_input"  type="text" value="<?php echo $boom['price']; ?>"/>
			</div>
		</div>
		<div class="modal_control bpad20">
			<button onclick="save_frame(<?php echo $boom['id']; ?>);" class="reg_button theme_btn"><?php echo $lang['save']; ?></button>
			<button class="reg_button default_btn cancel_modal"><?php echo $lang['cancel']; ?></button>
			<button onclick="delete_frame(<?php echo $boom['id']; ?>);" class="button fright rtl_fleft delete_btn"><i class="fa fa-trash-can"></i></button>
		</div>
	</div>
</div>