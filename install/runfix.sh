#!/bin/bash

## Resets various caches and configs

# Test command parameters
compile=1
resartserv=1
clearcahes=1
buildassests=1
migrate=1
showhelp=0
composer=1
nowarnmigrate=0
resetconfig=0
eyedraw=1

for i in "$@"
do
case $i in
	--no-compile) compile=0
	;;
	--no-restart) resartserv=0
	;;
	--no-clear) clearcahes=0
	;;
	--no-assets) buildassests=0
	;;
    --no-migrate|--nomigrate) migrate=0
	;;
	--no-eyedraw|-ned) eyedraw=0
	;;
    --help) showhelp=1
    ;;
	--no-composer) composer=0
	;;
	--no-warn-migrate) nowarnmigrate=1
	;;
	-fc|--reset-config) resetconfig=1
	;;
	*)  echo "Unknown command line: $i"
    ;;
esac
done

# Show help text
if [ $showhelp = 1 ]; then
    echo ""
    echo "DESCRIPTION:"
    echo "Applies various fixes to make sure files in in the correct place, database is migrated, code is compiled, etc."
    echo ""
    echo "usage: $0 <branch> [--help] [--no-compile] [--no-restart] [--no-clear ] [--no-assets] [--no-migrate]"
    echo ""
    echo "COMMAND OPTIONS:"
	echo ""
	echo "  --help         : Show this help"
    echo "  --no-compile   : Do not complile java modules"
	echo "  --no-restart   : Do not restart services"
	echo "  --no-clear     : Do not clear caches"
	echo "  --no-assets    : Do not (re)build assets"
    echo "  --no-migrate   : Do not run db migrations"
	echo "  --no-composer  : Do not update composer dependencies"
	echo "  --no-eyedraw   : Do not (re)import eyedraw configuration"
	echo ""
    exit 1
fi

source /etc/openeyes/env.conf

## Update tools
bash /vagrant/install/update-oe-tools.sh

cd /var/www/openeyes
if [ -f ".htaccess.sample" ]; then
echo Renaming .htaccess file
sudo mv .htaccess.sample .htaccess
if [ ! "$env" = "VAGRANT" ]; then chown -R www-data:www-data .htaccess; fi
fi

if [ -f "index.example.php" ]; then
echo Renaming index.php file
sudo mv index.example.php index.php
if [ ! "$env" = "VAGRANT" ]; then chown -R www-data:www-data index.php; fi
fi

if [ ! -f "protected/config/local/common.php" ]; then
    if [ -d "/etc/openeyes/backup/config/" ] && [ "$resetconfig" = "0" ]; then
        echo "\n\n*********** WARNING: Restoring backed up local configuration ... ***********\n\n"
		sudo mkdir -p protected/config/local
        sudo cp -R /etc/openeyes/backup/config/local/* protected/config/local/.
    else
        echo "WARNING: Copying sample configuration into local ..."
        #sudo cp -R protected/config/local.sample/* protected/config/local/.
		sudo mkdir -p protected/config/local
		sudo cp -n protected/config/local.sample/common.sample.php protected/config/local/common.php
		sudo cp -n protected/config/local.sample/console.sample.php protected/config/local/console.php
    fi;

	# Fix permissions
	sudo chown -R www-data:www-data protected/config/local
	sudo chmod -R 775 protected/config/local
	sudo sed -i "s/'username' => 'root',/'username' => 'openeyes',/" /var/www/openeyes/protected/config/local/common.php
	sudo sed -i "s/'password' => '',/'password' => 'openeyes',/" /var/www/openeyes/protected/config/local/common.php
fi;


# Make sure vendor directory found/linked
if [ ! -d "/var/www/openeyes/protected/vendors" ]; then
	echo Linking vendor framework
	sudo ln -s /usr/lib/openeyes/vendors /var/www/openeyes/protected/vendors
	sudo chown -R www-data:www-data /var/www/openeyes/protected/vendors 2>/dev/null || :
	if [ ! $? = 0 ]; then echo "unable to link vendors - this is expected for versions prior to v1.12"; fi
fi

# update composer and npm dependencies
if [ $composer == 1 ]; then
	if [ "$env" = "LIVE" ]; then
		echo "Installing/updating composer dependencies for LIVE"
		sudo composer install --no-dev --no-plugins --no-scripts

		echo "Installing/updating npm dependencies for LIVE"
		npm install --production
	else
		echo "Installing/updating composer dependencies"
		sudo composer install --no-plugins --no-scripts

		echo "Installing/updating npm dependencies"
		npm install
	fi
fi

## (re)-link dist directory for IOLMasterImport module and recompile
dwservrunning=0
# first check if service is running - if it is we stop it, then re-start at the end
if ps ax | grep -v grep | grep run-dicom-service.sh > /dev/null
	then
		dwservrunning=1
		echo "Stopping dicom-file-watcher..."
		sudo service dicom-file-watcher stop
fi

cd /var/www/openeyes/protected/javamodules/IOLMasterImport
sudo rm -rf dist/lib 2>/dev/null || :
sudo mkdir -p dist
sudo ln -s ../lib ./dist/lib
if [ ! $? = 0 ]; then echo "Failure is expeced in pre v1.12 releases (where IOLMasterImport does not exist)"; fi

# Compile IOLImporter
##TODO: When we have more java modules, replace with a generic compilation model
if [ $compile = 1 ]; then
  echo "
  Compiling IOLMAsterImport. Please wait....
  "
  sudo ./compile.sh > /dev/null 2>&1
  if [ ! $? = 0 ]; then echo "Failure is expeced in pre v1.12 releases (where IOLMasterImport does not exist)"; fi
fi

# restart the service if we stopped it
if [ $dwservrunning = 1 ] && [ $resartserv = 1 ]; then
	echo "Restarting dicom-file-watcher..."
	sudo service dicom-file-watcher start
fi

# Automatically migrate up, unless --no-migrate parameter is given
if [ "$migrate" = "1" ]; then
    echo ""
    echo "Migrating database..."
	if ! oe-migrate --quiet; then
		## Quit if migrate failed
		exit 1
	fi
    echo ""
else
	if [ "$nowarnmigrate" = "0" ]; then
	echo "
Migrations were not run automaically. If you need to run the database migrations, run command oe-migrate
"
	fi
fi

# import eyedraw config
if [ "$eyedraw" = "1" ]; then
	printf "\n\nImporting eyedraw configuration...\n\n"
	sudo php /var/www/openeyes/protected/yiic eyedrawconfigload --filename=/var/www/openeyes/protected/config/core/OE_ED_CONFIG.xml 2>/dev/null
fi

# Clear caches
if [ $clearcahes = 1 ]; then
	echo "Clearing caches..."
	sudo rm -rf /var/www/openeyes/protected/runtime/cache/* 2>/dev/null || :
	sudo rm -rf /var/www/openeyes/assets/* 2>/dev/null || :
	# Fix permissions
	sudo chown -R www-data:www-data /var/www/openeyes/protected/runtime/cache/
	sudo chown -R www-data:www-data /var/www/openeyes/assets
	sudo chmod -R 775 /var/www/openeyes/assets/
	echo ""
fi

if [ $buildassests = 1 ]; then
	echo "(re)building assets..."
	# use curl to ping the login page - forces php/apache to rebuild the assets directory
	curl -s http://localhost/site/login > /dev/null
fi

echo ""
echo "...Done"
echo ""
