#!/bin/sh
: ${InstallerVer=0.1}
: ${DIALOG=dialog} # change to Xdialog to run in X-Windows session

# !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
# !! Alpha Graphical instaler - does not yet work suitably 
# !! - use existing install-system.sh and install-oe.sh for now
# !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

# Verify we are running as root
if ! [ $(id -u) = 0 ]; then
   echo "This script must be run as root"
   exit 1
fi

#see if apt is available, else use apt-get
: ${APT=apt-get}
if hash apt 2>/dev/null; then APT="apt"; fi

# Check that the dialog package is available and install it if not
command -v dialog >/dev/null 2>&1 || { echo >&2 "Installing dialog package for menu. Please wait..."; sudo $APT install dialog -y; }

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
accept=0

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
    --accept) accept=1;
    		## Accepts the disclaimer, without pausing the installation
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
    echo "  -ssh		 : Use SSH protocol  - default is https"
    echo ""
    echo "  --accept	 : Indicate acceptance of the disclaimer without prompting"
    echo ""
    exit 1
fi

#!/bin/bash

# while-menu-dialog: a menu driven system information program

DIALOG_CANCEL=1
DIALOG_ESC=255
HEIGHT=0
WIDTH=0

display_result() {
  $DIALOG --title "$1" \
    --no-collapse \
    --msgbox "$result" 0 0
}

display_result_program() {
    $DIALOG --title "$1" \
      --prgbox "$result" 0 0
}

while true; do
  exec 3>&1
  selection=$($DIALOG \
    --backtitle "OpenEyes Installer ($InstallerVer)" \
    --title "Main Menu" \
    --clear \
    --cancel-label "Exit" \
    --menu "Please select:" $HEIGHT $WIDTH 6 \
    "1" "Install OpenEyes" \
    "2" "Update OpenEyes" \
    "3" "Switch to a different branch/tag" \
    "7" "Information" \
    "8" "Update this installer script" \
    2>&1 1>&3)
  exit_status=$?
  exec 3>&-
  case $exit_status in
    $DIALOG_CANCEL)
      clear
      echo "Program terminated."
      exit
      ;;
    $DIALOG_ESC)
      clear
      echo "Program aborted." >&2
      exit 1
      ;;
  esac
  case $selection in
    0 )
      clear
      echo "Program terminated."
      ;;
    2 )
      result=$(echo $selection was chosen)
      display_result "Not Implemented"
      ;;
    7 )
    exec 3>&1
    selection=$($DIALOG \
      --backtitle "OpenEyes Installer ($InstallerVer)" \
      --title "Main Menu" \
      --clear \
      --cancel-label "Back" \
      --menu "Please select:" $HEIGHT $WIDTH 4 \
      "1" "Display System Information" \
      "2" "Display Disk Space" \
      "3" "Display Home Space Utilization" \
      "4" "Display currently installed OpenEyes Versions" \
      2>&1 1>&3)
    exit_status=$?
    exec 3>&-
    case $exit_status in
      $DIALOG_CANCEL)
        #return
        ;;
      $DIALOG_ESC)
        #return
        ;;
    esac
    case $selection in
      0 )
        ;;
      1 )
        result=$(echo "Hostname: $HOSTNAME"; uptime)
        display_result "System Information"
        ;;
      2 )
        result=$(df -h)
        display_result "Disk Space"
        ;;
      3 )
        if [[ $(id -u) -eq 0 ]]; then
          result=$(du -sh /home/* 2> /dev/null)
          display_result "Home Space Utilization (All Users)"
        else
          result=$(du -sh $HOME 2> /dev/null)
          display_result "Home Space Utilization ($USER)"
        fi
        ;;
      4 )
        result="oe-which"
        display_result_program "OE Modules"
        ;;
    esac
      ;;
    * )
      result=$(echo $selection was chosen)
      display_result "Not Implemented"
      ;;
  esac
done
