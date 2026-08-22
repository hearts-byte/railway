<?php
$load_addons = 'stories';
require_once('../../../system/config_addons.php');
if (!boomAllow(9)) {
    die();
}
require_once __DIR__ . '/functions.php';
$s = stories_settings();
?>
<style>
</style>
<?php echo elementTitle('stories', 'loadLob(\'admin/setting_addons.php\');'); ?>
<div class="page_full">
	<div class="page_element">
		<div class="config_section">
			<div class="setting_element ">
				<p class="label"><?php echo $lang['limit_feature']; ?></p>
				<select id="set_stories_access">
					<?php echo listRank($s['access'], 1); ?>
				</select>
			</div>
			<div class="setting_element ">
				<p class="label">مدة بقاء الستوري (بالساعات)</p>
				<input type="number" id="set_stories_duration" min="1" value="<?php echo (int) $s['duration_hours']; ?>">
			</div>
			<div class="setting_element ">
				<p class="label">الحد الأقصى لطول النص</p>
				<input type="number" id="set_stories_max_text" min="1" value="<?php echo (int) $s['max_text_length']; ?>">
			</div>
			<div class="setting_element ">
				<p class="label">تفعيل النشر مقابل الذهب</p>
				<select id="set_stories_gold_enabled">
					<option value="1" <?php echo $s['gold_enabled'] ? 'selected' : ''; ?>><?php echo $lang['yes'] ?? 'نعم'; ?></option>
					<option value="0" <?php echo !$s['gold_enabled'] ? 'selected' : ''; ?>><?php echo $lang['no'] ?? 'لا'; ?></option>
				</select>
			</div>
			<div class="setting_element ">
				<p class="label">تكلفة النشر الافتراضية بالذهب</p>
				<input type="number" id="set_stories_gold_cost" min="0" value="<?php echo (int) $s['gold_cost']; ?>">
			</div>
			<button id="save_stories" onclick="saveStoriesSettings();" type="button" class="tmargin10 reg_button theme_btn"><i class="fa fa-floppy-o"></i> <?php echo $lang['save']; ?></button>
		</div>
		<div class="config_section">
			<script data-cfasync="false">
				saveStoriesSettings = function(){
					$.post('addons/stories/system/save_settings.php', {
						access: $('#set_stories_access').val(),
						duration_hours: $('#set_stories_duration').val(),
						max_text_length: $('#set_stories_max_text').val(),
						gold_enabled: $('#set_stories_gold_enabled').val(),
						gold_cost: $('#set_stories_gold_cost').val(),
						token: utk,
						}, function(response) {
							response = typeof response === 'string' ? JSON.parse(response) : response;
							if (response && response.success) {
								callSaved(system.saved, 1);
							} else {
								callSaved(system.error, 3);
							}
					});
				}
			</script>
		</div>
	</div>
</div>
