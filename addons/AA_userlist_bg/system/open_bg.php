<?php
$load_addons = 'AA_userlist_bg';
require_once('../../../system/config_addons.php');
$user = getMyListBg($data['user_id']);
if(!boomAllow($addons['addons_access'])){
	die();
}
?>
<div class="brow">
	<div class="bcell border_bottom">
		<div class="modal_top_menu">
			<div class="bcell_mid hpad15">
				<p class="label"><i class="fa fa-image bgrad24"></i> <?php echo $lang['list_bg']; ?></p>
			</div>
			<div class="modal_top_menu_empty"></div>
			<div class="cancel_modal cover_text modal_top_item"><i class="fa fa-times"></i></div>
		</div>
	</div>
</div>
<div class="pad10">
	<p class="label"><?php echo $lang['set_list_bg']; ?></p>
	<?php if($user['bg_file'] != ''){ ?>
		<div class="aa_ulbg_preview" style="background-image:url('addons/AA_userlist_bg/files/background/<?php echo htmlspecialchars($user['bg_file'], ENT_QUOTES, 'UTF-8'); ?>');"></div>
	<?php } ?>
	<input id="aa_ulbg_file" class="full_input" type="file" accept="image/png,image/jpeg,image/gif,image/webp">
	<button style="width:100%;margin:10px 0 0 0;" onclick="aaUploadMyListBg();" class="reg_button theme_btn"><i class="fa fa-upload"></i> <?php echo $lang['save']; ?></button>
	<button style="width:100%;margin:10px 0 0 0;" onclick="aaDeleteMyListBg();" class="reg_button delete_btn"><i class="fa fa-trash"></i> <?php echo $lang['delete_my_list_bg']; ?></button>
</div>
<script data-cfasync="false" type="text/javascript">
	aaUploadMyListBg = function(){
		var file = $('#aa_ulbg_file')[0].files[0];
		if(!file){
			callSaved(aaListBgError, 3);
			return;
		}
		var form = new FormData();
		form.append('file', file);
		form.append('upload_my_bg', 1);
		form.append('token', utk);
		$.ajax({
			url: 'addons/AA_userlist_bg/system/action.php',
			type: 'post',
			data: form,
			contentType: false,
			processData: false,
			cache: false,
			timeout: 15000,
			dataType: 'json',
			success: function(response){
				if(response && response.code == 1){
					aaListBgSetLocal(user_id, response.file);
					aaListBgApplyAll();
					callSaved(aaListBgSaved, 1);
					hideModal();
				}
				else {
					callSaved(aaListBgError, 3);
				}
			},
			error: function(){
				callSaved(aaListBgError, 3);
			}
		});
	}
	aaDeleteMyListBg = function(){
		$.post('addons/AA_userlist_bg/system/action.php', {
			delete_my_bg: 1,
			token: utk
		}, function(response){
			if(response && response.code == 1){
				aaListBgSetLocal(user_id, '');
				aaListBgApplyAll();
				callSaved(aaListBgRemoved, 1);
				hideModal();
			}
			else {
				callSaved(aaListBgError, 3);
			}
		}, 'json');
	}
</script>
