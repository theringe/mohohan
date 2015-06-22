<?php
	//軟體目錄設定
	$soft_path = "/var/www/mohohan";
	//承接變數開關(重複提交已在shell端做好防呆，所以不用理會)
	if ($_POST["mode"] == "start") {
		exec($soft_path."/ffemr/ffemr.sh start");
		sleep(10);
	} elseif ($_POST["mode"] == "stop") {
		exec($soft_path."/ffemr/ffemr.sh stop");
		sleep(10);
	}
	//確定模式的頁面顯示
	$path = $soft_path."/ffemr/ffemr.lock";
	if (file_exists($path)) {
		//存活模式已開啟
		echo '<form action="./index.php" method="post"><input type="hidden" name="mode" value="stop"><input type="submit" value="stop"></form>';
	} else {
		//存活模式未開啟
		echo '<form action="./index.php" method="post"><input type="hidden" name="mode" value="start"><input type="submit" value="start"></form>';
	}
?>
