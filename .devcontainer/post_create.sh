#!/bin/bash

# Install wp-env
sudo npm install -g @wordpress/env

# Trust git repo
git config --global --add safe.directory $PWD || { echo "git config failed"; exit 1; }
