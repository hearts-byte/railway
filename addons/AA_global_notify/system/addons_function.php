<?php
function aaGlobalNotifyRecentLogs($limit = 10){
	global $mysqli;
	$limit = (int) $limit;
	$logs = array();
	$find = $mysqli->query("
		SELECT aa_global_notify_log.*, boom_users.user_name
		FROM aa_global_notify_log
		LEFT JOIN boom_users ON aa_global_notify_log.sender_id = boom_users.user_id
		ORDER BY aa_global_notify_log.log_date DESC
		LIMIT $limit
	");
	if($find && $find->num_rows > 0){
		while($row = $find->fetch_assoc()){
			array_push($logs, $row);
		}
	}
	return $logs;
}
