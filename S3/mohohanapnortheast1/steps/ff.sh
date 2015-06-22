#!/bin/bash

#變數設定
abr=${1}
vbr=${2}

#MR作業
hadoop \
jar /home/hadoop/contrib/streaming/hadoop-streaming.jar \
-D mapred.reduce.tasks=0 \
-input  mohohan_i_conf \
-inputformat org.apache.hadoop.mapred.lib.NLineInputFormat \
-output mohohan_o_conf \
-mapper "/home/hadoop/ffmapper.sh ${abr} ${vbr}" \
-file   /home/hadoop/ffmapper.sh
