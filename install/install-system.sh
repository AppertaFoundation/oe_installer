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
    fallocate -l 1024M /swapfile 2>/dev/null || :
    chmod 600 /swapfile 2>/dev/null || :
    mkswap /swapfile 2>/dev/null || :
    swapon /swapfile 2>/dev/null || :
    echo /swapfile   none    swap    sw    0   0 >>/etc/fstab 2>/dev/null || :

    sysctl vm.swappiness=10
    echo 'vm.swappiness = 10' >> /etc/sysctl.conf
fi

#add repos for PHP5.6 and Java7
sudo -E add-apt-repository ppa:ondrej/php -y
sudo -E add-apt-repository ppa:openjdk-r/ppa -y


echo Performing package updates
# ffmpeg isn't supported on trusty, so a third party ppa is required
sudo -E add-apt-repository ppa:mc3man/gstffmpeg-keep -y
sudo -E add-apt-repository ppa:jonathonf/ffmpeg-3 -y
sudo apt update -y
sudo apt upgrade -y
sudo apt-get autoremove -y


echo Installing required system packages
# export DEBIAN_FRONTEND=noninteractive
# debconf-set-selections <<< 'mariadb-server-5.5 mysql-server/root_password password password'
# debconf-set-selections <<< 'mariadb-server-5.5 mysql-server/root_password_again password password'

sudo apt-get install -y git-core software-properties-common php5.6 php5.6-mbstring php5.6-zip imagemagick libjpeg62 mariadb-server mariadb-client debconf-utils unzip xfonts-75dpi default-jre libgamin0 gamin openjdk-7-jdk openjdk-8-jdk xfonts-base ruby ant libbatik-java libreoffice-core libreoffice-common libreoffice-writer libapache2-mod-php5.6 php5.6-cli php5.6-mysql php5.6-ldap php5.6-curl php5.6-xsl php5.6-gd php-imagick php5.6-mcrypt php5.6-imagick ffmpeg

# install node.js and npm
curl -sL https://deb.nodesource.com/setup_8.x | sudo -E bash -
sudo apt-get install -y nodejs
npm install -g npm

# Install grunt
echo "installing global npm dependencies"
npm install -g grunt-cli

# Download and install wkhtmltopdf/toimage (needed for printing and lightning viewer)
# switch to correct wkhtml version based on OS (trusty/xenial/bionic/etc)
echo -e "\n\nInstalling wkhtmltopdf...\n\n"
osver=`lsb_release -rs`
if [[ "$osver" == "14.04" ]]; then
    # Ubuntu 14.04
	sudo -E wget -O wkhtml.deb https://downloads.wkhtmltopdf.org/0.12/0.12.5/wkhtmltox_0.12.5-1.trusty_amd64.deb
elif [[ "$osver" == "16.04" ]]; then
	# Ubuntu 16.04
	sudo -E wget -O wkhtml.deb https://downloads.wkhtmltopdf.org/0.12/0.12.5/wkhtmltox_0.12.5-1.xenial_amd64.deb
elif [[ "$osver" == "18.04" ]]; then
	# Ubuntu 18.04
	sudo -E wget -O wkhtml.deb https://downloads.wkhtmltopdf.org/0.12/0.12.5/wkhtmltox_0.12.5-1.bionic_amd64.deb
fi
## TODO: replace with package manager. e.g, https://packagist.org/packages/h4cc/wkhtmltopdf-amd64 and https://packagist.org/packages/h4cc/wkhtmltoimage-amd64
sudo dpkg -i --force-depends wkhtml.deb || echo -e "\n\nWARNING WARNING WARNING:\n\nUnable to install wkhtmltopdf automatically\nPlease install manually"
sudo rm wkhtml.deb

if [ ! "$dependonly" = "1" ]; then
    #  Install pre-compiled FAM module and configure PHP to use it
#    sed -i "s/;   extension=msql.so/extension=fam.so/" /etc/php/5.6/apache2/php.ini
#    sed -i "s/;   extension=msql.so/extension=fam.so/" /etc/php/5.6/cli/php.ini
#    cp /vagrant/install/fam.so /usr/lib/php/5.6/20121212/


    # Enable display_errors and error logging for PHP, plus configure timezone
    mkdir /var/log/php 2>/dev/null || :
    chown www-data /var/log/php
	chown www-data /var/log/php
	sed -i "s/^display_errors = Off/display_errors = On/" /etc/php/5.6/apache2/php.ini
	sed -i "s/^display_startup_errors = Off/display_startup_errors = On/" /etc/php/5.6/apache2/php.ini
	sed -i "s/^;date.timezone =/date.timezone = \"Europe\/London\"/" /etc/php/5.6/apache2/php.ini
	sed -i "s/;error_log = php_errors.log/error_log = \/var\/log\/php_errors.log/" /etc/php/5.6/apache2/php.ini
	sed -i "s/^display_errors = Off/display_errors = On/" /etc/php/5.6/cli/php.ini
	sed -i "s/^display_startup_errors = Off/display_startup_errors = On/" /etc/php/5.6/cli/php.ini
	sed -i "s/;error_log = php_errors.log/error_log = \/var\/log\/php_errors.log/" /etc/php/5.6/cli/php.ini
	sed -i "s/^;date.timezone =/date.timezone = \"Europe\/London\"/" /etc/php/5.6/cli/php.ini

	sudo timedatectl set-timezone Europe/London

	a2enmod rewrite
    cp /vagrant/install/bashrc /etc/bash.bashrc
    source /vagrant/install/bashrc

    # Bind mysql to accept connections from remote servers
    ## TODO: only do this for vagrant environments
    sudo sed -i "s/\s*bind-address\s*=\s*127\.0\.0\.1/bind-address    = 0.0.0.0/" /etc/mysql/my.cnf
	sudo sed -i "s/\s*bind-address\s*=\s*127\.0\.0\.1/bind-address    = 0.0.0.0/" /etc/mysql/mariadb.conf.d/50-server.cnf
    sudo service mysql restart

	# disable terminal bell on tab / delete errors
	sudo sed -i "s/# set bell-style none/set bell-style none/" /etc/inputrc
	sudo sed -i "s/# set bell-style visible/set bell-style visible/" /etc/inputrc

fi

# Install php composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
mv composer.phar /usr/local/bin/composer

# ensure mcrypt has been installed sucesfully
sudo phpenmod mcrypt
sudo phpenmod imagick

echo --------------------------------------------------
echo SYSTEM SOFTWARE INSTALLED
echo Please check previous messages for any errors
echo --------------------------------------------------
