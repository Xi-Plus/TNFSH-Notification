<?php
date_default_timezone_set("Asia/Taipei");
require(__DIR__.'/config/config.php');
require(__DIR__.'/curl.php');
require(__DIR__.'/log.php');

$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'GET' && $_GET['hub_mode'] == 'subscribe' &&  $_GET['hub_verify_token'] == $C['FBWHtoken']) {
	echo $_GET['hub_challenge'];
} else if ($method == 'POST') {
	$inputJSON = file_get_contents('php://input');
	$input = json_decode($inputJSON, true);
	function SendMessage($tmid, $message, $token) {
		global $C;
		$post = array(
			"message" => $message,
			"access_token" => $C['FBpagetoken']
		);
		$res = cURL($C['FBAPI'].$tmid."/messages", $post);
		$res = json_encode($res, true);
		if (isset($res["error"])) {
			WriteLog("send message error: tmid=".$tmid." res=".json_encode($res));
		}
	}
	function GetTmid() {
		$res = cURL($C['FBAPI']."me/conversations?fields=participants,updated_time&access_token=".$C['FBpagetoken']);
		$updated_time = file_get_contents("updated_time.txt");
		$newesttime = $updated_time;
		while (true) {
			$res = json_decode($res, true);
			if (count($res["data"]) == 0) {
				break;
			}
			foreach ($res["data"] as $data) {
				if ($data["updated_time"] <= $updated_time) {
					break 2;
				}
				if ($data["updated_time"] > $newesttime) {
					$newesttime = $data["updated_time"];
				}
				foreach ($data["participants"]["data"] as $participants) {
					if ($participants["id"] != $C['FBpageid']) {
						$sth = $G["db"]->prepare("INSERT INTO `{$C['DBTBprefix']}user` (`uid`, `tmid`, `name`) VALUES (:uid, :tmid, :name)");
						$sth->bindValue(":uid", $participants["id"]);
						$sth->bindValue(":tmid", $data["id"]);
						$sth->bindValue(":name", $participants["name"]);
						$res= $sth->execute();
						break;
					}
				}
			}
			$res = cURL($res["paging"]["next"]);
		}
		file_put_contents("updated_time.txt", $newesttime);
	}
	foreach ($input['entry'] as $entry) {
		foreach ($entry['messaging'] as $messaging) {
			$mmid = "m_".$messaging['message']['mid'];
			$res = cURL($C['FBAPI'].$mmid."?fields=from&access_token=".$C['FBpagetoken']);
			$res = json_decode($res, true);
			$uid = $res["from"]["id"];

			$sth = $G["db"]->prepare("SELECT * FROM `{$C['DBTBprefix']}user` WHERE `uid` = :uid");
			$sth->bindValue(":uid", $uid);
			$sth->execute();
			$row = $sth->fetch(PDO::FETCH_ASSOC);
			if ($row === false) {
				GetTmid();
				$sth->execute();
				$row = $sth->fetch(PDO::FETCH_ASSOC);
				WriteLog("get uid ".$uid);
				if ($row === false) {
					WriteLog("uid not found ".json_encode($messaging));
					continue;
				}
			}
			$tmid = $row["tmid"];
			if (!isset($messaging['message']['text'])) {
				SendMessage($tmid, $M["nottext"]);
				continue;
			}
			$msg = $messaging['message']['text'];
			if ($msg[0] !== "/") {
				SendMessage($tmid, $M["notcommand"]);
				continue;
			}
			$msg = str_replace("\n", " ", $msg);
			$msg = preg_replace("/\s+/", " ", $msg);
			$cmd = explode(" ", $msg);
			switch ($cmd[0]) {
				case '/start':
					$sth = $G["db"]->prepare("UPDATE `{$C['DBTBprefix']}user` SET `fbmessage` = '1' WHERE `tmid` = :tmid");
					$sth->bindValue(":tmid", $tmid);
					$res = $sth->execute();
					$cnt = $sth->rowCount();
					if ($res && $cnt == 1) {
						SendMessage($tmid, $M["start"]);
					} else {
						WriteLog("start fail: uid=".$uid." res=".json_encode($res)." cnt=".$cnt);
						SendMessage($tmid, $M["fail"]);
					}
					break;
				
				case '/stop':
					$sth = $G["db"]->prepare("UPDATE `{$C['DBTBprefix']}user` SET `fbmessage` = '0' WHERE `tmid` = :tmid");
					$sth->bindValue(":tmid", $tmid);
					$res = $sth->execute();
					$cnt = $sth->rowCount();
					if ($res && $cnt == 1) {
						SendMessage($tmid, $M["stop"]);
					} else {
						WriteLog("stop fail: uid=".$uid." res=".json_encode($res)." cnt=".$cnt);
						SendMessage($tmid, $M["fail"]);
					}
					break;
				
				case '/help':
					SendMessage($tmid, $M["help"]);
					break;
				
				default:
					SendMessage($tmid, $M["wrongcommand"]);
					break;
			}
		}
	}
}
