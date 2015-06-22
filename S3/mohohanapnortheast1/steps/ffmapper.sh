#!/bin/bash

#變數設定
abr=${1}
vbr=${2}

#前置動作
uid=`whoami`
hash=`head -c 200 /dev/urandom | tr -dc _A-Z-a-z-0-9 | head -c 32`
tmpfolder=${uid}-${hash}
mkdir -p /mnt/$tmpfolder

#處理影音
read offset input
filename=`basename $input`
hadoop fs -get $input /mnt/$tmpfolder/$filename
filenameA=`basename $input`
filenameB=`basename $input .a.mp4`
if [ $filenameA != $filenameB ]; then
	#聲音
	for i in {1..2}
	do
		ffmpeg                                   \
		-y                                       \
		-i       /mnt/$tmpfolder/$filename       \
		-vn                                      \
		-acodec  libfaac                         \
		-ab      ${abr}k                         \
		-pass    ${i}                            \
		-threads 0                               \
		         /mnt/$tmpfolder/${filename}.ok.mp4 ;
	done
else
	#影像
	for i in {1..2}
	do
		ffmpeg                                   \
		-y                                       \
		-i       /mnt/$tmpfolder/$filename       \
		-vcodec  libx264                         \
		-b       ${vbr}k                         \
		-an                                      \
		-pass    ${i}                            \
		-threads 0                               \
		         /mnt/$tmpfolder/${filename}.ok.mp4 ;
	done
fi
hadoop fs -put /mnt/$tmpfolder/${filename}.ok.mp4 /user/$uid/mohohan_o_file

#後續動作
rm -rf /mnt/$tmpfolder
