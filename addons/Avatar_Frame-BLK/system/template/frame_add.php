<div class="pad15">
	<div class="modal_content">
	<div class="centered_element">
			<div class="tpad20">
				<div class="setting_element">
                   <p class="label"><?php echo $lang['frame_file']; ?></p>
                    <input id="set_file" class="full_input" type="file"/>
                </div> 
			</div>
			<div class="setting_element ">
				<p class="label"><?php echo $lang['limit_feature']; ?></p>
				<select id="add_rank">
					<?php echo listRank(1, 1); ?>
				</select>
			</div>
			<div class="setting_element ">
				<p class="label"><?php echo $lang['payment_method']; ?></p>
				<select id="add_method">
					<option value="1" <?php echo selCurrent('', 1); ?>><?php echo $lang['gold']; ?></option>
					<option value="2" <?php echo selCurrent('', 2); ?>><?php echo $lang['ruby']; ?></option>
				</select>
			</div>
			<div class="setting_element ">
				<p class="label"><?php echo $lang['amount_required']; ?></p>
				<input id="add_price" class="full_input"  type="text" value="10"/>
			</div>
		</div>
	<div class="modal_control centered_element bpad20">
		<button onclick="add_frame();" class="reg_button theme_btn"><?php echo $lang['save']; ?></button>
		<button class="reg_button default_btn cancel_modal"><?php echo $lang['cancel']; ?></button>
	</div>
	</div>
</div>