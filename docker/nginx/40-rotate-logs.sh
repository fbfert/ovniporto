#!/bin/sh
# Runs from the image's /docker-entrypoint.d before nginx starts. The container has no root
# (and so no crond): a background loop asks logrotate once a day, as the nginx user that owns
# the logs; logrotate itself decides when the weekly rotation is due (logrotate.conf).
set -e
(
    while true; do
        logrotate --state /tmp/logrotate.status /etc/logrotate.d/nginx || true
        sleep 86400
    done
) &
