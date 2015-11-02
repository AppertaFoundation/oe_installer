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


echo Downloading OpenEyes code base
cd /var/www
# TODO: if openeyes dir exists, delete it


git clone -b feature/install https://github.com/openeyes/OpenEyes.git openeyes 
cd openeyes/protected
unzip yii.zip
unzip vendors.zip


echo Installing OpenEyes modules
branch=master
cd /var/www/openeyes/protected/modules
git clone https://github.com/openeyes/EyeDraw.git eyedraw -b $branch
git clone https://github.com/openeyes/OphCiExamination.git OphCiExamination -b $branch
git clone https://github.com/openeyes/OphCiPhasing.git OphCiPhasing -b $branch
git clone https://github.com/openeyes/OphCoTherapyapplication.git OphCoTherapyapplication -b $branch
git clone https://github.com/openeyes/OphDrPrescription.git OphDrPrescription -b $branch
git clone https://github.com/openeyes/OphInBiometry.git OphInBiometry -b $branch
git clone https://github.com/openeyes/OphInVisualfields.git OphInVisualfields -b $branch
git clone https://github.com/openeyes/OphOuAnaestheticsatisfactionaudit.git OphOuAnaestheticsatisfactionaudit -b $branch
git clone https://github.com/openeyes/OphTrConsent.git OphTrConsent -b $branch
git clone https://github.com/openeyes/OphTrIntravitrealinjection.git OphTrIntravitrealinjection -b $branch
git clone https://github.com/openeyes/OphTrLaser.git OphTrLaser -b $branch
git clone https://github.com/openeyes/OphTrOperationbooking.git OphTrOperationbooking -b $branch
git clone https://github.com/openeyes/OphTrOperationnote.git OphTrOperationnote -b $branch
git clone https://github.com/openeyes/PatientTicketing.git PatientTicketing -b $branch


mkdir /var/www/openeyes/protected/runtime
mkdir /var/www/openeyes/cache
mkdir /var/www/openeyes/protected/cache
chmod 777 /var/www/openeyes/assets
chmod 777 /var/www/openeyes/cache
chmod 777 /var/www/openeyes/protected/cache
chmod 777 /var/www/openeyes/protected/runtime


echo Creating blank database
cd $installdir

echo "
drop database if exists openeyes;
create database openeyes;
grant all privileges on openeyes.* to openeyes@localhost identified by 'openeyes';
flush privileges;
" > /tmp/openeyes-mysql-create.sql

mysql -u root "-ppassword" < /tmp/openeyes-mysql-create.sql
rm /tmp/openeyes-mysql-create.sql


echo Downloading database
cd /var/www/openeyes/protected/modules
git clone -b release/v1.11 https://github.com/openeyes/Sample.git sample
cd sample/sql
mysql -uopeneyes "-popeneyes" -D openeyes < openeyes_testdata.sql


echo Performing database migrations

cd /var/www/openeyes/protected
./yiic migrate --interactive=0
./yiic migratemodules --interactive=0


echo Configuring Apache

echo "
<VirtualHost *:80>
ServerName hostname

DocumentRoot /var/www/openeyes
<Directory /var/www/openeyes>
    Options FollowSymLinks
    AllowOverride All
    Order allow,deny
    Allow from all
</Directory>

ErrorLog /var/log/apache2/error.log
LogLevel warn
CustomLog /var/log/apache2/access.log combined
</VirtualHost>
" > /etc/apache2/sites-available/000-default.conf

apache2ctl restart


# copy our commands to /usr/bin
cp /vagrant/install/oe-* /usr/bin

cp /vagrant/install/bashrc /home/vagrant/.bashrc
hostname OpenEyesVM


echo --------------------------------------------------
echo OPENEYES SOFTWARE INSTALLED
echo Please check previous messages for any errors
echo --------------------------------------------------
