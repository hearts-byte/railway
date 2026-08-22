<?php if (!defined('IN_SCRIPT')) { die('Access Denied'); }
$stories_me = stories_get_user(stories_current_user_id());
$stories_me_avatar = htmlspecialchars($stories_me['avatar'] ?? '/images/default_avatar.png');
?>
<!-- ==================== شريط الستوريات العلوي ==================== -->
<div id="stories-bar" class="stories-bar">
    <div class="stories-item stories-add-item" id="stories-add-btn" title="إضافة ستوري">
        <div class="stories-avatar-ring stories-ring-add">
            <img src="<?php echo $stories_me_avatar; ?>" alt="">
            <span class="stories-plus">+</span>
        </div>
        <span class="stories-name">إضافتك</span>
    </div>
    <div id="stories-bar-list" class="stories-bar-list">
        <!-- تُعبّأ ديناميكياً عبر script.js -->
    </div>
</div>

<!-- ==================== نافذة إنشاء ستوري ==================== -->
<div id="stories-create-modal" class="stories-overlay" style="display:none;">
    <div class="stories-modal">
        <div class="stories-modal-head">
            <span>ستوري جديدة</span>
            <button type="button" class="stories-close" data-close="create">×</button>
        </div>

        <div class="stories-tabs">
            <button type="button" class="stories-tab active" data-tab="text">نص</button>
            <button type="button" class="stories-tab" data-tab="image">صورة</button>
            <button type="button" class="stories-tab" data-tab="video">فيديو</button>
        </div>

        <form id="stories-create-form">
            <input type="hidden" name="type" id="stories-type-input" value="text">

            <div class="stories-pane" data-pane="text">
                <div id="stories-text-preview" class="stories-text-preview" style="background:#6c5ce7;color:#ffffff;">
                    اكتب نص الستوري هنا...
                </div>
                <textarea name="text" id="stories-text-input" maxlength="300" placeholder="اكتب شيئاً..."></textarea>
                <div class="stories-colors">
                    <span>الخلفية:</span>
                    <input type="color" name="bg_color" id="stories-bg-color" value="#6c5ce7">
                    <span>النص:</span>
                    <input type="color" name="text_color" id="stories-text-color" value="#ffffff">
                </div>
            </div>

            <div class="stories-pane" data-pane="image" style="display:none;">
                <input type="file" name="media" id="stories-image-input" accept="image/*">
                <img id="stories-image-preview" class="stories-media-preview" style="display:none;">
            </div>

            <div class="stories-pane" data-pane="video" style="display:none;">
                <input type="file" name="media" id="stories-video-input" accept="video/*">
                <video id="stories-video-preview" class="stories-media-preview" style="display:none;" controls></video>
            </div>

            <?php if (stories_settings()['gold_enabled']): ?>
            <div class="stories-gold-row">
                <label>
                    <input type="checkbox" id="stories-gold-toggle">
                    نشر مدفوع بالذهب
                </label>
                <input type="number" name="gold_cost" id="stories-gold-cost" min="1" value="10" style="display:none;">
            </div>
            <?php endif; ?>

            <button type="submit" class="stories-btn-submit">نشر الستوري</button>
        </form>
    </div>
</div>

<!-- ==================== عارض الستوري بملء الشاشة ==================== -->
<div id="stories-viewer" class="stories-viewer" style="display:none;">
    <div class="stories-progress-row" id="stories-progress-row"></div>

    <div class="stories-viewer-head">
        <img id="stories-viewer-avatar" src="" class="stories-viewer-avatar">
        <span id="stories-viewer-username" class="stories-viewer-username"></span>
        <span id="stories-viewer-time" class="stories-viewer-time"></span>
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
        <div class="stories-reply-row" id="stories-reply-row">
            <div class="stories-emojis">
                <span data-emoji="❤️">❤️</span>
                <span data-emoji="😂">😂</span>
                <span data-emoji="😮">😮</span>
                <span data-emoji="😢">😢</span>
                <span data-emoji="👏">👏</span>
                <span data-emoji="🔥">🔥</span>
            </div>
            <input type="text" id="stories-reply-input" placeholder="أرسل رداً..." maxlength="255">
            <button type="button" id="stories-reply-send">إرسال</button>
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
