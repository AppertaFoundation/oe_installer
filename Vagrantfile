# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.box = "precise64"
  config.vm.box_url = "http://files.vagrantup.com/precise64.box"

  config.vm.network :forwarded_port, host: ENV["OE_CUSTOM_HTTP_PORT"] || 8888, guest: 80
  config.vm.network :forwarded_port, host: ENV["OE_CUSTOM_SQL_PORT"] || 3333, guest: 3306
  config.vm.network "private_network", ip: ENV["OE_CUSTOM_IP"] || "192.168.50.4"
  config.vm.synced_folder "./", "/var/www", id: "vagrant-root", :mount_options => ["dmode=777,fmode=777"]

  config.vm.provider "virtualbox" do |v|
    v.customize ["modifyvm", :id, "--memory", 1024]
  end

  config.vm.provision :puppet do |puppet|
    puppet.manifests_path = "puppet"
    puppet.options = "--verbose --debug"
  end
end
