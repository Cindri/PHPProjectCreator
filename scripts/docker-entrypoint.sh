#!/bin/sh

docker-php-ext-install mysqli && docker-php-ext-enable mysqli

apt-get update && apt-get upgrade -y

while true
do
    echo "Press [CTRL+C] to stop.."
    sleep 1
done


