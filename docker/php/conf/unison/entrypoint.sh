#!/usr/bin/env bash

run_unison () {
    local status=1

    while [ $status != 0 ]; do
        su - liquid -c 'unison liquid'
        status=$?
    done
}





(run_unison; /usr/local/bin/check-unison.sh) &




sudo -u liquid sh -c '/usr/local/bin/unison -socket 5000 2>&1 >/dev/null' &


supervisord -n -c /etc/supervisord.conf
