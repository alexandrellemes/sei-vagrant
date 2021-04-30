#!/usr/bin/env bash

set -e

# Instalação de dependêcias do projeto
yum -y install php-dev libaio systemtap-sdt-devel

# Instala?o dos pacotes Basic e SDK Instant Client
#yum install -y /tmp/oracle-instantclient12.2-basic-12.2.0.1.0-1.x86_64.rpm
#yum install -y /tmp/oracle-instantclient12.2-devel-12.2.0.1.0-1.x86_64.rpm
#yum install -y /tmp/oracle-instantclient12.2-sqlplus-12.2.0.1.0-1.x86_64.rpm

# Install the Oracle Instant Client
mkdir /var/oracle -p

#ADD oracle/instantclient-basiclite-linux.x64-12.2.0.1.0.zip /var/oracle
cp /tmp/instantclient-basic-linux.x64-12.2.0.1.0.zip /var/oracle
cp /tmp/instantclient-sdk-linux.x64-12.2.0.1.0.zip /var/oracle
cp /tmp/instantclient-sqlplus-linux.x64-12.2.0.1.0.zip /var/oracle

unzip /var/oracle/instantclient-basic-linux.x64-12.2.0.1.0.zip -d /var/oracle
unzip /var/oracle/instantclient-sdk-linux.x64-12.2.0.1.0.zip -d /var/oracle
unzip /var/oracle/instantclient-sqlplus-linux.x64-12.2.0.1.0.zip -d /var/oracle

#Links simbólicos
ln -s /var/oracle/instantclient_12_2 /usr/local/instantclient
ln -s /var/oracle/instantclient_12_2 /var/oracle/instantclient

ln -s /var/oracle/instantclient_12_2/libclntsh.so.12.1 /var/oracle/instantclient_12_2/libclntsh.so
ln -s /var/oracle/instantclient_12_2/libocci.so.12.1 /var/oracle/instantclient_12_2/libocci.so
ln -s /var/oracle/instantclient_12_2/sqlplus /usr/bin/sqlplus

echo /var/oracle/instantclient_12_2 > /etc/ld.so.conf.d/oracle-instantclient
cd /var/oracle && ldconfig

chown -R root:apache /var/oracle

pecl channel-update pecl.php.net

# Install the OCI8 PHP extension
#Use 'pecl install oci8' to install for PHP 8.
#
#Use 'pecl install oci8-2.2.0' to install for PHP 7.
#
#Use 'pecl install oci8-2.0.12' to install for PHP 5.2 - PHP 5.6.
#
#Use 'pecl install oci8-1.4.10' to install for PHP 4.3.9 - PHP 5.1.
#RUN echo 'shared,instantclient,/var/oracle/instantclient_12_2' | pecl install -f oci8
export PHP_DTRACE=yes
echo 'shared,instantclient,/var/oracle/instantclient_12_2' | pecl install -f oci8-2.2.0
unset PHP_DTRACE

# Set up the Oracle environment variables
export ORACLE_HOME=/var/oracle/instantclient_12_2
export TNS_ADMIN=/var/oracle/instantclient_12_2
export LD_LIBRARY_PATH=/var/oracle/instantclient_12_2
export PATH=/var/oracle/instantclient_12_2/bin:$PATH
export C_INCLUDE_PATH=/var/oracle/instantclient_12_2/sdk/include

echo | tee /etc/profile.d/client.sh << EndOfMessage
export LD_LIBRARY_PATH=/var/oracle/instantclient_12_2
export ORACLE_HOME=/var/oracle/instantclient_12_2
LD_LIBRARY_PATH=/var/oracle/instantclient_12_2:$LD_LIBRARY_PATH
EndOfMessage

# Configuração de diretório do Oracle Instant Client no dynamic linker/loader
#echo /usr/lib/oracle/12.2/client64/lib > /etc/ld.so.conf.d/oracle-instantclient.conf
#
#mkdir /usr/lib/oracle/12.2/client64/network/admin -p
#
#export ORACLE_HOME=/usr/lib/oracle/12.2/client64
#export LD_LIBRARY_PATH=$ORACLE_HOME/lib
##export C_INCLUDE_PATH=$ORACLE_HOME/sdk/include
#export TNS_ADMIN=$ORACLE_HOME/network/admin
#export PATH=$PATH:$ORACLE_HOME/bin

#echo | tee /etc/profile.d/client.sh << EndOfMessage
#export ORACLE_HOME=/usr/lib/oracle/12.2/client64
#export LD_LIBRARY_PATH=$ORACLE_HOME/lib
#export TNS_ADMIN=$ORACLE_HOME/network/admin
#export PATH=$PATH:$ORACLE_HOME/bin
#EndOfMessage

echo '/etc/profile.d/client.sh'
cat /etc/profile.d/client.sh

echo 'executa /etc/profile.d/client.sh'
sh /etc/profile.d/client.sh

#ldconfig

# Instalação dos OCI8 extension
#pecl channel-update pecl.php.net

#export PHP_DTRACE=yes
#echo 'shared,instantclient,/usr/lib/oracle/12.2/client64' | pecl install -f oci8-2.2.0
#unset PHP_DTRACE

# Habilitação da extensão do Oracle 
echo "extension=oci8.so" > /etc/php.d/oci8.ini

