#!/bin/bash
PHPRC=$PWD/../etc/php7.2
export PHPRC
umask 022
export PHP_FCGI_CHILDREN
PHP_FCGI_MAX_REQUESTS=99999
export PHP_FCGI_MAX_REQUESTS
exec /usr/bin/php-cgi7.2
