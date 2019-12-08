#!/bin/bash

######### REMOTE GIT CONFIGURATION #########
REPO="git@github.com:andreapollastri/cipi.git" #Use your Github
BRANCH="master" #Choose your branch

######### DO NOT CHANGE ANYTHING IN THIS AREA #########
WORK_TREE="/home/###CIPI-USER###/web"
GIT_DIR="/home/###CIPI-USER###/git/deploy.git"
eval $(ssh-agent -s)
if [ -d "$GIT_DIR" ]; then
    cd $WORK_TREE
    git --work-tree=$WORK_TREE --git-dir=$GIT_DIR fetch
    git --work-tree=$WORK_TREE --git-dir=$GIT_DIR fetch origin --tags --force
    git --work-tree=$WORK_TREE --git-dir=$GIT_DIR checkout -f $BRANCH
    git --work-tree=$WORK_TREE --git-dir=$GIT_DIR merge origin/$BRANCH
else
    git init --bare $GIT_DIR
    rm -rf $WORK_TREE
    mkdir $WORK_TREE
    git --work-tree=$WORK_TREE --git-dir=$GIT_DIR remote add origin $REPO
    git --work-tree=$WORK_TREE --git-dir=$GIT_DIR fetch
    git --work-tree=$WORK_TREE --git-dir=$GIT_DIR fetch origin --tags --force
    git --work-tree=$WORK_TREE --git-dir=$GIT_DIR checkout -f $BRANCH
    git --work-tree=$WORK_TREE --git-dir=$GIT_DIR merge origin/$BRANCH
fi
######################################################

######### POST DEPLOY SCRIPTS HERE #########
#Example: composer update
