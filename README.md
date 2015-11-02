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


RUNNING BEHAT BROWSER TESTS

If you wish to perform browser tests, then follow these steps:
1. log on to the main VM window as vagrant/vagrant
2. From that terminal, run startx
3. From another terminal window (eg from vagrant ssh), run /var/www/behat/start-selenium-server.sh
4. From a third terminal window (eg from vagrant ssh), cd to /var/www/behat and run bin/behat --profile=n

where n is either chrome or firefox



INSTALLING OPENEYES ON A WINDOWS PLATFORM

Please use version 4.3.* of VirtualBox. Version 5 is known to have problems with shared folders.

These install scripts should also run on a windows platform.
If you receive an error about 'file cannot be created' (operation not permitted) on a very long filename, then chances are your installation directory along with the long filename has exceeded the 260 character limit imposed by certain parts of the Windows OS. Try changing your installion directory to something less than 30 charcters, eg C:\openeys
 
