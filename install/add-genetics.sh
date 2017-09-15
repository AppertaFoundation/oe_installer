#!/bin/bash

## adds or removes genetics modules

# Test command parameters
remove=0
showhelp=0

for i in "$@"
do
case $i in
	--remove|-remove|-r|-uninstall|--uninstall|-u|--disable|-disable) remove=1
	;;
    --help) showhelp=1
    ;;
	*)  echo "Unknown command line: $i"
    ;;
esac
done

# Show help text
if [ $showhelp = 1 ]; then
    echo ""
    echo "DESCRIPTION:"
    echo "Adds or removes the genetics modules. NOTE: You must have built from the V2.0 sample config for this to work"
    echo ""
    echo "usage: $0 [--remove ] [--help] "
    echo ""
    echo "COMMAND OPTIONS:"
	echo ""
	echo "  --help         : Show this help"
    echo "  --remove       : Remove the genetics modules (default it to add)"
	echo "                   NOTE: This will not remove any database migrations"
    echo "                   or data"
	echo ""
    exit 1
fi

source /etc/openeyes/env.conf

## Update tools
bash /vagrant/install/update-oe-tools.sh

if [ ! $remove = 1 ]; then
# Enable genetics modules
    echo "enabling genetics modules..."

    sudo sed -i "s#/\*'Genetics',#'Genetics',#" /var/www/openeyes/protected/config/local/common.php
    sudo sed -i "s#'OphInGeneticresults',\*/#'OphInGeneticresults',#" /var/www/openeyes/protected/config/local/common.php
else
    ## Disable genetics modules
    if grep -i "/\*'Genetics'" /var/www/openeyes/protected/config/local/common.php 2>/dev/null ; then
        echo "genetics already disabled"
    else
        sudo sed -i "s#'Genetics',#/\*'Genetics',#" /var/www/openeyes/protected/config/local/common.php
    fi

    if grep -i "'OphInGeneticresults',\*/" /var/www/openeyes/protected/config/local/common.php 2>/dev/null ; then
        echo ""
    else
        sudo sed -i "s#'OphInGeneticresults',#'OphInGeneticresults',\*/#" /var/www/openeyes/protected/config/local/common.php
    fi
fi
