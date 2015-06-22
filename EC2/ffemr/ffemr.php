#!/usr/bin/php
<?php
	//初始設定
	include("/var/www/mohohan/api/LIB_mysql.php");
	$m = new MYSQL_connector();
	$emr = "";
	$jobidrnd   = $argv[1];              //參數傳遞所需要的亂數
	$jobid      = $argv[2];              //EMR的實際jobid，查詢EMR狀態必須
	$jobidwhere = $argv[2]."_".$argv[1]; //因資料庫唯一性且存活模式下所有的jobid都相同所以還是要加入亂數區隔
	$progress = 0;
	$stepCount = 0;
	$stepLock  = FALSE;
	//mysql把jobidrnd正式取代成jobid
	$m->get_result(array("UPDATE `jobs` SET `jobid` = '".$jobidwhere."' WHERE `jobid` = '".$jobidrnd."';"));
	//mysql開始時間紀錄
	$starttime = time();
	$m->get_result(array("UPDATE `jobs` SET `starttime` = NOW() WHERE `jobid` = '".$jobidwhere."';"));
	do {
		//取得emr狀態
		$json = exec("/opt/elastic-mapreduce-ruby/elastic-mapreduce -c /opt/elastic-mapreduce-ruby/credentials.json --describe --jobflow ".$jobid." | tr '\n' ' '"); //apache執行要完整參數，key要改擁有者
		$json_arr = json_decode($json, true);
		$emr = $json_arr["JobFlows"][0]["ExecutionStatusDetail"]["State"];
		//計算下面的大小才會知道現在共有幾個(3步驟)，答案一定是3的倍數
		//進監控腳本後只取得第1次，未來隨著其他任務的提交這個數字會增長，但是第1次取得的數字就是目前這個job的3個step的最大值+1
		if ($stepLock == FALSE) {
			$stepLock = TRUE;
			$stepCount = count($json_arr["JobFlows"][0]["Steps"]);
		}
		switch ($emr) {
			case "STARTING":
				$progress = 0;
				echo "STARTING ".$progress." \n";
				$m->get_result(array("UPDATE `jobs` SET `status`   = 'STARTING'    WHERE `jobid` = '".$jobidwhere."';"));
				$m->get_result(array("UPDATE `jobs` SET `progress` = ".$progress." WHERE `jobid` = '".$jobidwhere."';"));
				$nowtime = time(); $duration = $nowtime-$starttime; $m->get_result(array("UPDATE `jobs` SET `duration` = ".$duration." WHERE `jobid` = '".$jobidwhere."';"));
				break;
			case "BOOTSTRAPPING":
				$progress = 10;
				echo "BOOTSTRAPPING ".$progress." \n";
				$m->get_result(array("UPDATE `jobs` SET `status`   = 'BOOTSTRAPPING'    WHERE `jobid` = '".$jobidwhere."';"));
				$m->get_result(array("UPDATE `jobs` SET `progress` = ".$progress."      WHERE `jobid` = '".$jobidwhere."';"));
				$nowtime = time(); $duration = $nowtime-$starttime; $m->get_result(array("UPDATE `jobs` SET `duration` = ".$duration." WHERE `jobid` = '".$jobidwhere."';"));
				break;
			case "WAITING":
				//存活模式才有的狀態(不確定一般模式會不會短暫落入這個狀態)
				if ($progress > 20) {
					//指定20是因為存活模式時的 RUNNING 會跳很快，但監控腳本每30秒才重整，所以難保下一次已經跳過 RUNNING 變成 WAITING 狀態了
					//這是任務執行後的PENDING
					$progress = 100;
					echo "COMPLETED ".$progress." \n";
					$emr = "COMPLETED";
					$m->get_result(array("UPDATE `jobs` SET `status`   = 'COMPLETED'         WHERE `jobid` = '".$jobidwhere."';"));
					$m->get_result(array("UPDATE `jobs` SET `progress` = ".$progress."       WHERE `jobid` = '".$jobidwhere."';"));
					$nowtime = time(); $duration = $nowtime-$starttime; $m->get_result(array("UPDATE `jobs` SET `duration` = ".$duration." WHERE `jobid` = '".$jobidwhere."';"));
				}
				break;
			case "RUNNING":
				if ($progress == 90) {
					//一般模式下當 Merge COMPLETED = 90 完成之後下次監控就會跳到 SHUTTING_DOWN ，但是生存模式在還有其他轉檔任務的情況下不會跳，所以當生存模式其他任務還在跑(RUNNING)但是自己這個任務已經好了的時候就要跳出
					$progress = 100;
					echo "COMPLETED ".$progress." \n";
					$emr = "COMPLETED";
					$m->get_result(array("UPDATE `jobs` SET `status`   = 'COMPLETED'         WHERE `jobid` = '".$jobidwhere."';"));
					$m->get_result(array("UPDATE `jobs` SET `progress` = ".$progress."       WHERE `jobid` = '".$jobidwhere."';"));
					$nowtime = time(); $duration = $nowtime-$starttime; $m->get_result(array("UPDATE `jobs` SET `duration` = ".$duration." WHERE `jobid` = '".$jobidwhere."';"));
					break;
				}
				//取得steps狀態，步驟1
				$steps_pre  = $json_arr["JobFlows"][0]["Steps"][$stepCount-3]["ExecutionStatusDetail"]["State"];
				switch ($steps_pre) {
					case "PENDING":
						$progress = 20;
						echo "Split PENDING ".$progress." \n";
						$m->get_result(array("UPDATE `jobs` SET `status`   = 'Split PENDING'    WHERE `jobid` = '".$jobidwhere."';"));
						$m->get_result(array("UPDATE `jobs` SET `progress` = ".$progress."      WHERE `jobid` = '".$jobidwhere."';"));
						$nowtime = time(); $duration = $nowtime-$starttime; $m->get_result(array("UPDATE `jobs` SET `duration` = ".$duration." WHERE `jobid` = '".$jobidwhere."';"));
						break;
					case "RUNNING":
						$progress = 20;
						echo "Split RUNNING ".$progress." \n";
						$m->get_result(array("UPDATE `jobs` SET `status`   = 'Split RUNNING'    WHERE `jobid` = '".$jobidwhere."';"));
						$m->get_result(array("UPDATE `jobs` SET `progress` = ".$progress."      WHERE `jobid` = '".$jobidwhere."';"));
						$nowtime = time(); $duration = $nowtime-$starttime; $m->get_result(array("UPDATE `jobs` SET `duration` = ".$duration." WHERE `jobid` = '".$jobidwhere."';"));
						break;
					case "COMPLETED":
						//取得steps狀態，步驟2
						$steps_ff   = $json_arr["JobFlows"][0]["Steps"][$stepCount-2]["ExecutionStatusDetail"]["State"];
						switch ($steps_ff) {
							case "PENDING":
								$progress = 30;
								echo "mohohan PENDING ".$progress." \n";
								$m->get_result(array("UPDATE `jobs` SET `status`   = 'mohohan PENDING'    WHERE `jobid` = '".$jobidwhere."';"));
								$m->get_result(array("UPDATE `jobs` SET `progress` = ".$progress."        WHERE `jobid` = '".$jobidwhere."';"));
								$nowtime = time(); $duration = $nowtime-$starttime; $m->get_result(array("UPDATE `jobs` SET `duration` = ".$duration." WHERE `jobid` = '".$jobidwhere."';"));
								break;
							case "RUNNING":
								//取得hadoop執行狀態(有可能本次EMR=RUNNING然後Hadoop=0.8下一次就直接是EMR=SHUTTING_DOWN，所以一旦切斷到之後的狀態就要讓Hadoop=1)
								$progress = exec("/opt/elastic-mapreduce-ruby/elastic-mapreduce -c /opt/elastic-mapreduce-ruby/credentials.json --jobflow ".$jobid." --ssh /home/hadoop/ffprogress.sh"); //apache執行要完整參數，key要改擁有者
								$progress = round($progress*100);
								$progress = 30+round($progress*0.5); //前面占30，Hadoop占50，後面占20
								echo "mohohan RUNNING ".$progress." \n";
								$m->get_result(array("UPDATE `jobs` SET `status`   = 'mohohan RUNNING'    WHERE `jobid` = '".$jobidwhere."';"));
								$m->get_result(array("UPDATE `jobs` SET `progress` = ".$progress."        WHERE `jobid` = '".$jobidwhere."';"));
								$nowtime = time(); $duration = $nowtime-$starttime; $m->get_result(array("UPDATE `jobs` SET `duration` = ".$duration." WHERE `jobid` = '".$jobidwhere."';"));
								break;
							case "COMPLETED":
								//取得steps狀態，步驟3
								$steps_post = $json_arr["JobFlows"][0]["Steps"][$stepCount-1]["ExecutionStatusDetail"]["State"];
								switch ($steps_post) {
									case "PENDING":
										$progress = 80;
										echo "Merge PENDING ".$progress." \n";
										$m->get_result(array("UPDATE `jobs` SET `status`   = '>Merge PENDING'    WHERE `jobid` = '".$jobidwhere."';"));
										$m->get_result(array("UPDATE `jobs` SET `progress` = ".$progress."       WHERE `jobid` = '".$jobidwhere."';"));
										$nowtime = time(); $duration = $nowtime-$starttime; $m->get_result(array("UPDATE `jobs` SET `duration` = ".$duration." WHERE `jobid` = '".$jobidwhere."';"));
										break;
									case "RUNNING":
										$progress = 80;
										echo "Merge RUNNING ".$progress." \n";
										$m->get_result(array("UPDATE `jobs` SET `status`   = 'Merge RUNNING'    WHERE `jobid` = '".$jobidwhere."';"));
										$m->get_result(array("UPDATE `jobs` SET `progress` = ".$progress."      WHERE `jobid` = '".$jobidwhere."';"));
										$nowtime = time(); $duration = $nowtime-$starttime; $m->get_result(array("UPDATE `jobs` SET `duration` = ".$duration." WHERE `jobid` = '".$jobidwhere."';"));
										break;
									case "COMPLETED":
										$progress = 90;
										echo "Merge COMPLETED ".$progress." \n";
										$m->get_result(array("UPDATE `jobs` SET `status`   = 'Merge COMPLETED'    WHERE `jobid` = '".$jobidwhere."';"));
										$m->get_result(array("UPDATE `jobs` SET `progress` = ".$progress."        WHERE `jobid` = '".$jobidwhere."';"));
										$nowtime = time(); $duration = $nowtime-$starttime; $m->get_result(array("UPDATE `jobs` SET `duration` = ".$duration." WHERE `jobid` = '".$jobidwhere."';"));
										break;
									default:
										echo "CRASH 4\n";
										$emr = "COMPLETED";
										$m->get_result(array("UPDATE `jobs` SET `status`   = 'CRASH 4'     WHERE `jobid` = '".$jobidwhere."';"));
										$m->get_result(array("UPDATE `jobs` SET `progress` = ".$progress." WHERE `jobid` = '".$jobidwhere."';"));
										$nowtime = time(); $duration = $nowtime-$starttime; $m->get_result(array("UPDATE `jobs` SET `duration` = ".$duration." WHERE `jobid` = '".$jobidwhere."';"));
										break;
								}
								break;
							default:
								echo "CRASH 3\n";
								$emr = "COMPLETED";
								$m->get_result(array("UPDATE `jobs` SET `status`   = 'CRASH 3'     WHERE `jobid` = '".$jobidwhere."';"));
								$m->get_result(array("UPDATE `jobs` SET `progress` = ".$progress." WHERE `jobid` = '".$jobidwhere."';"));
								$nowtime = time(); $duration = $nowtime-$starttime; $m->get_result(array("UPDATE `jobs` SET `duration` = ".$duration." WHERE `jobid` = '".$jobidwhere."';"));
								break;
						}
						break;
					default:
						echo "CRASH 2\n";
						$emr = "COMPLETED";
						$m->get_result(array("UPDATE `jobs` SET `status`   = 'CRASH 2'     WHERE `jobid` = '".$jobidwhere."';"));
						$m->get_result(array("UPDATE `jobs` SET `progress` = ".$progress." WHERE `jobid` = '".$jobidwhere."';"));
						$nowtime = time(); $duration = $nowtime-$starttime; $m->get_result(array("UPDATE `jobs` SET `duration` = ".$duration." WHERE `jobid` = '".$jobidwhere."';"));
						break;
				}
				break;
			case "SHUTTING_DOWN":
				$progress = 90;
				echo "SHUTTING_DOWN ".$progress." \n";
				$m->get_result(array("UPDATE `jobs` SET `status`   = 'SHUTTING_DOWN'     WHERE `jobid` = '".$jobidwhere."';"));
				$m->get_result(array("UPDATE `jobs` SET `progress` = ".$progress."       WHERE `jobid` = '".$jobidwhere."';"));
				$nowtime = time(); $duration = $nowtime-$starttime; $m->get_result(array("UPDATE `jobs` SET `duration` = ".$duration." WHERE `jobid` = '".$jobidwhere."';"));
				break;
			case "COMPLETED":
				$progress = 100;
				echo "COMPLETED ".$progress." \n";
				$m->get_result(array("UPDATE `jobs` SET `status`   = 'COMPLETED'         WHERE `jobid` = '".$jobidwhere."';"));
				$m->get_result(array("UPDATE `jobs` SET `progress` = ".$progress."       WHERE `jobid` = '".$jobidwhere."';"));
				$nowtime = time(); $duration = $nowtime-$starttime; $m->get_result(array("UPDATE `jobs` SET `duration` = ".$duration." WHERE `jobid` = '".$jobidwhere."';"));
				break;
			case "TERMINATED":
				$progress = 0;
				echo "TERMINATED ".$progress." \n";
				$emr = "COMPLETED";
				$m->get_result(array("UPDATE `jobs` SET `status`   = 'TERMINATED'        WHERE `jobid` = '".$jobidwhere."';"));
				$m->get_result(array("UPDATE `jobs` SET `progress` = ".$progress."       WHERE `jobid` = '".$jobidwhere."';"));
				$nowtime = time(); $duration = $nowtime-$starttime; $m->get_result(array("UPDATE `jobs` SET `duration` = ".$duration." WHERE `jobid` = '".$jobidwhere."';"));
				break;
			default:
				echo "CRASH 1\n";
				$emr = "COMPLETED";
				$m->get_result(array("UPDATE `jobs` SET `status`   = 'CRASH 1'        WHERE `jobid` = '".$jobidwhere."';"));
				$m->get_result(array("UPDATE `jobs` SET `progress` = ".$progress."    WHERE `jobid` = '".$jobidwhere."';"));
				$nowtime = time(); $duration = $nowtime-$starttime; $m->get_result(array("UPDATE `jobs` SET `duration` = ".$duration." WHERE `jobid` = '".$jobidwhere."';"));
				break;
		}
		sleep(5);
	} while ($emr != "COMPLETED");
	echo "DONE\n";
	//mysql結束時間紀錄
	$endtime = time();
	$m->get_result(array("UPDATE `jobs` SET `endtime` = NOW() WHERE `jobid` = '".$jobidwhere."';"));
	//mysql時間長度紀錄
	$duration = $endtime-$starttime;
	$m->get_result(array("UPDATE `jobs` SET `duration` = ".$duration." WHERE `jobid` = '".$jobidwhere."';"));
?>
