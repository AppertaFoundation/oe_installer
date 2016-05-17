#!/bin/bash

# Terminate on any failed commands
set -e


# Verify we are running as root
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root" 1>&2
   exit 1
fi


# Set up node and npm

if [ ! -d "/var/www/grunt" ]; then
  mkdir /var/www/grunt
fi
cd /var/www/grunt
cp /vagrant/install/package.json .
cp /vagrant/install/Gruntfile.js .

cd /var/www/grunt
apt-get -y install ruby-dev
sudo gem install compass --no-ri --no-rdoc


# install phpunit (we need version 3.x)

wget https://phar.phpunit.de/phpunit-3.7.38.phar
chmod +x phpunit-3.7.38.phar
mv phpunit-3.7.38.phar /usr/local/bin/phpunit


echo --------------------------------------------------
echo DEVTOOLS INSTALLED
echo Please check previous messages for any errors
echo --------------------------------------------------
