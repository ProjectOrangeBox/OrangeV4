#!/bin/bash

# This Folder
SCRIPTFOLDER="$( cd "$(dirname "$0")" ; pwd -P )"

FILES="$( cd "$(dirname "$0")" ; pwd -P )"
FILES="$FILES/files"

# Where is the application root folder? this Folder -1
ROOT="`dirname $SCRIPTFOLDER`"
ROOT="`dirname $ROOT`"
ROOT="`dirname $ROOT`"
ROOT="`dirname $ROOT`"

CODEIGNITER="$ROOT/vendor/codeigniter/framework";

echo "Application Root Path $ROOT"
echo "Files Folder $FILES"

mkdir -p $ROOT/bin

#find $SCRIPTFOLDER/files/bin/*.sh -type f | xargs chmod 775

find $SCRIPTFOLDER/files/bin/*.sh -type f -exec ln -s {} $ROOT/bin ';'
