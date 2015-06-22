<?php

//沒有帳號
if ($_POST["email"] == "") {
	$final = array("responseData" => null, "responseDetails" => "no email found.", "responseStatus" => "204");
	$final = myjson($final);
	echo $final;
	exit();
}

//沒有密碼
if ($_POST["pass"] == "") {
	$final = array("responseData" => null, "responseDetails" => "no pass found.", "responseStatus" => "204");
	$final = myjson($final);
	echo $final;
	exit();
}

//驗證身分失敗
include("./LIB_mysql.php");
$m = new MYSQL_connector();
$r = $m->get_result(array("SELECT COUNT(*) FROM `users` WHERE `email` = '".$_POST["email"]."' AND `pass` = '".md5($_POST["pass"])."';"));
if ($r[0][0] == 0) {
	$final = array("responseData" => null, "responseDetails" => "wrong user info", "responseStatus" => "401");
	$final = myjson($final);
	echo $final;
	exit();
}

//取得一些使用者基本資料
$r = $m->get_result(array("SELECT `uid` FROM `users` WHERE `email` = '".$_POST["email"]."';"));
$uid = $r[0][0];

//製作回傳json
$final = array(
	"responseData"    => array(
		"uid" => $uid,
	),
	"responseDetails" => "success",
	"responseStatus"  => "200"
);
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
