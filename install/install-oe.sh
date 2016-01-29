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

# Chose branch / tag to clone (default is master)
branch=master
if [ ! -z "$1" ]; then
	if [ "$1" != "--live" ]; then
		branch="$1"
	elif [ "$2" != "--live" ]; then
		branch="$2"
	fi
fi
echo "Installing openeyes $branch" 

echo Downloading OpenEyes code base
cd /var/www
# If openeyes dir exists, prompt user to delete it
if [ -d "openeyes" ]; then
	echo "CAUTION: openeyes folder already exists. This installer will delete it. Do you wish to continue."
	select yn in "Yes" "No"; do
		case $yn in
			Yes ) echo "OK, removing existing openeyes folder"; rm -rf openeyes; break;;
			No ) echo "OK, exiting..."; exit;;
		esac
	done

fi


# git clone -b $branch https://github.com/openeyes/OpenEyes.git openeyes
if ! git clone -b $branch https://github.com/openeyes/OpenEyes.git openeyes; then
	echo "Branch/Tag $branch does not exist. Do you want to use master instead? (Selecting NO will exit)"
	select yn in "Yes" "No"; do
		case $yn in
			Yes ) echo "OK, switching branch to master"; branch=master; git clone -b master https://github.com/openeyes/OpenEyes.git openeyes; break;;
			No ) echo "OK, exiting..."; exit;;
		esac
	done
fi
cd openeyes/protected
unzip -o yii.zip
unzip -o vendors.zip

# keep a copy of these zips around in case we checkout an older branch that does not include them
mkdir -p /usr/lib/openeyes
cp yii.zip /usr/lib/openeyes
cp vendors.zip /usr/lib/openeyes
cd /usr/lib/openeyes
if [ ! -d "yii" ]; then unzip yii.zip; fi
if [ ! -d "vendors" ]; then unzip vendors.zip; fi


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

echo Installing OpenEyes modules

oe-checkout $branch -f --nomigrate

if [ ! -d "/var/www/openeyes/protected/javamodules" ]; then
	mkdir -p /var/www/openeyes/protected/javamodules
fi




if ["$1" == "--live" -o "$2" == "--live"]; then
 echo "Installing for production - no patient data"
else
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
  # Options FollowSymLinks
  # AllowOverride All
  # Order allow,deny
  # Allow from all
</Directory>

ErrorLog /var/log/apache2/error.log
LogLevel warn
CustomLog /var/log/apache2/access.log combined
</VirtualHost>
" > /etc/apache2/sites-available/000-default.conf

apache2ctl restart
fi


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
