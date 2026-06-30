#!/bin/sh

sleep 3

./vendor/bin/phinx migrate -e production

exec php -S 0.0.0.0:8080 -t public