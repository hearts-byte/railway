<?php
$load_addons = 'AA_userlist_glow';
require_once('../../../system/config_addons.php');
?>
<?php echo elementTitle($data['addons'], 'loadLob(\'admin/setting_addons.php\');'); ?>
<div class="page_full">
    <div>
        <div class="tab_menu">
            <ul>
                <li class="tab_menu_item tab_selected" data="korsy" data-z="korsy_setting"><i class="fa fa-cogs"></i> <?php echo $lang['settings']; ?></li>
            </ul>
        </div>
    </div>
    <div class="page_element">
        <div id="korsy" class="tpad15">
            <div id="korsy_setting" class="tab_zone">
                <div class="setting_element ">
                    <p class="label"><?php echo $lang['limit_feature']; ?></p>
                    <select id="set_addon_access">
                        <?php echo listRank($addons['addons_access'], 1); ?>
                    </select>
                </div>
                <div class="setting_element ">
                    <p class="label"><?php echo $lang['limit_feature_staff']; ?></p>
                    <select id="set_addon_access_staff">
                        <?php echo listRank($addons['custom1'], 1); ?>
                    </select>
                </div>
                <button onclick="saveSettings();" type="button" class="clear_top reg_button theme_btn"><i class="fa fa-floppy-o"></i> <?php echo $lang['save']; ?></button>
            </div>
        </div>
        <div class="config_section">
            <script data-cfasync="false" type="text/javascript">
                var aaGlowConfigSaved = 'Ustawienia zostaly zapisane';
                var aaGlowConfigError = 'Nie udalo sie zapisac ustawien';
                saveSettings = function() {
                    $.post('addons/AA_userlist_glow/system/action.php', {
                        set_addon_access: $('#set_addon_access').val(),
                        set_addon_access_staff: $('#set_addon_access_staff').val(),
                        token: utk,
                    }, function(response) {
                        if (response == 1) {
                            callSaved(aaGlowConfigSaved, 1);
                        } else {
                            callSaved(aaGlowConfigError, 3);
                        }
                    });
                }
            </script>
        </div>
    </div>
</div>
