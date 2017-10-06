#!/bin/bash

# Process commandline parameters
gitroot="openeyes"
dependonly=0
showhelp=0

for i in "$@"
do
case $i in
	-d|--depend-only) dependonly=1
		## only (re)install dependencies
		;;
    --help) showhelp=1
    ;;
	*)  if [ ! -z "$i" ]; then
			echo "Unknown command line: $i. Try --help"
		fi
    ;;
esac
done

# Show help text
if [ $showhelp = 1 ]; then
    echo ""
    echo "DESCRIPTION:"
    echo "Installs system dependencies for OpenEyes"
    echo ""
    echo "usage: install-system.sh [--help] [--depend-only | -d]"
    echo ""
    echo "COMMAND OPTIONS:"
    echo "  --help         : Display this help text"
    echo "  --depend-only "
    echo "          | -d   : install/refresh dependencies, but do NOT (re)configure"
	echo ""
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

# Terminate if any command fails
set -e

# Verify we are running as root
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root" 1>&2
   exit 1
fi


# SET UP SWAP SPACE
if [ ! "$dependonly" = "1" ]; then
    fallocate -l 1024M /swapfile
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    echo /swapfile   none    swap    sw    0   0 >>/etc/fstab

    sysctl vm.swappiness=10
    echo 'vm.swappiness = 10' >> /etc/sysctl.conf
fi

echo Performing package updates
apt -y update


echo Installing required system packages
export DEBIAN_FRONTEND=noninteractive
debconf-set-selections <<< 'mariadb-server-5.5 mysql-server/root_password password password'
debconf-set-selections <<< 'mariadb-server-5.5 mysql-server/root_password_again password password'

apt install -y git-core libapache2-mod-php5 php5-cli php5-mysql php5-ldap php5-curl php5-xsl php5-gd imagemagick php5-imagick libjpeg62 mariadb-server mariadb-client debconf-utils unzip xfonts-75dpi default-jre libgamin0 gamin openjdk-7-jdk xfonts-base ruby ant libbatik-java libreoffice-core libreoffice-common libreoffice-writer php5-mcrypt

# install node.js and npm
curl -sL https://deb.nodesource.com/setup_8.x | sudo -E bash -
sudo apt install -y nodejs
npm install -g npm

# Install grunt
echo "installing global npm dependencies"
npm install -g grunt-cli

# wkhtmltox is now bundled in the repository. Original download location is:
# wget http://download.gna.org/wkhtmltopdf/0.12/0.12.2.1/wkhtmltox-0.12.2.1_linux-trusty-amd64.deb
cd /vagrant/install
dpkg -i --force-depends wkhtmltox-0.12.2.1_linux-trusty-amd64.deb

if [ ! "$dependonly" = "1" ]; then
    #  Install pre-compiled FAM module and configure PHP to use it
    sed -i "s/;   extension=msql.so/extension=fam.so/" /etc/php5/apache2/php.ini
    sed -i "s/;   extension=msql.so/extension=fam.so/" /etc/php5/cli/php.ini
    cp /vagrant/install/fam.so /usr/lib/php5/20121212/


    # Enable display_errors and error logging for PHP, plus configure timezone
    mkdir /var/log/php
    chown www-data /var/log/php
    sed -i "s/^display_errors = Off/display_errors = On/" /etc/php5/apache2/php.ini
    sed -i "s/^display_startup_errors = Off/display_startup_errors = On/" /etc/php5/apache2/php.ini
    sed -i "s/^;date.timezone =/date.timezone = \"Europe\/London\"/" /etc/php5/apache2/php.ini

    sed -i "s/;error_log = php_errors.log/error_log = \/var\/log\/php_errors.log/" /etc/php5/apache2/php.ini
    sed -i "s/^display_errors = Off/display_errors = On/" /etc/php5/cli/php.ini
    sed -i "s/^display_startup_errors = Off/display_startup_errors = On/" /etc/php5/cli/php.ini
    sed -i "s/;error_log = php_errors.log/error_log = \/var\/log\/php_errors.log/" /etc/php5/cli/php.ini
    sed -i "s/^;date.timezone =/date.timezone = \"Europe\/London\"/" /etc/php5/cli/php.ini

    a2enmod rewrite
    cp /vagrant/install/bashrc /etc/bash.bashrc
    source /vagrant/install/bashrc

    # Bind mysql to accept connections from remote servers
    ## TODO: only do this for vagrant environments
    sed -i "s/\s*bind-address\s*=\s*127\.0\.0\.1/bind-address    = 0.0.0.0/" /etc/mysql/my.cnf
    /etc/init.d/mysql restart
fi

# Install php composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
mv composer.phar /usr/local/bin/composer

# ensure mcrypt has been installed sucesfully
sudo php5enmod mcrypt

echo --------------------------------------------------
echo SYSTEM SOFTWARE INSTALLED
echo Please check previous messages for any errors
echo --------------------------------------------------
