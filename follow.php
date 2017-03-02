<?php
date_default_timezone_set("Asia/Taipei");
require(__DIR__.'/config/config.php');
require(__DIR__.'/curl.php');
require(__DIR__.'/log.php');

$sth = $G["db"]->prepare("SELECT * FROM `{$C['DBTBprefix']}input` ORDER BY `time` ASC");
$res = $sth->execute();
$row = $sth->fetchAll(PDO::FETCH_ASSOC);
function SendMessage($tmid, $message) {
	global $C;
	$post = array(
		"message" => $message,
		"access_token" => $C['FBpagetoken']
	);
	$res = cURL($C['FBAPI'].$tmid."/messages", $post);
	WriteLog("send message: tmid=".$tmid." message=".$message);
	$res = json_encode($res, true);
	if (isset($res["error"])) {
		WriteLog("send message error: tmid=".$tmid." res=".json_encode($res));
	}
}
function GetTmid() {
	global $C, $G;
	$res = cURL($C['FBAPI']."me/conversations?fields=participants,updated_time&access_token=".$C['FBpagetoken']);
	$updated_time = file_get_contents("updated_time.txt");
	$newesttime = $updated_time;
	while (true) {
		if ($res === false) {
			WriteLog("fetch uid: curl fail");
			break;
		}
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
					$res = $sth->execute();
					break;
				}
			}
		}
		$res = cURL($res["paging"]["next"]);
	}
	file_put_contents("updated_time.txt", $newesttime);
}
foreach ($row as $data) {
	$input = json_decode($data["input"], true);
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
				WriteLog("get uid ".$uid);
				GetTmid();
				$sth->execute();
				$row = $sth->fetch(PDO::FETCH_ASSOC);
				if ($row === false) {
					WriteLog("uid not found uid=".$uid." res=".json_encode($messaging));
					continue;
				} else {
					WriteLog("new user: uid=".$uid);
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
					if (isset($cmd[1])) {
						SendMessage($tmid, $M["start_too_many_arg"]);
						continue;
					}
					$sth = $G["db"]->prepare("UPDATE `{$C['DBTBprefix']}user` SET `fbmessage` = '1' WHERE `tmid` = :tmid");
					$sth->bindValue(":tmid", $tmid);
					$res = $sth->execute();
					if ($res) {
						SendMessage($tmid, $M["start"]);
					} else {
						WriteLog("start fail: uid=".$uid." res=".json_encode($res)." cnt=".$cnt);
						SendMessage($tmid, $M["fail"]);
					}
					break;
				
				case '/stop':
					if (isset($cmd[1])) {
						SendMessage($tmid, $M["stop_too_many_arg"]);
						continue;
					}
					$sth = $G["db"]->prepare("UPDATE `{$C['DBTBprefix']}user` SET `fbmessage` = '0' WHERE `tmid` = :tmid");
					$sth->bindValue(":tmid", $tmid);
					$res = $sth->execute();
					if ($res) {
						SendMessage($tmid, $M["stop"]);
					} else {
						WriteLog("stop fail: uid=".$uid." res=".json_encode($res)." cnt=".$cnt);
						SendMessage($tmid, $M["fail"]);
					}
					break;
				
				case '/last':
					$a = 0;
					$b = 1;
					if (isset($cmd[1]) && !isset($cmd[2])) {
						if (!ctype_digit($cmd[1])) {
							SendMessage($tmid, $M["/last1_arg1_notnum"]);
							continue;
						}
						$b = (int)$cmd[1];
						if ($b < 1) {
							SendMessage($tmid, $M["/last1_arg1_less_than_1"]);
							continue;
						}
						if ($b > $C['/last_limit']) {
							SendMessage($tmid, $M["/last1_arg1_limit_exceeded"]);
							continue;
						}
					}
					if (isset($cmd[2])) {
						if (!ctype_digit($cmd[1])) {
							SendMessage($tmid, $M["/last2_arg1_notnum"]);
							continue;
						}
						if (!ctype_digit($cmd[2])) {
							SendMessage($tmid, $M["/last_arg2_notnum"]);
							continue;
						}
						$a = (int)$cmd[1];
						if ($a < 0) {
							SendMessage($tmid, $M["/last_arg1_less_than_0"]);
							continue;
						}
						$b = (int)$cmd[2];
						if ($b < 1) {
							SendMessage($tmid, $M["/last_arg2_less_than_1"]);
							continue;
						}
						if ($b > $C['/last_limit']) {
							SendMessage($tmid, $M["/last2_arg2_limit_exceeded"]);
							continue;
						}
					}
					if (isset($cmd[3])) {
						SendMessage($tmid, $M["/last_too_many_arg"]);
						continue;
					}
					$sth = $G["db"]->prepare("SELECT * FROM `{$C['DBTBprefix']}news` ORDER BY `time` DESC LIMIT {$a},{$b}");
					$res = $sth->execute();
					$row = $sth->fetchAll(PDO::FETCH_ASSOC);
					if ($res) {
						if (count($row) == 0) {
							SendMessage($tmid, $M["/last_no_result"]);
						} else {
							if ($a == 0) {
								SendMessage($tmid, "顯示最後".$b."筆訊息");
							} else {
								SendMessage($tmid, "忽略最後".$a."筆，顯示".$b."筆訊息");
							}
							$idx = $a + $b;
							foreach (array_reverse($row) as $temp) {
								$msg = "#".$idx."\n".date("m/d", strtotime($temp["date"]))." ".$temp["department"]."：".$temp["text"]."\n".$temp["url"];
								SendMessage($tmid, $msg);
								$idx --;
							}
							SendMessage($tmid, "顯示更舊".$b."筆輸入 /last ".($a+$b)." ".$b);
						}
					} else {
						WriteLog("last fail: uid=".$uid." res=".json_encode($res)." cnt=".$cnt);
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
	$sth = $G["db"]->prepare("DELETE FROM `{$C['DBTBprefix']}input` WHERE `hash` = :hash");
	$sth->bindValue(":hash", $data["hash"]);
	$res = $sth->execute();
}
