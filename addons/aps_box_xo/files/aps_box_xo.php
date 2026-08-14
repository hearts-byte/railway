<div id="large_modal_xo" class="large_modal_out_custom ui-draggable">
	<div id="large_modal_custom" class="large_modal_custom modal_in">
		<div id="large_modal_content_xo" class="modal_content large_modal_content_xo">
		</div>
	</div>
</div>
<script>
boomAddCss('addons/aps_box_xo/files/style.css');
showEmptyModalCustom = function(r,s){
	hideAll();
	hideModal();
	if(!s){
		s = 400;
	}
	if(s == 0){
		s = 400;
	}
	$('.large_modal_custom').css('max-width', s+'px');
	$('#large_modal_content_xo').html(r);
	$('#large_modal_xo').show();
	offScroll();
	modalTop();
	selectIt();
}
var windWidth = $(window).width();
hideBoxXo = function(){
	$('#hide_xo_modal').toggleClass('hideXo');
    $('#hide_custom').toggleClass('fa-plus');
    $('#hide_custom').toggleClass('fa-minus');
}
hideModalCustomes = function(){
	$('#large_modal_content_xo').html('');
	$('#modal_top_xo').hide();
	$('.modal_top_xo').removeClass('EndGame');
	$('.close_modal_xo').removeClass('EndGameUser');
	onScroll();
}
$(document).on('click', '.close_modal_xo', function(){
		hideModalCustomes();
});
draggableXo = function(){
	$( "#large_modal_xo" ).draggable({
		handle: "#move_Xo",
		containment: "document",
	});
}
$(document).ready(function(){
	boxXO = function(){
	startXO();
	draggableXo();
	$.post('addons/aps_box_xo/system/box.php', { 
		token: utk,
		}, function(response) {
			if(windWidth < rightHide){
				showEmptyModalCustom(response, 330);
			}else{
				showEmptyModalCustom(response, 420);
			}
	});
   }
   boxXOPriv = function(){
	startXOPriv();
	$.post('addons/aps_box_xo/system/box.php', { 
		token: utk,
		}, function(response) {
			if(windWidth < rightHide){
				showEmptyModalCustom(response, 330);
			}else{
				showEmptyModalCustom(response, 420);
			}
	});
   }
   userBoxXO = function(id,my){
	draggableXo();
	$.post('addons/aps_box_xo/system/box_user.php', { 
	    xo: id,
		token: utk,
		}, function(response) {
		if(user_id != my){
		  startXoUser(id);
			if(windWidth < rightHide){
				showEmptyModalCustom(response, 330);
			}else{
				showEmptyModalCustom(response, 420);
			}
		}
	});
    }
	appInputMenu('addons/aps_box_xo/files/icon.png', 'boxXO();');
	appPrivInputMenu('addons/aps_box_xo/files/icon.png', 'boxXOPriv();');
});
saveMoveXouser = function(id,uid){
	$.post('addons/aps_box_xo/system/action.php', {
		    saveMoveXouser: 1,
		    id: id,
		    uid: uid,
			token: utk,
		}, function(response) {
	});
}
saveMoveXo = function(id,uid){
	$.post('addons/aps_box_xo/system/action.php', {
		    saveMoveXo: 1,
		    id: id,
		    uid: uid,
			token: utk,
		}, function(response) {
	});
}
startXOPriv = function(){
	var target = $('#get_private').attr('value');
	$.post('addons/aps_box_xo/system/action.php', {
		    startXOPriv: 1,
		    target: target,
			token: utk,
		}, function(response) {
			$('#message_content').focus();
			$("#private_content ul").append(response);
			scrollPriv(1); 
	});
}
startXO = function(){
	$.post('addons/aps_box_xo/system/action.php', {
		    startXO: 1,
			token: utk,
		}, function(response) {
	});
}
startXoUser = function(id){
	$.post('addons/aps_box_xo/system/action.php', {
		    startXoUser: 1,
		    id: id,
			token: utk,
		}, function(response) {
	});
}
EndGameXo = function(id,type){
	$.post('addons/aps_box_xo/system/action.php', {
		    type: type,
		    id: id,
		    EndGameXo: 1,
			token: utk,
		}, function(response) {
			
	    });
}
EndXO = function(id){
	$.post('addons/aps_box_xo/system/action.php', {
		    id: id,
		    EndXO: 1,
			token: utk,
		}, function(response) {
	});
}
EndUserXO = function(id){
	$.post('addons/aps_box_xo/system/action.php', {
		    id: id,
		    EndUserXO: 1,
			token: utk,
		}, function(response) {
	});
}
$(document).on('click', '.EndGame', function(){
	    var id = $(this).attr('data');
		clearInterval(showBoXAndInserts);
		EndXO(id);
});
$(document).on('click', '.EndGameUser', function(){
	    var id = $(this).attr('data');
		clearInterval(showBoXAndInsertsuser);
		EndUserXO(id);
});
</script>