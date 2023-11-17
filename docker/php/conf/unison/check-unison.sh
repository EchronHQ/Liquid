#!/usr/bin/env bash

while [ 1 == 1 ]; do
    ps aux | grep "[u]nison -repeat=watch liquid"
    if [ $? != 0 ]
    then
        (su - liquid -c 'unison -repeat=watch liquid') &
    fi
    sleep 10
done
