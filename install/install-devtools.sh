#!/bin/bash

# Terminate on any failed commands
set -e


# Verify we are running as root
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root" 1>&2
   exit 1
fi


apt-get -y install ruby-dev build-essential
sudo gem install compass --no-ri --no-rdoc

sudo npm install -g grunt-cli


echo --------------------------------------------------
echo DEVTOOLS INSTALLED
echo Please check previous messages for any errors
echo --------------------------------------------------
