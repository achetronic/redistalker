#!/bin/sh
pkill -f 'php -f /app/redistalker.php'
nohup php -f /app/redistalker.php &>/dev/null &
echo "sh /app/runtime/takeover.sh" | at now + 1 hour

