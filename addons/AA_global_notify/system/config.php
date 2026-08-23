<?php
$load_addons = 'AA_global_notify';
require_once('../../../system/config_addons.php');
?>
<?php echo elementTitle($lang['settings'] ?? 'Global Notify', 'loadLob(\'admin/setting_addons.php\');'); ?>
<div class="page_full">
	<div class="page_element">
		<div id="aagn_setting_zone">
			<div class="setting_element">
				<p class="label"><?php echo $lang['limit_feature']; ?></p>
				<select id="set_addon_access">
					<?php echo listRank($addons['addons_access'], 1); ?>
				</select>
			</div>
			<button onclick="aaGlobalNotifySaveSettings();" type="button" class="clear_top reg_button theme_btn"><i class="fa fa-floppy-o"></i> <?php echo $lang['save']; ?></button>
		</div>
	</div>

	<div class="config_section">
		<script data-cfasync="false" type="text/javascript">
			var aaGnError = <?php echo json_encode($lang['global_notify_error']); ?>;
			var aaGnSaved = <?php echo json_encode($lang['save']); ?>;

			aaGlobalNotifySaveSettings = function() {
				$.post('addons/AA_global_notify/system/action.php', {
					set_addon_access: $('#set_addon_access').val(),
					token: utk,
				}, function(response) {
					if (response == 1) {
						callSaved(aaGnSaved, 1);
					} else {
						callSaved(aaGnError, 3);
					}
				});
			}
		</script>
	</div>
</div>
