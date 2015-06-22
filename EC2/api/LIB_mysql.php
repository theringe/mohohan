<?php
	class MYSQL_connector {
		var $url    = "localhost";
		var $user   = "root";
		var $pass   = "[[YOUR_SPECIFIC_PASSWORD]]";
		var $db     = "mohohan";
		function get_result($query) {
			$R      = null;
			$c      = mysql_connect($this->url, $this->user, $this->pass) or die('Error with MySQL connection');
			mysql_query("SET NAMES 'utf8'");
			mysql_select_db($this->db);
			for ($i = 0; $i < count($query); $i++) {
				$rs = mysql_query($query[$i]) or die("query error:\t".$query[$i]);
			}
			//if rs not empty
			if ($rs != 1) {
				while($row = mysql_fetch_array($rs)){
					$tmp = null;
					for ($i = 0; $i < count($row)/2; $i++) {
						$tmp[$i] = $row[$i];
					}
					$R[] = $tmp;
				}
			}
			mysql_close($c);
			return $R;
		}
		function normal($q) {
			$q = str_replace("'", "''", $q);
			$q = str_replace("\\", "\\\\", $q);
			return $q;
		}
	}
?>