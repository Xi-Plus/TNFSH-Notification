<?php
if (PHP_SAPI != "cli") {
	exit("No permission");
}
require_once(__DIR__.'/function/SQL-function/sql.php');
require_once(__DIR__.'/config/config.php');

$query = new query;
$row = $query->SELECT();
$old = array();
foreach ($row as $temp) {
	$old[] = $temp["hash"];
}

$html = file_get_contents($cfg['fetch']);
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
		if ($cfg['archive']['on']) {
			echo " archiving";
			system("curl https://web.archive.org/save/".$match[4][$key]." > /dev/null 2>&1");
		}
		$query = new query;
		$query->value = array(
			array("date", $match[1][$key]."-".$match[2][$key]."-".$match[3][$key]),
			array("text", $match[5][$key]),
			array("department", $match[6][$key]),
			array("url", $match[4][$key]),
			array("hash", $hash)
		);
		$query->INSERT();
		$old[] = $hash;
		echo " New\n";
		$new_cnt++;
	} else echo " Old\n";
}
if ($new_cnt && $cfg['archive']['on']) {
	echo "list archiving\n";
	system("curl https://web.archive.org/save/".$cfg['fetch']." > /dev/null 2>&1");
}
?>
