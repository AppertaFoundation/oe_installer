#!/bin/bash

git config core.fileMode false 2>/dev/null

source /etc/openeyes/modules.conf
dir=$PWD


# Process commandline parameters
gitroot="openeyes"
force=0
migrate=1
fix=1
compile=1
customgitroot=0
nosummary=0
username=""
pass=""
httpuserstring=""
usessh=0
sshuserstring="git"
fixparams=""
showhelp=0

for i in "$@"
do
case $i in
	-f|-force|--force) force=1
		## Force will ignore any uncomitted changes and checkout over the top
		;;
	--nomigrate|--no-migrate|--n|-n) fixparams="$fixparams --no-migrate"
		## nomigrate will prevent database migrations from running automatically at the end of checkout
		;;
	--no-summary) nosummary=1
		## don't show summary at completion
		;;
	--no-fix) fix=0
		## don't run oe-fix at completion
		;;
	--no-compile) fixparams="$fixparams --no-compile"
		## don't compile java
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
    echo "Updates all modules to latest version (of current branch)"
    echo ""
    echo "usage: oe-update [--help] [--force | -f] [--no-migrate | -n] [--no-compile] [--no-summary]"
    echo ""
    echo "COMMAND OPTIONS:"
    echo "  --help         : Display this help text"
    echo "  --no-migrate "
    echo "          | -n   : Prevent database migrations running automatically after"
    echo "                   update"
    echo "  --force | -f   : forces the checkout, even if local changes are uncommitted"
    echo "  --no-compile   : Do not complile java modules after Checkout"
    echo "  --no-summary   : Do not display a summary of the checked-out modules after "
    echo "                   completion"
	echo ""
    exit 1
fi

sudo git config core.fileMode false 2>/dev/null

cd /var/www/openeyes/protected/modules 2>/dev/null
if [ -d "sample" ]; then modules=(${modules[@]} sample); fi # Add sample DB to checkout if it exists

if [ "$force" = 0 ]; then
    echo ""
  echo "checking for uncommited changes"

  changes=0
  modulelist=""

  for module in ${modules[@]}; do
    	if [ ! -d "$module" ]; then
    		if [ ! "$module" = "openeyes" ]; then printf "\e[31mModule $module not found\e[0m\n"; fi
    	else
    		if [ ! "$module" = "openeyes" ]; then cd $module; fi

    		# check if this is a git repo
    		if [ -d ".git" ] || [ "$module" = "openeyes" ]; then
    				sudo git diff --quiet
    				if [ ! $? = 0 ]; then
    				  changes=1
    				  modulelist="$modulelist $module"
    				fi
    		fi
    	fi

    	if [ ! "$module" = "openeyes" ]; then cd ..; fi
  done

  # ensure modules directory exists - prevents code being checked out to wrong place
  sudo mkdir -p /var/www/openeyes/protected/javamodules
  cd  /var/www/openeyes/protected/javamodules/
  for module in ${javamodules[@]}; do
    	if [ ! -d "$module" ]; then
    		printf "\e[31mModule $module not found\e[0m\n"
    	else
    		cd $module;
    		sudo git diff --quiet
    		if [ ! $? = 0 ]; then
    		  changes=1
    		  modulelist="$modulelist $module"
    		fi
    		cd ..
    	fi
  done

  #  If we have unstaged changes, then pause and  warn which modules are affected
  if [ "$changes" = "1" ]; then
        printf "\e[41m\e[97m  WARNING  \e[0m \n"
        echo "There are uncommitted changes in the following modules: $modulelist"
		echo "To continue and attempt to merge, select option 1"
		echo "To cancel and review changes, select option 2"
        printf "To discard these changes, run: \e[1m oe-update -f \e[0m \n"
        echo "Alternatively, manually git reset --hard to ignore, or git stash to keep, etc"
        printf "\e[41m\e[97m  WARNING  \e[0m \n";
        echo ""

		select yn in "Continue" "Cancel"; do
			case $yn in
				Continue ) echo "

Continuing update and attempting to merge...
If errors are encounted, you will need to fix manually or use oe-update - f to discard local changes

				"; accept="1"; break;;
				Cancel ) echo "
Cancelling...
				"; exit 1;;
			esac
		done
  fi
else
	# delete dependencies during force (they will get re-added by oe-fix)
	sudo rm -rf /var/www/openeyes/node_modules
fi

printf "\e[32mopeneyes: \e[0m"

# If force, then reset before pull
if [ "$force" = "1" ]; then
    echo "Resetting core. Any uncomitted changes have been lost..."
    git reset --hard
fi
# use -ff to kill all modules and reload
if [ "$killmodules" = "1" ]; then
    echo "Deleting modules. Any uncomitted changes have been lost..."
    rm -rf /var/www/openeyes/protected/modules
    git reset --hard
    sudo mkdir -p /var/www/openeyes/protected/modules
fi

cd /var/www/openeyes/protected/modules

# Pull the core
git pull

# pull the php modules

for module in ${modules[@]}; do
  if [ ! -d "$module" ]; then
		if [ ! "$module" = "openeyes" ]; then printf "\e[31mModule $module not found\e[0m\n"; fi
  else
        cd $module
    	if [ -d ".git" ]; then
    		printf "\e[32m$module: \e[0m"
    		if [ "$force" = "1" ]; then
                echo "Resetting. Any uncomitted changes have been lost..."
                git reset --hard
            fi
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
        if [ -d ".git" ]; then
            printf "\e[32m$module: \e[0m"
            if [ "$force" = "1" ]; then
                echo "Resetting. Any uncomitted changes have been lost..."
                git reset --hard
            fi
            git pull
        fi
      fi
done

# update composer
sudo composer self-update

# Now reset/relink various config files etc
if [ "$fix" = "1" ]; then  oe-fix $fixparams; fi

cd "$dir"
printf "\e[42m\e[97m  UPDATE COMPLETE  \e[0m \n"
if [ ! "$nosummary" = 1 ]; then oe-which; fi
echo ""
