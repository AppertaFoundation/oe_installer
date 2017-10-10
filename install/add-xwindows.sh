sudo add-apt-repository ppa:webupd8team/atom -y
sudo apt update


sudo apt-get install --no-install-recommends xubuntu-desktop -y



sudo chown -R vagrant:vagrant .
sudo find /home/vagrant/ -type d -exec chmod 750 {} +
sudo find /home/vagrant/ -type f -exec chmod 640 {} +

sudo gpasswd -a ubuntu www-data
sudo gpasswd -a ubuntu root

sudo apt install chromium-browser atom -y

echo "You must set a password for the Ubuntu user..."
sudo passwd ubuntu
