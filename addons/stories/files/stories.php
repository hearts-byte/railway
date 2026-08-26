<?php
/**
 * loadAddonsJs() تسوي include() خام بدون أي تغليف تلقائي، فلازم نحط
 * وسم <script> بأنفسنا صراحة هنا، وإلا الجافاسكربت يطبع كنص عادي بالصفحة.
 *
 * هذا الملف يتحمّل تلقائياً من الكور عبر loadAddonsJs() بدون أي تعديل يدوي
 * بملفات السكربت الأساسية. من هنا نحقن كل شي تحتاجه الإضافة (CSS + JS)،
 * والجافاسكربت نفسه يبني شريط الستوريات ويحقنه داخل نافذة "المتصلين"
 * الموجودة أصلاً بالسكربت (لا حاجة لتعديل chat.php أو head_load.php إطلاقاً).
 */
?>
<script data-cfasync="false">
(function(){
	if (document.getElementById('stories-addon-js')) { return; }

	// حقن CSS
	if (!document.getElementById('stories-addon-css')) {
		var link = document.createElement('link');
		link.id = 'stories-addon-css';
		link.rel = 'stylesheet';
		link.href = '/addons/stories/files/style.css';
		document.head.appendChild(link);
	}

	// حقن JS
	var script = document.createElement('script');
	script.id = 'stories-addon-js';
	script.src = '/addons/stories/files/script.js';
	script.defer = true;
	document.head.appendChild(script);
})();
</script>
