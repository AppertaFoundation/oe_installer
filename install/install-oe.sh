#!/bin/bash

# Terminate if any command fails
set -e


# Verify we are running as root
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root" 1>&2
   exit 1
fi


echo Downloading OpenEyes code base
cd /var/www
# TODO: if openeyes dir exists, delete it


git clone -b feature/IOLMaster https://github.com/openeyes/OpenEyes.git openeyes 
cd openeyes/protected
unzip yii.zip
unzip vendors.zip

# keep a copy of these zips around in case we checkout an older branch that does not include them
mkdir -p /usr/lib/openeyes
cp yii.zip /usr/lib/openeyes
cp vendors.zip /usr/lib/openeyes
cd /usr/lib/openeyes
unzip yii.zip
unzip vendors.zip


echo Installing OpenEyes modules
branch=master
cd /var/www/openeyes/protected/modules
git clone https://github.com/openeyes/EyeDraw.git eyedraw -b $branch
git clone https://github.com/openeyes/OphCiExamination.git OphCiExamination -b $branch
git clone https://github.com/openeyes/OphCiPhasing.git OphCiPhasing -b $branch
git clone https://github.com/openeyes/OphCoTherapyapplication.git OphCoTherapyapplication -b $branch
git clone https://github.com/openeyes/OphCoCorrespondence.git OphCoCorrespondence -b $branch
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

if [ ! -d "/var/www/openeyes/protected/javamodules" ]; then
  mkdir -p /var/www/openeyes/protected/javamodules
fi
cd /var/www/openeyes/protected/javamodules
git clone https://github.com/openeyes/IOLMasterImport.git IOLMasterImport -b $branch


mkdir -p /var/www/openeyes/cache
mkdir -p /var/www/openeyes/assets
mkdir -p /var/www/openeyes/protected/cache
mkdir -p /var/www/openeyes/protected/runtime
chmod 777 /var/www/openeyes/cache
chmod 777 /var/www/openeyes/assets
chmod 777 /var/www/openeyes/protected/cache
chmod 777 /var/www/openeyes/protected/runtime
if [ ! `grep -c '^vagrant:' /etc/passwd` = '1' ]; then
  chown -R www-data:www-data /var/www/*
fi


if [ ! "$1" == "--live" ]; then
echo Creating blank database
cd $installdir

echo "
drop database if exists openeyes;
create database openeyes;
grant all privileges on openeyes.* to 'openeyes'@'%' identified by 'openeyes';
flush privileges;
" > /tmp/openeyes-mysql-create.sql

mysql -u root "-ppassword" < /tmp/openeyes-mysql-create.sql
rm /tmp/openeyes-mysql-create.sql


echo Downloading database
cd /var/www/openeyes/protected/modules
git clone -b release/v1.11.2 https://github.com/openeyes/Sample.git sample
cd sample/sql
mysql -uroot "-ppassword" -D openeyes < openeyes_sample_data.sql
fi


echo Performing database migrations

cd /var/www/openeyes/protected
./yiic migrate --interactive=0
./yiic migratemodules --interactive=0


if [ ! "$1" == "--live" ]; then
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
fi


# copy our commands to /usr/bin
cp /vagrant/install/oe-* /usr/bin

# copy our new configs to /etc/openeyes
mkdir /etc/openeyes
cp /vagrant/install/etc/openeyes/* /etc/openeyes/
cp /vagrant/install/bashrc /etc/bash.bashrc

# The default environment type is assumed to be DEV/AWS.
# If we are on a vagrant box, set it to DEV/VAGRANT
# For live systems, /etc/openeyes/env.conf will have to be edited manually

if [ `grep -c '^vagrant:' /etc/passwd` = '1' ]; then
  hostname OpenEyesVM
  sed -i "s/envtype=AWS/envtype=VAGRANT/" /etc/openeyes/env.conf
  cp /vagrant/install/bashrc /home/vagrant/.bashrc
fi

if [ "$1" == "--live" ]; then
echo "# env can be one of DEV or LIVE
# envtype can be one of LIVE, AWS or VAGRANT

env=LIVE
envtype=LIVE
" >/etc/openeyes/env.conf
fi


# Copy DICOM related files in place as required
cp /vagrant/install/dicom-file-watcher.conf /etc/init/
cp /vagrant/install/dicom /etc/cron.d/
cp /vagrant/install/run-dicom-service.sh /usr/local/bin
chmod +x /usr/local/bin/run-dicom-service.sh

useradd iolmaster -s /bin/false -m
mkdir /home/iolmaster/test
mkdir /home/iolmaster/incoming
chown iolmaster:www-data /home/iolmaster/*
chmod 775 /home/iolmaster/*


echo --------------------------------------------------
echo OPENEYES SOFTWARE INSTALLED
echo Please check previous messages for any errors
echo --------------------------------------------------
