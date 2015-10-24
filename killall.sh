# kill scripts
killprocess() {
    ps aux | grep $1 > /dev/null
    mypid=$(pidof $1)
    if [ "$mypid" != "" ]; then
        kill -9 $(pidof $1)
        if [[ "$?" == "0" ]]; then
            echo "PID $mypid ($1) killed."
        fi
    else
        echo "None killed."
    fi
    return;
}


if [ $# -eq 0 ]; then
    killprocess /usr/bin/php
else
    killprocess /usr/bin/php $1
fi
