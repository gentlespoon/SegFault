#!/bin/sh

echo "======== Start Updating ========\n"
echo "\n\n> mkdir ../tmp\n"
mkdir ../tmp
echo "================================\n"

echo "\n\n> wget https://github.com/gentlespoon/SegFault/archive/master.zip -O ../tmp/SegFault.zip\n"
wget https://github.com/gentlespoon/SegFault/archive/master.zip -O ../tmp/SegFault.zip
echo "================================\n"

echo "\n\n> unzip ../tmp/SegFault.zip -d ../tmp\n"
unzip ../tmp/SegFault.zip -d ../tmp
echo "================================\n"

echo "\n\n> cp -r ../tmp/SegFault-master/* ../\n"
cp -r ../tmp/SegFault-master/* ../
echo "================================\n"

echo "\n\n> rm ../tmp -rf\n"
rm ../tmp -rf
echo "======= Finished Update ========\n"
