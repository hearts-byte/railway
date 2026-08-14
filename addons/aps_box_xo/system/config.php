<?php
$load_addons = 'aps_box_xo';
require_once('../../../system/config_addons.php');
if(!boomAllow(9)){
	die();
}
?>
<style>
</style>
<?php echo elementTitle($data['addons'], 'loadLob(\'admin/setting_addons.php\');'); ?>
<div class="page_full">
	<div class="page_element">
		<div class="config_section">
			<div class="setting_element ">
				<p class="label"><?php echo $lang['limit_feature']; ?></p>
					<select id="set_Like_access">
						<?php echo listRank($data['addons_access'], 1); ?>
					</select>
			</div>
			<button id="save_pasteit" onclick="saveRankLike();" type="button" class="tmargin10 reg_button theme_btn"><i class="fa fa-floppy-o"></i> <?php echo $lang['save']; ?></button>
		</div>
		<div class="config_section">
			<script data-cfasync="false">
				saveRankLike = function(){
					$.post('addons/AA_profile_like/system/action.php', {
						save: 1,
						set_Like_access: $('#set_Like_access').val(),
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
