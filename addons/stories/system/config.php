<?php
$load_addons = 'stories';
require_once('../../../system/config_addons.php');
?>
<?php echo elementTitle(boomUnderClear(BOOM_ADDONS), 'loadLob(\'admin/setting_addons.php\');'); ?>
<div class="page_full">
	<div class="page_element">
		<div id="stories_setting" class="tpad15 tab_zone">
			<div id="stories_setting_zone">
				<div class="setting_element">
					<p class="label"><?php echo $lang['limit_feature']; ?></p>
					<select id="set_addon_access">
						<?php echo listRank($addons['addons_access'], 1); ?>
					</select>
				</div>
				<button onclick="storiesSaveSettings();" type="button" class="clear_top reg_button theme_btn"><i class="fa fa-floppy-o"></i> <?php echo $lang['save']; ?></button>
			</div>
		</div>
	</div>

	<div class="config_section">
		<script data-cfasync="false" type="text/javascript">
			var storiesSaved = <?php echo json_encode($lang['save']); ?>;

			storiesSaveSettings = function() {
				$.post('addons/stories/system/action.php', {
					do: 'set_access',
					set_addon_access: $('#set_addon_access').val(),
					token: utk,
				}, function(response) {
					if (response == 1) {
						callSaved(storiesSaved, 1);
					} else {
						callError();
					}
				});
			}
		</script>
	</div>
</div>
