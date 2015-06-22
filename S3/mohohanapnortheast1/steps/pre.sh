#!/bin/bash

#變數設定
workingdir="/mnt"
media=${1}

#變數處理
mediadir=`dirname ${media}`
mediafile=`basename ${media}`

#從S3複製檔案到local
cd ${workingdir}
mkdir media
cd media
s3cmd get ${media} ./${mediafile}

#影音分離
ffmpeg -i ${mediafile} -vn -acodec copy ${mediafile}.a.mp4
ffmpeg -i ${mediafile} -vcodec copy -an ${mediafile}.v.mp4

#BUG處理：切割音頻的時候會有軟體不支援的問題，如果這樣的話就直接轉音頻不是用切的
audiosize=$(stat -c%s "${mediafile}.a.mp4")
if [[ $audiosize -eq 0 ]]; then
	ffmpeg -y -i ${mediafile} -vn -acodec libfaac -ab 192k ${mediafile}.a.mp4
fi

#影片分割
mkvmerge --split size:32m ${mediafile}.v.mp4 -o ${mediafile}.v.%1d.mp4

#取得影片分割數
splits=`find ${workingdir}/media -name '*.v.*.mp4' | wc -l`

#製作MR腳本
echo /user/`whoami`/mohohan_i_file/${mediafile}.a.mp4 >> mohohan_i_conf.txt
for ((i=1;i<=${splits};i++)); do echo /user/`whoami`/mohohan_i_file/${mediafile}.v.${i}.mp4 >> mohohan_i_conf.txt; done

#上傳檔案與腳本至HDFS
hadoop fs -mkdir mohohan_i_conf
hadoop fs -mkdir mohohan_i_file
hadoop fs -put mohohan_i_conf.txt mohohan_i_conf
for ((i=1;i<=${splits};i++)); do hadoop fs -put ${mediafile}.v.${i}.mp4 mohohan_i_file; done
hadoop fs -put ${mediafile}.a.mp4 mohohan_i_file
hadoop fs -mkdir mohohan_o_file
