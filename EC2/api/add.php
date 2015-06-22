<?php

//沒輸入URL
if (!isset($_POST["media"]) || $_POST["media"] == "") {
	$final = array("responseData" => null, "responseDetails" => "no media url", "responseStatus" => "204");
	$final = myjson($final);
	echo $final;
	exit();
}
//URL格式不對
if (!preg_match('/^(s3):\/\/.*\.(mov|mpg|m2ts|vob)/i', $_POST["media"])) {
	$final = array("responseData" => null, "responseDetails" => "url must be s3:// URI in mov/mpg extension", "responseStatus" => "204");
	$final = myjson($final);
	echo $final;
	exit();
}
//沒輸入ABR
if (!isset($_POST["abr"]) || $_POST["abr"] == "") {
	$final = array("responseData" => null, "responseDetails" => "no abr", "responseStatus" => "204");
	$final = myjson($final);
	echo $final;
	exit();
}
//ABR格式不對(非數字)
if (!is_numeric($_POST["abr"])) {
	$final = array("responseData" => null, "responseDetails" => "wrong abr format, must be numbers.", "responseStatus" => "406");
	$final = myjson($final);
	echo $final;
	exit();
}
//沒輸入VBR
if (!isset($_POST["vbr"]) || $_POST["vbr"] == "") {
	$final = array("responseData" => null, "responseDetails" => "no vbr", "responseStatus" => "204");
	$final = myjson($final);
	echo $final;
	exit();
}
//VBR格式不對(非數字)
if (!is_numeric($_POST["vbr"])) {
	$final = array("responseData" => null, "responseDetails" => "wrong vbr format, must be numbers.", "responseStatus" => "406");
	$final = myjson($final);
	echo $final;
	exit();
}

//隨機生成亂數先插入jobid這個還沒有值的欄位
$jobidrnd = random_gen(30);

//寫入mysql
include("./LIB_mysql.php");
$m = new MYSQL_connector();
$m->get_result(array("INSERT INTO `jobs` (`uid`, `media`, `abr`, `vbr`, `jobid`) VALUES ('".$_POST["uid"]."', '".$_POST["media"]."', '".$_POST["abr"]."', '".$_POST["vbr"]."', '".$jobidrnd."');"));

//執行任務
exec("/var/www/mohohan/ffemr/ffemr.sh ".$_POST["media"]." ".$_POST["abr"]." ".$_POST["vbr"]." ".$jobidrnd." &");

//回傳json
$final = array(
	"responseData"     => "add job ".$_POST["media"]." with ABR ".$_POST["abr"]." and VBR ".$_POST["vbr"]." ok.",
	"responseDetails"  => "success",
	"responseStatus"   => "200"
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
//亂數生成
function random_gen($length) {
	$random= "";
	srand((double)microtime()*1000000);
	$char_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$char_list .= "abcdefghijklmnopqrstuvwxyz";
	$char_list .= "1234567890";
	for($i = 0; $i < $length; $i++) {
		$random .= substr($char_list,(rand()%(strlen($char_list))), 1);
	}
	return $random;
}

?>
