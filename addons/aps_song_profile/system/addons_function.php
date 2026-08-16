<?php
function addonTemplate($target,$boom = ''){
	global $mysqli, $addons, $lang;
	return boomTemplate('../addons/'.$addons['addons'].'/system/template/'.$target, $boom); 
}
