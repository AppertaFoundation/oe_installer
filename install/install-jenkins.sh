#!/bin/bash

# Terminate on any failed commands
set -e


# Verify we are running as root

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root" 1>&2
   exit 1
fi


# Verify we are on the Ubuntu VM

hostname=`uname -n`
if [[ $hostname != "OpenEyesVM" ]]; then
  echo You must run this script on the virtual box
  exit 1
fi


# Get Jenkins

wget -q -O - https://jenkins-ci.org/debian/jenkins-ci.org.key | apt-key add -
echo deb http://pkg.jenkins-ci.org/debian binary/ > /etc/apt/sources.list.d/jenkins.list
apt-get update
apt-get install -y jenkins


# Add the jenkins user to sudoers and make jenkins/www-data the owner/group for web files

adduser jenkins sudo
chown -R jenkins:www-data /var/www/openeyes/*
 


echo --------------------------------------------------
echo JENKINS INSTALLED
echo Please check previous messages for any errors
echo --------------------------------------------------
