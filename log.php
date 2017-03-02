<?php
function WriteLog($message="") {
	global $C, $G;
	$time = date("Y-m-d H:i:s");
	$hash = md5(json_encode(array("time"=>$time, "message"=>$message)));
	$sth = $G["db"]->prepare("INSERT INTO `{$C['DBTBprefix']}log` (`time`, `message`, `hash`) VALUES (:time, :message, :hash)");
	$sth->bindValue(":time", $time);
	$sth->bindValue(":message", $message);
	$sth->bindValue(":hash", $hash);
	$sth->execute();
}
