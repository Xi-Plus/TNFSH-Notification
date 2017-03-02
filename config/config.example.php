<?php

$C['archive']['archive.org'] = true;
$C['archive']['archive.is'] = true;

$C['FBpageid'] = 'page_id';
$C['FBpagetoken'] = 'page_token';
$C['FBWHtoken'] = 'Webhooks_token';
$C['FBAPI'] = 'https://graph.facebook.com/v2.8/';

$C["DBhost"] = 'localhost';
$C['DBname'] = 'dbname';
$C['DBuser'] = 'user';
$C['DBpass'] = 'pass';
$C['DBTBprefix'] = 'tnfsh_notification_';

$C['fetch'] = 'http://www.tnfsh.tn.edu.tw/files/501-1000-1012-1.php';

$G["db"] = new PDO ('mysql:host='.$C["DBhost"].';dbname='.$C["DBname"].';charset=utf8', $C["DBuser"], $C["DBpass"]);

$M["nottext"] = "僅接受文字訊息";
$M["notcommand"] = "本粉專由機器人自動運作\n".
	"啟用訊息通知請輸入 /start\n".
	"顯示所有命令輸入 /help";
$M["start"] = "已啟用訊息通知";
$M["stop"] = "已停用訊息通知";
$M["help"] = "可用命令\n".
	"/start 啟用訊息通知\n".
	"/stop 停用訊息通知\n".
	"/last 顯示最後一筆通知\n".
	"/last N 顯示最後N筆通知\n".
	"/help 顯示所有命令";
$M["fail"] = "指令失敗";
$M["/last_wrong_cnt"] = "參數1錯誤\n".
	"第一個參數應為一個數字，例如 /last 3 顯示最後3筆通知";
$M["/last_too_many_arg"] = "參數個數錯誤\n".
	"不使用參數顯示最後一筆通知\n".
	"第一個參數設定顯示筆數，例如 /last 3 顯示最後3筆通知";
$M["/last_no_result"] = "查無任何通知";
$M["wrongcommand"] = "無法辨識命令\n".
	"輸入 /help 取得可用命令";
