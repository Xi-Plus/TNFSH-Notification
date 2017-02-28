<?php
require(__DIR__.'/config/config.php');

if (!in_array(PHP_SAPI, array("cli", "apache2handler"))) {
	exit("No permission");
}
define("EOL", (PHP_SAPI==="apache2handler"?"<br>\n":PHP_EOL));

$sth = $G["db"]->prepare("SELECT * FROM `{$C['DBTBprefix']}log`");
$sth->execute();
$row = $sth->fetchAll(PDO::FETCH_ASSOC);

$old = array();
foreach ($row as $temp) {
	$old[] = $temp["hash"];
}

$html = file_get_contents($C['fetch']);
$start = strpos($html, "日期");
$html = substr($html, $start);
$html = str_replace(array("\n", "\t"), "", $html);

$pattern = '/<tr.*?<td.*?>(\d*?)-(\d*?)-(\d*?) <\/td><td.*?<a.*?href="(.*?)".*?>(.*?)<\/a>.*?<td.*?>(.*?)<\/td.*?tr>/';
preg_match_all($pattern, $html ,$match);
$new_cnt = 0;
foreach ($match[0] as $key => $value) {
	$data = array($match[1][$key], $match[2][$key], $match[3][$key], $match[4][$key], $match[5][$key], $match[6][$key]);
	echo $match[5][$key];
	$hash = md5(serialize($data));
	if (!in_array($hash, $old)) {
		if ($C['archive']['archive.org']) {
			system("curl -s -o /dev/null/ https://web.archive.org/save/".$match[4][$key]);
			echo " archive.org";
		}
		if ($C['archive']['archive.is']) {
			system("curl -s -o /dev/null/ https://archive.is/submit/ -d 'url=".$match[4][$key]."&anyway=1'");
			echo " archive.is";
		}
		$sth = $G["db"]->prepare("INSERT INTO `".$C['DBTBprefix']."log` (`date`, `text`, `department`, `url`, `hash`) VALUES (:date, :text, :department, :url, :hash)");
		$sth->bindValue(":date", $match[1][$key]."-".$match[2][$key]."-".$match[3][$key]);
		$sth->bindValue(":text", $match[5][$key]);
		$sth->bindValue(":department", $match[6][$key]);
		$sth->bindValue(":url", $match[4][$key]);
		$sth->bindValue(":hash", $hash);
		$sth->execute();

		$old[] = $hash;
		echo " New".EOL;
		$new_cnt++;
	} else echo " Old".EOL;
}
if ($new_cnt) {
	echo "list archiving";
	if ($C['archive']['archive.org']) {
		system("curl -s -o /dev/null/ https://web.archive.org/save/".$C['fetch']);
		echo " archive.org";
	}
	if ($C['archive']['archive.is']) {
		system("curl -s -o /dev/null/ https://archive.is/submit/ -d 'url=".$C['fetch']."&anyway=1'");
		echo " archive.is";
	}
	echo "done".EOL;
}
?>
