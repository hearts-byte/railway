<?php
$load_addons = 'blk_emoticons';
require('../../../system/config_addons.php');

if(!canManageAddons()){
	die();
}
?>
<?php echo elementTitle($lang['blk_emoticons_title'], 'loadLob(\'admin/setting_addons.php\');'); ?>
<style>
.blk_emo_cat_row{ display:flex; align-items:center; gap:10px; padding:8px; border-bottom:1px solid rgba(120,120,120,.15); }
.blk_emo_cat_icon{ width:32px; height:32px; object-fit:contain; background:rgba(120,120,120,.08); border-radius:6px; }
.blk_emo_cat_name{ flex:1; }
.blk_emo_grid{ display:flex; flex-wrap:wrap; gap:8px; max-height:320px; overflow-y:auto; }
.blk_emo_item{ width:70px; text-align:center; position:relative; padding:6px; border:1px solid rgba(120,120,120,.15); border-radius:6px; }
.blk_emo_item img{ width:36px; height:36px; object-fit:contain; cursor:pointer; }
.blk_emo_item span{ display:block; font-size:11px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.blk_emo_del{ position:absolute; top:2px; left:2px; cursor:pointer; color:#e33; }
.blk_emo_modal_wrap{ position:fixed; top:0; right:0; bottom:0; left:0; background:rgba(0,0,0,.5); z-index:9999; display:flex; align-items:center; justify-content:center; }
.blk_emo_modal{ background:var(--bg-color, #fff); width:90%; max-width:480px; border-radius:8px; overflow:hidden; }
</style>
<div class="page_full">
	<div class="page_element">
		<div class="config_section">
			<p class="label"><?php echo $lang['blk_categories_label']; ?></p>
			<div id="blk_categories_list" class="blk_emo_categories"></div>
		</div>
		<div class="config_section">
			<p class="label"><?php echo $lang['blk_add_category']; ?></p>
			<div class="setting_element">
				<input id="blk_new_cat_name" class="full_input" type="text" placeholder="<?php echo $lang['blk_category_name']; ?>"/>
			</div>
			<div class="setting_element">
				<p class="ex_admin sub_text"><?php echo $lang['blk_category_icon']; ?></p>
				<input id="blk_new_cat_icon" type="file" accept=".png,.svg,.gif"/>
			</div>
			<button onclick="blkAddCategory();" type="button" class="tmargin10 reg_button theme_btn"><i class="fa fa-plus"></i> <?php echo $lang['blk_add_btn']; ?></button>
		</div>
	</div>
</div>

<div id="blk_manage_modal" class="blk_emo_modal_wrap" style="display:none;">
	<div class="blk_emo_modal">
		<div class="top_mod">
			<div class="top_mod_empty pad10">
				<b id="blk_manage_title"></b>
			</div>
			<div class="top_mod_option" onclick="blkCloseManage();">
				<i class="fa fa-times"></i>
			</div>
		</div>
		<div class="pad10">
			<div id="blk_files_grid" class="blk_emo_grid"></div>
			<div class="setting_element tmargin10">
				<input id="blk_upload_input" type="file" accept=".png,.svg,.gif" multiple/>
				<button onclick="blkUploadFiles();" type="button" class="tmargin10 reg_button theme_btn"><i class="fa fa-upload"></i> <?php echo $lang['blk_upload_btn']; ?></button>
			</div>
			<p class="ex_admin sub_text"><?php echo $lang['blk_manage_hint']; ?></p>
		</div>
	</div>
</div>

<script data-cfasync="false">
var blkCurrentCat = '';
var blkCurrentLabel = '';
var blkNoEmoText = '<?php echo $lang['blk_no_emo']; ?>';
var blkConfirmDeleteCat = '<?php echo $lang['blk_confirm_delete_cat']; ?>';
var blkNewCodePrompt = '<?php echo $lang['blk_new_code_prompt']; ?>';

blkLoadCategories = function(){
	$.post('addons/blk_emoticons/system/action.php', { op: 'list' }, function(response){
		var data = JSON.parse(response);
		if(!data || data.code != 1){
			callError(system.error);
			return;
		}
		var html = '';
		$.each(data.categories, function(i, cat){
			html += '<div class="blk_emo_cat_row">';
			if(cat.icon){
				html += '<img class="blk_emo_cat_icon" src="'+cat.icon+'">';
			}
			else {
				html += '<div class="blk_emo_cat_icon"></div>';
			}
			html += '<span class="blk_emo_cat_name">'+cat.label+' <small>('+cat.count+')</small></span>';
			html += '<button type="button" class="reg_button theme_btn" onclick="blkManageCategory(\''+cat.name+'\', \''+cat.label+'\');"><i class="fa fa-cog"></i></button>';
			if(!cat.is_base){
				html += '<button type="button" class="reg_button" onclick="blkDeleteCategory(\''+cat.name+'\');"><i class="fa fa-trash"></i></button>';
			}
			html += '</div>';
		});
		$('#blk_categories_list').html(html);
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
				$('#blk_new_cat_name').val('');
				$('#blk_new_cat_icon').val('');
				blkLoadCategories();
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
			blkLoadCategories();
		}
		else {
			callError(system.error);
		}
	});
}

blkManageCategory = function(name, label){
	blkCurrentCat = name;
	blkCurrentLabel = label;
	$('#blk_manage_title').text(label);
	$('#blk_manage_modal').show();
	blkLoadFiles();
}

blkCloseManage = function(){
	$('#blk_manage_modal').hide();
	blkCurrentCat = '';
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
			html += '<div class="blk_emo_item">';
			html += '<img src="'+f.src+'" title=":'+f.code+':" onclick="blkRenameFile(\''+f.file+'\', \''+f.code+'\');">';
			html += '<span>:'+f.code+':</span>';
			html += '<i class="fa fa-times blk_emo_del" onclick="blkDeleteFile(\''+f.file+'\');"></i>';
			html += '</div>';
		});
		if(data.files.length == 0){
			html = '<p class="ex_admin sub_text">'+blkNoEmoText+'</p>';
		}
		$('#blk_files_grid').html(html);
	});
}

blkUploadFiles = function(){
	var input = $('#blk_upload_input')[0];
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
			if(data.code == 1){
				callSuccess(system.saved);
				$('#blk_upload_input').val('');
				blkLoadFiles();
				blkLoadCategories();
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
			blkLoadCategories();
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

blkLoadCategories();
</script>
