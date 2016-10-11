# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.require_version ">= 1.5"

PLUGINS = %w(vagrant-auto_network vagrant-hostsupdater)

PLUGINS.reject! { |plugin| Vagrant.has_plugin? plugin }

unless PLUGINS.empty?
  print "The following plugins will be installed: #{PLUGINS.join ", "} continue? [Y/n]: "
  unless ['no', 'n'].include? $stdin.gets.strip.downcase
    PLUGINS.each do |plugin|
      system("vagrant plugin install #{plugin}")
      puts
    end
  end
  puts "Please run again"
  exit 1
end

AutoNetwork.default_pool = "172.16.0.0/24"

Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.box_check_update = true

  config.vm.hostname = "openeyes.vm"

  config.vm.network :forwarded_port, host: 8888, guest: 80
  config.vm.network :forwarded_port, host: 3333, guest: 3306
  config.vm.network "private_network", :auto_network => true
  config.vm.synced_folder "./www/", "/var/www/", id: "vagrant-root", type: 'nfs', create: true

  # Prefer VMware Fusion before VirtualBox
  config.vm.provider "vmware_fusion"
  config.vm.provider "virtualbox"

  # VirtualBox
  config.vm.provider "virtualbox" do |v|
    v.memory = "768"
    v.gui = true
  end

  # VMWare Fusion
  config.vm.provider "vmware_fusion" do |v, override|
    override.vm.box = "puppetlabs/ubuntu-14.04-64-nocm"
    v.vmx["displayname"] = "Openeyes"
    v.vmx["memsize"] = "1024"
    v.vmx["numvcpus"] = "1"
  end

  config.hostsupdater.remove_on_suspend = true

end
