# OpenEyes Installation Guide

This repository should be the only one checked out when building a new OpenEyes machine instance.

**Disclaimer:**
> _OpenEyes is provided under a GPL v3 license and all terms of that license apply ([https://www.gnu.org/licenses/gpl-3.0.html](https://www.gnu.org/licenses/gpl-3.0.html)). 
> Use of the OpenEyes software or code is entirely at your own risk. Neither the OpenEyes Foundation, ACROSS Health Ltd 
> nor Moorfields Eye Hospital NHS Foundation Trust accept any responsibility for loss or damage to any person, property or
> reputation as a result of using the software or code. No warranty is provided by any party, implied or
> otherwise. This software and code is not guaranteed safe to use in a clinical environment and you
> should make your own assessment on the suitability for such use._

**Index:**

  1. [Installing for Development - using Vagrant and Virtual Box](#installing-for-development-using-vagrant-and-virtualbox)
    * [Installing for Mac OS X 10.6 or later](#installing-for-mac-os-x-106-or-later)
    * [Installing for Windows 7 or later](#installing-for-windows-7-or-later)
    * [Default development tools](#default-development-tools)
    * [Additional development tools](#additional-development-tools)
  2. [Installing for Live/Production use](#installing-for-live-use)

## Installing for Development (using Vagrant and VirtualBox)

The default development setup uses a Mac OSX (preferred) or Windows host, and runs a virtual server provided by VirtualBox and 
managed/setup by Vagrant. Vagrant is not essential (see live use setup below) but offers a few advantages for developers, such as:

+ Installs and configures a basic server for you
+ Creates shared folders so code edited on the host is available directly on the virtual machine
+ Maps the install directory on the host to /vagrant on the VM.
+ Forwards port 8888 on the host to port 80 on the VM (so the host can access the VM website at localhost:8888)
+ Forwards port 3333 on the host to port 3306 on the VM (for host access to the VM's MySQL database)


#### Installing for Mac OS X 10.6 or later

**NOTE:** These instructions assume you are installing in to a directory called ~/openeyes on the host. You may change this path as you see fit.

Software required:

1. Vagrant: [https://www.vagrantup.com/downloads.html](https://www.vagrantup.com/downloads.html)
2. VirtualBox: [https://www.virtualbox.org/wiki/Downloads](https://www.virtualbox.org/wiki/Downloads)
3. Git: [https://git-scm.com/downloads](https://git-scm.com/downloads)


```
1. Install Vagrant, VirtualBox and Git as listed above.
2. From a terminal window, change to your home directory. Run: cd ~
3. Clone OpenEyes/oe_installer to a directory of your choice. Run: git clone https://github.com/openeyes/oe_installer.git ~/openeyes
4. Create the virtual server from your project directory. Run: cd ~/openeyes && vagrant up
5. Once built (approx. 5 minutes), run: vagrant ssh
6. You should now be connected to the terminal of your vagrant box (command line will show vagrant@vagrant-ubuntu-trusty-64~>
7. Run the following commands (on the terminal of the vagrant box):
  i. sudo /vagrant/install/install-system.sh
  ii. sudo /vagrant/install/install-oe.sh
```

At this point you should have a fully working OpenEyes server, reachable at http://localhost:8888 or http://192.168.90.100
You can follow the sections below on [Default development tools](#default-development-tools) and [Additional development tools](#additional-development-tools) should you need them.

#### Installing for Windows 7 or later
**IMPORTANT:** For this installer to run, the vagrant up command must be issued from a command window with elevated priviledges (admin command prompt). This is due to a limitation with VirtualBox not being able to create symbolic links to shared folders unless it has admin rights. Failure to do this will result in unpredictable consequences!

**NOTE:** These instructions assume you are installing in to a directory called C:\openeyes on the host. You may change this 
path as you see fit. However, please note that Windows struggles with filenames longer than 275 characters on file shares, so
keep this path below 30 characters. You can map a longer pathname to a drive letter using the subst command.

Software required:

1. Vagrant: [https://www.vagrantup.com/downloads.html](https://www.vagrantup.com/downloads.html)
2. VirtualBox: [https://www.virtualbox.org/wiki/Downloads](https://www.virtualbox.org/wiki/Downloads)
3. Git: [https://git-scm.com/downloads](https://git-scm.com/downloads)

```
1. Install Vagrant, VirtualBox and Git as listed above.
2. From an administrative terminal window, change to your root directory. Run: cd c:\
3. Clone OpenEyes/oe_installer to a directory of your choice. Run: git clone https://github.com/openeyes/oe_installer.git c:\openeyes
4. Change to the newly created openeyes directory. Run: cd openeyes
5. Create the virtual server from your project directory. Run: vagrant up
6. Once built (approx. 5 minutes), run: vagrant ssh
7. From within the vagrant box, run the following commands:
  i. sudo /vagrant/install/install-system.sh
  ii. sudo /vagrant/install/install-oe.sh
```

At this point you should have a fully working OpenEyes server, reachable at localhost:8888 or 192.168.90.100.
You can follow the sections below on [Default development tools](#default-development-tools) and [Additional development tools](#additional-development-tools) should you need them.

##### Additional concerns for Windows users

File shares under VirtualBox are pretty slow in version 5. You can try swapping to Version 4.3.x (any version between these two does not seem to work).
If you find the command prompt very slow on the virtual machine, you can remove the coloured command prompt (which displays the current branch if you
are in a git repository directory). To remove this, edit the /etc/bash.bashrc file and add the second PS1 line (shown below) at the end:

```
PS1="\e[0m\n\e[44m\e[97m \u@\h \e[41m\$(gitbranch)\e[0m\n\w>"
PS1="u@\h\n\w>"
```

Note that changes only take effect on subsequent logins.

### Default development tools

Several additional terminal commands have been created for you in /usr/bin, to aid development. These can be run from any directory within the Vagrant Virtual Machine, but should be run as root (or sudo - e.g., sudo oe-xxxxx).

+ oe-which: tells you the current branch name for each of your code modules
+ oe-checkout <branch>: will go through each code module and try to checkout the requested branch or version (e.g., sudo oe-checkout v1.13)
+ oe-update: will go through each code module and update (i.e. pull the latest) code
+ oe-reset: will drop the current database and re-install it (also runs oe-migrate to bring the newly installed database up to date)
+ oe-migrate: performs database migrations (normally used after a change to a newer code branch)
+ oe-fix: fixes some links to yii framework and vendor libraries when switching between pre and post v1.12 releases

For example, to get the latest build and all database changes, you will need to run: sudo oe-update

### Additional development tools

If you require behat (running the selenium UI tests), then from the VM run the following command:

```
  sudo /vagrant/install/install-behat.sh
```

If you require PHP unit tests, or modify any SASS or CSS or Javascript, then you will need the additional dev tools. From the 
VM run the following command:

```
  sudo /vagrant/install/install-devtools.sh
```


If you wish to run these extra tools via Jenkins, you can also install the pre-configured Jenkins suite. From the 
VM run the following command:

```
  sudo /vagrant/install/install-jenkins.sh
```


## Installing for live use

This section assumes you are installing OpenEyes on a virtual machine, (other than one built by Vagrant), a cloud server (such as AWS) or a 
physical server, and it is intended for non-development, and that your home directory is /home/me (you can modify accordingly).

```
Install Ubuntu 14.04 LTS. Minimal installation, no software packages (except ssh server)
You are expected to know the root password and the username/password of a normal user.
Log on as your normal user (presumed to be 'me' for this guide - amend as required)
Run: sudo apt-get install git
Change to your home directory. Run: cd /home/me 
Clone the oe_installer repository to your home directory. Run: git clone https://github.com/openeyes/oe_installer.git /home/me/openeyes
Run: sudo /vagrant/install/install-system.sh
Run: sudo /vagrant/install/install-oe.sh --live
```

At this point you have a working server running on localhost (or your assigned IP address). You may wish to edit `/etc/apache2/sites-available/000-default.conf`
and set the ServerName directive to your chosen domain name (apache configuration knowledge required).
 
There is nothing to stop you from using this server configuration for development, and following the Default and Additional development tools sections above. However such use is outside our prescribed method for development.


## Upgrading a live server to v1.12

V1.12 represents a significant change in some code layout from previous versions. As a result, the safest upgrade path is to completely replace
all code with a newly checked out site. Each live deployment will have their own minor differences, so only outline directions are provided here.

```
+ Back-up the database 
+ Back up the /var/www/openeyes directories
+ Clone the oe_installer repository to a directory
+ cd to the cloned oe_installer directory
+ Run: sudo upgrade-oe.sh 
+ Edit the file /etc/openeyes/db.conf with your database credentials
+ Edit the /var/www/openeyes/protected/config/local/common.php file with values from your backed up copy
```
