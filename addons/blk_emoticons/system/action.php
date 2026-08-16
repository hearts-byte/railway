<?php
$load_addons = 'blk_emoticons';
require(__DIR__ . '/../../../system/config_addons.php');

if(!canManageAddons()){
	echo json_encode(array('code'=> 0));
	die();
}

session_write_close();

// مسارات مجلدات الإيموجي الأساسية بالكور (خارج مجلد الإضافات)
define('BLK_EMO_DIR', realpath(__DIR__ . '/../../../emoticon'));
define('BLK_EMO_ICON_DIR', realpath(__DIR__ . '/../../../emoticon_icon'));
$blk_supported = array('png', 'svg', 'gif');

// اسم فئة آمن (اسم مجلد): حروف/أرقام إنجليزية صغيرة، شرطة، شرطة سفلية فقط
function blkSafeName($name){
	$name = strtolower(trim($name));
	$name = preg_replace('/[^a-z0-9_\-]/', '', $name);
	return $name;
}

// اسم/كود إيموجي آمن (اسم الملف بدون الامتداد)
function blkSafeCode($name){
	$name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);
	return $name;
}

function blkExt($filename){
	return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// يرجع المسار الفعلي لمجلد الفئة (أو الجذر إذا كانت الفئة الأساسية base_emo)
function blkCategoryPath($cat){
	if($cat == '' || $cat == 'base_emo'){
		return BLK_EMO_DIR;
	}
	$cat = blkSafeName($cat);
	if($cat == ''){
		return false;
	}
	return BLK_EMO_DIR . '/' . $cat;
}

function blkCountEmo($dir){
	global $blk_supported;
	$count = 0;
	if(!is_dir($dir)){
		return 0;
	}
	foreach(scandir($dir) as $f){
		if($f == '.' || $f == '..' || is_dir($dir.'/'.$f)) continue;
		if(in_array(blkExt($f), $blk_supported)) $count++;
	}
	return $count;
}

function blkListCategories(){
	global $blk_supported;
	$cats = array();

	// الفئة الأساسية (جذر مجلد emoticon مباشرة)
	global $lang;
	$cats[] = array(
		'name'=> 'base_emo',
		'label'=> isset($lang['blk_base_category']) ? $lang['blk_base_category'] : 'Base',
		'icon'=> '',
		'count'=> blkCountEmo(BLK_EMO_DIR),
		'is_base'=> true,
	);

	foreach(scandir(BLK_EMO_DIR) as $f){
		$full = BLK_EMO_DIR . '/' . $f;
		if($f == '.' || $f == '..' || !is_dir($full)) continue;

		$icon = '';
		if(BLK_EMO_ICON_DIR){
			foreach($blk_supported as $ext){
				if(file_exists(BLK_EMO_ICON_DIR . '/' . $f . '.' . $ext)){
					$icon = 'emoticon_icon/' . $f . '.' . $ext;
					break;
				}
			}
		}

		$cats[] = array(
			'name'=> $f,
			'label'=> $f,
			'icon'=> $icon,
			'count'=> blkCountEmo($full),
			'is_base'=> false,
		);
	}
	return $cats;
}

function blkListFiles($cat){
	global $blk_supported;
	$path = blkCategoryPath($cat);
	if(!$path || !is_dir($path)){
		return array();
	}
	$files = array();
	$rel = ($cat == '' || $cat == 'base_emo') ? '' : blkSafeName($cat).'/';
	foreach(scandir($path) as $f){
		if($f == '.' || $f == '..' || is_dir($path.'/'.$f)) continue;
		$ext = blkExt($f);
		if(!in_array($ext, $blk_supported)) continue;
		$code = preg_replace('/\.[^.]*$/', '', $f);
		$files[] = array(
			'file'=> $f,
			'code'=> $code,
			'src'=> 'emoticon/' . $rel . $f,
		);
	}
	return $files;
}

$op = isset($_POST['op']) ? $_POST['op'] : '';

// ------- عرض كل الفئات -------
if($op == 'list'){
	echo json_encode(array('code'=> 1, 'categories'=> blkListCategories()));
	die();
}

// ------- عرض ملفات فئة معينة -------
if($op == 'list_files'){
	$cat = escape($_POST['category']);
	echo json_encode(array('code'=> 1, 'files'=> blkListFiles($cat)));
	die();
}

// ------- إضافة فئة جديدة -------
if($op == 'add_category'){
	$name = blkSafeName($_POST['name']);
	if($name == '' || $name == 'base_emo' || strlen($name) > 30){
		echo json_encode(array('code'=> 0));
		die();
	}
	$path = BLK_EMO_DIR . '/' . $name;
	if(file_exists($path)){
		echo json_encode(array('code'=> 0, 'msg'=> 'exists'));
		die();
	}
	if(!mkdir($path, 0755)){
		echo json_encode(array('code'=> 0));
		die();
	}
	if(isset($_FILES['icon']) && $_FILES['icon']['error'] == 0 && BLK_EMO_ICON_DIR){
		$ext = blkExt($_FILES['icon']['name']);
		if(in_array($ext, $blk_supported)){
			move_uploaded_file($_FILES['icon']['tmp_name'], BLK_EMO_ICON_DIR . '/' . $name . '.' . $ext);
		}
	}
	echo json_encode(array('code'=> 1));
	die();
}

// ------- حذف فئة كاملة -------
if($op == 'delete_category'){
	$name = blkSafeName($_POST['name']);
	if($name == '' || $name == 'base_emo'){
		echo json_encode(array('code'=> 0));
		die();
	}
	$path = BLK_EMO_DIR . '/' . $name;
	$real = realpath($path);
	if(!$real || strpos($real, BLK_EMO_DIR) !== 0 || !is_dir($real)){
		echo json_encode(array('code'=> 0));
		die();
	}
	foreach(scandir($real) as $f){
		if($f == '.' || $f == '..') continue;
		@unlink($real . '/' . $f);
	}
	@rmdir($real);
	if(BLK_EMO_ICON_DIR){
		foreach($blk_supported as $ext){
			$iconFile = BLK_EMO_ICON_DIR . '/' . $name . '.' . $ext;
			if(file_exists($iconFile)) @unlink($iconFile);
		}
	}
	echo json_encode(array('code'=> 1));
	die();
}

// ------- رفع إيموجي (ملف أو أكثر) لفئة -------
if($op == 'upload'){
	$cat = escape($_POST['category']);
	$path = blkCategoryPath($cat);
	if(!$path || !is_dir($path) || empty($_FILES['emo'])){
		echo json_encode(array('code'=> 0));
		die();
	}
	$count = count($_FILES['emo']['name']);
	$ok = 0;
	for($i=0; $i<$count; $i++){
		if($_FILES['emo']['error'][$i] != 0) continue;
		$ext = blkExt($_FILES['emo']['name'][$i]);
		if(!in_array($ext, $blk_supported)) continue;
		$code = blkSafeCode(pathinfo($_FILES['emo']['name'][$i], PATHINFO_FILENAME));
		if($code == '') continue;
		$dest = $path . '/' . $code . '.' . $ext;
		if(move_uploaded_file($_FILES['emo']['tmp_name'][$i], $dest)){
			$ok++;
		}
	}
	echo json_encode(array('code'=> $ok > 0 ? 1 : 0, 'uploaded'=> $ok));
	die();
}

// ------- حذف ملف إيموجي واحد -------
if($op == 'delete_file'){
	$cat = escape($_POST['category']);
	$file = str_replace(array('/', '\\', '..'), '', $_POST['file']);
	$path = blkCategoryPath($cat);
	if(!$path || !is_dir($path)){
		echo json_encode(array('code'=> 0));
		die();
	}
	$full = $path . '/' . $file;
	$real = realpath($full);
	if(!$real || strpos($real, BLK_EMO_DIR) !== 0 || !is_file($real)){
		echo json_encode(array('code'=> 0));
		die();
	}
	@unlink($real);
	echo json_encode(array('code'=> 1));
	die();
}

// ------- إعادة تسمية كود إيموجي (تعديل) -------
if($op == 'rename_file'){
	$cat = escape($_POST['category']);
	$file = str_replace(array('/', '\\', '..'), '', $_POST['file']);
	$newCode = blkSafeCode($_POST['new_code']);
	$path = blkCategoryPath($cat);
	if(!$path || !is_dir($path) || $newCode == ''){
		echo json_encode(array('code'=> 0));
		die();
	}
	$full = $path . '/' . $file;
	$real = realpath($full);
	if(!$real || strpos($real, BLK_EMO_DIR) !== 0 || !is_file($real)){
		echo json_encode(array('code'=> 0));
		die();
	}
	$ext = blkExt($file);
	$newFull = $path . '/' . $newCode . '.' . $ext;
	if(file_exists($newFull)){
		echo json_encode(array('code'=> 0, 'msg'=> 'exists'));
		die();
	}
	rename($real, $newFull);
	echo json_encode(array('code'=> 1));
	die();
}

echo json_encode(array('code'=> 0));
die();
?>
