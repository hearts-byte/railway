<?php
$load_addons = 'aps_song_profile';
require_once('../../../system/config_addons.php');
if(!boomAllow(11)){
	die();
}
echo elementTitle($addons['addons'], 'loadLob(\'admin/setting_addons.php\');');
?>
<div class="page_full">
	<div class="page_element">
		<div class="tpad15">
			<div class="tab_zone">
				<div class="setting_element">
					<p class="label"><?php echo $lang['limit_feature']; ?></p>
					<select id="set_addon_access">
						<?php echo listRank($addons['addons_access'], 1); ?>
					</select>
				</div>
				<div class="setting_element">
				    <p class="label"><?php echo $lang['max_file']; ?></p>
				    <select id="set_size">
					    <?php echo optionCount($addons["custom1"], 1, 50, 1, "mb"); ?>
                    </select>
				</div>
			</div>
			<button onclick="savePmusicSettings();" type="button" class="clear_top reg_button theme_btn"><?php echo $lang['save']; ?></button>
		</div>
		<div class="config_section">
			<script data-cfasync="false" type="text/javascript">
				savePmusicSettings = function(){
					$.post('addons/aps_song_profile/system/action.php', {
						save_settings: 1,
						set_addon_access: $('#set_addon_access').val(),
						set_size: $('#set_size').val(),
						token: utk,
						}, function(response) {
							if(response == 1){
								callSaved(system.saved, 1);
							}
							else{
								callSaved(system.error, 3);
							}
					});	
				}
			</script>
		</div>
	</div>
</div>
