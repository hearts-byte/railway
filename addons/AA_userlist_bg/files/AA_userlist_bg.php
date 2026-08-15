<?php
include(addonsLang('AA_userlist_bg'));

$list_bg = array();
$find_bg = $mysqli->query("SELECT user_id, bg_file FROM userlist_bg");

if($find_bg && $find_bg->num_rows > 0){
	while($row = mysqli_fetch_object($find_bg)){
		array_push($list_bg, $row);
	}
}

if($find_bg){
	mysqli_free_result($find_bg);
}

$jsonco = array('bgUser' => $list_bg);
?>

<style>

.user_item.aa_ulbg_set{
	position:relative;
	overflow:hidden;
	isolation:isolate;
	border-radius:6px;

	display:flex;
	align-items:center;

	min-height:58px;
	box-sizing:border-box;
}

.user_item.aa_ulbg_set .aa_ulbg_layer,
.user_item.aa_ulbg_set .aa_ulbg_overlay{
	position:absolute;
	inset:0;
	pointer-events:none;
}

.user_item.aa_ulbg_set .aa_ulbg_layer{
	z-index:0;
	background-repeat:no-repeat;
	background-position:center center;
	background-size:cover;
}

.user_item.aa_ulbg_set .aa_ulbg_overlay{
	z-index:1;
	background:linear-gradient(
		90deg,
		rgba(0,0,0,0.58),
		rgba(0,0,0,0.2) 56%,
		rgba(0,0,0,0.58)
	);
}

.user_item.aa_ulbg_set > *{
	position:relative;
	z-index:2;
}

.user_item.aa_ulbg_set .avatar,
.user_item.aa_ulbg_set .user_avatar{
	flex-shrink:0;
}

.user_item.aa_ulbg_set .user_name,
.user_item.aa_ulbg_set .user_details,
.user_item.aa_ulbg_set .user_text{
	flex:1;
	min-width:0;
}

.aa_ulbg_preview{
	width:100%;
	height:86px;
	margin-bottom:10px;
	border-radius:8px;
	border:1px solid rgba(255,255,255,0.14);
	background-size:cover;
	background-position:center;
	background-repeat:no-repeat;
}

</style>

<script data-cfasync="false" type="text/javascript">

window.aaListBgData = <?php echo json_encode($jsonco, JSON_HEX_TAG); ?>;

var aaListBgSaved = '<?php echo $lang['list_bg_saved']; ?>';
var aaListBgRemoved = '<?php echo $lang['list_bg_removed']; ?>';
var aaListBgError = '<?php echo $lang['list_bg_error']; ?>';

var aaListBgRefreshRequest = null;
var aaListBgRefreshTimer = null;
var aaListBgLastRefresh = 0;
var aaListBgMinRefresh = 60000;
var aaListBgApplyTimer = null;

$(document).ready(function(){

	aaListBgNormalize = function(){
		if(!aaListBgData || !Array.isArray(aaListBgData.bgUser)){
			aaListBgData = { bgUser: [] };
		}
	}

	aaListBgSetLocal = function(userid, file){

		userid = String(userid);

		aaListBgNormalize();

		aaListBgData.bgUser = aaListBgData.bgUser.filter(function(item){
			return String(item.user_id) !== userid;
		});

		if(file !== ''){
			aaListBgData.bgUser.push({
				user_id: userid,
				bg_file: file
			});
		}
	}

	aaListBgGetUserId = function(item){

		var node = $(item);

		var userId =
			node.data('id') ||
			node.attr('data-id') ||
			node.find('.get_info').attr('data') ||
			'';

		if(userId !== ''){
			return String(userId).replace(/\D/g, '');
		}

		return '';
	}

	aaListBgGet = function(userid){

		aaListBgNormalize();

		var found = aaListBgData.bgUser.filter(function(item){
			return String(item.user_id) === String(userid)
			&& item.bg_file !== '';
		});

		return found.length ? found[0].bg_file : '';
	}

	aaListBgUrl = function(file){
		return 'addons/AA_userlist_bg/files/background/' + encodeURIComponent(file);
	}

	aaListBgApplyItem = function(item){

		var node = $(item);

		var userId = aaListBgGetUserId(item);

		node.removeClass('aa_ulbg_set');

		node.find('> .aa_ulbg_layer, > .aa_ulbg_overlay').remove();

		if(userId === ''){
			return;
		}

		var bg = aaListBgGet(userId);

		if(bg !== ''){

			node.addClass('aa_ulbg_set');

			node.append(
				'<span class="aa_ulbg_layer" style="background-image:url(\'' + aaListBgUrl(bg) + '\');"></span>' +
				'<span class="aa_ulbg_overlay"></span>'
			);
		}
	}

	aaListBgApplyAll = function(){

		document.querySelectorAll('.user_item').forEach(function(item){
			aaListBgApplyItem(item);
		});
	}

	aaListBgScheduleApply = function(){

		clearTimeout(aaListBgApplyTimer);

		aaListBgApplyTimer = setTimeout(function(){
			aaListBgApplyAll();
		}, 120);
	}

	aaListBgRefresh = function(callback, force){

		var now = Date.now();

		if(aaListBgRefreshRequest){

			if(typeof callback === 'function'){
				callback();
			}

			return;
		}

		if(force !== true && (now - aaListBgLastRefresh) < aaListBgMinRefresh){

			if(typeof callback === 'function'){
				callback();
			}

			return;
		}

		aaListBgLastRefresh = now;

		aaListBgRefreshRequest = $.ajax({
			url: 'addons/AA_userlist_bg/system/api.php',
			type: 'post',
			cache: false,
			timeout: 8000,
			data: { token: utk }

		}).done(function(response){

			try{
				aaListBgData =
					(typeof response === 'string')
					? JSON.parse(response)
					: response;
			}
			catch(e){}

			aaListBgNormalize();

			if(typeof callback === 'function'){
				callback();
			}

		}).always(function(){
			aaListBgRefreshRequest = null;
		});
	}

	var target = $('#chat_right_data')[0];

	if(target){

		var observerUserListBg = new MutationObserver(function(){

			aaListBgScheduleApply();

			clearTimeout(aaListBgRefreshTimer);

			aaListBgRefreshTimer = setTimeout(function(){
				aaListBgRefresh(aaListBgApplyAll);
			}, 1500);
		});

		observerUserListBg.observe(target, {
			attributes:false,
			childList:true,
			characterData:false,
			subtree:true
		});
	}

	aaListBgApplyAll();
});

</script>

<?php if (boomAllow($addons['addons_access'])) { ?>

<script data-cfasync="false" type="text/javascript">

$(document).ready(function(){

	<?php if (boomAllow($addons['custom1'])) { ?>

	$(".avstaff").append(
		"<div data='' onclick='showListBgBoxStaff(this)' class='avset avitem rcustom'>" +
		"<span class='list_icon'>" +
		"<i class='fa fa-image bgrad24'></i>" +
		"</span> <?php echo $lang['open_list_bg']; ?></div>"
	);

	<?php } ?>

	$(".avself").append(
		"<div data='' onclick='showListBgBox(this)' class='avset avitem rcustom'>" +
		"<span class='list_icon'>" +
		"<i class='fa fa-image bgrad24'></i>" +
		"</span> <?php echo $lang['open_list_bg']; ?></div>"
	);

	showListBgBoxStaff = function(source){

		var target = $(source).attr('data');

		$.post(
			'addons/AA_userlist_bg/system/open_bg_staff.php',
			{
				target: target,
				token: utk
			},
			function(response){

				if(response == 0){
					callSaved(aaListBgError, 3);
				}
				else{
					showEmptyModal(response, 360);
				}
			}
		);
	}

	showListBgBox = function(){

		$.post(
			'addons/AA_userlist_bg/system/open_bg.php',
			{
				token: utk
			},
			function(response){

				if(response == 0){
					callSaved(aaListBgError, 3);
				}
				else{
					showEmptyModal(response, 360);
				}
			}
		);
	}
});

</script>

<?php } ?>