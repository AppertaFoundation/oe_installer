#!/bin/bash

echo "Configuring SAMBA share 'openeyes'. NOTE: This script should only be run once, otherwise it will mess up your /etc/samba/smb.conf"

sudo apt update
sudo apt install samba -y

sudo smbpasswd -a vagrant

echo "
[openeyes]
path = /var/www/openeyes
valid users = vagrant
read only = no
" >> /etc/samba/smb.conf
