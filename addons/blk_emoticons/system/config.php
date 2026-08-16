<?php
$load_addons = 'blk_emoticons';
require('../../../system/config_addons.php');

if(!canManageAddons()){
	die();
}
?>
<?php echo elementTitle($lang['blk_emoticons_title'], 'loadLob(\'admin/setting_addons.php\');'); ?>
<style>
.blk_tabs{ display:flex; background:#fff; border-bottom:1px solid #eee; }
.blk_tab_btn{ flex:1; text-align:center; padding:12px 5px; cursor:pointer; color:#888; font-weight:bold; border-bottom:3px solid transparent; }
.blk_tab_btn.blk_active{ color:#222; border-bottom:3px solid #8b5cf6; background:#f2eefc; }

.blk_purple_bar{ background:#8b5cf6; padding:8px 12px; display:flex; align-items:center; gap:10px; overflow-x:auto; direction:ltr; }
.blk_plus_btn{ min-width:34px; height:34px; border-radius:50%; background:#22c55e; color:#fff; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:18px; flex-shrink:0; }
.blk_cat_icon_btn{ width:34px; height:34px; border-radius:8px; object-fit:contain; background:rgba(255,255,255,.25); cursor:pointer; flex-shrink:0; padding:2px; }
.blk_cat_icon_btn.blk_selected{ background:#fff; box-shadow:0 0 0 2px #fff; }

.blk_list{ background:#fff; }
.blk_row{ display:flex; flex-direction:row; align-items:center; justify-content:space-between; padding:12px 15px; border-bottom:1px solid #eee; direction:ltr; }
.blk_row_del{ color:#e0342c; cursor:pointer; font-size:18px; width:24px; text-align:center; flex-shrink:0; }
.blk_row_home{ color:#555; font-size:18px; width:24px; text-align:center; flex-shrink:0; }
.blk_row_name{ flex:1; direction:rtl; text-align:right; padding:0 12px; font-weight:bold; color:#333; }
.blk_row_thumb{ width:32px; height:32px; object-fit:contain; flex-shrink:0; }
.blk_empty_row{ padding:20px; text-align:center; color:#999; }

/* ---- نوافذ منبثقة (Modal) ---- */
.blk_modal_overlay{ display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:9999; align-items:center; justify-content:center; }
.blk_modal_overlay.blk_show{ display:flex; }
.blk_modal_box{ background:#fff; width:88%; max-width:360px; border-radius:14px; padding:22px 18px 18px; direction:rtl; position:relative; box-shadow:0 10px 30px rgba(0,0,0,.25); }
.blk_modal_close{ position:absolute; top:14px; left:14px; font-size:20px; color:#333; cursor:pointer; line-height:1; }
.blk_modal_title{ text-align:center; font-size:16px; font-weight:bold; color:#333; margin:6px 0 20px; }
.blk_modal_label{ font-size:13px; color:#555; margin-bottom:6px; }
.blk_modal_field{ margin-bottom:16px; }
.blk_modal_field input[type="text"], .blk_modal_field select{ width:100%; box-sizing:border-box; background:#f2f2f5; border:1px solid #e5e5ea; border-radius:8px; padding:10px 12px; font-size:14px; color:#333; }
.blk_pick_btn{ width:100%; background:#17b6d4; color:#fff; border:none; border-radius:8px; padding:12px; font-size:14px; cursor:pointer; text-align:center; }
.blk_pick_row{ display:flex; align-items:center; gap:8px; background:#f2f2f5; border:1px solid #e5e5ea; border-radius:8px; padding:10px 12px; font-size:13px; color:#888; cursor:pointer; }
.blk_pick_row span{ flex:1; text-align:center; }
.blk_pick_name_btn{ background:#eee; border-radius:6px; padding:6px 10px; font-size:12px; color:#555; flex-shrink:0; }
.blk_modal_btns{ display:flex; gap:10px; margin-top:6px; }
.blk_modal_btns button{ flex:1; border:none; border-radius:8px; padding:12px; font-size:14px; color:#fff; cursor:pointer; }
.blk_btn_cancel{ background:#8b5cf6; }
.blk_btn_ok{ background:#17b6d4; }
.blk_selected_count{ text-align:center; font-size:12px; color:#666; margin-top:8px; }
</style>

<div class="blk_tabs">
	<div id="blk_tab_btn_emo" class="blk_tab_btn blk_active" onclick="blkTab(1);"><?php echo $lang['blk_emoticons_title']; ?></div>
	<div id="blk_tab_btn_cat" class="blk_tab_btn" onclick="blkTab(2);"><?php echo $lang['blk_categories_label']; ?></div>
</div>

<!-- تبويب الإيموجيات: شريط الفئات + قائمة ملفات الفئة المختارة -->
<div id="blk_zone_emo" class="blk_zone">
	<div class="blk_purple_bar" id="blk_cat_strip">
		<div class="blk_plus_btn" onclick="blkOpenEmoModal();"><i class="fa fa-plus"></i></div>
	</div>
	<div id="blk_emo_list" class="blk_list"></div>
</div>

<!-- تبويب فئة الإيموجي: قائمة الفئات (الباقات) -->
<div id="blk_zone_cat" class="blk_zone" style="display:none;">
	<div class="blk_purple_bar">
		<div class="blk_plus_btn" onclick="blkOpenCatModal();"><i class="fa fa-plus"></i></div>
	</div>
	<div id="blk_cat_list" class="blk_list"></div>
</div>

<!-- نافذة: اختر ايموجي (رفع ملفات لفئة موجودة) -->
<div id="blk_modal_emo" class="blk_modal_overlay">
	<div class="blk_modal_box">
		<i class="fa fa-times blk_modal_close" onclick="blkCloseEmoModal();"></i>
		<div class="blk_modal_title"><?php echo $lang['blk_choose_emoji_title']; ?></div>
		<div class="blk_pick_btn" onclick="$('#blk_emo_file_input').click();"><i class="fa fa-paperclip"></i> <?php echo $lang['blk_choose_files']; ?></div>
		<input type="file" id="blk_emo_file_input" accept=".png,.svg,.gif" multiple style="display:none;" onchange="blkOnEmoFilesPicked();">
		<div class="blk_selected_count" id="blk_emo_selected_count"></div>
		<div class="blk_modal_btns">
			<button class="blk_btn_cancel" onclick="blkCloseEmoModal();"><?php echo $lang['cancel']; ?></button>
			<button class="blk_btn_ok" onclick="blkDoUploadFiles();"><?php echo $lang['add']; ?></button>
		</div>
	</div>
</div>

<!-- نافذة: إضافة فئة جديدة -->
<div id="blk_modal_cat" class="blk_modal_overlay">
	<div class="blk_modal_box">
		<i class="fa fa-times blk_modal_close" onclick="blkCloseCatModal();"></i>
		<div class="blk_modal_field">
			<div class="blk_modal_label"><?php echo $lang['blk_category_name']; ?></div>
			<input id="blk_new_cat_name" type="text"/>
		</div>
		<div class="blk_modal_field">
			<div class="blk_modal_label"><?php echo $lang['blk_category_type']; ?></div>
			<select id="blk_new_cat_type">
				<option value="normal"><?php echo $lang['blk_category_type_normal']; ?></option>
			</select>
		</div>
		<div class="blk_modal_field">
			<div class="blk_modal_label"><?php echo $lang['blk_choose_emoji_ext']; ?></div>
			<div class="blk_pick_row" onclick="$('#blk_new_cat_icon').click();">
				<div class="blk_pick_name_btn"><?php echo $lang['blk_choose_file']; ?></div>
				<span id="blk_new_cat_icon_name"><?php echo $lang['blk_no_file_chosen']; ?></span>
			</div>
			<input type="file" id="blk_new_cat_icon" accept=".png,.svg,.gif" style="display:none;" onchange="blkOnCatIconPicked();">
		</div>
		<div class="blk_modal_btns">
			<button class="blk_btn_cancel" onclick="blkCloseCatModal();"><?php echo $lang['cancel']; ?></button>
			<button class="blk_btn_ok" onclick="blkAddCategory();"><?php echo $lang['save']; ?></button>
		</div>
	</div>
</div>

<script data-cfasync="false">
var blkCurrentCat = 'base_emo';
var blkNoEmoText = '<?php echo $lang['blk_no_emo']; ?>';
var blkConfirmDeleteCat = '<?php echo $lang['blk_confirm_delete_cat']; ?>';
var blkNewCodePrompt = '<?php echo $lang['blk_new_code_prompt']; ?>';
var blkNoFileChosenText = '<?php echo $lang['blk_no_file_chosen']; ?>';
var blkChooseFileText = '<?php echo $lang['blk_choose_file']; ?>';
var blkDefaultIcon = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="%23ffd23f"/></svg>';

blkTab = function(t){
	if(t == 1){
		$('#blk_zone_emo').show();
		$('#blk_zone_cat').hide();
		$('#blk_tab_btn_emo').addClass('blk_active');
		$('#blk_tab_btn_cat').removeClass('blk_active');
		blkLoadCategoryStrip();
	}
	else {
		$('#blk_zone_emo').hide();
		$('#blk_zone_cat').show();
		$('#blk_tab_btn_cat').addClass('blk_active');
		$('#blk_tab_btn_emo').removeClass('blk_active');
		blkLoadCategoryManageList();
	}
}

// ------- نافذة إضافة إيموجي -------
blkOpenEmoModal = function(){
	$('#blk_emo_file_input').val('');
	$('#blk_emo_selected_count').text('');
	$('#blk_modal_emo').addClass('blk_show');
}
blkCloseEmoModal = function(){
	$('#blk_modal_emo').removeClass('blk_show');
}
blkOnEmoFilesPicked = function(){
	var input = $('#blk_emo_file_input')[0];
	$('#blk_emo_selected_count').text(input.files.length ? (input.files.length + ' ' + blkChooseFileText) : '');
}

// ------- نافذة إضافة فئة -------
blkOpenCatModal = function(){
	$('#blk_new_cat_name').val('');
	$('#blk_new_cat_icon').val('');
	$('#blk_new_cat_icon_name').text(blkNoFileChosenText);
	$('#blk_modal_cat').addClass('blk_show');
}
blkCloseCatModal = function(){
	$('#blk_modal_cat').removeClass('blk_show');
}
blkOnCatIconPicked = function(){
	var f = $('#blk_new_cat_icon')[0].files[0];
	$('#blk_new_cat_icon_name').text(f ? f.name : blkNoFileChosenText);
}

// ------- شريط الفئات أعلى تبويب الإيموجيات -------
blkLoadCategoryStrip = function(){
	$.post('addons/blk_emoticons/system/action.php', { op: 'list' }, function(response){
		var data = JSON.parse(response);
		if(!data || data.code != 1){
			callError(system.error);
			return;
		}
		var html = '<div class="blk_plus_btn" onclick="blkOpenEmoModal();"><i class="fa fa-plus"></i></div>';
		$.each(data.categories, function(i, cat){
			var icon = cat.icon ? cat.icon : blkDefaultIcon;
			var sel = (cat.name == blkCurrentCat) ? ' blk_selected' : '';
			html += '<img class="blk_cat_icon_btn'+sel+'" src="'+icon+'" title="'+cat.label+'" onclick="blkSelectCategory(\''+cat.name+'\');">';
		});
		$('#blk_cat_strip').html(html);
		blkLoadFiles();
	});
}

blkSelectCategory = function(name){
	blkCurrentCat = name;
	blkLoadCategoryStrip();
}

blkLoadFiles = function(){
	$.post('addons/blk_emoticons/system/action.php', { op: 'list_files', category: blkCurrentCat }, function(response){
		var data = JSON.parse(response);
		if(!data || data.code != 1){
			callError(system.error);
			return;
		}
		var html = '';
		$.each(data.files, function(i, f){
			html += '<div class="blk_row">';
			html += '<i class="fa fa-trash blk_row_del" onclick="blkDeleteFile(\''+f.file+'\');"></i>';
			html += '<span class="blk_row_name">'+f.code+'</span>';
			html += '<img class="blk_row_thumb" src="'+f.src+'" title=":'+f.code+':" onclick="blkRenameFile(\''+f.file+'\', \''+f.code+'\');">';
			html += '</div>';
		});
		if(data.files.length == 0){
			html = '<div class="blk_empty_row">'+blkNoEmoText+'</div>';
		}
		$('#blk_emo_list').html(html);
	});
}

blkDoUploadFiles = function(){
	var input = $('#blk_emo_file_input')[0];
	if(!input.files.length){
		callError(system.error);
		return;
	}
	var fd = new FormData();
	fd.append('op', 'upload');
	fd.append('category', blkCurrentCat);
	$.each(input.files, function(i, file){
		fd.append('emo[]', file);
	});
	$.ajax({
		url: 'addons/blk_emoticons/system/action.php',
		type: 'POST',
		data: fd,
		contentType: false,
		processData: false,
		success: function(response){
			var data = JSON.parse(response);
			$('#blk_emo_file_input').val('');
			if(data.code == 1){
				callSuccess(system.saved);
				blkCloseEmoModal();
				blkLoadFiles();
				blkLoadCategoryStrip();
			}
			else {
				callError(system.error);
			}
		},
		error: function(){ callError(system.error); }
	});
}

blkDeleteFile = function(file){
	$.post('addons/blk_emoticons/system/action.php', { op: 'delete_file', category: blkCurrentCat, file: file }, function(response){
		var data = JSON.parse(response);
		if(data.code == 1){
			blkLoadFiles();
			blkLoadCategoryStrip();
		}
		else {
			callError(system.error);
		}
	});
}

blkRenameFile = function(file, oldCode){
	var newCode = prompt(blkNewCodePrompt, oldCode);
	if(!newCode || newCode == oldCode){
		return;
	}
	$.post('addons/blk_emoticons/system/action.php', { op: 'rename_file', category: blkCurrentCat, file: file, new_code: newCode }, function(response){
		var data = JSON.parse(response);
		if(data.code == 1){
			callSuccess(system.saved);
			blkLoadFiles();
		}
		else {
			callError(system.error);
		}
	});
}

// ------- قائمة الفئات (تبويب فئة الإيموجي) -------
blkLoadCategoryManageList = function(){
	$.post('addons/blk_emoticons/system/action.php', { op: 'list' }, function(response){
		var data = JSON.parse(response);
		if(!data || data.code != 1){
			callError(system.error);
			return;
		}
		var html = '';
		$.each(data.categories, function(i, cat){
			var icon = cat.icon ? cat.icon : blkDefaultIcon;
			html += '<div class="blk_row">';
			if(cat.is_base){
				html += '<i class="fa fa-home blk_row_home"></i>';
			}
			else {
				html += '<i class="fa fa-trash blk_row_del" onclick="blkDeleteCategory(\''+cat.name+'\');"></i>';
			}
			html += '<span class="blk_row_name">'+cat.name+'</span>';
			html += '<img class="blk_row_thumb" src="'+icon+'">';
			html += '</div>';
		});
		$('#blk_cat_list').html(html);
	});
}

blkAddCategory = function(){
	var name = $('#blk_new_cat_name').val();
	if(!name){
		callError(system.error);
		return;
	}
	var fd = new FormData();
	fd.append('op', 'add_category');
	fd.append('name', name);
	var iconFile = $('#blk_new_cat_icon')[0].files[0];
	if(iconFile){
		fd.append('icon', iconFile);
	}
	$.ajax({
		url: 'addons/blk_emoticons/system/action.php',
		type: 'POST',
		data: fd,
		contentType: false,
		processData: false,
		success: function(response){
			var data = JSON.parse(response);
			if(data.code == 1){
				callSuccess(system.saved);
				blkCloseCatModal();
				blkLoadCategoryManageList();
			}
			else {
				callError(system.error);
			}
		},
		error: function(){ callError(system.error); }
	});
}

blkDeleteCategory = function(name){
	if(!confirm(blkConfirmDeleteCat)){
		return;
	}
	$.post('addons/blk_emoticons/system/action.php', { op: 'delete_category', name: name }, function(response){
		var data = JSON.parse(response);
		if(data.code == 1){
			callSuccess(system.saved);
			if(blkCurrentCat == name){
				blkCurrentCat = 'base_emo';
			}
			blkLoadCategoryManageList();
		}
		else {
			callError(system.error);
		}
	});
}

blkTab(1);
</script>
