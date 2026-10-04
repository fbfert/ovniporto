#!/bin/sh
# Runs from the nginx image's /docker-entrypoint.d before nginx starts:
# busybox crond runs /etc/periodic/daily, where logrotate checks the weekly rotation.
set -e
crond -b -l 8
