#!/bin/sh

LOAD="$(cat /proc/loadavg | awk -F. '{print $1}')"

if [ $LOAD -gt 8 ] ; then
	echo "load too high: $LOAD, sleeping"
	sleep 20
	sleep $LOAD
	echo "$(date) load too high: $LOAD, sleeping" >> /tmp/foo
else
	echo "load acceptable: $LOAD, continuing"
fi
