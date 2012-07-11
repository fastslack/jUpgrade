#!/bin/bash
#
# * jUpgrade
# *
# * @author      Matias Aguirre
# * @email       maguirre@matware.com.ar
# * @url         http://www.matware.com.ar
# * @license     GNU/GPL
# 

PROJECT="jupgrade"
VERSION="2.5.2"

DIR="com_$PROJECT"
PACKAGE="com_$PROJECT-$VERSION.zip"

# copy all needed files
rm -rf $DIR
cp -r ../trunk $DIR

# delete version-control stuff and other files
find $DIR -name ".git" -type d -exec rm -rf {} \;
find $DIR -name ".DS_Store" -exec rm -rf {} \;

# delete unused files
rm $DIR/admin/${PROJECT}.xml
rm $DIR/TODO

# create package
rm $PACKAGE
zip -rq $PACKAGE $DIR

# create symlink
rm -rf com_${PROJECT}-latest.zip
ln -s $PACKAGE com_${PROJECT}-latest.zip

# cleanup
rm -rf $DIR
