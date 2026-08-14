<?php if(boomAllow($addons['addons_access']) ){  ?>
<script data-cfasync="false">
$(document).ready(function(){
	appInputMenu('addons/<?php echo $addons['addons']; ?>/files/icon.png', 'avatar_frame_box();');
});
avatar_frame_box = function(t) {
	$.post('addons/<?php echo $addons['addons']; ?>/system/action.php', {
	get_box: 1
	}, function(response) {
	   showModal(response,480);
	});
}
unset_frame = function(frame) {
	$.post('addons/<?php echo $addons['addons']; ?>/system/action.php', {
		unset_frame: 1
	}, function(response) {
		if(response == 1){
			callSuccess(system.saved); 
			hideModal();
		} else {
			callError(system.error);
		}
	});
}
set_frame = function(id){
	$.ajax({
		url: 'addons/<?php echo $addons['addons']; ?>/system/action.php',
		type: "post",
		cache: false,
		dataType: 'json',
		data: { set_frame: id },
		success: function(response){
			if(response == 1){
				hideModal();
				update_frame(); 
	            update_frame_userlist(); 
                update_frame_chat(); 
				callSuccess(system.saved);
			} else {
				callError(system.error);
			}
		},
		error: function(){
			return false;
		}
	});
}
</script>
<?php } ?>
<script data-cfasync="false">
var sttData = '';
var rqWait = 0;
update_frame = function(sel,cls){
	if (rqWait == 0) {
		rqWait = 1;
		$.post('addons/<?php echo $addons['addons']; ?>/system/action.php', {get_user_frames_list: 1,token: utk }, 
		function (response) {
			sttData = JSON.parse(response);
			rqWait = 0;
		}); 
	}
}
avatar_frame_observer = function(target){
	const observer = new MutationObserver(function (mutations) { mutations.forEach((mutation) => { 
		update_frame_userlist(); 
		update_frame_chat(); 
    }); });	 
	observer.observe(target, { attributes: true,childList: true,characterData: true,});
}
update_frame_userlist = function(){
	const this_stt = document.querySelectorAll('.user_item_avatar');
	this_stt.forEach((item) => { 
		let $nodes = $(item); 	
		$nodes.each(function(){
			$(this).children().each(function(){
				if ($(this).hasClass('frame_class_userlist') ) $(this).remove();
			})
		})
		const uID = ($nodes.parent().attr('data-id').toString());
		sttData.filter( (item) => item.user_id === uID,).forEach((item) => {
			$nodes.append('<img class="frame_class_userlist" src="addons/<?php echo $addons['addons']; ?>/files/frames/'+item.tumb+'" />');
		});
	});
    
}
update_frame_chat = function(){
	const this_stt = document.querySelectorAll('.chat_avatar');
	this_stt.forEach((item) => { 
		let $nodes = $(item); 	
		$nodes.each(function(){
			$(this).children().each(function(){
				if ($(this).hasClass('frame_class_chat') ) $(this).remove();
			})
		})
		const uID = ($nodes.attr('data-id').toString());
		sttData.filter( (item) => item.user_id === uID,).forEach((item) => {
			$nodes.append('<img class="frame_class_chat" src="addons/<?php echo $addons['addons']; ?>/files/frames/'+item.tumb+'" />');
		});
	});
}
update_frame();
updcsr = setInterval(update_frame, 60000);
avatar_frame_observer($("#chat_right_data")[0]);
avatar_frame_observer($("#chat_logs_container")[0]);
</script>
<style><?php require_once('style.css'); ?></style>