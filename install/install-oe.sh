#!/bin/bash

# Terminate if any command fails
set -e


# Verify we are running as root
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root" 1>&2
   exit 1
fi

# copy our commands to /usr/bin
cp /vagrant/install/oe-* /usr/bin

# copy our new configs to /etc/openeyes
mkdir -p /etc/openeyes
cp -f /vagrant/install/etc/openeyes/* /etc/openeyes/
cp -f /vagrant/install/bashrc /etc/bash.bashrc

# set command options
# Chose branch / tag to clone (default is master)
# set live
branch=master
live=0
develop=0
force=0

# Process command line inputs
for i in "$@"
do
case $i in
    --live|--l) live=1 ;;
	--develop|--d) develop=1 ;;
	--force|--f) force=1;;
	*) if [ ! -z "$i" ]; then branch=$1; fi
            # any other text, assume is a branch name
    ;;
esac
done


echo "Installing openeyes $branch" 

echo Downloading OpenEyes code base
cd /var/www
# If openeyes dir exists, prompt user to delete it
if [ -d "openeyes" ]; then
	if [ "$force" = "1" ]; then
		rm -rf openeyes
	else
		echo "CAUTION: openeyes folder already exists. This installer will delete it. Any uncommitted changes will be lost. Do you wish to continue?"
		select yn in "Yes" "No"; do
			case $yn in
				Yes ) echo "OK, removing existing openeyes folder"; rm -rf openeyes; break;;
				No ) echo "OK, exiting..."; exit;;
			esac
		done
	fi

fi

if [ "$develop" = 1 ]; then
	oe-checkout $branch -f --nomigrate --develop
else
	oe-checkout $branch -f --nomigrate
fi

cd /var/www/openeyes/protected
echo "uzipping yii. Please wait..."
if unzip -oq yii.zip ; then echo "."; fi
if unzip -oq vendors.zip ; then echo "."; fi

git submodule init
git submodule update -f

# keep a copy of these zips around in case we checkout an older branch that does not include them
mkdir -p /usr/lib/openeyes
cp yii.zip /usr/lib/openeyes 2>/dev/null || :
cp vendors.zip /usr/lib/openeyes 2>/dev/null || :
cd /usr/lib/openeyes
if [ ! -d "yii" ]; then echo "."; if unzip -oq yii.zip ; then echo "."; fi; fi
if [ ! -d "vendors" ]; then echo "."; if unzip -oq vendors.zip ; then echo "."; fi; fi




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

							
if [ ! "$live" = "1" ]; then
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


if [ ! "$live" = "1" ]; then
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


# The default environment type is assumed to be DEV/AWS.
# If we are on a vagrant box, set it to DEV/VAGRANT
# For live systems, /etc/openeyes/env.conf will have to be edited manually

if [ `grep -c '^vagrant:' /etc/passwd` = '1' ]; then
  hostname OpenEyesVM
  sed -i "s/envtype=AWS/envtype=VAGRANT/" /etc/openeyes/env.conf
  cp /vagrant/install/bashrc /home/vagrant/.bashrc
fi

if [ "$live" = "1" ]; then
echo "# env can be one of DEV or LIVE
# envtype can be one of LIVE, AWS or VAGRANT
env=LIVE
envtype=LIVE
" >/etc/openeyes/env.conf
fi


# Copy DICOM related files in place as required
cp -f /vagrant/install/dicom-file-watcher.conf /etc/init/
cp -f /vagrant/install/dicom /etc/cron.d/
cp -f /vagrant/install/run-dicom-service.sh /usr/local/bin
chmod +x /usr/local/bin/run-dicom-service.sh

id -u iolmaster &>/dev/null || useradd iolmaster -s /bin/false -m
mkdir -p /home/iolmaster/test
mkdir -p /home/iolmaster/incoming
chown iolmaster:www-data /home/iolmaster/*
chmod 775 /home/iolmaster/*

oe-which

echo --------------------------------------------------
echo OPENEYES SOFTWARE INSTALLED
echo Please check previous messages for any errors
echo --------------------------------------------------
