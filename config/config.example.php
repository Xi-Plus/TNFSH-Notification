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
$M["notcommand"] = "本粉專由機器人自動運作\n啟用訊息通知請輸入 /start\n顯示所有命令輸入 /help";
$M["start"] = "已啟用訊息通知";
$M["stop"] = "已停用訊息通知";
$M["help"] = "可用命令\n/start 啟用訊息通知\n/stop 停用訊息通知\n/help 顯示所有命令";
$M["fail"] = "指令失敗";
$M["wrongcommand"] = "無法辨識命令\n輸入 /help 取得可用命令";
