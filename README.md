This repository should be the first/only one checked out when building a new OpenEyes development machine instance.

It contains/will contain the following files and directories:

README.md	This file
Vagrantfile	The Vagrant VM configuration
/behat		The behat configuration (if behat install has been run)
/install        The installation scripts and utility commands
/www		The code base once fully checked out (this is syncd to the virtual machine as /var/www)


HOW TO INSTALL OPENEYES (This instructions assume you are using OS X 10.6 or later)

1. Install Vagrant, VirtualBox (v4.3, v5 does not work) and Git command line tools on your system
2. Clone OpenEyes/oe_installer to a directory of your choice
3. Change to your repo directory and run: vagrant up
4. Once built, run vagrant ssh
5. From within the vagrant box, run the following commands:
sudo /vagrant/install/install-system.sh
sudo /vagrant/install/install-oe.sh

At this point you should have a fully working OpenEyes instance, reachable at localhost:8888 or 192.168.0.100

If you require behat, then from the VM run the following command:
sudo /vagrant/install/install-behat.sh

If you require dev tools (eg compiling sass changes, running phpunit), you need to run:
sudo /vagrant/install/install-devtools.sh

