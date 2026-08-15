<?php
include(addonsLang('AA_userlist_glow'));

$user_glow = array();
$find_user_glow = $mysqli->query("SELECT user_id, glow_color FROM userlist_glow");
if($find_user_glow && $find_user_glow->num_rows > 0){
	while($row = mysqli_fetch_object($find_user_glow)){
		array_push($user_glow, $row);
	}
}
if($find_user_glow){
	mysqli_free_result($find_user_glow);
}

$jsonco = array('glowUser' => $user_glow);
?>
<script data-cfasync="false" src="addons/AA_userlist_glow/files/spectrum.js?v=1.49"></script>
<script data-cfasync="false" type="text/javascript">
	window.userData = <?php echo json_encode($jsonco, JSON_HEX_TAG); ?>;
	var aaGlowSaved = 'Zapisano';
	var aaGlowRemoved = 'Poswiata zostala usunieta';
	var aaGlowError = 'Nie udalo sie zapisac poswiaty';
	var aaGlowConfig = {
		attributes: false,
		childList: true,
		characterData: false,
		subtree: true
	};
	var aaGlowRefreshRequest = null;
	var aaGlowRefreshTimer = null;
	var aaGlowLastRefresh = 0;
	var aaGlowMinRefresh = 60000;
	var aaGlowApplyTimer = null;

	$(document).ready(function() {
		boomAddCss('addons/AA_userlist_glow/files/spectrum.css');

		aaGlowNormalize = function(){
			if(!userData || !Array.isArray(userData.glowUser)){
				userData = { glowUser: [] };
			}
		}
		aaGlowSetLocal = function(userid, color){
			userid = String(userid);
			aaGlowNormalize();
			userData.glowUser = userData.glowUser.filter(function(item){
				return String(item.user_id) !== userid;
			});
			if(color !== ''){
				userData.glowUser.push({
					user_id: userid,
					glow_color: color
				});
			}
		}
		aaGlowGetUserId = function(item){
			var node = $(item);
			var userId = node.data('id') || node.attr('data-id') || node.find('.get_info').attr('data') || '';
			if(userId !== ''){
				return String(userId).replace(/\D/g, '');
			}
			return '';
		}
		getUserGlow = function(userid){
			aaGlowNormalize();
			return userData.glowUser.filter(function(item){
				return String(item.user_id) === String(userid) && item.glow_color !== '';
			});
		}
		aaGlowStyle = function(color){
			return {
				'box-shadow': '0 0 15px ' + color + ', 0 0 6px ' + color + ' inset',
				'border': '1px solid ' + color,
				'border-radius': '5px'
			};
		}
		aaGlowClearItem = function(item){
			$(item).css({
				'box-shadow': '',
				'border': '',
				'border-radius': ''
			});
		}
		aaGlowApplyItem = function(item){
			var node = $(item);
			var userId = aaGlowGetUserId(item);
			aaGlowClearItem(item);
			if(userId === ''){
				return;
			}
			var glow = getUserGlow(userId);
			if(glow.length && glow[0].glow_color !== ''){
				node.css(aaGlowStyle(glow[0].glow_color));
			}
		}
		aaGlowApplyAll = function(){
			document.querySelectorAll('.user_item').forEach(function(item){
				aaGlowApplyItem(item);
			});
		}
		aaGlowScheduleApply = function(){
			clearTimeout(aaGlowApplyTimer);
			aaGlowApplyTimer = setTimeout(function(){
				aaGlowApplyAll();
			}, 120);
		}
		aaGlowRefresh = function(callback, force){
			var now = Date.now();
			if(aaGlowRefreshRequest){
				if(typeof callback === 'function'){
					callback();
				}
				return;
			}
			if(force !== true && (now - aaGlowLastRefresh) < aaGlowMinRefresh){
				if(typeof callback === 'function'){
					callback();
				}
				return;
			}
			aaGlowLastRefresh = now;
			aaGlowRefreshRequest = $.ajax({
				url: 'addons/AA_userlist_glow/system/api.php',
				type: 'post',
				cache: false,
				timeout: 8000,
				data: { token: utk }
			}).done(function(response){
				try {
					userData = (typeof response === 'string') ? JSON.parse(response) : response;
				}
				catch(e) {}
				aaGlowNormalize();
				if(typeof callback === 'function'){
					callback();
				}
			}).fail(function(){
				if(typeof callback === 'function'){
					callback();
				}
			}).always(function(){
				aaGlowRefreshRequest = null;
			});
		}

		$(document).on('click', '.get_info', function(){
			var userId = $(this).attr('data') || $(this).data('id') || '';
			$('.large_modal_in').css({
				'box-shadow': '',
				'border': ''
			});
			getUserGlow(userId).forEach(function(glow){
				$('.large_modal_in').css({
					'box-shadow': '0 0 19px ' + glow.glow_color,
					'border': '2px solid ' + glow.glow_color
				});
			});
		});

		var target = $('#chat_right_data')[0];
		if(target){
			var observerUserListGlow = new MutationObserver(function(){
				aaGlowScheduleApply();
				clearTimeout(aaGlowRefreshTimer);
				aaGlowRefreshTimer = setTimeout(function(){
					aaGlowRefresh(aaGlowApplyAll);
				}, 1500);
			});
			observerUserListGlow.observe(target, aaGlowConfig);
		}
		aaGlowApplyAll();
	});
</script>
<?php if (boomAllow($addons['addons_access'])) { ?>
	<script data-cfasync="false" type="text/javascript">
		$(document).ready(function() {
			<?php if (boomAllow($addons['custom1'])) { ?>
				$(".avstaff").append("<div data='' onclick='showListGlowBoxStaff(this)' class='avset avitem rcustom'><span class='list_icon'><i class='fa fa-paint-brush bgrad24'></i></span> <?php echo $lang['open_list_glow']; ?></div>");
			<?php } ?>
			$(".avself").append("<div data='' onclick='showListGlowBox(this)' class='avset avitem rcustom'><span class='list_icon'><i class='fa fa-paint-brush bgrad24'></i></span> <?php echo $lang['open_list_glow']; ?></div>");
			showListGlowBoxStaff = function(source) {
				var target = $(source).attr('data');
				$.post('addons/AA_userlist_glow/system/open_colors_staff.php', {
					target: target,
					token: utk,
				}, function(response) {
					if (response == 0) {
						callSaved(aaGlowError, 3);
					} else {
						showEmptyModal(response, 300);
					}
				});
			}
			showListGlowBox = function() {
				$.post('addons/AA_userlist_glow/system/open_colors.php', {
					token: utk,
				}, function(response) {
					if (response == 0) {
						callSaved(aaGlowError, 3);
					} else {
						showEmptyModal(response, 300);
					}
				});
			}
		});
	</script>
<?php } ?>
