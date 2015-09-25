#!/bin/bash
sudo apt-get autoremove -y
PROVISION_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

ANSIBLE_SCRIPT=$PROVISION_DIR/ansible/ansible.sh
chmod u+x $ANSIBLE_SCRIPT
$ANSIBLE_SCRIPT
