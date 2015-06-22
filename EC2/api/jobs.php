<?php

include("./LIB_mysql.php");
$m = new MYSQL_connector();
$r = $m->get_result(array("SELECT `jid`, `media`, `abr`, `vbr`, `starttime`, `endtime`, `duration`, `status`, `progress` FROM `jobs` WHERE `uid` = '".$_GET["uid"]."' ORDER BY `jid` DESC;"));
$final = array("aaData" => $r);
$final = myjson($final);
echo $final;

//例外狀況的json處理函數們
function myjson($code) {
	$code = json_encode(urlencodeAry($code));
	return urldecode($code);
}
function urlencodeAry($data) {
	if(is_array($data)) {
		foreach($data as $key=>$val) {
			$data[$key] = urlencodeAry($val);
		}
		return $data;
	} else {
		return urlencode($data);
	}
}

?>
