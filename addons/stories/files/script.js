/* =========================================================
   Stories Addon - Frontend Logic
   الإضافة تبني نفسها بالكامل عبر الجافاسكربت (بدون أي تعديل يدوي
   بملفات السكربت الأساسية) وتحقن شريط الستوريات داخل نافذة
   "المتصلين" الموجودة أصلاً، مباشرة بعد شريط أيقونات التبويبات
   (#right_panel_bar) وقبل منطقة المحتوى المتغيّرة حسب التبويب
   (#chat_right_data) — فيبقى ظاهر دايمًا بغض النظر عن أي تبويب مفتوح.
   ========================================================= */
(function () {
    'use strict';

    var API = '/addons/stories/system/action.php';
    var DEFAULT_DURATION = 5000; // مدة عرض النص/الصورة بالمللي ثانية
    var MAX_IMAGE_MB = 20;
    var MAX_VIDEO_MB = 50;
    var MAX_VIDEO_SECONDS = 60;

    var state = {
        users: [],
        currentUserIndex: 0,
        stories: [],
        currentStoryIndex: 0,
        timer: null,
        progressStart: 0,
        progressDuration: DEFAULT_DURATION,
        paused: false,
        isOwner: false,
        currentStoryId: null,
        myStoryIndex: -1, // فهرس ستورياتي أنا داخل state.users (لو موجودة)
        myAvatar: '',
        myUsername: '',
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // الصفحة تكون خلصت تحميلها أصلاً وقت ما يوصل هذا السكربت (يتحمّل متأخر)
        init();
    }

    function init() {
        // نبحث عن نقطة الحقن (شريط أيقونات نافذة المتصلين). لو ما لقيناها
        // (الصفحة الحالية ما فيها نافذة متصلين، مثلاً صفحة تسجيل الدخول)
        // نوقف بصمت.
        var anchor = document.getElementById('right_panel_bar');
        if (!anchor || !anchor.parentNode) return;
        if (document.getElementById('stories-bar')) return; // محقونة مسبقاً

        apiGet({ do: 'me' }).then(function (res) {
            if (!res.success) {
                console.error('stories: فشل جلب بيانات المستخدم ->', res.error || res);
                return;
            }
            state.myAvatar = res.avatar || '';
            state.myUsername = res.username || '';

            injectMarkup(anchor);
            bindCreateModal();
            bindViewerControls();
            loadBar();
        });
    }

    /* ==================== بناء وحقن العناصر بالـ DOM ==================== */
    function injectMarkup(anchor) {
        // شريط الستوريات: يُحقن كأخ مباشر بعد شريط الأيقونات وقبل منطقة
        // المحتوى المتغيّرة (#chat_right_data)، فما يتأثر بتبديل التبويبات
        var barWrap = document.createElement('div');
        barWrap.innerHTML = buildBarHtml();
        var barEl = barWrap.firstElementChild;
        anchor.parentNode.insertBefore(barEl, anchor.nextSibling);

        // نافذة الإنشاء + العارض بملء الشاشة: تُحقن بنهاية body لأنها
        // عناصر عائمة (position:fixed) بغض النظر عن مكانها بالـ DOM
        var overlaysWrap = document.createElement('div');
        overlaysWrap.innerHTML = buildCreateModalHtml() + buildViewerHtml();
        while (overlaysWrap.firstChild) {
            document.body.appendChild(overlaysWrap.firstChild);
        }

        // شريطنا يضيف ارتفاعاً جديداً داخل نافذة المتصلين، فيدفع منطقة
        // المحتوى (#chat_right_data) للأسفل بدون ما يقل ارتفاعها المحدد
        // مسبقاً بالـ CSS الأصلي، فيفيض جزء من أسفلها خلف الشريط السفلي.
        // نعوّض هذا بتصغير ارتفاعها بمقدار ارتفاع شريطنا بالضبط تلقائياً.
        shrinkChatRightData(barEl);
    }

    function shrinkChatRightData(barEl) {
        var target = document.getElementById('chat_right_data');
        if (!target) return;

        function apply() {
            var barHeight = barEl.offsetHeight;
            if (!barHeight) return;
            target.style.setProperty('height', 'calc(100% - ' + barHeight + 'px)', 'important');
            target.style.setProperty('max-height', 'calc(100% - ' + barHeight + 'px)', 'important');
        }

        apply();
        // لو تغيّر حجم الشاشة (تدوير الجوال مثلاً) قد يتغيّر ارتفاع الشريط
        window.addEventListener('resize', apply);
        // بعض الأجهزة تحسب الأبعاد بشكل نهائي بعد أول رسم بمهلة بسيطة
        setTimeout(apply, 300);
    }

    function buildBarHtml() {
        var avatar = escapeHtml(state.myAvatar);
        return (
            '<div id="stories-bar" class="stories-bar">' +
                '<div class="stories-item stories-add-item" title="إضافة ستوري">' +
                    '<div class="stories-avatar-ring stories-ring-add" id="stories-my-ring">' +
                        '<img src="' + avatar + '" alt="" id="stories-my-avatar">' +
                        '<span class="stories-plus" id="stories-add-btn">+</span>' +
                    '</div>' +
                    '<span class="stories-name">إضافتك</span>' +
                '</div>' +
                '<div id="stories-bar-list" class="stories-bar-list"></div>' +
            '</div>'
        );
    }

    function buildCreateModalHtml() {
        return (
            '<div id="stories-create-modal" class="stories-overlay" style="display:none;">' +
                '<div class="stories-modal aagn-style-modal">' +
                    '<div class="brow">' +
                        '<div class="bcell border_bottom">' +
                            '<div class="modal_top_menu">' +
                                '<div class="bcell_mid hpad15"><p class="label"><i class="fa fa-image bgrad24"></i> ستوري جديدة</p></div>' +
                                '<div class="modal_top_menu_empty"></div>' +
                                '<div class="cancel_modal cover_text modal_top_item stories-close" data-close="create"><i class="fa fa-times"></i></div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="pad10">' +
                        '<div class="modal_menu hpad15 centered_element">' +
                            '<ul>' +
                                '<li class="modal_menu_item modal_selected stories-tab" data-tab="text"><i class="fa fa-font"></i> نص</li>' +
                                '<li class="modal_menu_item stories-tab" data-tab="image"><i class="fa fa-image"></i> صورة</li>' +
                                '<li class="modal_menu_item stories-tab" data-tab="video"><i class="fa fa-video-camera"></i> فيديو</li>' +
                            '</ul>' +
                        '</div>' +
                        '<form id="stories-create-form">' +
                            '<input type="hidden" name="type" id="stories-type-input" value="text">' +
                            '<div class="stories-pane" data-pane="text">' +
                                '<div id="stories-text-preview" class="stories-text-preview" style="background:#6c5ce7;color:#ffffff;">اكتب نص الستوري هنا...</div>' +
                                '<p class="label tpad10">النص</p>' +
                                '<textarea name="text" id="stories-text-input" maxlength="300" style="width:100%;" placeholder="اكتب شيئاً..."></textarea>' +
                                '<div class="stories-colors">' +
                                    '<span>الخلفية:</span><input type="color" name="bg_color" id="stories-bg-color" value="#6c5ce7">' +
                                    '<span>النص:</span><input type="color" name="text_color" id="stories-text-color" value="#ffffff">' +
                                '</div>' +
                            '</div>' +
                            '<div class="stories-pane" data-pane="image" style="display:none;">' +
                                '<p class="label tpad10">اختر صورة</p>' +
                                '<div class="lite_olay proplayer_btn stories-pick-btn">' +
                                    '<i class="fa fa-image"></i> <span id="stories-image-filename">اختيار صورة من الجهاز</span>' +
                                    '<input type="file" name="media" id="stories-image-input" class="up_input" accept="image/*">' +
                                '</div>' +
                                '<div class="stories-upload-hint">الحد الأقصى ' + MAX_IMAGE_MB + ' ميجابايت</div>' +
                                '<img id="stories-image-preview" class="stories-media-preview" style="display:none;">' +
                            '</div>' +
                            '<div class="stories-pane" data-pane="video" style="display:none;">' +
                                '<p class="label tpad10">اختر فيديو</p>' +
                                '<div class="lite_olay proplayer_btn stories-pick-btn">' +
                                    '<i class="fa fa-video-camera"></i> <span id="stories-video-filename">اختيار فيديو من الجهاز</span>' +
                                    '<input type="file" name="media" id="stories-video-input" class="up_input" accept="video/*">' +
                                '</div>' +
                                '<div class="stories-upload-hint" id="stories-video-hint">الحد الأقصى ' + MAX_VIDEO_MB + ' ميجابايت، ومدة ' + MAX_VIDEO_SECONDS + ' ثانية</div>' +
                                '<video id="stories-video-preview" class="stories-media-preview" style="display:none;" controls></video>' +
                            '</div>' +
                            '<div class="stories-upload-progress" id="stories-upload-progress"><div class="fill" id="stories-upload-progress-fill"></div></div>' +
                            '<div class="stories-upload-status" id="stories-upload-status"></div>' +
                            '<button type="submit" style="width:100%;margin:10px 0 0 0;" class="reg_button theme_btn stories-btn-submit"><i class="fa fa-paper-plane"></i> نشر الستوري</button>' +
                        '</form>' +
                    '</div>' +
                '</div>' +
            '</div>'
        );
    }

    function buildViewerHtml() {
        return (
            '<div id="stories-viewer" class="stories-viewer" style="display:none;">' +
                '<div class="stories-progress-row" id="stories-progress-row"></div>' +
                '<div class="stories-viewer-head">' +
                    '<img id="stories-viewer-avatar" src="" class="stories-viewer-avatar">' +
                    '<span id="stories-viewer-username" class="stories-viewer-username"></span>' +
                    '<span id="stories-viewer-time" class="stories-viewer-time"></span>' +
                    '<button type="button" id="stories-delete-btn" class="stories-delete-btn" style="display:none;" title="حذف الستوري"><i class="fa fa-trash"></i></button>' +
                    '<button type="button" id="stories-viewer-close" class="stories-close">×</button>' +
                '</div>' +
                '<div class="stories-viewer-body" id="stories-viewer-body">' +
                    '<div class="stories-nav-zone stories-nav-prev" id="stories-nav-prev"></div>' +
                    '<div class="stories-nav-zone stories-nav-next" id="stories-nav-next"></div>' +
                    '<div id="stories-media-container" class="stories-media-container"></div>' +
                '</div>' +
                '<div class="stories-viewer-footer">' +
                    '<button type="button" id="stories-viewers-btn" class="stories-viewers-btn" style="display:none;">👁 <span id="stories-views-count">0</span> مشاهدة</button>' +
                    '<div class="stories-reactions-row" id="stories-reactions-row">' +
                        '<button type="button" class="stories-reaction-icon" data-emoji="❤️">❤️</button>' +
                        '<button type="button" class="stories-reaction-icon" data-emoji="😂">😂</button>' +
                        '<button type="button" class="stories-reaction-icon" data-emoji="😮">😮</button>' +
                        '<button type="button" class="stories-reaction-icon" data-emoji="😢">😢</button>' +
                        '<button type="button" class="stories-reaction-icon" data-emoji="👏">👏</button>' +
                        '<button type="button" class="stories-reaction-icon" data-emoji="🔥">🔥</button>' +
                    '</div>' +
                '</div>' +
                '<div id="stories-viewers-panel" class="stories-viewers-panel" style="display:none;">' +
                    '<div class="stories-viewers-head"><span>المشاهدون</span><button type="button" class="stories-close" id="stories-viewers-close">×</button></div>' +
                    '<div id="stories-viewers-list" class="stories-viewers-list"></div>' +
                '</div>' +
            '</div>'
        );
    }

    /* ==================== طلبات AJAX ====================
       ملاحظة: utk متغير جافاسكربت عام موجود أصلاً بصفحة الدردشة (رمز الحماية للجلسة)
       ولازم يترسل مع كل طلب عشان يعدي فحص الحماية بالسيرفر. */
    function apiGet(params) {
        // نرسلها POST مثل باقي طلبات الإضافة (بدل GET) لأن فحص الحماية checkToken()
        // بالكور يتحقق من $_POST['token'] فقط، فطلبات GET كانت ترجع فارغة بصمت.
        var formData = new FormData();
        params = Object.assign({}, params, { token: (typeof utk !== 'undefined' ? utk : '') });
        Object.keys(params).forEach(function (k) { formData.append(k, params[k]); });
        return fetch(API, { method: 'POST', credentials: 'same-origin', body: formData })
            .then(function (r) { return r.json(); })
            .catch(function (err) {
                console.error('stories: تعذرت قراءة رد الخادم', err);
                return { success: false, error: 'تعذر الاتصال بالخادم' };
            });
    }
    function apiPost(params, formData) {
        formData = formData || new FormData();
        params = Object.assign({}, params, { token: (typeof utk !== 'undefined' ? utk : '') });
        Object.keys(params).forEach(function (k) { formData.append(k, params[k]); });
        return fetch(API, { method: 'POST', credentials: 'same-origin', body: formData })
            .then(function (r) { return r.json(); })
            .catch(function (err) {
                console.error('stories: تعذرت قراءة رد الخادم', err);
                return { success: false, error: 'تعذر الاتصال بالخادم' };
            });
    }

    /* نشر ستوري بصورة/فيديو مع تتبع تقدّم الرفع الحقيقي عبر XMLHttpRequest
       (fetch ما يعطي حدث progress لعملية الرفع نفسها). fd تكون جاهزة
       ومعبّأة مسبقاً بكل الحقول بما فيها token؛ هنا فقط نضيف do=create. */
    function submitCreate(fd, showProgress) {
        fd.append('do', 'create');
        if (!showProgress || typeof XMLHttpRequest === 'undefined') {
            return fetch(API, { method: 'POST', credentials: 'same-origin', body: fd })
                .then(function (r) { return r.json(); })
                .catch(function (err) {
                    console.error('stories: تعذرت قراءة رد الخادم', err);
                    return { success: false, error: 'تعذر الاتصال بالخادم' };
                });
        }
        return new Promise(function (resolve) {
            var xhr = new XMLHttpRequest();
            var fill = document.getElementById('stories-upload-progress-fill');
            var wrap = document.getElementById('stories-upload-progress');
            var status = document.getElementById('stories-upload-status');
            xhr.open('POST', API, true);
            xhr.withCredentials = true;
            xhr.upload.addEventListener('progress', function (e) {
                if (e.lengthComputable && fill) {
                    fill.style.width = Math.round((e.loaded / e.total) * 100) + '%';
                }
            });
            // خلص إرسال البيانات للسيرفر (100%)، لكن السيرفر لسا يعالج الملف
            // (فحص النوع الحقيقي + حفظه + تسجيله بقاعدة البيانات)، فنبيّن
            // حالة "جاري المعالجة" بدل ما يبان الشريط متوقف بدون أي تفاعل
            xhr.upload.addEventListener('load', function () {
                if (wrap) wrap.classList.add('stories-upload-processing');
                if (status) status.textContent = 'جاري نشر الستوري، ثوانٍ...';
            });
            xhr.onload = function () {
                if (wrap) wrap.classList.remove('stories-upload-processing');
                if (status) status.textContent = '';
                try {
                    resolve(JSON.parse(xhr.responseText));
                } catch (err) {
                    console.error('stories: تعذرت قراءة رد الخادم', err);
                    resolve({ success: false, error: 'تعذر قراءة رد الخادم' });
                }
            };
            xhr.onerror = function () {
                if (wrap) wrap.classList.remove('stories-upload-processing');
                if (status) status.textContent = '';
                resolve({ success: false, error: 'تعذر الاتصال بالخادم' });
            };
            xhr.send(fd);
        });
    }

    /* ==================== شريط الستوريات ==================== */
    function loadBar() {
        apiGet({ do: 'bar' }).then(function (res) {
            if (!res.success) {
                console.error('stories: فشل تحميل شريط الستوريات ->', res.error || res);
                return;
            }
            state.users = res.users;
            renderBar(res.users);
        });
    }

    function renderBar(users) {
        var wrap = document.getElementById('stories-bar-list');
        if (!wrap) return;
        wrap.innerHTML = '';
        state.myStoryIndex = -1;

        // نحدّث حلقة "إضافتك": لو عندي ستوريات منشورة فعلاً تتلون الحلقة
        // زي باقي الأعضاء (متدرجة = فيها جديد لم يُشاهَد)، وإلا تبقى رمادية عادية.
        var myRing = document.getElementById('stories-my-ring');
        if (myRing) {
            myRing.classList.remove('has-new', 'seen');
        }

        users.forEach(function (u, idx) {
            if (u.is_me) {
                state.myStoryIndex = idx;
                if (myRing) {
                    myRing.classList.add(u.has_new ? 'has-new' : 'seen');
                }
                return; // المستخدم الحالي له عنصر "إضافتك" منفصل بالفعل
            }
            var item = document.createElement('div');
            item.className = 'stories-item';
            item.innerHTML =
                '<div class="stories-avatar-ring ' + (u.has_new ? 'has-new' : 'seen') + '">' +
                '<img src="' + escapeHtml(u.avatar || '') + '" alt="">' +
                '</div>' +
                '<span class="stories-name">' + escapeHtml(u.username || '') + '</span>';
            item.addEventListener('click', function () { openViewerForUserIndex(idx); });
            wrap.appendChild(item);
        });
    }

    /* ==================== نافذة الإنشاء ==================== */
    function bindCreateModal() {
        var addBtn = document.getElementById('stories-add-btn'); // زر "+" الصغير
        var myAvatar = document.getElementById('stories-my-avatar'); // صورة "إضافتك" نفسها
        var modal = document.getElementById('stories-create-modal');
        var form = document.getElementById('stories-create-form');
        var tabs = document.querySelectorAll('.stories-tab');
        var typeInput = document.getElementById('stories-type-input');

        function openCreateModal() { modal.style.display = 'flex'; }

        // زر "+" يفتح نافذة إنشاء ستوري جديدة دايمًا
        addBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            openCreateModal();
        });

        // الضغط على صورة "إضافتك": لو عندي ستوريات منشورة يفتحها للمشاهدة،
        // ولو ما عندي أي ستوري يفتح نافذة الإنشاء مباشرة
        if (myAvatar) {
            myAvatar.addEventListener('click', function () {
                if (state.myStoryIndex >= 0) {
                    openViewerForUserIndex(state.myStoryIndex);
                } else {
                    openCreateModal();
                }
            });
        }
        modal.querySelector('[data-close="create"]').addEventListener('click', function () {
            modal.style.display = 'none';
        });

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) { t.classList.remove('modal_selected'); });
                tab.classList.add('modal_selected');
                var name = tab.getAttribute('data-tab');
                typeInput.value = name;
                document.querySelectorAll('.stories-pane').forEach(function (p) {
                    p.style.display = (p.getAttribute('data-pane') === name) ? 'block' : 'none';
                });
            });
        });

        var textInput = document.getElementById('stories-text-input');
        var preview = document.getElementById('stories-text-preview');
        var bgColor = document.getElementById('stories-bg-color');
        var textColor = document.getElementById('stories-text-color');
        textInput.addEventListener('input', function () {
            preview.textContent = textInput.value || 'اكتب نص الستوري هنا...';
        });
        bgColor.addEventListener('input', function () { preview.style.background = bgColor.value; });
        textColor.addEventListener('input', function () { preview.style.color = textColor.value; });

        var imgInput = document.getElementById('stories-image-input');
        var imgPreview = document.getElementById('stories-image-preview');
        var imgFilename = document.getElementById('stories-image-filename');
        var imgHint = null; // لا يوجد رسالة خطأ منفصلة للصورة حاليًا، الحد يظهر بالتلميح الثابت
        imgInput.addEventListener('change', function () {
            if (!imgInput.files[0]) return;
            var file = imgInput.files[0];
            if (file.size > MAX_IMAGE_MB * 1024 * 1024) {
                alert('حجم الصورة أكبر من ' + MAX_IMAGE_MB + ' ميجابايت، اختر صورة أصغر');
                imgInput.value = '';
                return;
            }
            imgFilename.textContent = file.name;
            imgPreview.src = URL.createObjectURL(file);
            imgPreview.style.display = 'block';
        });

        var vidInput = document.getElementById('stories-video-input');
        var vidPreview = document.getElementById('stories-video-preview');
        var vidFilename = document.getElementById('stories-video-filename');
        var vidHint = document.getElementById('stories-video-hint');
        function resetVideoHint() {
            vidHint.textContent = 'الحد الأقصى ' + MAX_VIDEO_MB + ' ميجابايت، ومدة ' + MAX_VIDEO_SECONDS + ' ثانية';
            vidHint.classList.remove('stories-hint-error');
        }
        vidInput.addEventListener('change', function () {
            if (!vidInput.files[0]) return;
            var file = vidInput.files[0];

            if (file.size > MAX_VIDEO_MB * 1024 * 1024) {
                vidHint.textContent = 'حجم الفيديو أكبر من ' + MAX_VIDEO_MB + ' ميجابايت، اختر فيديو أصغر';
                vidHint.classList.add('stories-hint-error');
                vidInput.value = '';
                vidPreview.style.display = 'none';
                return;
            }

            vidFilename.textContent = file.name;
            vidPreview.src = URL.createObjectURL(file);
            vidPreview.style.display = 'block';
            resetVideoHint();

            // نتحقق من مدة الفيديو الحقيقية بعد ما تنقرأ بياناته الوصفية
            vidPreview.addEventListener('loadedmetadata', function checkDuration() {
                vidPreview.removeEventListener('loadedmetadata', checkDuration);
                if (vidPreview.duration && vidPreview.duration > MAX_VIDEO_SECONDS) {
                    vidHint.textContent = 'مدة الفيديو (' + Math.round(vidPreview.duration) + 'ث) أطول من الحد المسموح (' + MAX_VIDEO_SECONDS + 'ث)';
                    vidHint.classList.add('stories-hint-error');
                    vidInput.value = '';
                    vidPreview.style.display = 'none';
                }
            });
        });

        var progressWrap = document.getElementById('stories-upload-progress');
        var progressFill = document.getElementById('stories-upload-progress-fill');

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var submitBtn = form.querySelector('.stories-btn-submit');

            var type = typeInput.value;
            if (type === 'image' && !imgInput.files[0]) {
                alert('اختر صورة أولاً');
                return;
            }
            if (type === 'video' && !vidInput.files[0]) {
                alert('اختر فيديو أولاً');
                return;
            }

            submitBtn.disabled = true;

            var fd = new FormData();
            fd.append('type', type);
            fd.append('bg_color', bgColor.value);
            fd.append('text_color', textColor.value);
            fd.append('token', (typeof utk !== 'undefined' ? utk : ''));

            if (type === 'text') {
                fd.append('text', textInput.value);
            } else if (type === 'image') {
                fd.append('media', imgInput.files[0]);
            } else if (type === 'video') {
                fd.append('media', vidInput.files[0]);
            }

            var showProgress = (type === 'image' || type === 'video');
            if (showProgress) {
                progressWrap.style.display = 'block';
                progressWrap.classList.remove('stories-upload-processing');
                progressFill.style.width = '0%';
                document.getElementById('stories-upload-status').textContent = '';
            }

            submitCreate(fd, showProgress).then(function (res) {
                submitBtn.disabled = false;
                progressWrap.style.display = 'none';
                if (res.success) {
                    modal.style.display = 'none';
                    form.reset();
                    preview.textContent = 'اكتب نص الستوري هنا...';
                    imgPreview.style.display = 'none';
                    vidPreview.style.display = 'none';
                    imgFilename.textContent = 'اختيار صورة من الجهاز';
                    vidFilename.textContent = 'اختيار فيديو من الجهاز';
                    resetVideoHint();
                    tabs.forEach(function (t) { t.classList.remove('modal_selected'); });
                    tabs[0].classList.add('modal_selected');
                    typeInput.value = 'text';
                    document.querySelectorAll('.stories-pane').forEach(function (p) {
                        p.style.display = (p.getAttribute('data-pane') === 'text') ? 'block' : 'none';
                    });
                    loadBar();
                } else {
                    alert(res.error || 'تعذر نشر الستوري');
                }
            });
        });
    }

    /* ==================== عارض الستوري بملء الشاشة ==================== */
    function openViewerForUserIndex(index) {
        state.currentUserIndex = index;
        var user = state.users[index];
        if (!user) return;
        state.isOwner = !!user.is_me;

        document.getElementById('stories-viewer-avatar').src = user.avatar || '';
        document.getElementById('stories-viewer-username').textContent = user.username || '';

        apiGet({ do: 'feed', user_id: user.user_id }).then(function (res) {
            if (!res.success || !res.stories.length) return;
            state.stories = res.stories;
            state.currentStoryIndex = 0;
            document.getElementById('stories-viewer').style.display = 'flex';
            buildProgressBars();
            playStory(0);
        });
    }

    function buildProgressBars() {
        var row = document.getElementById('stories-progress-row');
        row.innerHTML = '';
        state.stories.forEach(function () {
            var bar = document.createElement('div');
            bar.className = 'stories-progress-bar';
            bar.innerHTML = '<div class="fill"></div>';
            row.appendChild(bar);
        });
    }

    function playStory(idx) {
        clearTimeout(state.timer);
        if (idx < 0) {
            return; // أول ستوري بالفعل، ما نسوي شي (نتجاهل السحب/السهم الزائد)
        }
        if (idx >= state.stories.length) {
            // خلصت آخر ستوري لهذا العضو: نسكر العارض فقط ولا ننتقل تلقائياً
            // لستوري العضو التالي بالقائمة (بناءً على طلب المستخدم)
            return closeViewer();
        }

        state.currentStoryIndex = idx;
        var story = state.stories[idx];
        state.currentStoryId = story.id;

        resetProgressBars(idx);

        var container = document.getElementById('stories-media-container');
        container.innerHTML = '';

        var viewersBtn = document.getElementById('stories-viewers-btn');
        viewersBtn.style.display = state.isOwner ? 'inline-block' : 'none';
        document.getElementById('stories-reactions-row').style.display = state.isOwner ? 'none' : 'flex';
        document.getElementById('stories-delete-btn').style.display = state.isOwner ? 'inline-block' : 'none';
        document.getElementById('stories-views-count').textContent = story.views;

        if (story.type === 'text') {
            var slide = document.createElement('div');
            slide.className = 'stories-text-slide';
            slide.style.background = story.bg_color || '#6c5ce7';
            slide.style.color = story.text_color || '#fff';
            slide.textContent = story.content;
            container.appendChild(slide);
            state.progressDuration = DEFAULT_DURATION;
            startProgress(idx, DEFAULT_DURATION);
        } else if (story.type === 'image') {
            var img = document.createElement('img');
            img.src = story.content;
            container.appendChild(img);
            state.progressDuration = DEFAULT_DURATION;
            startProgress(idx, DEFAULT_DURATION);
        } else if (story.type === 'video') {
            var video = document.createElement('video');
            video.src = story.content;
            video.autoplay = true;
            video.playsInline = true;
            container.appendChild(video);
            video.addEventListener('loadedmetadata', function () {
                // نحرّك شريط التقدّم بصرياً فقط بدون مؤقّت مستقل؛ الانتقال
                // للستوري التالية يعتمد حصراً على حدث "ended" الحقيقي للفيديو
                // (كان سابقاً فيه مؤقّتين يشتغلون بنفس الوقت تقريباً، فكان
                // أحياناً يصير قفز/تكرار بصري بسيط عند نهاية الفيديو)
                animateProgressBar(idx, (video.duration || 15) * 1000);
            });
            video.addEventListener('ended', function () { playStory(idx + 1); });
        }

        markViewed(story.id);
    }

    function resetProgressBars(activeIdx) {
        var bars = document.querySelectorAll('#stories-progress-row .stories-progress-bar .fill');
        bars.forEach(function (fill, i) {
            fill.style.transition = 'none';
            fill.style.width = i < activeIdx ? '100%' : '0%';
        });
    }

    function startProgress(idx, duration) {
        animateProgressBar(idx, duration);
        state.timer = setTimeout(function () { playStory(idx + 1); }, duration);
    }

    function animateProgressBar(idx, duration) {
        var bars = document.querySelectorAll('#stories-progress-row .stories-progress-bar .fill');
        var fill = bars[idx];
        if (fill) {
            requestAnimationFrame(function () {
                fill.style.transition = 'width ' + duration + 'ms linear';
                fill.style.width = '100%';
            });
        }
    }

    function markViewed(storyId) {
        apiPost({ do: 'view', story_id: storyId });
    }

    function bindViewerControls() {
        var viewer = document.getElementById('stories-viewer');
        document.getElementById('stories-viewer-close').addEventListener('click', closeViewer);

        document.getElementById('stories-nav-prev').addEventListener('click', function () {
            playStory(state.currentStoryIndex - 1);
        });
        document.getElementById('stories-nav-next').addEventListener('click', function () {
            playStory(state.currentStoryIndex + 1);
        });

        // إيقاف مؤقت أثناء الضغط المطوّل (مشابه لواتساب/انستغرام)
        var body = document.getElementById('stories-viewer-body');
        body.addEventListener('mousedown', pauseStory);
        body.addEventListener('touchstart', pauseStory, { passive: true });
        body.addEventListener('mouseup', resumeStory);
        body.addEventListener('touchend', resumeStory);

        // دعم السحب (يمين/يسار) على الجوال
        var touchStartX = 0;
        body.addEventListener('touchstart', function (e) { touchStartX = e.touches[0].clientX; }, { passive: true });
        body.addEventListener('touchend', function (e) {
            var diff = e.changedTouches[0].clientX - touchStartX;
            if (Math.abs(diff) < 50) return; // سحب بسيط جداً، تجاهل
            if (diff > 0) {
                playStory(state.currentStoryIndex - 1); // سحب لليمين = السابق
            } else {
                playStory(state.currentStoryIndex + 1); // سحب لليسار = التالي
            }
        });

        document.getElementById('stories-viewers-btn').addEventListener('click', openViewersPanel);
        document.getElementById('stories-viewers-close').addEventListener('click', function () {
            document.getElementById('stories-viewers-panel').style.display = 'none';
        });

        document.getElementById('stories-delete-btn').addEventListener('click', function () {
            if (!confirm('متأكد إنك تبي تحذف هذي الستوري؟')) return;
            apiPost({ do: 'delete', story_id: state.currentStoryId }).then(function (res) {
                if (!res.success) {
                    alert('تعذر حذف الستوري');
                    return;
                }
                state.stories.splice(state.currentStoryIndex, 1);
                if (!state.stories.length) {
                    closeViewer();
                    return;
                }
                buildProgressBars();
                playStory(Math.min(state.currentStoryIndex, state.stories.length - 1));
                loadBar();
            });
        });

        document.querySelectorAll('.stories-reaction-icon').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                var emoji = btn.getAttribute('data-emoji');
                sendReaction('emoji', emoji);
                showReactionFeedback(btn, emoji);
            });
        });

        document.addEventListener('keydown', function (e) {
            if (viewer.style.display === 'none') return;
            if (e.key === 'ArrowRight') playStory(state.currentStoryIndex - 1);
            if (e.key === 'ArrowLeft') playStory(state.currentStoryIndex + 1);
            if (e.key === 'Escape') closeViewer();
        });
    }

    function pauseStory() {
        state.paused = true;
        clearTimeout(state.timer);
        var video = document.querySelector('#stories-media-container video');
        if (video) video.pause();
        document.querySelectorAll('#stories-progress-row .fill').forEach(function (f) {
            var computed = getComputedStyle(f).width;
            f.style.transition = 'none';
            f.style.width = computed;
        });
    }

    function resumeStory() {
        if (!state.paused) return;
        state.paused = false;
        var video = document.querySelector('#stories-media-container video');
        if (video) {
            video.play();
            return;
        }
        playStory(state.currentStoryIndex);
    }

    function openViewersPanel() {
        apiGet({ do: 'viewers', story_id: state.currentStoryId }).then(function (res) {
            if (!res.success) return;
            var list = document.getElementById('stories-viewers-list');
            list.innerHTML = '';
            res.viewers.forEach(function (v) {
                var row = document.createElement('div');
                row.className = 'stories-viewer-row';
                row.innerHTML =
                    '<img src="' + escapeHtml(v.avatar || '') + '">' +
                    '<span>' + escapeHtml(v.username || '') + '</span>' +
                    (v.reaction ? '<span class="stories-viewer-reaction">' + escapeHtml(v.reaction) + '</span>' : '');
                list.appendChild(row);
            });
            document.getElementById('stories-viewers-panel').style.display = 'flex';
        });
    }

    function sendReaction(type, content) {
        apiPost({ do: 'react', story_id: state.currentStoryId, type: type, content: content });
    }

    // تأكيد بصري سريع لإرسال التفاعل: الزر ينبض + رمز يطير للأعلى ويتلاشى،
    // بدون أي حاجة لحقل كتابة أو رسالة إرسال إضافية
    function showReactionFeedback(btn, emoji) {
        btn.classList.remove('stories-reaction-sent');
        void btn.offsetWidth; // إعادة تشغيل الأنيميشن لو ضغط بسرعة أكثر من مرة
        btn.classList.add('stories-reaction-sent');

        var fly = document.createElement('span');
        fly.className = 'stories-reaction-fly';
        fly.textContent = emoji;
        var rect = btn.getBoundingClientRect();
        fly.style.left = (rect.left + rect.width / 2 - 13) + 'px';
        fly.style.bottom = (window.innerHeight - rect.top) + 'px';
        document.body.appendChild(fly);
        setTimeout(function () { fly.remove(); }, 1000);
    }

    function closeViewer() {
        clearTimeout(state.timer);
        document.getElementById('stories-viewer').style.display = 'none';
        document.getElementById('stories-viewers-panel').style.display = 'none';
        loadBar(); // تحديث الحلقات الملوّنة بعد المشاهدة
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str == null ? '' : str;
        return div.innerHTML;
    }
})();
