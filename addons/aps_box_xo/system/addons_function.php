<?php
function getDataXo($id){
	global $mysqli;
	$datas = array();
	$get_data = $mysqli->query("SELECT * FROM ps_box_xo WHERE id = '$id'");
	if($get_data->num_rows > 0){
		$datas = $get_data->fetch_assoc();
	}
	return $datas;
}
?>