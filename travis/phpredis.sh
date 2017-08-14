#!/bin/bash

mkdir /tmp/build-phpredis && cd /tmp/build-phpredis \
    && git clone --depth=50 https://github.com/phpredis/phpredis \
    && cd phpredis \
    && phpize \
    && echo "Configuring phpredis..." \
    && ./configure > /dev/null \
    && echo "Building phpredis..." \
    && make -j4 > /dev/null \
    && echo "Installing phpredis..." \
    && sudo make install > /dev/null

echo 'extension = redis.so' >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
