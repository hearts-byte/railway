<?php
$load_addons = 'AA_global_notify';
require_once('../../../system/config_addons.php');
$aa_gn_logs = aaGlobalNotifyRecentLogs(10);
?>
<?php echo elementTitle($lang['global_notify_send'] ?? 'Global Notify', 'loadLob(\'admin/setting_addons.php\');'); ?>
<div class="page_full">
	<div>
		<div class="tab_menu">
			<ul>
				<li class="tab_menu_item tab_selected" data="aagn_send" data-z="aagn_send_zone"><i class="fa fa-bullhorn"></i> <?php echo $lang['global_notify_send']; ?></li>
				<li class="tab_menu_item" data="aagn_setting" data-z="aagn_setting_zone"><i class="fa fa-cogs"></i> <?php echo $lang['settings']; ?></li>
			</ul>
		</div>
	</div>
	<div class="page_element">
		<div id="aagn_send" class="tpad15 tab_zone">
			<div id="aagn_send_zone">
				<div class="setting_element">
					<p class="label"><?php echo $lang['global_notify_message']; ?></p>
					<textarea id="aagn_message" maxlength="300" rows="4" style="width:100%;" placeholder="<?php echo $lang['global_notify_placeholder']; ?>"></textarea>
				</div>
				<div class="setting_element">
					<p class="label"><?php echo $lang['global_notify_publish_as']; ?></p>
					<select id="aagn_publish_as">
						<option value="system"><?php echo $lang['global_notify_as_system']; ?></option>
						<option value="self"><?php echo $lang['global_notify_as_self']; ?></option>
					</select>
				</div>
				<button onclick="aaGlobalNotifySendNow();" type="button" class="clear_top reg_button theme_btn"><i class="fa fa-paper-plane"></i> <?php echo $lang['global_notify_send']; ?></button>
			</div>

			<?php if(!empty($aa_gn_logs)){ ?>
			<div class="hpad15 tpad15">
				<p class="label bold"><?php echo $lang['global_notify_recent']; ?></p>
				<?php foreach($aa_gn_logs as $log){ ?>
				<div class="setting_element">
					<p class="sub_text">
						<b><?php echo $log['user_name']; ?></b>
						(<?php echo $log['publish_as'] == 'self' ? $lang['global_notify_as_self'] : $lang['global_notify_as_system']; ?>)
						— <?php echo $log['recipients']; ?> <?php echo $lang['global_notify_recipients']; ?>
					</p>
					<p class="text_micro sub_date"><?php echo displayDate($log['log_date']); ?></p>
					<p class="sub_text"><?php echo htmlspecialchars($log['message'], ENT_QUOTES, 'UTF-8'); ?></p>
				</div>
				<?php } ?>
			</div>
			<?php } ?>
		</div>

		<div id="aagn_setting" class="tpad15 tab_zone" style="display:none;">
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
	</div>

	<div class="config_section">
		<script data-cfasync="false" type="text/javascript">
			var aaGnSent  = <?php echo json_encode($lang['global_notify_sent']); ?>;
			var aaGnError = <?php echo json_encode($lang['global_notify_error']); ?>;
			var aaGnEmpty = <?php echo json_encode($lang['global_notify_empty']); ?>;
			var aaGnSaved = <?php echo json_encode($lang['save']); ?>;

			aaGlobalNotifySendNow = function() {
				var msg = $('#aagn_message').val().trim();
				if (msg === '') {
					callSaved(aaGnEmpty, 3);
					return;
				}
				$.post('addons/AA_global_notify/system/action.php', {
					global_notify_message: msg,
					global_notify_publish_as: $('#aagn_publish_as').val(),
					token: utk,
				}, function(response) {
					if (response > 0) {
						callSaved(aaGnSent, 1);
						loadLob('admin/setting_addons.php');
					} else {
						callSaved(aaGnError, 3);
					}
				});
			}

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
