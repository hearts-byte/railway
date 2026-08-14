<?php
$load_addons = 'aps_box_xo';
require_once('../../../system/config_addons.php');
if(!boomAllow(9)){
	die();
}
?>
<style>
</style>
<?php echo elementTitle('aps_box_xo', 'loadLob(\'admin/setting_addons.php\');'); ?>
<div class="page_full">
	<div class="page_element">
		<div class="config_section">
			<div class="setting_element ">
				<p class="label"><?php echo $lang['limit_feature']; ?></p>
					<select id="set_Xo_access">
						<?php echo listRank($data['addons_access'], 1); ?>
					</select>
			</div>
			<button id="save_pasteit" onclick="saveRankXo();" type="button" class="tmargin10 reg_button theme_btn"><i class="fa fa-floppy-o"></i> <?php echo $lang['save']; ?></button>
		</div>
		<div class="config_section">
			<script data-cfasync="false">
				saveRankXo = function(){
					$.post('addons/aps_box_xo/system/action.php', {
						save: 1,
						set_Xo_access: $('#set_Xo_access').val(),
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
			</script>
		</div>
	</div>
</div>
