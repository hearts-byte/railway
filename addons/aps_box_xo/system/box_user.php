<?php
$load_addons = 'aps_box_xo';
require_once('../../../system/config_addons.php');
$id = escape($_POST['xo']);
$data_xo = getDataXo($id);

$error_game = '<div class="modal_top"><div class="modal_top_empty bold"></div><div class="modal_top_element close_modal"><i class="fa fa-times"></i></div></div><p class="full_game">'.$lang['game_full'].'</p>';

if($data_xo['uid'] == $data['user_id']){
}else if($data_xo['uid'] != 0){
	die($error_game);
}
if($data['user_id'] == $data_xo['tid']){
	die($error_game);
}
?>
<div class="modal_top_xo">
	<span class="p_X"><b data="" id="xo_my"></b> <b class="p_Xred">VS</b> <b data="" id="xo_user"></b></span>
	<div class="modal_top_element">
		<i id="hide_custom" onclick="hideBoxXo();" class="fa fa-minus"></i>
	</div>
	<div class="modal_top_element close_modal_xo EndGameUser">
		<i class="fa fa-times"></i>
	</div>
</div>
 <div class="xo_players_row" id="xo_players_row">
	<span class="p_X"><b id="xo_my_inline"></b> <b class="p_Xred">VS</b> <b id="xo_user_inline"></b></span>
 </div>
 <p class="turns" id="turns"></p>
  <div data-uid="" data="" class="wrap">
   <div class="X_O" id="A1" onclick="insert(this.id);"></div>
   <div class="X_O" id="A2" onclick="insert(this.id);"></div>
   <div class="X_O" id="A3" onclick="insert(this.id);"></div>
   <div class="X_O" id="A4" onclick="insert(this.id);"></div>
   <div class="X_O" id="A5" onclick="insert(this.id);"></div>
   <div class="X_O" id="A6" onclick="insert(this.id);"></div>
   <div class="X_O" id="A7" onclick="insert(this.id);"></div>
   <div class="X_O" id="A8" onclick="insert(this.id);"></div>
   <div class="X_O" id="A9" onclick="insert(this.id);"></div>
  </div>
  <div class="user_win" id="win"></div>
<script>
let user_xo = <?php echo $id; ?>;
let gameEnded = false;
let wineer = "<?php echo $lang['wineer']; ?>";
let cen = "<?php echo $lang['cen']; ?>";
let error_xo = "<?php echo $lang['error_xo']; ?>";
let wait_start = "<?php echo $lang['wait_start']; ?>";
let start_me = "<?php echo $lang['start_me']; ?>";
function winnerEnds(){
	$("#A1 #A2 #A3 #A4 #A5 #A6 #A7 #A8 #A9").html('');
}
var wineers = [];
function winner(){
	for(var i=1; i<=9; i++){
		wineers[i] = document.getElementById('A'+i).innerHTML;
	}
	if(wineers[3]==wineers[5] && wineers[5]==wineers[7] && wineers[3] !=""){
		EndGameXo(user_xo,1);
		winnerEnds();
	}else
	if(wineers[1]==wineers[5] && wineers[5]==wineers[9] && wineers[1] !=""){
		EndGameXo(user_xo,1);
		winnerEnds();
	}else
	//-----
	if(wineers[1]==wineers[2] && wineers[2]==wineers[3] && wineers[1] !=""){
		EndGameXo(user_xo,1);
		winnerEnds();
	}else
	if(wineers[4]==wineers[5] && wineers[5]==wineers[6] && wineers[4] !=""){
		EndGameXo(user_xo,1);
		winnerEnds();
	}else
	if(wineers[7]==wineers[8] && wineers[8]==wineers[9] && wineers[7] !=""){
		EndGameXo(user_xo,1);
		winnerEnds();
	}else
	//-----
	if(wineers[1]==wineers[4] && wineers[4]==wineers[7] && wineers[1] !=""){
		EndGameXo(user_xo,1);
		winnerEnds();
	}else
	if(wineers[2]==wineers[5] && wineers[5]==wineers[8] && wineers[2] !=""){
		EndGameXo(user_xo,1);
		winnerEnds();
	}else
	if(wineers[3]==wineers[6] && wineers[6]==wineers[9] && wineers[3] !=""){
		EndGameXo(user_xo,1);
		winnerEnds();
	}else
	if(wineers[1] && wineers[2] && wineers[3] && wineers[4] && wineers[5] && wineers[6] && wineers[7] && wineers[8] && wineers[9] != ""){
		EndGameXo(user_xo,2);
		winnerEnds();
	}
	
}
showBoXAndInsertuser = function(){
	$.ajax({
		url: "addons/aps_box_xo/system/data_addon_user.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: {
			token: utk
		},
		success: function(response){
		var my = response.my;
		var user = response.user;
		//start
		$("#xo_my").html(my);
		$("#xo_user").html(user);
		$("#xo_my_inline").html(my);
		$("#xo_user_inline").html(user);
		$("#xo_my").attr("data", "o"); 
		$(".wrap").attr("data", response.turn); 
		$(".wrap").attr("data-uid", response.uid); 
		//A+i
		$("#A1").html(response.A1);
		$("#A2").html(response.A2);
		$("#A3").html(response.A3);
		$("#A4").html(response.A4);
		$("#A5").html(response.A5);
		$("#A6").html(response.A6);
		$("#A7").html(response.A7);
		$("#A8").html(response.A8);
		$("#A9").html(response.A9);
		if(response.win == 1){
			$("#win").html( wineer +':' + response.win_user);
		}else if(response.win == 2){
			$("#win").html(cen);
		}
		if(response.win == 1 || response.win == 2){
			if(!gameEnded){
				gameEnded = true;
				clearInterval(showBoXAndInsertsuser);
				setTimeout(function(){
					EndUserXO(user_xo);
					hideModalCustomes();
				}, 2500);
			}
		}
		if(response.uid == 0){
			$("#turns").html(wait_start);
		}else
		if(response.turn == user_id){
			$("#turns").html(start_me);
		}else{
			$("#turns").html('');
		}
		},
		error: function(){
			return false;
		}
	});
	
}
var showBoXAndInsertsuser = setInterval(showBoXAndInsertuser, 900);
showBoXAndInsertuser();
function insert(id){
	var turn = $('.wrap').attr('data');
	var uid = $('.wrap').attr('data-uid');
	var S = document.getElementById(id);
	var my = $('#xo_my').attr('data');
	if(user_id == turn && S.innerHTML ==""){
		S.innerHTML= my;
		saveMoveXouser(id,uid);
		$(".wrap").attr("data", 0); 
		winner();
	}else{
		callSaved(error_xo, 3);
	}
}
</script>
