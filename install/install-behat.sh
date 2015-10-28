#!/bin/bash

# Terminate on nay failed commands
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
if [[ $hostname != "vagrant-ubuntu-trusty-64" ]]; then
  echo You must run this script on the virtual box
  exit 1
fi


# Get behat and its dependencies

cd /vagrant/install
rm -rf /var/www/behat
git clone git://github.com/Behat/Behat.git /var/www/behat 
cd /var/www/behat

echo "
{
    "require-dev": {
        "behat/behat": "~2",
        "behat/mink": "~1",
        "behat/yii-extension": "~1",
        "behat/mink-extension": "~1",
        "fabpot/goutte": "1.*@stable",
        "behat/mink-goutte-driver": "~1",
        "behat/mink-selenium2-driver": "~1",
        "sensiolabs/behat-page-object-extension": "1.0.1"
    },
    "config": {
        "bin-dir": "bin/"
    },
    "autoload": {
        "classmap": ["features/bootstrap/Pages/OpenEyesPage.php"]
  }
}
" > /tmp/openeyes-mysql-create.sql

curl https://getcomposer.org/composer.phar -o composer.phar
chmod +x composer.phar
mv composer.phar /usr/bin/composer
composer install


# symlink directories

cd /var/www/behat
rm -rf features
#rm -rf reports
ln -s /var/www/openeyes/features features
#ln -s /var/www/openeyes/reports reports
ln -s /var/www/openeyes/protected/yii vendor/yiisoft/yii


# download chrome and firefox

wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
sudo dpkg -i --force-depends google-chrome-stable_current_amd64.deb
apt-get install firefox


# Get the latest chromedriver for linux chrome

LATEST=$(wget -q -O - http://chromedriver.storage.googleapis.com/LATEST_RELEASE)
wget http://chromedriver.storage.googleapis.com/$LATEST/chromedriver_linux64.zip
unzip chromedriver_linux64.zip && rm chromedriver_linux64.zip
cp chromedriver /usr/bin/


