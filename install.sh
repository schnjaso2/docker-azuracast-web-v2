#!/usr/bin/env bash

while [[ "$1" =~ ^- && ! "$1" == "--" ]]; do case $1 in
    --dev)
        APP_ENV="development"
        shift
        ;;
esac; shift; done
if [[ "$1" == '--' ]]; then shift; fi

PKG_OK=$(dpkg-query -W --showformat='${Status}\n' ansible|grep "install ok installed")
echo "Checking for Ansible: $PKG_OK"

if [[ "" == "$PKG_OK" ]]; then
    apt-get update
         apt-get install -q -y software-properties-common
         add-apt-repository -y ppa:ansible/ansible

         apt-get update
         apt-get install -q -y python2.7 python-pip python-mysqldb ansible
fi

APP_ENV="${APP_ENV:-production}"

echo "Installing AzuraCast (Environment: $APP_ENV)"
ansible-playbook util/ansible/deploy.yml --inventory=util/ansible/hosts --extra-vars "app_env=$APP_ENV"
