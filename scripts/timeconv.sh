#!/bin/sh
echo -n $(date -u -d "UTC 1970-01-01 $1 secs" +"%Y%m%d %H:%M:%S")
