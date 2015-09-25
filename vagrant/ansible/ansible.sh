#!/bin/bash
#
#	The Script does the following:
#	1.1) installs ansible on machine if needed.
#	1.2) installs "python-apt" so ansible can continue installing other packages it self if needed
#	2) Disables Python Buffered output to enable real time output for vagrant provisioning
#	3) Detects it's own directory
#	4) Creates a temp file
#	5) Copies hosts file to temp file
#	6) Removes Executable flag from copied temp hosts file
#	7) Runs ansible playbook
#	8) Cleans up after it self
#
#	Steps 4 trough 6 and 8 are to ensure compatibility when running Vagrant from Windows host
#

sudo apt-get install ansible python-apt -y --no-install-recommends
export PYTHONUNBUFFERED=1
ANSIBLE_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
TEMP_HOSTS_FILE=$(tempfile)
cp $ANSIBLE_DIR/hosts $TEMP_HOSTS_FILE
chmod -x $TEMP_HOSTS_FILE
ansible-playbook -i $TEMP_HOSTS_FILE $ANSIBLE_DIR/playbook.yml
rm $TEMP_HOSTS_FILE
