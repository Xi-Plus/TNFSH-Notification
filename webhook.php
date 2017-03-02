<?php
date_default_timezone_set("Asia/Taipei");
require(__DIR__.'/config/config.php');
require(__DIR__.'/log.php');

$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'GET' && $_GET['hub_mode'] == 'subscribe' &&  $_GET['hub_verify_token'] == $C['FBWHtoken']) {
	echo $_GET['hub_challenge'];
} else if ($method == 'POST') {
	$inputJSON = file_get_contents('php://input');
	$time = date("Y-m-d H:i:s");
	$hash = md5(json_encode(array("time"=>$time, "input"=>$inputJSON)));
	$sth = $G["db"]->prepare("INSERT INTO `{$C['DBTBprefix']}input` (`time`, `input`, `hash`) VALUES (:time, :input, :hash)");
	$sth->bindValue(":time", $time);
	$sth->bindValue(":input", $inputJSON);
	$sth->bindValue(":hash", $hash);
	$res = $sth->execute();
	WriteLog("got: ".$inputJSON);
	pclose(popen("php follow.php", "r"));
}