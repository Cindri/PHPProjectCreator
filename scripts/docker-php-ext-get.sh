#!/usr/bin/env sh
set -e

dir=/usr/src/php

if [ ! -f "$dir/.docker-extracted" ]; then
    echo >&2 "error: PHP source required, run 'docker-php-source extract' first"
    exit 1
fi

dir="$dir/ext"

usage() {
    echo "usage: $0 module-name module-version"
    echo "   ie: $0 redis 4.3.0"
}

name=$1
version=$2

if [ -z "$name" ]; then
    usage >&2
    exit 1
fi

if [ -z "$version" ]; then
    usage >&2
    exit 1
fi

mkdir -p "$dir/$name"
curl -fsSL "https://pecl.php.net/get/$name-$version.tgz" | tar xvz -C "$dir/$name" --strip 1