<?php

$C['archive']['archive.org'] = true;
$C['archive']['archive.is'] = true;

$C['FBpageid'] = 'page_id';
$C['FBpagetoken'] = 'page_token';
$C['FBWHtoken'] = 'Webhooks_token';
$C['FBAPI'] = 'https://graph.facebook.com/v2.8/';

$C['TGchatid'] = 'chat_id';
$C['TGtoken'] = 'access_token';

$C["DBhost"] = 'localhost';
$C['DBname'] = 'dbname';
$C['DBuser'] = 'user';
$C['DBpass'] = 'pass';
$C['DBTBprefix'] = 'tnfsh_notification_';

$C['Pagename'] = "Tnfsh公佈欄通知";
$C['PagenameTG'] = "TNFSH公佈欄通知";

$C['UnreadLimit'] = 86400*7;
$C['UnreadLimitText'] = "7天";

$C['LogKeep'] = 86400*7;

$C['fetch'] = 'http://www.tnfsh.tn.edu.tw/files/501-1000-1012-1.php';

$C["allowsapi"] = array("cli");

$G["db"] = new PDO ('mysql:host='.$C["DBhost"].';dbname='.$C["DBname"].';charset=utf8', $C["DBuser"], $C["DBpass"]);

$C['last_limit'] = 20;
