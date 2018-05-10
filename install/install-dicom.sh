#!/bin/bash

# Copy DICOM related files in place as required
sudo cp -f /vagrant/install/dicom-file-watcher.service /etc/systemd/system/
 # sudo cp -f /vagrant/install/dicom /etc/cron.d/ # shouldn't be needed
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
