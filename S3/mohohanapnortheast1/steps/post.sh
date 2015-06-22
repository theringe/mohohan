#!/bin/bash

#變數設定
workingdir="/mnt"
media=${1}

#變數處理
mediadir=`dirname ${media}`
mediafile=`basename ${media}`

#取得影片分割數
splits=`find ${workingdir}/media -name '*.v.*.mp4' | wc -l`

#從HDFS下載檔案
cd ${workingdir}/media
hadoop fs -get mohohan_o_file mohohan_o_file

#製作影片合併腳本
mergestr="mkvmerge -o mohohan_o_file/${mediafile}.v.mp4.ok.mp4 "
for ((i=1;i<=${splits};i++)) do
	if [[ $i -eq 1 ]]; then
		mergestr="${mergestr} "
	else
		mergestr="${mergestr} +"
	fi
	mergestr="${mergestr}mohohan_o_file/${mediafile}.v.${i}.mp4.ok.mp4"
done

#影片合併
`$mergestr`

#影音合併
ffmpeg -i mohohan_o_file/${mediafile}.a.mp4.ok.mp4 -i mohohan_o_file/${mediafile}.v.mp4.ok.mp4 -vcodec copy -acodec copy ${mediafile}.mp4

#清除HDFS檔案
hadoop fs -rmr mohohan_i_conf
hadoop fs -rmr mohohan_i_file
hadoop fs -rmr mohohan_o_conf
hadoop fs -rmr mohohan_o_file

#清除本地檔案
rm -rf mohohan_o_file
rm -f  mohohan_i_conf.txt
rm -f  ${mediafile}.a.*
rm -f  ${mediafile}.v.*

#將檔案複製到S3
s3cmd put --acl-public ${mediafile}.mp4 ${mediadir}/${mediafile}.mp4 --rr

#清除本地檔案
rm -f  ${mediafile}
rm -f  ${mediafile}.mp4
