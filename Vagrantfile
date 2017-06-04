Vagrant.configure("2") do |config|

  config.vm.box = "ubuntu/trusty64"

  config.vm.provision :shell, path: "bin/vagrant.sh"

  config.vm.hostname = "thegreenhouse"

  config.vm.network :forwarded_port, host: 4567, guest: 80
  config.vm.network :forwarded_port, host: 8181, guest: 8181
  config.vm.network :private_network, ip: "172.31.1.2"

  config.vm.provider "virtualbox" do |v|
    v.memory = 1024
    v.cpus = 2
  end

end