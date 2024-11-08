# News-Aggregator Project

## Description

News-Aggregator is a Laravel-based application designed to fetch news from various sources and filter them based on user
preferences. Utilizing a RESTful API, it allows users to receive news updates tailored to their interests.

- Database: mySql
- PHP : 8.0
- Laravel : 9.19

## Features
- User Authentication(register, login, Password reset)

- Fetch news from multiple sources.(NewsAPI, The Guardian, New York Times)

- RESTful API for retrieving news and preferences user.

- Filter news based on user preferences.

- Swagger documentation `http://127.0.0.1:1000/api/documentation`

## Docker
        $ docker-compose up -d
        $ docker ps
        $ docker exec -it <contaner-id> bash

Run below commands inside docker container

## Install

        $ composer install
        $ cp .env.example .env
        $ php artisan migrate
        $ php artisan db:seed
        $ php artisan key:generate

for run schedule:

     $  php artisan articles:fetch
