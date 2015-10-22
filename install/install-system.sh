#!/bin/bash

# Terminate if any command fails
set -e

# Verify we are running as root
FILE="/tmp/out.$$"
GREP="/bin/grep"
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root" 1>&2
   exit 1
fi


# SET UP SWAP SPACE

fallocate -l 1024M /swapfile
chmod 600 /swapfile
mkswap /swapfile
swapon /swapfile
echo /swapfile   none    swap    sw    0   0 >>/etc/fstab


echo Performing package updates
apt-get -y update


echo Installing required system packages
debconf-set-selections <<< 'mysql-server mysql-server/root_password password password'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password password'
apt-get install -y git-core libapache2-mod-php5 php5-cli php5-mysql php5-ldap php5-curl php5-xsl wkhtmltopdf libjpeg62 mysql-server mysql-client debconf-utils unzip

a2enmod rewrite

echo --------------------------------------------------
echo SYSTEM SOFTWARE INSTALLED
echo Please check previous messages for any errors
echo --------------------------------------------------


