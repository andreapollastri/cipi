#!/bin/bash

./vendor/bin/sail bash -c "php artisan migrate:fresh --seed"
