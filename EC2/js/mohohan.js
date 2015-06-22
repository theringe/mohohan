//變數宣告
var ccuid;

//cookie管理
function myTrim(str) {
	var start = -1,
	end = str.length;
	while (str.charCodeAt(--end) < 33);
	while (str.charCodeAt(++start) < 33);
	return str.slice(start, end + 1);
};
function getCookie(name) {
	if (document.cookie.length>0) {
		var c_list = document.cookie.split("\;");
		for ( i in c_list ) {
			var cook = c_list[i].split("=");
			//直接比對有問題，第2個之後的token name會有空白，所以比對之前要去除空白
			if (myTrim(cook[0]) == myTrim(name)) {
				return unescape(cook[1]);
			}
		}
	}
  	return null
}
function delCookie (name) {
	var exp = new Date();
	exp.setTime(exp.getTime()-1);
	var cval = getCookie(name);
	document.cookie = name+"="+cval+"; expires="+exp.toGMTString();
}

//表單登入動作
function form_login() {
	$("#login_form").ajaxSubmit({
		dataType: 'json',
		success: function(data){
			if (data.responseStatus == 200) {
				//登入成功
				$("#form_login").dialog("close");
				//設定cookie
				var ccuid  = "ccuid="+data.responseData.uid;
				document.cookie = ccuid;
				//進入控制面板
				$("#cp").dialog("open");
				//提醒一些注意事項
				$("#notice").dialog("open");
				//載入任務表格(覆蓋一開始就載入的表格)
				var ccuid = getCookie("ccuid");
				$("#jobs").dataTable({
					bDestroy: true,
					bJQueryUI: true,
					sAjaxSource: "./api/jobs.php?uid="+ccuid
				});
			} else {
				//登入失敗
				$("#msg").text(data.responseDetails);
				$("#msg").dialog("open");
			}
		},
		error: function(){
			//登入失敗
			$("#msg").text("Server error, please try again later.");
			$("#msg").dialog("open");
		}
	});
}

//表單登出動作
function form_logout() {
	var ccuid = getCookie("ccuid");
	//有cookie要順便清除
	if (ccuid != null) {
		ccuid = "ccuid="+ccuid;
		delCookie(ccuid);
	}
	location.reload();
}

//表單登入視窗
$("#form_login").dialog({
	title:     "Login mohohan",
	autoOpen:  false,
	width:     330,
	height:    360,
	show:      "clip",
	resizable: false,
	buttons: {
		"notice": function() {
			$("#notice").dialog("open");
		},
		"login": function() {
			form_login();
		}
	}
});

//提醒視窗
$("#msg").dialog({
	title:     "mohohan",
	autoOpen:  false,
	width:     300,
	height:    200,
	modal:     true,
	resizable: false,
});

//注意事項視窗
$("#notice").dialog({
	title:     "notice",
	autoOpen:  false,
	width:     1000,
	height:    650,
	resizable: true,
});

//控制面板
$("#cp").dialog({
	title:     "Control Panel",
	autoOpen:  false,
	width:     1000,
	height:    600,
	resizable: true,
	buttons: {
		"notice": function() {
			$("#notice").dialog("open");
		},
		"new job": function() {
			$("#add").dialog("open");
		},
		"logout": function() {
			form_logout();
		}
	}
});

//判斷表單登入還是cookie登入
$(document).ready(function() {
	var ccuid = getCookie("ccuid");
	if (ccuid == null) {
		$("#form_login").dialog("open");
	} else {
		$("#cp").dialog("open");
	}
});

//任務表格
$(document).ready(function() {
	var ccuid = getCookie("ccuid");
	$("#jobs").dataTable({
		bJQueryUI: true,
		sAjaxSource: "./api/jobs.php?uid="+ccuid
	}).fnSort([[0,'desc']]);
});

//新任務動作
function form_add() {
	var ccuid = getCookie("ccuid");
	document.add_form.uid.value=ccuid;
	$("#add_form").ajaxSubmit({
		dataType: 'json',
		success: function(res){
			if (res.responseStatus == 200) {
				//成功
				$("#msg").text(res.responseData);
				$("#msg").dialog("open");
				$("#add").dialog("close");
			} else {
				//失敗
				$("#msg").text(res.responseDetails);
				$("#msg").dialog("open");
			}
		},
		error: function(){
			//失敗
			$("#msg").text("Server error, please try again later.");
			$("#msg").dialog("open");
		}
	});
}

//新任務視窗
$("#add").dialog({
	title:     "Add a new job",
	autoOpen:  false,
	width:     400,
	height:    300,
	modal:     true,
	resizable: false,
	buttons: {
		"add": function() {
			form_add();
			$("#add").dialog("close");
		}
	}
});

//週期性的重整表格
$(document).ready(function() {
	window.setInterval(
		function() {
			var ccuid = getCookie("ccuid");
			$("#jobs").dataTable({
				bDestroy: true,
				bJQueryUI: true,
				sAjaxSource: "./api/jobs.php?uid="+ccuid
			}).fnSort([[0,'desc']]);
		},
		10000
	);
});

//使用方法
$('#media').qtip({
	content: 'The input file format can only be mpg or mov in this demo. You must specify the media file in S3 URI (e.g., s3://yourbucket/path/to/your/input) as input. Then, grant the list/update/delete permission to theringe@gmail.com as your input folder, the output file will be in s3://yourbucket/path/to/your/input.mp4',
	style: 'dark'
});
$('#abr').qtip({
	content: 'Audio bitrate. mohohan transcodes your file in AAC format in 2 pass mode.',
	style: 'dark'
});
$('#vbr').qtip({
	content: 'Video bitrate. mohohan transcodes your file in H.264 format in 2 pass mode.',
	style: 'dark'
});

//Google Analytics
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-2186032-2']);
_gaq.push(['_trackPageview']);
(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
