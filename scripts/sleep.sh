#!/bin/sh

LOAD="$(cat /proc/loadavg | awk -F. '{print $1}')"
iter=1

while [ $LOAD -gt 12 ] ; do
	echo "load too high: $LOAD, sleeping - $iter"
	echo "$(date) load too high: $LOAD, sleeping - $iter" >> /tmp/foo
	sleep 5
	iter=$(( $iter + 1 ))
	LOAD="$(cat /proc/loadavg | awk -F. '{print $1}')"
done

if [ $iter -gt 1 ] ; then
	echo "load acceptable again, continuing after $iter sleep cycles" >> /tmp/foo
fi
