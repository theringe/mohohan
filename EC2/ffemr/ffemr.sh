#!/bin/bash
#apache執行權限的微調，指定家目錄
export HOME=/var/www
#變數設定
soft_path="/var/www/mohohan"
s3bucket="mohohanapnortheast1"
ins_num="2"
ins_type="m1.large"
#初始模式參數設定
mode=${1}
#模式判定
if [[ ${mode} == "start" ]]; then
	#打開存活模式
	if [ -f ${soft_path}/ffemr/ffemr.lock ]; then
		#存活模式已開啟，不用打開
		echo "no need to start"
	else
		#存活模式未開啟，可以打開
		#EMR指令(部分)
		jobidraw=`/opt/elastic-mapreduce-ruby/elastic-mapreduce \
		-c /opt/elastic-mapreduce-ruby/credentials.json \
		--create \
		--alive \
		--name "mohohan" \
		--num-instances ${ins_num} \
		--master-instance-type ${ins_type} \
		--slave-instance-type  ${ins_type} \
		--log-uri s3://${s3bucket}/logs/ \
		--bootstrap-action s3://${s3bucket}/steps/bootstrap.sh`
		#取出jobid
		jobid=${jobidraw/Created job flow /}
		#jobid寫入lockfile
		echo ${jobid} > ${soft_path}/ffemr/ffemr.lock
	fi
elif [[ ${mode} == "stop" ]]; then
	#關閉存活模式
	if [ -f ${soft_path}/ffemr/ffemr.lock ]; then
		#存活模式已開啟，可以關閉
		#讀取lockfile拿到jobid
		jobid=`cat ${soft_path}/ffemr/ffemr.lock`
		#EMR指令(部分)
		/opt/elastic-mapreduce-ruby/elastic-mapreduce \
		-c /opt/elastic-mapreduce-ruby/credentials.json \
		-j ${jobid} \
		--terminate ;
		#刪除lockfile
		rm -f ${soft_path}/ffemr/ffemr.lock
	else
		#存活模式未開啟，不用關閉
		echo "no need to stop"
	fi
else
	#單次模式(由add.php提交的)
	#參數設定
	media=${1}
	abr=${2}
	vbr=${3}
	jobidrnd=${4}
	if [ -f ${soft_path}/ffemr/ffemr.lock ]; then
		#存活模式已開啟狀態
		#讀取lockfile拿到jobid
		jobid=`cat ${soft_path}/ffemr/ffemr.lock`
		#EMR指令(部分)
		/opt/elastic-mapreduce-ruby/elastic-mapreduce \
		-c /opt/elastic-mapreduce-ruby/credentials.json \
		-j ${jobid} \
		--jar  s3://${s3bucket}/steps/script-runner.jar \
		--step-name "pre: ${media}" \
		--args "s3://${s3bucket}/steps/pre.sh,${media}" ;
		/opt/elastic-mapreduce-ruby/elastic-mapreduce \
		-c /opt/elastic-mapreduce-ruby/credentials.json \
		-j ${jobid} \
		--jar  s3://${s3bucket}/steps/script-runner.jar \
		--step-name "ff: ${media}" \
		--args "s3://${s3bucket}/steps/ff.sh,${abr},${vbr}" ;
		/opt/elastic-mapreduce-ruby/elastic-mapreduce \
		-c /opt/elastic-mapreduce-ruby/credentials.json \
		-j ${jobid} \
		--jar  s3://${s3bucket}/steps/script-runner.jar \
		--step-name "post: ${media}" \
		--args "s3://${s3bucket}/steps/post.sh,${media}" ;
	else
		#存活模式未開啟狀態，舊版本的唯一狀態
		#EMR指令(全部)
		jobidraw=`/opt/elastic-mapreduce-ruby/elastic-mapreduce \
		-c /opt/elastic-mapreduce-ruby/credentials.json \
		--create \
		--name "mohohan" \
		--num-instances ${ins_num} \
		--master-instance-type ${ins_type} \
		--slave-instance-type  ${ins_type} \
		--log-uri s3://${s3bucket}/logs/ \
		--bootstrap-action s3://${s3bucket}/steps/bootstrap.sh \
		--jar  s3://${s3bucket}/steps/script-runner.jar \
		--step-name "pre: ${media}" \
		--args "s3://${s3bucket}/steps/pre.sh,${media}" \
		--jar  s3://${s3bucket}/steps/script-runner.jar \
		--step-name "ff: ${media}" \
		--args "s3://${s3bucket}/steps/ff.sh,${abr},${vbr}" \
		--jar  s3://${s3bucket}/steps/script-runner.jar \
		--step-name "post: ${media}" \
		--args "s3://${s3bucket}/steps/post.sh,${media}"`
		#取出jobid
		jobid=${jobidraw/Created job flow /}
	fi
	#啟動監控進度程序
	${soft_path}/ffemr/ffemr.php ${jobidrnd} ${jobid} &
fi
