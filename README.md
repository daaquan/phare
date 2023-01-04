# API Skeleton

## Get Started

### Requirements

To run this application on your machine, you need at least:

* PHP >= 8.1
* Phalcon >= 5.0
* MySQL >= 8.0
* Nginx Web Server

### Installing Dependencies via Composer

```shell
cd app-folder
composer install
cp .env.example .env
```

### Run tests

```shell
./vendor/bin/pest

./vendor/bin/pest --parallel --processes=10

./vendor/bin/pest --coverage --min=90
```