#!/bin/bash

echo "system info..."
php --version
composer -V

echo "installing application dependencies..."
composer install

echo "*** Adding Composer Dependencies to the $PATH from $WORKSPACE ***"
export PATH=$PATH:$WORKSPACE/vendor/bin

echo "dependency info..."
phing -version
phpunit --version

echo "building..."
phing build