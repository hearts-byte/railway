<?php
$load_addons = basename(dirname(dirname(__FILE__)));
require_once('../../../system/config_addons.php');
define('ADDON', $load_addons);
if(!canManageAddons()){
	die();
}
echo elementTitle($load_addons, 'loadLob(\'admin/setting_addons.php\');');
?>
<div class="page_full">
    <div>
		<div class="tab_menu">
			<ul>
				<li class="tab_menu_item tab_selected" onclick="configAddons('<?php echo $load_addons; ?>');" data="<?php echo $load_addons; ?>" data-z="gift_list"> <?php echo $lang['frame_list']; ?></li>
				<li class="tab_menu_item" data="<?php echo $load_addons; ?>" data-z="gift_settings"> <?php echo $lang['settings']; ?></li>
			</ul>
		</div>
	</div>
	<div class="page_element">
    <div class="tpad15">
		<div id="<?php echo $load_addons; ?>">
            <div id="gift_list" class="tab_zone">
			    <div class="setting_element ">
			        <button onclick="gift_add_box();" type="button" class="clear_top reg_button theme_btn"><?php echo $lang['add']; ?></button>
					<br>
			        <div id="gift_zone" class="tpad15" style="position: relative;display: inline-block;">
			            <?php echo frames_list_admin();?>
			        </div>
			    </div>
			</div>
            <div id="gift_settings" class="tab_zone hide_zone ">
   			    <div class="setting_element ">
					<p class="label"><?php echo $lang['limit_feature']; ?></p>
					<select id="set_access">
						<?php echo listRank($addons['addons_access'], 1); ?>
					</select>
				</div>
   			    <div class="setting_element ">
					<p class="label"><?php echo $lang['frame_coins']; ?></p>
					<select id="settings_price">
						<?php echo listRank($addons['custom1'], 1); ?>
					</select>
				</div>
				<button onclick="save_frame_settings();" type="button" class="clear_top reg_button theme_btn"><?php echo $lang['save']; ?></button>
			</div>
        </div>
	</div>
	</div>
</div>
<div class="config_section">
<script data-cfasync="false" type="text/javascript">
$(document).ready(function(){
	$( ".select_gift " ).on('click', function() {
        var id  = $(this).data('id');
		adminEditFrame(id);
    });
});	

//gift
var waituplodlogoimg = 0;
add_frame = function(){
	var file_data = $("#set_file").prop("files")[0];
	if($("#set_file").val() === ""){
		callSaved(system.noFile, 3);
	} else {
		if(waituplodlogoimg == 0){
			waituplodlogoimg = 1;
			uploadIcon('avat_icon_gif', 1);
			var form_data = new FormData();
			form_data.append("file", file_data)
			form_data.append("add_frame", 1)
			form_data.append("add_rank", 1)
			form_data.append("add_method", 1)
			form_data.append("token", utk)
			form_data.append("add_price", $('#add_price').val())
			$.ajax({  
				url: "addons/<?php echo $load_addons; ?>/system/action.php",
				dataType: 'json',
				cache: false,
				contentType: false,
				processData: false,
				data: form_data,
				type: 'post',
				success: function(response){
					if(response == 2){
						callError(system.wrongFile);
					}
					else if(response == 1){
						callSuccess(system.saved);
						hideModal();
						configAddons('<?php echo $load_addons; ?>');
					} else {
						callError(system.error);
					}
					uploadIcon('avat_icon_gif', 2);
					waituplodlogoimg = 0;
				},
				error: function(){
					callError(system.error);
					uploadIcon('avat_icon_gif', 2);
					waituplodlogoimg = 0;
				}
			})
		}
		else {
			return false;
		}
	}
}
save_frame_settings = function(id) {
	$.post('addons/<?php echo $load_addons; ?>/system/action.php', {
		save_settings: 1,
		set_access: $('#set_access').val(),
		set_price: $('#settings_price').val(),
		}, function(response) {
			if(response){
			    callSuccess(system.saved);
			} else {
				callError(system.error);
			}
	});
}
delete_frame = function(id) {
	$.post('addons/<?php echo $load_addons; ?>/system/action.php', {
		delete_frame: id
		}, function(response) {
			if(response){
			    configAddons('<?php echo $load_addons; ?>');
			} else {
				callError(system.error);
			}
			hideModal();
	});
}
gift_add_box = function(id) {
	$.post('addons/<?php echo $load_addons; ?>/system/action.php', {
		gift_add_box: 1
		}, function(response) {
			showModal(response,400);
	});
}
adminEditFrame  = function(id) {
	$.post('addons/<?php echo $load_addons; ?>/system/action.php', {
		edit_frame: id
		}, function(response) {
			showModal(response,400);		
	});
}
save_frame = function(id) {
	var price = $('#set_price').val();
	var rank = $('#set_rank').val();
	var method = $('#set_method').val();
	var price = $('#set_price').val();
	$.post('addons/<?php echo $load_addons; ?>/system/action.php', {
		save_frame: id,
		save_price: price,
		save_rank: rank,
		save_method: method,
		}, function(response) {
		    callSuccess(system.saved);
			configAddons('<?php echo $load_addons; ?>');
			hideModal();
	});
}
</script>
 </div>