This repository should be the first/only one checked out when building a new OpenEyes development machine instance.

It contains/will contain the following files and directories:

/code		The code base once fully checked out (this is syncd to the virtual machine as /var/www/openeyes)
/install	The installation scripts (this directory can be removed after installation)
Vagrantfile	The initial vagrant machine definition



HOW TO INSTALL OPENEYES (This instructions assume you are using OS X 10.6 or later)

1. Install Vagrant, VirtualBox (v4.3, v5 does not work) and Git command line tools on your system (eg openeyes)
2. Clone OpenEyes/installation to a directory of your choice
3. Change to your repo directory and run: vagrant up
4. Once built, run vagrant ssh
5. From within the vagrant box, run the following commands:
sudo /vagrant/install/install.sh

6. The install script performs the following steps:
Creates a swap file on the VM
Installs base system software
Clones the OpenEyes repositories to your /code subdirectory
Downloads and installs the sample mysql database

At this point you should have a fully working OpenEyes instance, reachable at localhost:8888

If you require further dev tools (eg for compiling sass changes, running phpunit or Jenkins), you need to run: sudo /vagrant/install/install-devtools.sh

