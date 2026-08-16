<?php
/*===============================================*
 |                                               |
 |   Developer        :  [Dedar]                 |
 |                                               |
 |   Addon name       :  [bet_gold]               |
 |                                               |
 |   Version          :  [1.0]                   |
 |                                               |
 |   Codychat version :  [Codychat 6]            |
 |                                               |
 |   Store            :  [Dedar Store]           |
 *===============================================*/
$load_addons = 'bet_gold'; // تعیین نام افزونه برای بارگذاری
require('../../../system/config_addons.php'); // بارگذاری تنظیمات افزونه
if(!canManageAddons()){ // بررسی دسترسی کاربر به مدیریت افزونه
    die(); // اگر دسترسی نداشته باشد، اجرای کد متوقف می‌شود
}
?>
<?php echo elementTitle($addons['addons'], 'loadLob(\'admin/setting_addons.php\');'); ?>

<!-- Start of page -->
<div class="page_full">
    <div>
        <div class="tab_menu">
            <ul>
                <li class="tab_menu_item tab_selected" data="codeit" data-z="codeit_settings">
                    <?php echo $lang['settings']; // عنوان تب تنظیمات ?>
                </li>
            </ul>
        </div>
    </div>

    <!-- Panel Settings -->
    <div class="page_element">
        <div class="tpad15">
            <div id="codeit">
                <div id="codeit_settings" class="tab_zone">
                    <!-- Limit Feature -->
                    <div class="setting_element">
                        <p class="label"><?php echo $lang['limit_feature']; // عنوان ویژگی ?></p>
                       
                        <select id="set_bet_gold_access">
                            
                            <?php echo listRank($addons['addons_access']); // فهرست دسترسی‌ها ?>
                        </select>
                     
                    </div>

                   
               

                    <!-- Developer Name -->
                    <div class="setting_element">
                        <p class="label">Name Developer</p>
                        <input id="develname" class="full_input" value="<?php echo $addons['custom2']; // نام توسعه‌دهنده ?>" placeholder="Write the name of the developer here" type="text"/>
                    </div>

                    <!-- Save Button -->
                    <button onclick="savebet_gold();" type="button" class="tmargin10 reg_button theme_btn">
                        <i class="fa fa-floppy-o"></i> <?php echo $lang['save']; // دکمه ذخیره ?>
                    </button>
                    <p style="text-align:right"><?php echo $lang['TitleTimeMenu']; ?></p> 
                </div>
            </div>
        </div>
        <div class="config_section">
            <script data-cfasync="false" type="text/javascript">
            // تابع ذخیره تنظیمات با استفاده از AJAX
            savebet_gold = function() {
                $.post('addons/bet_gold/system/action.php', { // ارسال درخواست POST به action.php
                    set_bet_gold_access: $('#set_bet_gold_access').val(), // مقدار انتخاب شده برای دسترسی
                    develname: $('#develname').val(), // نام توسعه‌دهنده
                   
                }, function(response) {
                    if (response == 5) {
                        callSuccess(system.saved); // نمایش پیام موفقیت
                    } else if (response == 'Invalid key!') { 
                        callError('The name is wrong, write the name of the Addons developer'); // نمایش خطای نام نامعتبر
                    } else {
                        callError(system.error); // نمایش خطای عمومی
                    }
                });
            }
            </script>
        </div>
    </div>
</div>
