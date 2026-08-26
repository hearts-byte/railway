<?php
if (!defined('BOOM')) { die('Access Denied'); }
require_once __DIR__ . '/helpers.php';

if (!stories_is_logged_in()) { return; }

$stories_me = stories_get_user(stories_current_user_id());
$stories_me_avatar = htmlspecialchars($stories_me['avatar'] ?? stories_avatar_url(''));
?>
<!-- ==================== شريط الستوريات العلوي ==================== -->
<div id="stories-bar" class="stories-bar">
    <div class="stories-item stories-add-item" id="stories-add-item" title="إضافة ستوري">
        <div class="stories-avatar-ring stories-ring-add" id="stories-my-ring">
            <img src="<?php echo $stories_me_avatar; ?>" alt="" id="stories-my-avatar">
            <span class="stories-plus" id="stories-add-btn">+</span>
        </div>
        <span class="stories-name">إضافتك</span>
    </div>
    <div id="stories-bar-list" class="stories-bar-list">
        <!-- تُعبّأ ديناميكياً عبر script.js -->
    </div>
</div>

<!-- ==================== نافذة إنشاء ستوري ==================== -->
<div id="stories-create-modal" class="stories-overlay" style="display:none;">
    <div class="stories-modal aagn-style-modal">

        <div class="brow">
            <div class="bcell border_bottom">
                <div class="modal_top_menu">
                    <div class="bcell_mid hpad15">
                        <p class="label"><i class="fa fa-image bgrad24"></i> ستوري جديدة</p>
                    </div>
                    <div class="modal_top_menu_empty"></div>
                    <div class="cancel_modal cover_text modal_top_item stories-close" data-close="create">
                        <i class="fa fa-times"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="pad10">

            <div class="modal_menu hpad15 centered_element">
                <ul>
                    <li class="modal_menu_item modal_selected stories-tab" data-tab="text"><i class="fa fa-font"></i> نص</li>
                    <li class="modal_menu_item stories-tab" data-tab="image"><i class="fa fa-image"></i> صورة</li>
                    <li class="modal_menu_item stories-tab" data-tab="video"><i class="fa fa-video-camera"></i> فيديو</li>
                </ul>
            </div>

            <form id="stories-create-form">
                <input type="hidden" name="type" id="stories-type-input" value="text">

                <div class="stories-pane" data-pane="text">
                    <div id="stories-text-preview" class="stories-text-preview" style="background:#6c5ce7;color:#ffffff;">
                        اكتب نص الستوري هنا...
                    </div>
                    <p class="label tpad10">النص</p>
                    <textarea name="text" id="stories-text-input" maxlength="300" style="width:100%;" placeholder="اكتب شيئاً..."></textarea>
                    <div class="stories-colors">
                        <span>الخلفية:</span>
                        <input type="color" name="bg_color" id="stories-bg-color" value="#6c5ce7">
                        <span>النص:</span>
                        <input type="color" name="text_color" id="stories-text-color" value="#ffffff">
                    </div>
                </div>

                <div class="stories-pane" data-pane="image" style="display:none;">
                    <p class="label tpad10">اختر صورة</p>
                    <div class="lite_olay proplayer_btn stories-pick-btn">
                        <i class="fa fa-image"></i> <span id="stories-image-filename">اختيار صورة من الجهاز</span>
                        <input type="file" name="media" id="stories-image-input" class="up_input" accept="image/*">
                    </div>
                    <img id="stories-image-preview" class="stories-media-preview" style="display:none;">
                </div>

                <div class="stories-pane" data-pane="video" style="display:none;">
                    <p class="label tpad10">اختر فيديو</p>
                    <div class="lite_olay proplayer_btn stories-pick-btn">
                        <i class="fa fa-video-camera"></i> <span id="stories-video-filename">اختيار فيديو من الجهاز</span>
                        <input type="file" name="media" id="stories-video-input" class="up_input" accept="video/*">
                    </div>
                    <video id="stories-video-preview" class="stories-media-preview" style="display:none;" controls></video>
                </div>

                <button type="submit" style="width:100%;margin:10px 0 0 0;" class="reg_button theme_btn stories-btn-submit"><i class="fa fa-paper-plane"></i> نشر الستوري</button>
            </form>

        </div>
    </div>
</div>

<!-- ==================== عارض الستوري بملء الشاشة ==================== -->
<div id="stories-viewer" class="stories-viewer" style="display:none;">
    <div class="stories-progress-row" id="stories-progress-row"></div>

    <div class="stories-viewer-head">
        <img id="stories-viewer-avatar" src="" class="stories-viewer-avatar">
    <div class="stories-viewer-head">
        <img id="stories-viewer-avatar" src="" class="stories-viewer-avatar">
        <span id="stories-viewer-username" class="stories-viewer-username"></span>
        <span id="stories-viewer-time" class="stories-viewer-time"></span>
        <button type="button" id="stories-delete-btn" class="stories-delete-btn" style="display:none;" title="حذف الستوري"><i class="fa fa-trash"></i></button>
        <button type="button" id="stories-viewer-close" class="stories-close">×</button>
    </div>

    <div class="stories-viewer-body" id="stories-viewer-body">
        <div class="stories-nav-zone stories-nav-prev" id="stories-nav-prev"></div>
        <div class="stories-nav-zone stories-nav-next" id="stories-nav-next"></div>
        <div id="stories-media-container" class="stories-media-container"></div>
    </div>

    <div class="stories-viewer-footer">
        <button type="button" id="stories-viewers-btn" class="stories-viewers-btn" style="display:none;">
            👁 <span id="stories-views-count">0</span> مشاهدة
        </button>
        <div class="stories-reactions-row" id="stories-reactions-row">
            <button type="button" class="stories-reaction-icon" data-emoji="❤️">❤️</button>
            <button type="button" class="stories-reaction-icon" data-emoji="😂">😂</button>
            <button type="button" class="stories-reaction-icon" data-emoji="😮">😮</button>
            <button type="button" class="stories-reaction-icon" data-emoji="😢">😢</button>
            <button type="button" class="stories-reaction-icon" data-emoji="👏">👏</button>
            <button type="button" class="stories-reaction-icon" data-emoji="🔥">🔥</button>
        </div>
    </div>

    <!-- قائمة المشاهدين (تظهر فقط لصاحب الستوري) -->
    <div id="stories-viewers-panel" class="stories-viewers-panel" style="display:none;">
        <div class="stories-viewers-head">
            <span>المشاهدون</span>
            <button type="button" class="stories-close" id="stories-viewers-close">×</button>
        </div>
        <div id="stories-viewers-list" class="stories-viewers-list"></div>
    </div>
</div>
