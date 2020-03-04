#!/bin/sh
#
# Script to stop RaspiSMS daemons
#
PID_DIR="/var/run/raspisms"
DAEMON_LAUNCHER_PID_FILE="/var/run/raspisms/RaspiSMS Daemon Launcher.pid"
PID_FILES=$PID_DIR/*.pid


kill_process()
{
    local PID=$1
    kill "$PID"

    return $?
}

#Kill daemon launcher if available
if [ -f "$DAEMON_LAUNCHER_PID_FILE" ]
then
    printf "Stop RaspiSMS daemon Launcher..."
    PID=$(cat "$DAEMON_LAUNCHER_PID_FILE")
    $(kill_process "$PID")
    RETURN=$?

    if [ $RETURN -eq 0 ]
    then
        rm -f "$DAEMON_LAUNCHER_PID_FILE"
        printf "success.\n"
    else
        printf "failed.\n"
        exit 1
    fi
fi

sleep 1

printf "Stop RaspiSMS remaining daemons..."
for f in $PID_FILES
do
    [ -f "$f" ] || continue #Bypass no real file return on empty dir

    printf "."
    PID=$(cat "$f")
    kill_process "$PID"
    rm -f "$f"
done
printf "Done.\n"

exit 0
