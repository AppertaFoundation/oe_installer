# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.require_version ">= 2.0"

PLUGINS = %w(vagrant-auto_network vagrant-hostsupdater vagrant-vbguest vagrant-faster)

PLUGINS.reject! { |plugin| Vagrant.has_plugin? plugin }



module OS
    def OS.windows?
        (/cygwin|mswin|mingw|bccwin|wince|emx/ =~ RUBY_PLATFORM) != nil
    end

    def OS.mac?
        (/darwin/ =~ RUBY_PLATFORM) != nil
    end

    def OS.unix?
        !OS.windows?
    end

    def OS.linux?
        OS.unix? and not OS.mac?
    end
end

unless PLUGINS.empty?
  print "The following plugins will be installed: #{PLUGINS.join ", "} "
#  unless ['no', 'n'].include? $stdin.gets.strip.downcase
    PLUGINS.each do |plugin|
	  # Exclude windows only plugins from unix hosts
	  if OS.unix? and "#{plugin}" == "vagrant-winnfsd"
	    break;
	  end
      system("vagrant plugin install #{plugin}")
      puts
    end
#  end

   puts "Please run again"
   exit 1
end


AutoNetwork.default_pool = "172.16.0.0/24"

$script = <<SCRIPT
cd /vagrant/install
installparams="-f -d --accept"
# copy ssh key(s) if exists
# NOTE: Expects key to be called id_rsa. If using a custom name ssh file, also provide .ssh/config with "IdentityFile ~/.ssh/<cusom shh name>"
if [ "$(ls -A .ssh/id*)" ]; then
    sudo -H -u vagrant bash -c 'cp .ssh/* ~/.ssh 2>/dev/null || :'
    installparams="$installparams -ssh"
    if [ ! -f "/home/vagrant/.ssh/config" ]; then
        sudo -H -u vagrant bash -c 'echo "IdentityFile ~/.ssh/id_rsa" > ~/.ssh/config'
    fi
	chmod 700 /home/vagrant/.ssh/id*
	sudo -H -u vagrant bash -c '$(ssh-agent)  2>/dev/null'
	# attempt ssh authentication and store key signature
	sudo -H -u vagrant bash -c 'ssh -oStrictHostKeyChecking=no git@github.com -T'
fi
bash install-system.sh
sudo -H -u vagrant INSTALL_PARAMS="$installparams" bash -c 'echo "Install will use $INSTALL_PARAMS"'
sudo -H -u vagrant INSTALL_PARAMS="$installparams" bash -c '/vagrant/install/install-oe.sh $INSTALL_PARAMS'
SCRIPT

Vagrant.configure(2) do |config|
  config.vm.box = "generic/ubuntu1604"
  config.vm.box_check_update = false

  config.vm.hostname = "openeyes.vm"


  config.vm.network :forwarded_port, host: 8888, guest: 80
  config.vm.network :forwarded_port, host: 3333, guest: 3306
  config.vm.network "private_network", :auto_network => true

	# Setup synced folders - MacOS uses nfs and shares www to host. Windows uses VirtualBox default and www foler lives internally (use add-samba-share.sh to share www folder to Windows host)
	if OS.unix?
		config.vm.synced_folder ".", "/vagrant", type: 'nfs'
		config.vm.synced_folder "./www/", "/var/www/", id: "vagrant-root", create: true, type: 'nfs'

	elsif OS.windows?
        config.vm.synced_folder ".", "/vagrant"
		config.vm.synced_folder "./www", "/var/www", create: true
    end

  # Prefer VMWare fusion before VirtualBox
  config.vm.provider "vmware_fusion"
  config.vm.provider "virtualbox"

	# Give VM 1/4 system memory or 768MB, whichever is greater (not used in VirtualBox as that used vagrant-faster plugin)
	if OS.mac?
		# sysctl returns Bytes and we need to convert to MB
		mem = `sysctl -n hw.memsize`.to_i / 1024
	elsif OS.linux?
		# meminfo shows KB and we need to convert to MB
		mem = `grep 'MemTotal' /proc/meminfo | sed -e 's/MemTotal://' -e 's/ kB//'`.to_i
	elsif OS.windows?
		# Windows code via https://github.com/rdsubhas/vagrant-faster
		mem = `wmic computersystem Get TotalPhysicalMemory`.split[1].to_i / 1024
	end

	mem = mem / 1024 / 4

	if mem < 768
		mem = 768
	elsif mem > 2028
		mem = 2048
	end


  # VirtualBox
  config.vm.provider "virtualbox" do |v|
    v.gui = true
	v.customize [ "guestproperty", "set", :id, "/VirtualBox/GuestAdd/VBoxService/--timesync-set-threshold", 10000 ]
    v.customize ["modifyvm", :id, "--vram", "56"]
    v.customize ["modifyvm", :id, "--accelerate2dvideo", "on"]
    v.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/vagrant-root", "1"]
    v.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/var/www/", "1"]
    v.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/vagrant/", "1"]
  end

  # VMWare Fusion
  config.vm.provider "vmware_fusion" do |v, override|
    override.vm.box = "puppetlabs/ubuntu-14.04-64-nocm"
    v.vmx["displayname"] = "Openeyes"
    v.vmx["memsize"] = mem.to_s
    v.vmx["numvcpus"] = "1"
  end

  # Hyper-V
  config.vm.provider "hyperv" do |h, override|
	#override.vm.box = "generic/ubuntu1604"

	# manual ip
	override.vm.provision "shell",
      run: "always",
      inline: "sudo ifconfig eth0 172.16.0.2 netmask 255.255.255.0 up"

	#override.vm.provision "shell",
    #  run: "always",
    #  inline: "sudo route add default gw 172.16.0.1"

	h.vmname = "OpenEyes"
	h.cpus = 2
	h.memory = 768
	h.maxmemory = mem
	h.ip_address_timeout = 200
    h.vm_integration_services = {
      guest_service_interface: true,
      heartbeat: true,
      key_value_pair_exchange: true,
      shutdown: true,
      time_synchronization: true,
      vss: true
    }
    h.auto_start_action = "Nothing"
    h.auto_stop_action = "ShutDown"
  end

  config.vm.provision "shell", inline: $script, keep_color: true

  config.hostsupdater.remove_on_suspend = true

end
