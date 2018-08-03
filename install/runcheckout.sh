#!/bin/bash
source /etc/openeyes/modules.conf
dir=$PWD

# Process commandline parameters
gitroot="appertafoundation"
defaultbranch=master
force=0
killmodules=0
resetconfig=0
killconfigbackup=0
migrate=1
fix=1
compile=1
customgitroot=0
nosummary=0
branch=$defaultbranch
username=""
pass=""
httpuserstring=""
usessh=0
sshuserstring="git"
fixparams=""
showhelp=0
sample=0
sampleonly=0

# Read in stored git config (username, root, usessh, etc)
source /etc/openeyes/git.conf

# store original ssh value, needed for updating remotes during pull
previousssh=$usessh

if [ -z "$1" ]; then showhelp=1; fi

for i in "$@"
do
case $i in
	-f|-force|--force) force=1
		## Force will ignore any uncomitted changes and checkout over the top
		;;
	-ff|--killmodules|--kill-modules) force=1; killmodules=1
		## killmodules should only be used when moving backwards from versions 1.12.1 or later to version 1.12 or earlier - removes the /protected/modules folder and re-clones all modules
		;;
	-fc|--reset-config) resetconfig=1; fixparams="$fixparams --reset-config"
	## remove local config files and either restore from backup (if available) or reset to sample configuration
	;;
	-fff) force=1; killmodules=1; killconfigbackup=1
		## killmodules should only be used when moving backwards from versions 1.12.1 or later to version 1.12 or earlier - removes the /protected/modules folder and re-clones all modules
		;;
	-ffc) resetconfig=1; killconfigbackup=1; fixparams="$fixparams --reset-config"
	## Delete backups and reset config
	;;
	--delete-backup) killconfigbackup=1
	## Delete configuration backups from /etc/openeyes
	;;
	--develop|--d|-d) defaultbranch=develop
		## develop will use develop baranches when the named branch does not exist for a module (by default it would use master)
		;;
	--nomigrate|--no-migrate|--n|-n) fixparams="$fixparams --no-migrate"
		## nomigrate will prevent database migrations from running automatically at the end of checkout
		;;
	--root|-r|--r|--remote) customgitroot=1
		## Await custom root for git repo in next parameter
		;;
	--no-summary) nosummary=1
		## don't show summary of checkout at completion
		;;
	--no-fix) fix=0
		## don't run oe-fix at completion
		;;
	--no-pull|--nopull) nopull=1
		## Do not issue git pull after checkout
		;;
	--no-compile) compile=0
		## don't compile java
	;;
    -u*) username="${i:2}"
    ;;
    -p*) pass="${i:2}"
    ;;
	--ssh|-ssh) usessh=1
	;;
	--https|-https|--htps|-htps) usessh=0
	;;
    --help) showhelp=1
    ;;
	--sample) sample=1
	;;
	--sample-only) sampleonly=1
	;;
	*)  if [ ! -z "$i" ]; then
			if [ "$customgitroot" = "1" ]; then
				gitroot=$i;
				customgitroot=0
				## Set root path to repo
			elif [ "$branch" == "master" ]; then
				branch=$i
			else echo "Unknown command line: $i"
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
    echo "Checks-out all modules of a specified branch. If a module does not exist locally then it will be cloned"
    echo ""
    echo "usage: $0 <branch> [--help] [--force | -f] [--no-migrate | -n] [--kill-modules | -ff ] [--no-compile] [--no-pull] [-r <remote>] [--no-summary] [--develop | -d] [-u<username>]  [-p<password>]"
    echo ""
    echo "COMMAND OPTIONS:"
    echo "  <branch>       : Checkout / clone the specified <branch>"
    echo "  --help         : Display this help text"
    echo "  --no-migrate "
    echo "          | -n   : Prevent database migrations running automatically after"
    echo "                   checkout"
	echo "	--no-pull		: Prevent automatic fast-forward to latest remote head"
    echo "  --force | -f   : forces the checkout, even if local changes are uncommitted"
    echo "  --kill-modules "
    echo "          | -ff  : Will delete all items from protected/modules and "
	echo "				     delete local configuration before checking out."
    echo "                   This may be required when moving between major releases"
	echo "					 !!USE WITH CAUTION!!"
	echo "	--reset-config "
	echo "		| -fc	   : Reset config/local/common.php to default settings"
	echo "				   : WARNING: Will destroy existing config"
	echo "  --delete-backup : Deletes backups from /etc/openeyes. Use in "
	echo "					  conjunction with --reset-config to fully reset config"
    echo "  --no-compile   : Do not complile java modules after Checkout"
    echo "  -r <remote>    : Use the specifed remote github fork - defaults to openeyes"
    echo "  --develop "
    echo "           |-d   : If specified branch is not found, fallback to develop branch"
    echo "                   - default woud fallback to master"
    echo "  --no-summary   : Do not display a summary of the checked-out modules after "
    echo "                   completion"
    echo "  -u<username>   : Use the specified <username> for connecting to github"
    echo "                   - default is anonymous"
    echo "  -p<password>   : Use the specified <password> for connecting to github"
    echo "                   - default is to prompt"
    echo "	-ssh		   : Use SSH protocol  - default is https"
	echo "	-https		   : Use HTTPS protocol  - default is https"
	echo ""
    exit 1
fi

echo ""
echo "Checking out $branch..."
echo ""

# Collect username if not already set
if [[ -z $username ]] && [[ $usessh = 0 ]]; then
	echo ""
	echo "-----------------------------------------------------------------------------------"
	echo "-  Please supply your github username - this is required to access private repos  -"
	echo "-----------------------------------------------------------------------------------"
	echo ""
	echo "github username: "

	read username
fi

# store username and git settings out to disk
echo "username=$username
gitroot=$gitroot
usessh=$usessh" | sudo tee /etc/openeyes/git.conf > /dev/null

# Set to cache password in memory (should only ask once per day or each reboot)
git config --global credential.helper 'cache --timeout=86400'

# Create correct user string to pass to github
if [ ! -z $username ]; then
    if [ ! -z $pass ]; then
		sshuserstring="git"
        httpuserstring="${username}:${pass}@"
    else
		sshuserstring="git"
        httpuserstring="${username}@"
    fi
fi

# Set base url string for cloning all repos
basestring="https://${httpuserstring}github.com/$gitroot"

# If using ssh, change the basestring to use ssh format
if [ $usessh = 1 ]; then
	basestring="git@github.com:$gitroot"

	$(ssh-agent)  2>/dev/null
	# attempt ssh authentication
	ssh git@github.com -T

fi

git config --global core.fileMode false 2>/dev/null
git config core.fileMode false 2>/dev/null


cd /var/www/openeyes/protected/modules 2>/dev/null

# Add sample DB to checkout if it exists or if --sample has been set
if [[ -d "sample" ]] || [[ $sample = 1 ]]; then modules=(${modules[@]} sample); fi

# if in sample only mode, we want only the sample module and nothing else
if [ $sampleonly = 1 ]; then modules=(sample); javamodules=(); fi

######################################################
# update remote if changing from https to ssh method #
######################################################
if [ ! $usessh = $previousssh ]; then

	for module in ${modules[@]}; do
		# only run if module exists
	  if [ ! -d "$module" ]; then
		  if [ ! "$module" = "openeyes" ]; then
			  break
		  fi
	  fi
	  echo "updating remote for $module"

			  if [ ! "$module" = "openeyes" ]; then cd $module; fi

			  # check if this is a git repo (and exists)
			  if [ -d ".git" ] || [ "$module" = "openeyes" ]; then

			  	# change the remote to new basestring
				git remote set-url origin $basestring/$module.git

			  fi

	  if [ ! "$module" = "openeyes" ]; then cd ..; fi
	done

	cd  /var/www/openeyes/protected/javamodules/
	for module in ${javamodules[@]}; do
	  if [ ! -d "$module" ]; then
		  break
	  else
		   	echo "updating remote for $module"
			cd $module;
			# change the remote to new basestring
	  		git remote set-url origin $basestring/$module.git
			cd ..
	  fi
	done
fi
##### END update remote #####

cd /var/www/openeyes/protected/modules 2>/dev/null

if [ ! "$force" = "1" ]; then
    echo ""
	echo "checking for uncommited changes"

	  changes=0
	  modulelist=""

	  for module in ${modules[@]}; do
		if [ ! -d "$module" ]; then
			if [ ! "$module" = "openeyes" ]; then printf "\e[31mModule $module not found\e[0m\n"
				break
			fi
		fi

				if [ ! "$module" = "openeyes" ]; then cd $module; fi

				# check if this is a git repo
				if [ -d ".git" ] || [ "$module" = "openeyes" ]; then
						git diff --quiet
						if [ ! $? = 0 ]; then
						  changes=1
						  modulelist="$modulelist $module"
						fi
				fi


		if [ ! "$module" = "openeyes" ]; then cd ..; fi
	  done

	  cd  /var/www/openeyes/protected/javamodules/
	  for module in ${javamodules[@]}; do
		if [ ! -d "$module" ]; then
			printf "\e[31mModule $module not found\e[0m\n"
			break
		else
			cd $module;
			git diff --quiet
			if [ ! $? = 0 ]; then
			  changes=1
			  modulelist="$modulelist $module"
			fi
			cd ..
		fi
	  done

	  #  If we have unstaged changes, then abort and  warn which modules are affected
	  if [ "$changes" = "1" ]; then
		printf "\e[41m\e[97m  CHECKOUT ABORTED  \e[0m \n"
		echo "There are uncommitted changes in the following modules: $modulelist"
		printf "To ignore these changes, run: \e[1m $0 $branch -f \e[0m \n"
		echo "Alternatively, manually git reset --hard to ignore, or git stash to keep, etc"
		printf "\e[41m\e[97m  CHECKOUT ABORTED  \e[0m \n";
		echo ""
		exit 1
	  fi
	#fi
else
	# delete dependencies during force (they will get re-added by oe-fix)
	sudo rm -rf /var/www/openeyes/node_modules 2>/dev/null
fi

if [ $killconfigbackup = 1 ]; then
	# delete backups from /etc/openeyes
	echo "
	************** DELETING BACKUPS FROM /etc/openeyes *******************
	"
	sudo rm -rf /etc/openeyes/backup
fi

# If -ff was speified, kill all existing modules and re-clone
if [ $killmodules = 1 ]; then
	echo ""
	echo "Removing all modules from protected/modules and protected/javamodules..."
	echo ""
	sudo rm -rf /var/www/openeyes/protected/modules
	sudo rm -rf /var/www/openeyes/protected/javamodules
	echo "Deleting all local configuration an non-tracked files..."
	cd /var/www/openeyes
	git clean -fx

	sudo mkdir /var/www/openeyes/protected/modules
	sudo mkdir /var/www/openeyes/protected/javamodules
	sudo chmod 777 /var/www/openeyes/protected/modules
	sudo chmod 777 /var/www/openeyes/protected/javamodules
fi

# Check out or clone the code modules
cd /var/www

for module in ${modules[@]}; do

  clone=0

  # Move from openeyes repo to modules - NOTE THAT openeyes must be the first module in the modules list, otherwise things go very wrong!
  if [ ! "$module" = "openeyes" ]; then
	  #ensure modules directory exists
	  mkdir -p /var/www/openeyes/protected/modules
      cd /var/www/openeyes/protected/modules
  fi

  # Determine if module already exists. If not, clone it
  if [ ! -d "$module" ]; then
      clone=1
  fi

  if [ $clone = 1 ]; then

	printf "\e[32m$module: Doesn't currently exist - cloning from : ${basestring}/${module}.git \e[0m"

	# checkout branch. If branch doesn't exist then get master instead
	if ! git clone -b $branch ${basestring}/${module}.git ; then
		echo "falling back to $defaultbranch branch for $module..."
		if ! git clone -b $defaultbranch ${basestring}/${module}.git ; then
			# If we cannot find $defaultbranch, then fall back to repository's defualt branch (catches situation where default branch is not master)
			echo "falling back to default branch for $module..."
			if ! git clone ${basestring}/${module}.git ; then
				# If we cannot find default branch at specifeid remote, fall back to OE git hub
				if [ "$gitroot != "openeyes ]; then
					echo "could not find $defaultbranch at $gitroot remote. Falling back to openeyes official repo"
					git clone -b $defaultbranch ${basestring/$gitroot/openeyes}/${module}.git
				fi
			fi
		fi
	fi
  else

    dirchanged=0
    processgit=1

    # Determine if module is part of core or a sub-repo - don't process modules that are part of core
    if [ ! "$module" = "openeyes" ]; then
        # If we're not processing the parent openeyes repo, then traverse into the module's subdir
        cd $module
    else
        cd /var/www/openeyes
    fi

	if [ ! -d ".git" ]; then processgit=0; fi

    if [ $processgit = 1 ]; then
		printf "\e[32m$module: \e[0m"
		git reset --hard
		git fetch --all
		git checkout tags/$branch 2>/dev/null
		if [ ! $? = 0 ]; then git checkout $branch 2>/dev/null; fi
		if [ ! $? = 0 ]; then echo "no branch $branch exists, switching to $defaultbranch"; git checkout $defaultbranch 2>/dev/null; fi

		## fast forward to latest head
		if [ ! "$nopull" = "1" ]; then
			echo "Pulling latest changes: "
			git pull
		fi
	fi

  fi
done

# Check out the Java modules
mkdir -p /var/www/openeyes/protected/javamodules
cd /var/www/openeyes/protected/javamodules
for module in ${javamodules[@]}; do
  if [ ! -d "$module" ]; then
    printf "\e[32m$module: Doesn't currently exist - cloning from ${basestring}/${module}.git: \e[0m"
	# checkout branch. If branch doesn't exist then get master instead
			if ! git clone -b $branch ${basestring}/${module}.git ; then
				echo "falling back to $defaultbranch branch for $module..."
				if ! git clone -b $defaultbranch ${basestring}/${module}.git ; then
					# If we cannot find $defaultbranch, then fall back to repository's defualt branch (catches situation where default branch is not master)
					echo "falling back to default branch for $module..."
					if ! git clone ${basestring}/${module}.git ; then
						# If we cannot find default branch at specifeid remote, fall back to OE git hub
						if [ "$gitroot != "openeyes ]; then
							echo "could not find $defaultbranch at $gitroot remote. Falling back to openeyes official repo"
							git clone -b $defaultbranch ${basestring/$gitroot/openeyes}/${module}.git
						fi
					fi
				fi
			fi
  else
    cd $module
	printf "\e[32m$module: \e[0m"
    git reset --hard
    git fetch --all
    git checkout tags/$branch 2>/dev/null
    if [ ! $? = 0 ]; then git checkout $branch 2>/dev/null; fi
	if [ ! $? = 0 ]; then echo "no branch $branch exists, switching to $defaultbranch"; git checkout $defaultbranch 2>/dev/null; fi

	## fast forward to latest head
	if [ ! "$nopull" = "1" ]; then
		echo "Pulling latest changes: "
		git pull
	fi

    cd ..
  fi
done

if [ "$resetconfig" = "1" ]; then
	echo "
WARNING: Resetting local config to defaults
"
	sudo rm -rf /var/www/protected/config/local/*.php
fi

# Now reset/relink various config files etc
if [ "$fix" = "1" ]; then  oe-fix $fixparams; fi

cd "$dir"

# Show summary of checkout
if [ ! "$nosummary" = "1" ]; then
	oe-which
	printf "\e[42m\e[97m  CHECKOUT COMPLETE  \e[0m \n"
fi

echo ""
