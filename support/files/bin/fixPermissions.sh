#!/bin/bash

if [ "$EUID" -ne 0 ]
  then echo "Please run using sudo or as root"
  exit
fi

# This Folder
ROOT="$( cd "$(dirname "$0")" ; pwd -P )"

# This Folder -1
ROOT="`dirname $ROOT`"

# ----------------

echo $ROOT

find $ROOT -type f | xargs chmod 664
find $ROOT -type d | xargs chmod 775

find $ROOT -type f | xargs chown admin
find $ROOT -type d | xargs chown admin

find $ROOT -type f | xargs chgrp administrators
find $ROOT -type d | xargs chgrp administrators

find $ROOT/bin/*.sh -type f | xargs chmod 775

find $ROOT/var/* -type d | xargs chmod 777
