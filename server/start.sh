#!/bin/bash 

composer install \
&& php bin/console make:migration \
&& php bin/console doctrine:migrations:migrate \
&& symfony serve
