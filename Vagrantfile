Vagrant.configure("2") do |config|
    # vagrant box
    config.vm.box = "vagrant-ubuntu64"

    # Access virtual machine http://localhost from local http://localhost:8080
    config.vm.network "forwarded_port", guest: 80, host: 8080

    # synced folders
    config.vm.synced_folder "~/Developer", "/home/vagrant/code"

    # nginx site serve
    config.vm.provision "shell",
        inline: "bash /vagrant/scripts/serve.sh \"mobilesinch.app\" \"/home/vagrant/code/mobilesinch/public\" \"80\""
end