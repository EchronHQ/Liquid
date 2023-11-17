#!/usr/bin/env bash

# Run in foreground to warmup
su - liquid -c "unison liquid"

# Run unison server
su - liquid -c "unison -repeat=watch liquid > /home/liquid/custom_unison.log 2>&1 &"
