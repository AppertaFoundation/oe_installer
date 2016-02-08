#!/bin/bash

# Terminate if any command fails
set -e

# set command options
# Chose branch / tag to clone (default is master)
# set live
branch=master # By default, will always upgrade to latest master branch
customgitroot=0
gitroot=openeyes
# Process command line inputs
for i in "$@"
do
case $i in
	--root|-r|--r|--remote) customgitroot=1
		## Await custom root for git repo in net parameter
		;;
	*)  if [ ! -z "$i" ]; then 
			if [ "$customgitroot" = "1" ]; then
				gitroot=$i
				customgitroot=0
				## Set root path to repo
			else
				if [ "$branch" == "master" ]; then branch=$i; else echo "Unknown command line: $i"; fi
				## Set branch name
			fi
		fi
    ;;
esac
done

echo "
WARNING: This script will overwrite the openeyes software in /var/www/openeyes.
but will NOT affect any databases or apache configurations.
 
PLEASE ENSURE THAT YOU HAVE A BACKUP BEFORE PROCEEDING

ARE YOU SURE?"

		select yn in "Yes" "No"; do
			case $yn in
				Yes ) break;;
				No ) echo "OK, aborting. Nothing has been changed...
				"; exit;;
			esac
		done

# Call the installer in upgrade mode
/vagrant/install/install-oe.sh $branch --upgrade -f


