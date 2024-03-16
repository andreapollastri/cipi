#!/bin/bash

echo `df -h / | awk '/\// {print $(NF-1)}'`
