#!/bin/bash

# Terminate if any command fails
set -e

echo "WARNING: This script will install the openeyes software in /var/www/openeyes (which it expects to not exist)"
echo "but will NOT affect any databases or apache configurations. "

read -p "Are you sure? " -n 1 -r
if [[ $REPLY =~ ^[Yy]$ ]]
then
  /vagrant/install/install-oe.sh --live
  oe-migrate
fi

