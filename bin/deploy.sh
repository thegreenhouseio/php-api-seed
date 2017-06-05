#!/bin/bash

# assumes build.sh has been run
DEPLOY_DIR="/var/www/html/"

echo "preparing deployment directory..."
sudo rm -rvf $DEPLOY_DIR*.phar
sudo rm -rvf $DEPLOY_DIR*.php

echo "deploying project..."
sudo cp -v build/tgh-api.phar $DEPLOY_DIR
sudo cp -v build/controller.php $DEPLOY_DIR