<?php
if(!function_exists('addonTemplate')){
    function addonTemplate($target, $boom = ''){
	    global $mysqli,$data,$addons,$lang;
	    return boomAddonsTemplate('../addons/'.$addons['addons'].'/system/template/'.$target, $boom); 
    }
}
function frames_list_admin(){
	global $mysqli, $data, $addons, $lang;
	$list = [];
	if(($cache = redisGetObject('avatar_frame:list'))){
		return createPag($cache, 24, array('template'=> '../addons/'.BOOM_ADDONS.'/system/template/frame_item_admin', 'style'=> 'list'));
	}
	$frames = $mysqli->query("SELECT * FROM avatar_frame ORDER BY id ASC");
	if($frames->num_rows > 0){
		while($frame = $frames->fetch_assoc()){
		    $list[] = $frame;
    	}
		redisSetObject('avatar_frame:list', $list);
	}
	return createPag($list, 24, array('template'=> '../addons/'.BOOM_ADDONS.'/system/template/frame_item_admin', 'style'=> 'list'));
}
function frames_box_list(){
	global $mysqli, $data, $addons, $lang;
	$list = [];
	$frames = $mysqli->query("SELECT * FROM avatar_frame ORDER BY id ASC");
	if($frames->num_rows > 0){
		while($frame = $frames->fetch_assoc()){
			$list[] = $frame;
		}
	}
	return createPag($list, 24, array('template'=> '../addons/'.BOOM_ADDONS.'/system/template/frame_item', 'style'=> 'list'));
}
