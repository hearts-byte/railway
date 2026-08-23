<?php
$load_addons = 'AA_global_notify';
require_once('../../../system/config_addons.php');
if(!boomAllow($addons['addons_access'])){
	die();
}
?>
<div class="brow">
	<div class="bcell border_bottom">
		<div class="modal_top_menu">
			<div class="bcell_mid hpad15">
				<p class="label"><i class="fa fa-bullhorn bgrad24"></i> <?php echo $lang['open_global_notify']; ?></p>
			</div>
			<div class="modal_top_menu_empty">
			</div>
			<div class="cancel_modal cover_text modal_top_item">
				<i class="fa fa-times"></i>
			</div>
		</div>
	</div>
</div>
<div class="pad10">
	<p class="label"><?php echo $lang['global_notify_message']; ?></p>
	<textarea id="aagn_modal_message" maxlength="300" rows="4" style="width:100%;" placeholder="<?php echo $lang['global_notify_placeholder']; ?>"></textarea>
	<p class="label tpad10"><?php echo $lang['global_notify_publish_as']; ?></p>
	<select id="aagn_modal_publish_as" style="width:100%;">
		<option value="system"><?php echo $lang['global_notify_as_system']; ?></option>
		<option value="self"><?php echo $lang['global_notify_as_self']; ?></option>
	</select>
	<button style="width:100%;margin: 10px 0 0 0;" onclick="aaGlobalNotifySendModal();" class="reg_button theme_btn"><i class="fa fa-paper-plane"></i> <?php echo $lang['global_notify_send']; ?></button>
</div>
<script data-cfasync="false" type="text/javascript">
	aaGlobalNotifySendModal = function() {
		var msg = $('#aagn_modal_message').val().trim();
		if (msg === '') {
			callSaved(<?php echo json_encode($lang['global_notify_empty']); ?>, 3);
			return;
		}
		$.post('addons/AA_global_notify/system/action.php', {
			global_notify_message: msg,
			global_notify_publish_as: $('#aagn_modal_publish_as').val(),
			token: utk,
		}, function(response) {
			if (response > 0) {
				callSaved(<?php echo json_encode($lang['global_notify_sent']); ?>, 1);
				hideModal();
			} else {
				callSaved(<?php echo json_encode($lang['global_notify_error']); ?>, 3);
			}
		});
	}
</script>
