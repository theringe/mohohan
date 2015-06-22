<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>mohohan - koichi</title>
		<script type="text/javascript" src="./js/jquery-ui-1.8.19.custom/js/jquery-1.7.1.min.js"></script>
		<script type="text/javascript" src="./js/jquery-ui-1.8.19.custom/js/jquery-ui-1.8.19.custom.min.js"></script>
		<script type="text/javascript" src="./js/jquery.form.js"></script>
		<script type="text/javascript" src="./js/DataTables-1.9.1/media/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="./js/jquery.qtip-1.0.0-rc3.min.js"></script>
		<link rel="stylesheet" type="text/css" href="./js/jquery-ui-1.8.19.custom/css/smoothness/jquery-ui-1.8.19.custom.css" />
		<link rel="stylesheet" type="text/css" href="./js/DataTables-1.9.1/media/css/jquery.dataTables.css" />
	</head>
	<body>
		<!-- 登入視窗 -->
		<div id="form_login">
			<form id="login_form" method="post" action="./api/login.php">
				user: <input type="text"     name="email" class="text ui-widget-content ui-corner-all" /><br>
				pass: <input type="password" name="pass"  class="text ui-widget-content ui-corner-all" /><br>
				<br>
				massive random string gen:<br>
				<a href="http://hunt.snar.cc" target="_blank">http://hunt.snar.cc</a><br>
				super easy file hosting:<br>
				<a href="http://c.hiyu.co" target="_blank">http://c.hiyu.co</a><br>
				New advertising captcha:<br>
				<a href="http://www.riyu.ws" target="_blank">http://www.riyu.ws</a>
			</form>
		</div>
		<!-- 提醒視窗 -->
		<div id="msg"></div>
		<!-- 控制面板 -->
		<div id="cp">
			<!-- 任務表格 -->
			<table id="jobs">
				<thead>
					<tr>
						<th width="5%">Job id</th>
						<th width="30%">Media URL</th>
						<th width="5%">ABR</th>
						<th width="5%">VBR</th>
						<th width="5%">Start Time</th>
						<th width="5%">End Time</th>
						<th width="5%">Duration</th>
						<th width="10%">Status</th>
						<th width="5%">Progress</th>
					</tr>
				</thead>
			</table>
		</div>
		<!-- 新任務視窗 -->
		<div id="add">
			<form name="add_form" id="add_form" method="post" action="./api/add.php">
				<input type="hidden" name="uid">
				Media URL: <input id="media" type="text" name="media" class="text ui-widget-content ui-corner-all" /><br>
				ABR:
				<select id="abr" name="abr" class="text ui-widget-content ui-corner-all">
					<option value="32">32k</option>
					<option value="64">64k</option>
					<option value="96" selected>96k (default)</option>
					<option value="128">128k</option>
					<option value="192">192k</option>
				</select><br>
				VBR:
				<select id="vbr" name="vbr" class="text ui-widget-content ui-corner-all">
					<option value="256">256k</option>
					<option value="512">512k</option>
					<option value="1024" selected>1024k (default)</option>
					<option value="2048">2048k</option>
					<option value="3000">3000k</option>
					<option value="4096">4096k</option>
				</select><br>
			</form>
		</div>
		<!-- 注意事項視窗 -->
		<div id="notice">
			<ul>
				<li><b>mohohan koichi</b> is an experimental <font color="red">public cloud video transcode system</font> implemented with AWS, hadoop, and ffmpeg. Its goal is to <font color="red">reduce the transcode time</font>.</li>
				<li>You can <a href="https://www.youtube.com/watch?v=1lxE9ZB_LEE" target="_blank">watch this video</a> to understand how it work and <a href="http://www.gwms.com.tw/TREND_HadoopinTaiwan2012/1002download/C3.pdf" target="_blank">download my slide</a> on <a href="http://www.hadoopintaiwan.com/" target="_blank">hadoop in Taiwan 2012</a>.</li>
				<li>I can only launch <font color="red">40 instances at most</font> with my AWS account. If you get the CRASH 1 status, that means other people are using this demo (each transcode costs 6 instances) at the same time, please wait for a while and retry :)</li>
				<li>As above mentioned, each transcode named mohohan costs <font color="red">6 AWS instances in m1.large mode</font>. If you want to test with over 6 instances or in c1.xlarge ones, please <a href="mailto:theringe@gmail.com" target="_blank">email me</a>.</li>
				<li>
					I've used <font color="red">10 AWS instances in c1.xlarge mode</font> for benchmarking (compared with <a href="http://zencoder.com" target="_blank">zencoder</a>, the powerful online transcode system) and the results shows below:<br><br>
					<table border="1">
						<tr>
							<td><a href="http://zencoder.com" target="_blank">zencoder</a></td>
							<td>mohohan</td>
						</tr>
						<tr>
							<td><a href="./images/z.png" target="_blank"><img src="./images/z.png" width="160" height="120"></a></td>
							<td><a href="./images/m.png" target="_blank"><img src="./images/m.png" width="160" height="120"></a></td>
						</tr>
					</table><br>
				</li>
				<li>mohohan transcodes your mpg/mov file with <font color="red">AAC in audio and H.264 in video</font>. Please read the usage tip when adding jobs.</li>
				<li>mohohan server located in AWS t1.micro instance withoud CDN, it is <font color="red">experiment and demo only</font>.</li>
				<li>There is also a <font color="red">private cloud version</font> named <b>mohohan hiromi</b> for personal, studio, and soho use. Please feel free to <a href="mailto:theringe@gmail.com" target="_blank">email me</a> for detail.</li>
				<?php
					//echo "<li>My name is Chun-Han Chen and <a href=\"./cv/cv.pdf\" target=\"_blank\">this is my CV</a>.</li>";
				?>
			</ul>
		</div>
		<script type="text/javascript" src="./js/mohohan.js"></script>
	</body>
</html>
