# Pots

Event's making revolution.

## Starting 

`docker-compose up --build -d`

client will serve on [localhost](http://localhost)

api platform run on [localhost:5000/api](http://localhost:5000/api)

phpmyadmin is running on [localhost:5001](http://localhost:5001)

## Appliquer les fixtures

**En développement uniquement.**

`php bin/console doctrine:fixtures:load`