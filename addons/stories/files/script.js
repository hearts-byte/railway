/* =========================================================
   Stories Addon - Frontend Logic
   ========================================================= */
(function () {
    'use strict';

    var API = '/addons/stories/system/action.php';
    var DEFAULT_DURATION = 5000; // مدة عرض النص/الصورة بالمللي ثانية

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
    };

    document.addEventListener('DOMContentLoaded', init);

    // نافذة المتصلين تتحدّث دورياً عبر AJAX وتستبدل محتوى الحاوية بالكامل،
    // فتفقد أزرار الإضافة (وشريط الستوريات نفسه) أي أحداث كانت مربوطة عليها.
    // هذا المراقب يعيد الربط تلقائياً كل مرة يظهر فيها شريط جديد بالصفحة.
    var storiesObserver = new MutationObserver(function () {
        init();
    });
    storiesObserver.observe(document.body || document.documentElement, {
        childList: true,
        subtree: true,
    });

    function init() {
        var bar = document.getElementById('stories-bar');
        if (!bar) return; // الإضافة غير مضمّنة بهذه الصفحة
        if (bar.dataset.storiesBound) return; // مربوطة مسبقاً، تجنّب ازدواج الأحداث
        bar.dataset.storiesBound = '1';
        loadBar();
        bindCreateModal();
        bindViewerControls();
    }

    /* ==================== طلبات AJAX ==================== */
    function apiGet(params) {
        params = Object.assign({}, params, { token: (typeof utk !== 'undefined' ? utk : '') });
        return fetch(API + '?' + new URLSearchParams(params), { credentials: 'same-origin' })
            .then(function (r) { return r.json(); });
    }
    function apiPost(params, formData) {
        formData = formData || new FormData();
        params = Object.assign({}, params, { token: (typeof utk !== 'undefined' ? utk : '') });
        Object.keys(params).forEach(function (k) { formData.append(k, params[k]); });
        return fetch(API, { method: 'POST', credentials: 'same-origin', body: formData })
            .then(function (r) { return r.json(); });
    }

    /* ==================== شريط الستوريات ==================== */
    function loadBar() {
        apiGet({ do: 'bar' }).then(function (res) {
            if (!res.success) return;
            state.users = res.users;
            renderBar(res.users);
        });
    }

    function renderBar(users) {
        var wrap = document.getElementById('stories-bar-list');
        wrap.innerHTML = '';
        users.forEach(function (u, idx) {
            if (u.is_me) return; // المستخدم الحالي له عنصر "إضافتك" منفصل بالفعل
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
        var addBtn = document.getElementById('stories-add-btn');
        var modal = document.getElementById('stories-create-modal');
        var form = document.getElementById('stories-create-form');
        var tabs = document.querySelectorAll('.stories-tab');
        var typeInput = document.getElementById('stories-type-input');

        addBtn.addEventListener('click', function () { modal.style.display = 'flex'; });
        modal.querySelector('[data-close="create"]').addEventListener('click', function () {
            modal.style.display = 'none';
        });

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) { t.classList.remove('active'); });
                tab.classList.add('active');
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
        imgInput.addEventListener('change', function () {
            if (!imgInput.files[0]) return;
            imgPreview.src = URL.createObjectURL(imgInput.files[0]);
            imgPreview.style.display = 'block';
        });

        var vidInput = document.getElementById('stories-video-input');
        var vidPreview = document.getElementById('stories-video-preview');
        vidInput.addEventListener('change', function () {
            if (!vidInput.files[0]) return;
            vidPreview.src = URL.createObjectURL(vidInput.files[0]);
            vidPreview.style.display = 'block';
        });

        var goldToggle = document.getElementById('stories-gold-toggle');
        var goldCost = document.getElementById('stories-gold-cost');
        if (goldToggle) {
            goldToggle.addEventListener('change', function () {
                goldCost.style.display = goldToggle.checked ? 'inline-block' : 'none';
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var submitBtn = form.querySelector('.stories-btn-submit');
            submitBtn.disabled = true;

            var fd = new FormData();
            var type = typeInput.value;
            fd.append('type', type);
            fd.append('bg_color', bgColor.value);
            fd.append('text_color', textColor.value);

            if (type === 'text') {
                fd.append('text', textInput.value);
            } else if (type === 'image' && imgInput.files[0]) {
                fd.append('media', imgInput.files[0]);
            } else if (type === 'video' && vidInput.files[0]) {
                fd.append('media', vidInput.files[0]);
            }

            if (goldToggle && goldToggle.checked) {
                fd.append('gold_cost', goldCost.value);
            }

            apiPost({ do: 'create' }, fd).then(function (res) {
                submitBtn.disabled = false;
                if (res.success) {
                    modal.style.display = 'none';
                    form.reset();
                    preview.textContent = 'اكتب نص الستوري هنا...';
                    imgPreview.style.display = 'none';
                    vidPreview.style.display = 'none';
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
            return goToAdjacentUser(-1);
        }
        if (idx >= state.stories.length) {
            return goToAdjacentUser(1);
        }

        state.currentStoryIndex = idx;
        var story = state.stories[idx];
        state.currentStoryId = story.id;

        resetProgressBars(idx);

        var container = document.getElementById('stories-media-container');
        container.innerHTML = '';

        var viewersBtn = document.getElementById('stories-viewers-btn');
        viewersBtn.style.display = state.isOwner ? 'inline-block' : 'none';
        document.getElementById('stories-reply-row').style.display = state.isOwner ? 'none' : 'flex';
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
                startProgress(idx, (video.duration || 15) * 1000);
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
        var bars = document.querySelectorAll('#stories-progress-row .stories-progress-bar .fill');
        var fill = bars[idx];
        if (fill) {
            requestAnimationFrame(function () {
                fill.style.transition = 'width ' + duration + 'ms linear';
                fill.style.width = '100%';
            });
        }
        state.timer = setTimeout(function () { playStory(idx + 1); }, duration);
    }

    function goToAdjacentUser(direction) {
        var nextIndex = state.currentUserIndex + direction;
        closeViewer();
        // نبحث عن مستخدم آخر لديه ستوري (تجاهل "أنا" لأنه بدون ستوريات في القائمة الرئيسية غالباً)
        if (state.users[nextIndex]) {
            openViewerForUserIndex(nextIndex);
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

        document.querySelectorAll('.stories-emojis span').forEach(function (el) {
            el.addEventListener('click', function () {
                sendReaction('emoji', el.getAttribute('data-emoji'));
            });
        });
        document.getElementById('stories-reply-send').addEventListener('click', function () {
            var input = document.getElementById('stories-reply-input');
            if (!input.value.trim()) return;
            sendReaction('message', input.value.trim());
            input.value = '';
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
                    '<span>' + escapeHtml(v.username || '') + '</span>';
                list.appendChild(row);
            });
            document.getElementById('stories-viewers-panel').style.display = 'flex';
        });
    }

    function sendReaction(type, content) {
        apiPost({ do: 'react', story_id: state.currentStoryId, type: type, content: content });
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
