#!/bin/bash

#MR運作時取得的工作進度
job_arr=(`hadoop job -list all | grep "job_"`)
#長度都是6的倍數，任務碼都是在1/6的位置，狀態碼都是在2/6的位置
len=${#job_arr[*]}

#實際判斷
if [[ ${len} -eq 0 ]]; then
	#執行前(陣列沒長度，取得空值)
	echo 0
else
	#會到這裡的長度都是6的倍數
	jobstatus=${job_arr[${len}-5]}
	if [[ ${jobstatus} == "2" ]]; then
		#執行完
		echo 1
	else
		#執行中
		jobid=${job_arr[${len}-6]}
		progressraw=`hadoop job -status ${jobid} | grep "map() completion:"`
		progress=${progressraw/map() completion: /}
		echo ${progress}
	fi
fi
