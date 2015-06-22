==========================================================================
Demo EC2 micro
54.248.108.31 ubuntu
mohohan.no-ip.org
mysql root/2o
==========================================================================
打包給人看的時候需要注意移除密碼或其他資訊的檔案
EC2/sql/mohohan.sql
	INSERT INTO `users`
EC2/api/LIB_mysql.php
EC2/admin/.htpasswd
S3/mohohanapnortheast1/deb/* 整個殺掉
S3/mohohanapnortheast1/hiromicert/* 整個殺掉
S3/mohohanapnortheast1/steps/bootstrap.sh
	s3cfg x 2
==========================================================================

裝EMR

設定權限
/opt/elastic-mapreduce-ruby         - www-data
/opt/elastic-mapreduce-ruby/EMR.pem - 600
/var/www                            - www-data

設定權限
ffemr/ffemr.sh  - 755
admin/.htpasswd - 600

改路徑
admin/.htaccess => 1個：admin密碼檔案路徑
admin/index.php => 1個：確認lock檔案存在路徑
ffemr/ffemr.php => 1個：執行任務exec
ffemr/ffemr.sh  => 1個：監控進度程序，不是apache執行權限的微調
api/add.php     => 1個：mysql lib 的路徑

改一次要開幾台轉檔還有機型
ffemr.sh
