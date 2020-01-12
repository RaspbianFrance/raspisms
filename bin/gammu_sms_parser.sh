#!/bin/sh
date=$(date +%Y%m%d%H%M%S%N)
first_time=1

RECEPTION_DIR='/opt/raspisms/datas/gammu'

if [ ! -d $RECEPTION_DIR ]
then
	mkdir "$RECEPTION_DIR"
fi

for i in `seq $SMS_MESSAGES` ; do
	eval "sms_number=\"\${SMS_${i}_NUMBER}\""
	eval "sms_text=\"\${SMS_${i}_TEXT}\""
	
	if [ $first_time -eq 1 ]
	then
		sms="$sms_number:"
		first_time=0
	fi

	sms="$sms$sms_text"
done

if [ -z "$SMS_MESSAGES" ]
then
	exit 0
fi

echo "$sms" >> "$RECEPTION_DIR/$PHONE_ID-${date}.txt"
