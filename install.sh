#!/bin/bash

# SELinux Permissive
sudo setenforce 0

# set timezone JST
sudo timedatectl set-timezone America/Sao_Paulo

# EPEL
sudo yum install -y epel-release
sudo yum install -y vim git

# install docker
sudo yum remove docker \
    docker-client \
    docker-client-latest \
    docker-common \
    docker-latest \
    docker-latest-logrotate \
    docker-logrotate \
    docker-selinux \
    docker-engine-selinux \
    docker-engine

sudo yum install -y yum-utils \
    device-mapper-persistent-data \
    lvm2

sudo yum-config-manager \
    --add-repo \
    https://download.docker.com/linux/centos/docker-ce.repo

sudo yum install -y docker-ce

# install docker-compose
sudo mkdir -p /opt/bin/
sudo curl -L https://github.com/docker/compose/releases/download/1.21.2/docker-compose-$(uname -s)-$(uname -m) > ./docker-compose
sudo mv docker-compose /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# vagrant user add docker group
sudo gpasswd -a vagrant docker

# docker running
sudo systemctl enable docker
sudo systemctl start docker

echo "End install..."
