#!/bin/bash

git config core.fileMode false 2>/dev/null

source /etc/openeyes/modules.conf
dir=$PWD

#  If -f was not specified on the command line, then check each module
#  to see if any changes are unstaged.

# Check root
cd /var/www/openeyes
printf "\e[32mopeneyes: \e[0m"
if [ "$1" = "-f" ]; then git reset --hard; fi
# use -ff to kill all modules and reload
if [ "$1" = "-ff" ]; then rm -rf /var/www/openeyes/protected/modules; git reset --hard; fi
git pull

# Check out the PHP modules

cd /var/www/openeyes/protected/modules
if [ -d "sample" ]; then modules=(${modules[@]} sample); fi # Add sample DB to checkout if it exists

for module in ${modules[@]}; do
  if [ ! -d "$module" ]; then
		if [ ! "$module" = "openeyes" ]; then printf "\e[31mModule $module not found\e[0m\n"; fi
  else
    cd $module
	if [ -d ".git" ]; then
		printf "\e[32m$module: \e[0m"
		if [ "$1" = "-f" ]; then git reset --hard; fi
		git pull
	fi
    cd ..
  fi
done

# Check out the Java modules

cd /var/www/openeyes/protected/javamodules
for module in ${javamodules[@]}; do
  if [ ! -d "$module" ]; then
    printf "\e[31mModule $module not found\e[0m\n"
  else
    cd $module
    printf "\e[32m$module: \e[0m"
    if [ "$1" = "-f" ]; then git reset --hard; fi
    git pull
    cd ..
  fi
done

# update composer
sudo composer self-update

# Now reset/relink various config files etc
oe-fix


cd "$dir"
printf "\e[42m\e[97m  UPDATE COMPLETE  \e[0m \n"
oe-which
echo ""
