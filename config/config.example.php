<?php

$C['archive']['archive.org'] = true;
$C['archive']['archive.is'] = true;

$C['fb']['page_id'] = '589448474491665';

$C["DBhost"] = 'localhost';
$C['DBname'] = 'dbname';
$C['DBuser'] = 'user';
$C['DBpass'] = 'pass';
$C['DBTBprefix'] = 'tnfsh_notification_';

$C['fetch'] = 'http://www.tnfsh.tn.edu.tw/files/501-1000-1012-1.php';

$G["db"] = new PDO ('mysql:host='.$C["DBhost"].';dbname='.$C["DBname"].';charset=utf8', $C["DBuser"], $C["DBpass"]);
