#!/bin/bash

# Copy DICOM related files in place as required
if [[ `lsb_release -rs` == "14.04" ]]; then
    # Ubuntu 14.04 uses upstart / init.d
    sudo cp -f /vagrant/install/dicom-file-watcher.conf /etc/init/
else
    # Ubuntu 14.10 and higher uses systemd
    sudo cp -f /vagrant/install/dicom-file-watcher.service /etc/systemd/system/
fi

sudo cp -f /vagrant/install/dicom /etc/cron.d/
sudo chmod 0644 /etc/cron.d/dicom

sudo cp -f /vagrant/install/run-dicom-service.sh /usr/local/bin
sudo chmod +x /usr/local/bin/run-dicom-service.sh

sudo id -u iolmaster &>/dev/null || sudo useradd iolmaster -s /bin/false -m
sudo mkdir -p /home/iolmaster/test
sudo mkdir -p /home/iolmaster/incoming
sudo chown iolmaster:www-data /home/iolmaster/*
sudo chmod 775 /home/iolmaster/*

sudo systemctl daemon-reload
sudo systemctl enable dicom-file-watcher

sudo systemctl start dicom-file-watcher
