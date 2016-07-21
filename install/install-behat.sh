#!/bin/bash

# Terminate on any failed commands
set -e


# Verify we are running as root
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root" 1>&2
   exit 1
fi


# Verify we are on the Ubuntu VM or AWS
user="vagrant"
if [ `grep -c '^vagrant:' /etc/passwd` = 0 ]; then
  user="ubuntu"
  if [ `grep -c '^ubuntu:' /etc/passwd` = 0 ]; then
    echo You must run this script on the Virtual Machine or AWS instance
    exit 1
  fi
fi


# Get behat
cd /vagrant/install
rm -rf /var/www/behat
git clone http://github.com/Behat/Behat.git /var/www/behat 
cd /var/www/behat

cp /vagrant/install/composer.json /var/www/behat/
cp /vagrant/install/behat.yml /var/www/behat/


# symlink directories

cd /var/www/behat
rm -rf features
mkdir -p vendor/yiisoft
ln -s ../openeyes/features features
ln -s ../openeyes/protected protected
cd vendor/yiisoft
ln -s ../../../openeyes/protected/yii/ yii
cd /var/www/behat


# download and run composer to get behat dependencies

curl https://getcomposer.org/composer.phar -o composer.phar
chmod 777 composer.phar
cp composer.phar /usr/bin/composer
mv composer.phar composer
sudo composer update

# composer does not generate the correct behat executable  - replace it with our own
mv /var/www/behat/bin/behat /var/www/behat/bin/behat-old
cp /vagrant/install/behat /var/www/behat/bin/


# download chrome and firefox

apt-get install -y --force-yes xorg jwm firefox

wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add -
echo "deb http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google-chrome.list
apt-get update
apt-get install -y google-chrome-stable


# Get the latest chromedriver for linux chrome

cd /var/www/behat
LATEST=$(wget -q -O - http://chromedriver.storage.googleapis.com/LATEST_RELEASE)
wget http://chromedriver.storage.googleapis.com/$LATEST/chromedriver_linux64.zip
unzip chromedriver_linux64.zip && rm chromedriver_linux64.zip
cp chromedriver /usr/bin/


# Download selenium standalone server 2.48.2

wget http://selenium-release.storage.googleapis.com/2.53/selenium-server-standalone-2.53.0.jar
cp /vagrant/install/start-selenium-server.sh /var/www/behat


# Link JWM window manager to X
echo "exec /usr/bin/jwm" > /home/$user/.xsession


# Clean out some irrelevant files
rm LICENSE *.md
chmod +x start-selenium-server.sh 


# Reset file permissions and ownership in behat directory
cd /var/www/behat
chmod -R 755 *
if [ ! "$user" = "vagrant" ]; then
  cd /var/www/behat
  chown -R www-data:www-data *
fi


echo --------------------------------------------------
echo BEHAT INSTALLED
echo Please check previous messages for any errors
echo --------------------------------------------------
