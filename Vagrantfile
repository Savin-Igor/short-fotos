Vagrant.configure("2") do |config|

    config.vm.box = "relativkreativ/ubuntu-18-minimal"
    config.vm.box_check_update  = false

    config.vm.hostname = "book"
    config.vm.define "book"

    config.vm.network "private_network", ip: "192.168.33.10"
    config.vm.network  "forwarded_port", guest: 80, host: 9876, auto_correct: true

	# Mount project files
    config.vm.synced_folder "src/", "/var/www/html", :mount_options => ["dmode=777", "fmode=777"]

    # Configure VirtualBox params
    config.vm.provider "vmware_desktop|virtualbox" do |vb|
        vb.memory   = 2048
        vb.cpus     = 2
        vb.gui		= false
    end

    # Optional NFS. Make sure to remove other synced_folder line too
    #config.vm.synced_folder ".", "/var/www/html", :nfs => { :mount_options => ["dmode=777","fmode=777"] }

end
