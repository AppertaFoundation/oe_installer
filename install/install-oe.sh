#!/bin/bash


# Verify we are running as root
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root" 1>&2
   exit 1
fi

# Find real folder name where this script is located, then try symlinking it to /vagrant
# This is needed for non-vagrant environments - will silenly fail if /vagrant already exists
SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

# try the symlink - this is expected to fail in a vagrant environment
# TODO detect if running in a vagrant environment and don't try linking (it isn't necessary)
ln -s $DIR/.. /vagrant 2>/dev/null
if [ ! $? = 1 ]; then
	echo "
	$DIR has been symlinked to /vagrant
	"
fi

# copy our commands to /usr/bin
cp -f /vagrant/install/oe-* /usr/bin

# copy our new configs to /etc/openeyes (don't overwrite existing config)
mkdir -p /etc/openeyes
cp -n /vagrant/install/etc/openeyes/* /etc/openeyes/
cp -n /vagrant/install/bashrc /etc/bash.bashrc

# set command options
# Chose branch / tag to clone (default is master)
# set live
branch=master
defaultbranch=master
live=0
develop=0
force=0
customgitroot=0
gitroot=openeyes
cleanconfig=0
username=""
pass=""
httpuserstring=""
usessh=0
sshuserstring="git"
showhelp=0
checkoutparams=""

# Process command line inputs
for i in "$@"
do
case $i in
    --live|-l|--upgrade) live=1
		## live will install for production ready environment
		;;
	--develop|-d|--d) develop=1; defaultbranch=develop; checkoutparams="$checkoutparams $i"
		## develop set default branches to develop if the named branch does not exist for a module
		;;
	--force|-f|--f) force=1
		## force will delete the www/openeyes directory without prompting - use with caution - useful to refresh an installation, or when moving between versions <=1.12 and verrsions >= 1.12.1
		;;
	--clean|-ff|--ff) force=1; cleanconfig=1
		## will completely wipe any existing openeyes configuration from /etc/openeyes - use with caution
		;;
	--root|-r|--r|--remote) customgitroot=1
		## Await custom root for git repo in net parameter
		;;
    -u*) username="${i:2}"; checkoutparams="$checkoutparams $i"
    ;;
    -p*) pass="${i:2}"; checkoutparams="$checkoutparams $i"
    ;;
    --ssh|-ssh) usessh=1; checkoutparams="$checkoutparams $i"
	;;
    --help) showhelp=1
    ;;
	*)  if [ ! -z "$i" ]; then
			if [ "$customgitroot" = "1" ]; then
				gitroot=$i
				customgitroot=0
                $checkoutparams="$checkoutparams -r $i"
				## Set root path to repo
			else
				if [ "$branch" == "master" ]; then branch=$i; else echo "Unknown command line: $i"; fi
				## Set branch name
			fi
		fi
    ;;
esac
done

# Show help text
if [ $showhelp = 1 ]; then
    echo ""
    echo "DESCRIPTION:"
    echo "Installs the openeyes application"
    echo ""
    echo "usage: $0 <branch> [--help] [--force | -f] [--no-migrate | -n] [--kill-modules | -ff ] [--no-compile] [-r <remote>] [--no-summary] [--develop | -d] [-u<username>]  [-p<password>]"
    echo ""
    echo "COMMAND OPTIONS:"
    echo "  <branch>       : Install the specified <branch> / tag - default is to install master"
    echo "  --help         : Display this help text"
    echo "  --force | -f   : delete the www/openeyes directory without prompting "
    echo "                   - use with caution - useful to refresh an installation,"
    echo "                     or when moving between versions <=1.12 and versions >= 1.12.1"
    echo "  --clean | -ff  : will completely wipe any existing openeyes configuration "
    echo "                   out. This is required when switching between versions <= 1.12 "
    echo "                   from /etc/openeyes - use with caution"
    echo "  -r <remote>    : Use the specifed remote github fork - defaults to openeyes"
    echo "  --develop "
    echo "           |-d   : If specified branch is not found, fallback to develop branch"
    echo "                   - default woud fallback to master"
    echo "  -u<username>   : Use the specified <username> for connecting to github"
    echo "                   - default is anonymous"
    echo "  -p<password>   : Use the specified <password> for connecting to github"
    echo "                   - default is to prompt"
    echo "  -ssh		   : Use SSH protocol  - default is https"
    echo ""
    exit 1
fi


echo "

Installing openeyes $branch from https://gitgub.com/$gitroot

"


# Terminate if any command fails
set -e

echo "
Downloading OpenEyes code base...
"

cd /var/www

# If openeyes dir exists, prompt user to delete it
if [ -d "openeyes" ]; then
	if [ ! "$force" = "1" ]; then
		echo "
CAUTION: openeyes folder already exists.
This installer will delete it. Any uncommitted changes will be lost!
If you're upgrading this is necessary.
Do you wish to continue?
"
		select yn in "Yes" "No"; do
			case $yn in
				Yes ) echo "OK."; force="1"; break;;
				No ) echo "OK, aborting. Nothing has been changed...
				"; exit;;
			esac
		done
	fi

	if [ "$force" = "1" ]; then
        # If cleanconfig (-ff) has been given on the command line, then completely
        # wipe the existing oe config before continuing. USE WITH EXTREME CAUTION
		if [ "$cleanconfig" = "1" ]; then
			echo "cleaning old config from /etc/openeyes"
			rm -rf /etc/openeyes
			mkdir /etc/openeyes
			cp -f /vagrant/install/etc/openeyes/* /etc/openeyes/
			cp -f /vagrant/install/bashrc /etc/bash.bashrc
		fi
        # END of cleanconfig

		if [ -d "openeyes/protected/config" ]; then
			echo "backing up previous configuration to /etc/openeyes/backup"
			mkdir -p /etc/openeyes/backup/config
			cp -f -r openeyes/protected/config/* /etc/openeyes/backup/config/
		fi

		echo "Removing existing openeyes folder"
		rm -rf openeyes
	fi

fi

echo calling oe-checkout with $checkoutparams
oe-checkout $branch -f --no-migrate --no-summary --no-fix $checkoutparams


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
mkdir -p /var/www/openeyes/protected/cache/events
mkdir -p /var/www/openeyes/protected/files
mkdir -p /var/www/openeyes/protected/runtime
mkdir -p /var/www/openeyes/protected/runtime/cache
chmod 777 /var/www/openeyes/cache
chmod 777 /var/www/openeyes/assets
chmod 777 /var/www/openeyes/protected/cache
chmod 777 /var/www/openeyes/protected/cache/events
chmod 777 /var/www/openeyes/protected/files
chmod 777 /var/www/openeyes/protected/runtime
chmod 777 /var/www/openeyes/protected/runtime/cache

if [ ! `grep -c '^vagrant:' /etc/passwd` = '1' ]; then
	chown -R www-data:www-data /var/www/*
fi

# call oe-fix
oe-fix

if [ ! "$live" = "1" ]; then
    echo ""
	echo "Creating blank database..."
	cd $installdir

	echo "
	drop database if exists openeyes;
	create database openeyes;
	grant all privileges on openeyes.* to 'openeyes'@'%' identified by 'openeyes';
	flush privileges;
	" > /tmp/openeyes-mysql-create.sql

	mysql -u root "-ppassword" < /tmp/openeyes-mysql-create.sql
	rm /tmp/openeyes-mysql-create.sql


    # Create correct user string to pass to github
    if [ ! -z $username ]; then
        if [ ! -z $pass ]; then
    		sshuserstring="$username"
            httpuserstring="${username}:${pass}@"
        else
    		sshuserstring="$username"
            httpuserstring="${username}@"
        fi
    fi

    # Set base url string for cloning all repos
    basestring="https://${httpuserstring}github.com/$gitroot"

    # If using ssh, change the basestring to use ssh format
    if [ $usessh = 1 ]; then
    	basestring="${sshuserstring}@github.com:$gitroot"
    fi


	echo Downloading database
	cd /var/www/openeyes/protected/modules
	if ! git clone -b $branch ${basestring}/Sample.git sample ; then
		echo "$branch doesn't exist for sample database. Falling back to $defaultbranch branch for openeyes..."
        if ! git clone -b $defaultbranch ${basestring}/sample.git sample ; then
			# If we cannot find default branch at specifeid remote, fall back to OE git hub
			if [ "$gitroot != "openeyes ]; then
				echo "could not find $defaultbranch at $gitroot remote. Falling back to openeyes official repo"
				git clone -b $defaultbranch ${basestring/$gitroot/openeyes}/sample.git sample
			fi
		fi
	fi

	cd sample/sql
	mysql -uroot "-ppassword" -D openeyes < openeyes_sample_data.sql

	# # Set banner to show branch name
	echo "
	use openeyes;
	UPDATE openeyes.setting_installation s SET s.value='New openeyes installation - $branch' WHERE s.key='watermark';
	" > /tmp/openeyes-mysql-setbanner.sql

	mysql -u root "-ppassword" < /tmp/openeyes-mysql-setbanner.sql
	rm /tmp/openeyes-mysql-setbanner.sql

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
  cp -f /vagrant/install/bashrc /home/vagrant/.bashrc
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

echo ""
oe-which

echo --------------------------------------------------
echo OPENEYES SOFTWARE INSTALLED
echo Please check previous messages for any errors
echo --------------------------------------------------
