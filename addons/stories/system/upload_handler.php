<?php
if (!defined('IN_SCRIPT')) { die('Access Denied'); }

/**
 * يعالج رفع ملف صورة/فيديو للستوري بشكل آمن.
 * - يتحقق من الحجم والامتداد.
 * - يتحقق من نوع الملف الحقيقي (MIME) وليس فقط الامتداد الظاهري.
 * - يولّد اسم ملف عشوائي بالكامل (لا يعتمد على اسم الملف الأصلي إطلاقاً).
 *
 * @param array $file عنصر من $_FILES
 * @return array ['success'=>bool,'path'=>string,'type'=>string,'error'=>string]
 */
function stories_handle_upload($file)
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return array('success' => false, 'error' => 'حدث خطأ أثناء رفع الملف');
    }

    if ($file['size'] > STORIES_MAX_FILE_SIZE) {
        return array('success' => false, 'error' => 'حجم الملف أكبر من الحد المسموح به');
    }

    $original_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = array_merge(STORIES_ALLOWED_IMAGE, STORIES_ALLOWED_VIDEO);
    if (!in_array($original_ext, $allowed, true)) {
        return array('success' => false, 'error' => 'امتداد الملف غير مسموح به');
    }

    // التحقق الحقيقي من نوع الملف عبر فحص محتواه (وليس الاعتماد على الامتداد فقط)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $real_mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $mime_map = array(
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'mp4'  => 'video/mp4',
        'mov'  => 'video/quicktime',
        'webm' => 'video/webm',
    );

    if (!isset($mime_map[$original_ext]) || $real_mime !== $mime_map[$original_ext]) {
        return array('success' => false, 'error' => 'نوع الملف الحقيقي لا يطابق امتداده');
    }

    $is_video = in_array($original_ext, STORIES_ALLOWED_VIDEO, true);
    $type = $is_video ? 'video' : 'image';

    if (!is_dir(STORIES_UPLOAD_DIR)) {
        @mkdir(STORIES_UPLOAD_DIR, 0755, true);
    }

    // اسم ملف عشوائي بالكامل لمنع تخمين المسار أو رفع أكواد قابلة للتنفيذ باسم مضلل
    $safe_name = bin2hex(random_bytes(16)) . '.' . $original_ext;
    $target_path = STORIES_UPLOAD_DIR . $safe_name;

    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        return array('success' => false, 'error' => 'فشل حفظ الملف على الخادم');
    }

    @chmod($target_path, 0644);

    return array(
        'success' => true,
        'path'    => STORIES_UPLOAD_URL . $safe_name,
        'type'    => $type,
    );
}
