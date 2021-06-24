#!/bin/bash

echo 'Step 1- Update your Ubuntu Linux Box.'
sudo apt-get update -y
sudo apt-get install -y curl uidmap

echo 'Step 2 –  Download and run a get-docker.sh script to install docker.'
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

echo 'Test version'
curl -fsSL https://get.docker.com -o test-docker.sh
sudo sh test-docker.sh

#echo 'Step 3- Add a user to the Docker group.'
#sudo apt-get install -y docker-ce-rootless-extras
#dockerd-rootless-setuptool.sh install

echo 'Step 4 – Check Docker Version'
docker --version
