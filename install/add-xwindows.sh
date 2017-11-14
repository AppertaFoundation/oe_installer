sudo add-apt-repository ppa:webupd8team/atom -y
sudo apt update


sudo apt-get install --no-install-recommends xubuntu-desktop -y


sudo chown -R vagrant:vagrant /home/vagrant
sudo find /home/vagrant/ -type d -exec chmod 750 {} +
sudo find /home/vagrant/ -type f -exec chmod 640 {} +

sudo gpasswd -a vagrant www-data
sudo gpasswd -a vagrant root
sudo gpasswd -a ubuntu www-data
sudo gpasswd -a ubuntu root

sudo apt install chromium-browser atom -y

# Remove some unessessary bloat
sudo apt-get remove gnome-mines gnome-sudoku pidgin pidgin-otr xchat thunderbird exo-utils firefox transmission-gtk gmusicbrowser -y
sudo apt-get autoremove -y

echo "You must set a password for the Ubuntu user..."
sudo passwd ubuntu
