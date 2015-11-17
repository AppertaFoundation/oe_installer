#!/bin/bash

# Terminate on any failed commands
set -e


# Verify we are running as root

FILE="/tmp/out.$$"
GREP="/bin/grep"
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root" 1>&2
   exit 1
fi


# Verify we are on the Ubuntu VM

hostname=`uname -n`
if [[ $hostname != "OpenEyesVM" ]]; then
  echo You must run this script on the virtual box
  exit 1
fi


# Set up node and npm

if [ ! -d "/var/www/grunt" ]; then
  mkdir /var/www/grunt
fi
cd /var/www/grunt
cp /vagrant/install/package.json .
cp /vagrant/install/Gruntfile.js .


apt-get -y install nodejs npm ruby-dev ruby-compass
ln -s /usr/bin/nodejs /usr/bin/node
sudo npm update -g npm
sudo npm install grunt --save-dev
sudo npm install -g grunt-cli
sudo npm install
sudo npm install grunt-contrib-uglify --save-dev
sudo npm install grunt-contrib-concat --save-dev
sudo gem install compass --no-ri --no-rdoc


# install ant
apt-get -y ant


echo --------------------------------------------------
echo DEVTOOLS INSTALLED
echo Please check previous messages for any errors
echo --------------------------------------------------
