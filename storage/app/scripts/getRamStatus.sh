#!/bin/bash

echo `free -m | awk '/Mem:/ { printf("%3.1f%", $3/$2*100) }'`
