#!/bin/bash

cd /home/docker/discoDocker/docker

echo 'Excluir maquinas ... fase 1'
sudo docker ps -a | awk '{ print $14 }' | xargs sudo docker rm $0
echo 'Excluir maquinas ... fase 2'
sudo docker ps -a | awk '{ print $15 }' | xargs sudo docker rm $0

echo 'Monta os containers...'
sudo docker-compose up -d --build

#echo 'Atualiza a maquina virtual'
#sudo docker exec -it php-apache apt update
#sudo docker exec -it php-apache apt -y upgrade

echo 'Desabilita o site default'
#sudo docker exec -it php-apache sh -c "a2dissite 000-default"

#echo 'Habilita o site de desenvolvimento'
sudo docker exec -it php-apache sh -c "a2ensite meusite.local"

echo 'Reinicia o apache2'
sudo docker exec -it php-apache sh -c "service apache2 reload"

#echo 'Reinicia o memcached'
#sudo docker exec -it memcached service memcached restart

echo 'Modulos habilitados...'
sudo docker exec -it php-apache php -m | grep -E 'memcache|oci8|uploadprogress|mcrypt'

#echo 'Acesso a maquina virtual'
#sudo docker exec -it php-apache bash

