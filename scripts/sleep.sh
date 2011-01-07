#!/bin/sh

LOAD="$(cat /proc/loadavg | awk -F. '{print $1}')"

if [ $LOAD -gt 10 ] ; then
	echo "load to high: $LOAD, sleeping"
	sleep 10
else
	echo "load acceptable: $LOAD, continuing"
fi
