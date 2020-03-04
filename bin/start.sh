#!/bin/sh
#
# Script to start RaspiSMS daemons
#
PID_DIR="/var/run/raspisms/"
LOG_FILE="/var/log/raspisms/daemons.log"
SCRIPT=$(readlink -f "$0")
RASPISMS_DIR=$(readlink -f "${SCRIPT%/*}/../")
CONSOLE_PATH="$RASPISMS_DIR/console.php"
COMMAND="php $CONSOLE_PATH controllers/internals/Console.php launcher"

#Create PID DIR IF NOT EXISTS
if [ ! -d $PID_DIR ]
then
    mkdir $PID_DIR
fi

#Create log file if not exists
if [ ! -f $LOG_FILE ]
then
    touch $LOG_FILE
    chmod 700 $LOG_FILE
fi

#Run command to start daemons
$COMMAND
exit $?
