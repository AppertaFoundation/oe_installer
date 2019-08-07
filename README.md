# RETIRED
This installer was retired after the OpenEyes v3.0 release. It is now recommended to use the [openeyes/docker project](https://github.com/openeyes/docker) or the Docker images available at the [appertaopeneyes docker hub repository](https://hub.docker.com/u/appertaopeneyes)

---
---

# OpenEyes Installation Guide

This repository should be the only one checked out when building a new OpenEyes machine instance.

**Disclaimer:**
> _OpenEyes is provided under a GNU Affero General Public License v3 license and all terms of that license apply ([https://www.gnu.org/licenses/agpl-3.0.html](https://www.gnu.org/licenses/agpl-3.0.html)).
> Use of the OpenEyes software or code is entirely at your own risk. Neither the OpenEyes Foundation, ABEHR Digital Ltd
> nor any other party accept any responsibility for loss or damage to any person, property or
> reputation as a result of using the software or code. No warranty is provided by any party, implied or
> otherwise. This software and code is not guaranteed safe to use in a clinical environment and you
> should make your own assessment on the suitability for such use._

**Index:**

  1. [Installing for Development or testing - using Vagrant and Virtual Box](#installing-for-development-using-vagrant-and-virtualbox)
    * [Installing development environment on Mac OS X 10.6 or later hosts](#installing-development-environment-on-mac-os-x-106-or-later-hosts)
    * [Installing development environmant on Windows 7 or later hosts](#installing-development-environment-on-windows-7-or-later-hosts)
    * [Additional development tools](#additional-development-tools)
  2. [SSH usage with github](#ssh-usage-with-github)
    * [Provisioning vagrant from private repos](#Provisioning-vagrant-from-private-repos)
    * [Using SSH on a live install, or switching from HTTPS to SSH](#Using-SSH-on-a-live-install-or-switching-from-HTTPS-to-SSH)
        * [Setup SSH key](#setup-ssh-key)
        * [Switch existing install to ssh](#Switch-existing-install-to-ssh)
        * [New (live) install with ssh](#New-live-install-with-ssh)
  3. [Installing for Live/Production use](#installing-for-live-use)
  4. [Management Tools](#management-tools)
  5. [Default connection and password info](#default-connection-and-password-info)
    * [Vagrant Virtual Machine Console](#vagrant-virtual-machine-console)
    * [MySQL](#mysql)

# Installing for Development or testing (using Vagrant and VirtualBox or Hyper-V)

The default development setup uses a Mac OSX (preferred) or Windows host, and runs a virtual server provided by VirtualBox with managed setup by Vagrant. Vagrant is not essential (see live use setup below) but offers a few advantages for developers, such as:

* Installs and configures a basic server for you
* Creates shared folders so code edited on the host is available directly on the virtual machine
* Maps the install directory on the host to /vagrant on the VM.
* [VirtualBox] creates a host entry named openeyes.vm. So the website will be available at http://openeyes.vm and MySQL at openeyes.vm:3306
  * Note that the site will also NATted to http://localhost:8888 as an alternative, and localhost:3333 for MySQL
* [Hyper-V] creates a host entry named openeyes.mshome.net. So the website is avialable at http://openeyes.mshome.net and MySQL is available at openeyes.mshome.net:3306


### Installing development environment on Mac OS X 10.6 or later hosts

**NOTE:** These instructions assume you are installing in to a directory called ~/openeyes on the host. You may change this path as you see fit.

#### Software required:

1. Vagrant: [https://www.vagrantup.com/downloads.html](https://www.vagrantup.com/downloads.html)
2. VirtualBox: [https://www.virtualbox.org/wiki/Downloads](https://www.virtualbox.org/wiki/Downloads)

#### Steps

1. Install Vagrant, VirtualBox and Git, following default install instructions from the above links.
2. From a terminal window, change to your home directory. Run: `cd ~`
3. Clone OpenEyes/oe_installer to a directory of your choice. Run: `git clone git@github.com:openeyes/oe_installer.git` 
    3.1 (note, you will need an SSH key configured first - see [Setup SSH Key](#setup-ssh-key). Alternativey you can use `git clone https://github.com/openeyes/oe_installer` and then switch to SSH later on
4. Create the virtual server from your project directory. Run: `cd ~/oe_installer && vagrant up`
5. It will take 5 - 10 minutes for the install to complete (depending on the speed of your internet connection)
6. At this point you should have a fully working OpenEyes server, reachable at http://localhost:8888
7. You can follow the sections below on [Additional development tools](#additional-development-tools) should you need them.



### Installing development environment on Windows 7 or later hosts
> **IMPORTANT:** For this installer to run, the vagrant up command must be issued from a command window with <u>elevated privileges</u> (admin command prompt). This is due to a limitation with VirtualBox not being able to create symbolic links to shared folders unless it has admin rights. Failure to do this will result in unpredictable consequences!

**NOTE:** These instructions assume you are installing in to a directory called `C:\oe_installer` on the host. You may change this path as you see fit. However, please note that Windows struggles with filenames longer than 275 characters on file shares, so keep this path below 30 characters. You can map a longer pathname to a drive letter using the subst command.

#### Software required:

1. Vagrant: [https://www.vagrantup.com/downloads.html](https://www.vagrantup.com/downloads.html)
2. VirtualBox: [https://www.virtualbox.org/wiki/Downloads](https://www.virtualbox.org/wiki/Downloads)
  * Or Microsoft Hyper-V

#### Steps
1. Install Vagrant, VirtualBox and Git, following default install instructions from the above links.
    1.1 When installing git, choose to use bash tools, not the default Windows command prompt
2. From an ADMINISTRATIVE command prompt window, change to your root directory.
    Run: `cd c:\`
3. Clone OpenEyes/oe_installer.
    Run: `git clone git@github.com:openeyes/oe_installer.git` 
    3.1 (note, you will need an SSH key configured first - see [Setup SSH Key](#setup-ssh-key). Alternativey you can use `git clone https://github.com/openeyes/oe_installer` and then switch to SSH later on
4. Change to the newly created oe_installer directory.
    Run: `cd oe_installer`
5. Create the virtual server from your project directory.
    Run: `vagrant up`
    5.1. If this is your first time running Vagrant, it may install some plugins first, then ask you to run again. In which case, run `vagrant up` again to continue
6. It will take 5 - 10 minutes for the install to complete (depending on the speed of your internet connection)
7. At this point you should have a fully working OpenEyes server, reachable at http://localhost:8888 (http://openeyes.mshome.net for HyperV)




# SSH usage with github
## Provisioning vagrant from private repos
If using the private repos, you will need to add your SSH key to your user account's .ssh folder *BEFORE* running `vagrant up`. On windows this is %HOMEPATH%\.ssh. On Mac and Linux this is ~/.ssh

Your SSH key should be named id_rsa or id_github, and it should be mapped to your gitgub user account (https://github.com/settings/keys).

*IMPORTANT* - Your SSH key must not have a password, otherwise it cannot be used in automated scripts

## Using SSH on a live install, or switching from HTTPS to SSH

By Default, the installer will first try to use SSH, then fall back to HTTPS to connect to github. When using private repos without SSH, this means that you will be prompted for your username and password when running git pull, push, etc. Also note that if you use 2-Factor Authentication then HTTPS requires a special password. Therefore it is strongly recommended to use SSH

# Installing for live use

This section assumes you are installing OpenEyes on a virtual machine, (other than one built by Vagrant), a cloud server (such as AWS) or a
physical server, and it is intended for non-development, and that your home directory is /home/me (you can modify accordingly).


1. Install Ubuntu 18.04 LTS. Minimal installation, no software packages (except ssh server)
    (You are expected to know the root password and the username/password for your server).
2. Log on as your normal user (presumed to be 'ubuntu' for this guide - amend as required)
3. Run: `sudo apt install git`
5. Clone the openeyes repository to /var/www/openeyes. Run: `cd /var/www && git clone https://github.com/openeyes/oe_installer.git`
6. Run `export OE_MODE="LIVE"`
7. Run: `/var/www/openeyes/protected/scripts/install-system.sh`
8. Run: `/var/www/openeyes/protected/scripts/install-oe.sh`

At this point you have a working server running on localhost (or your assigned IP address). You may wish to edit `/etc/apache2/sites-available/000-default.conf`
and set the ServerName directive to your chosen domain name (apache configuration knowledge required).

There is nothing to stop you from using this server configuration for development, by following the Default and Additional development tools sections above. However such use is outside our prescribed method for development.

# Management tools

Several additional terminal commands have been created for you in ``/usr/bin`, to aid development. These can be run from any directory within the Virtual Machine (e.g, run `vagrant ssh` first). You should <u>not</u> run these commands as sudo.

+ **oe-which**: tells you the current branch name for each of your code modules
+ **oe-checkout** <branch>: will go through each code module and try to checkout the requested branch or version (e.g., `oe-checkout v3.0`) - also runs oe-migrate to bring the newly installed database up to date
+ **oe-update**: will go through each code module and update (i.e. pull the latest) code
+ **oe-reset**: will drop the current database and re-install it (also runs oe-migrate to bring the newly installed database up to date)
+ **oe-migrate**: performs database migrations (normally used after a change to a newer code branch)
+ **oe-fix**: performs a number of actions to put the code and data into a consistent state, updates dependencies and imports eyedraw configuration.

For example, to get the latest build and all database changes, you will need to run: `oe-update`

**NOTE:** Most of these commands provide a number of optional switches. Use oe-<command> --help to see available options (e.g., `oe-checkout --help`)

# Default connection and password info

## Vagrant Virtual Machine Console
To login to the console of the vagrant VM, you'll need the following info.

#### Using vagrant ssh
This is the preferred method.
+ From a terminal / admin command prompt, change directory to your oe_installer directory
+ Run: `vagrant ssh`


#### Direct access to console in VirtualBox / HyperV
To login to the virtual Box machine directly use the following details:

+ Username: vagrant
+ Password: vagrant



## MySQL
Default access details for the MySQL Database are:

```
Port: 3306
Username: openeyes
Password: openeyes
```
