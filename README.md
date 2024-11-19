# News Aggregator API

This project is a **News Aggregator API** built using **Laravel**. It provides endpoints for managing articles, user preferences, and other features. It is designed to be run in a local development environment using **Laravel Sail** (Docker).

## Requirements

Before setting up the project, ensure that you have the following installed:

- **Docker**: Sail uses Docker to create containers for your environment.
    - [Docker Installation](https://docs.docker.com/get-docker/)
- **Docker Compose**: This is required for managing multi-container Docker applications.
    - [Install Docker Compose](https://docs.docker.com/compose/install/)
- **Git**: To clone the repository.
    - [Git Installation](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)

## Installation

### 1. Clone the repository

First, clone the project repository to your local machine:

```bash
git clone https://github.com/Abhilash-k-p/news-aggregator-api.git
```

### 2. Navigate into the project directory

```bash
cd news-aggregator-api
```

### 3.  Set up environment variables
Copy the .env.example file to create your .env file

```bash
cp .env.example .env
```

### 4.  Set up docker

Run following command inside project root folder

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

```bash
./vendor/bin/sail up -d
```

To run any commands related to laravel, you can go inside docker bash using this command

```bash
./vendor/bin/sail shell
```

### 5.  Set up database

make sure everything right in .env file and run below command inside docker shell
```bash
php artisan migrate && php artisan db:seed
```

### 6. set up articles from 3 source

Run below command to sync data from 3 news sources to our DB

```bash
php artisan app:fetch-articles-command
```
I have added it in scheduler, to run hourly

```php
// console.php file
// fetc[News_aggregator_collection.postman_collection.json](../../Downloads/News_aggregator_collection.postman_collection.json)h articles hourly
Schedule::command('app:fetch-articles-command')->hourly();
```
Here is the complete post man collection of the APIs
https://drive.google.com/file/d/15W_l_P0mGEvBhJNXDvUVzgGbrz49BTVP/view?usp=sharing

### 5.  Run test
I have created good amount of tests. To execute tests run following command inside docker shell
```bash
php artisan test
```
Alternatively you can use 
```bash
./vendor/bin/sail artisan test
```
outside docker shell
