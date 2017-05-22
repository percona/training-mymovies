#!/bin/bash

echo "Updating http://buzzr.duckdns.org/"

curl "https://www.duckdns.org/update?domains=buzzr&token=31babfdd-7c62-4320-a610-d66e63778558&ip=$1"

echo;
