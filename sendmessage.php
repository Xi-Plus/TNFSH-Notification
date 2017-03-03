<?php
function SendMessage($tmid, $message) {
	global $C;
	$post = array(
		"message" => $message,
		"access_token" => $C['FBpagetoken']
	);
	$res = cURL($C['FBAPI'].$tmid."/messages", $post);
	WriteLog("send message: tmid=".$tmid." message=".$message);
	$res = json_decode($res, true);
	if (isset($res["error"])) {
		WriteLog("send message error: tmid=".$tmid." msg=".$message." res=".json_encode($res));
		return false;
	}
	return true;
}
