#!/bin/bash

######### REMOTE GIT CONFIGURATION #########
GITHUB="andreapollastri/cipi.git" #Choose your Github private/public repo
BRANCH="master" #Choose your repository branch here

######### DO NOT CHANGE ANYTHING IN THIS AREA #########
SSH_KEY="/home/###CIPI-USER###/git/deploy"
WORK_TREE="/home/###CIPI-USER###/web"
GIT_DIR="/home/###CIPI-USER###/git/deploy.git"
chmod 600 $SSH_KEY
eval $(ssh-agent -s)
ssh-add $SSH_KEY
REPO="git@github.com:$GITHUB"
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
