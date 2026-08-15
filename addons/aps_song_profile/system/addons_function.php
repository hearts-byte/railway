<?php
function addonTemplate($target,$boom = ''){
	global $mysqli, $data, $lang;
	return boomTemplate('../addons/'.$data['addons'].'/system/template/'.$target, $boom); 
}