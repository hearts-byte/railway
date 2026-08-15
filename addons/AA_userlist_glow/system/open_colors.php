<?php
$load_addons = 'AA_userlist_glow';
require_once('../../../system/config_addons.php');
$user = getMyGlow($data['user_id']);
if (!boomAllow($addons['addons_access'])) {
	die();
}
?>
<style>
	.aa_glow_picker_wrap { display:flex; align-items:center; justify-content:center; margin-top:8px; }
	.aa_glow_picker { width:100%; height:48px; padding:0; border:0; border-radius:8px; background:transparent; cursor:pointer; }
	.aa_glow_preview { height:48px; margin-top:10px; border-radius:8px; border:1px solid rgba(255,255,255,.18); box-shadow:0 0 15px var(--aa-glow-preview), 0 0 6px var(--aa-glow-preview) inset; }
</style>
<div class="brow">
	<div class="bcell border_bottom">
		<div class="modal_top_menu">
			<div class="bcell_mid hpad15">
				<p class="label"><i class='fa fa-paint-brush bgrad24'></i> <?php echo $lang['list_glow']; ?></p>
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
	<p class="label"><?php echo $lang['set_list_glow']; ?></p>
	<div class="aa_glow_picker_wrap">
		<input type="color" id="name_glow_color_user_pick" class="aa_glow_picker" value="<?php echo htmlspecialchars($user['glow_color'] != '' ? substr($user['glow_color'], 0, 7) : '#3f6fff', ENT_QUOTES, 'UTF-8'); ?>"/>
		<input type="hidden" id="name_glow_color_user" value="<?php echo htmlspecialchars($user['glow_color'] != '' ? substr($user['glow_color'], 0, 7) : '#3f6fff', ENT_QUOTES, 'UTF-8'); ?>"/>
	</div>
	<div id="aa_glow_preview_user" class="aa_glow_preview"></div>
	<button style="width:100%;margin: 10px 0 0 0;" onclick="exSetNewUserListGlow();" class="reg_button theme_btn"><i class="fa fa-save"></i> <?php echo $lang['save']; ?></button>
	<button style="width:100%;margin: 10px 0 0 0;" onclick="exDeleteUserListGlow();" class="reg_button delete_btn"><i class="fa fa-trash"></i> <?php echo $lang['delete_my_glow']; ?></button>
</div>
<script data-cfasync="false" type="text/javascript">
	aaGlowCleanHexUser = function(color) {
		color = String(color || '').trim();
		if(/^#[0-9A-Fa-f]{6}$/.test(color)){
			return color.toUpperCase();
		}
		return '';
	}
	aaGlowPreviewUser = function(color) {
		var clean = aaGlowCleanHexUser(color);
		if(clean === ''){
			clean = '#3F6FFF';
		}
		$('#name_glow_color_user').val(clean);
		$('#name_glow_color_user_pick').val(clean);
		$('#aa_glow_preview_user').css('--aa-glow-preview', clean);
	}
	$('#name_glow_color_user_pick').on('input change', function(){
		aaGlowPreviewUser($(this).val());
	});
	aaGlowPreviewUser($('#name_glow_color_user').val());

	exSetNewUserListGlow = function() {
		var color = aaGlowCleanHexUser($('#name_glow_color_user').val());
		if(color === ''){
			callSaved(aaGlowError, 3);
			return;
		}
		$.post('addons/AA_userlist_glow/system/action.php', {
			user_list_glow_user: color,
			token: utk,
		}, function(response) {
			if (response == 1) {
				aaGlowSetLocal(user_id, color);
				aaGlowRefresh(aaGlowApplyAll);
				callSaved(aaGlowSaved, 1);
				hideModal();
			} else {
				callSaved(aaGlowError, 3);
			}
		});
	}
	exDeleteUserListGlow = function() {
		$.post('addons/AA_userlist_glow/system/action.php', {
			del_user_list_glow_user: 1,
			token: utk,
		}, function(response) {
			if (response == 1 || response == 2) {
				aaGlowSetLocal(user_id, '');
				aaGlowApplyAll();
				callSaved(aaGlowRemoved, 1);
				hideModal();
			} else {
				callSaved(aaGlowError, 3);
			}
		});
	}
</script>
