# TAREAS BACKEND

Basic self hosted task manager

<!-- ABOUT THE PROJECT -->

## About The Project

Backend for the [task manager client](https://github.com/Enchufadoo/tareas-front)

### Built With

[![Laravel][Laravel.php]][Laravel-url]


<!-- GETTING STARTED -->

## Getting Started

Build instruction for running locally the project

### Prerequisites

* Docker / Docker compose
* PHP
* Composer

Tested with:

* PHP 8.3.3
* Composer 2.4.4

### Installation

1. Clone the repo
   ```sh
   git clone https://github.com/Enchufadoo/tareas-back
   ```
2. Create a .env file from the sample
   ```sh
   cp .env.example .env
   ```
   Complete the following variables
   ```
   DB_PASSWORD -> Any MySQL user password
   WWWGROUP -> Group for your current user, usually 1001
   WWWUSER -> ID for your current user, usually 10001
   APP_PORT -> PORT the application will run
   ```
3. Start the docker containers

   ```sh
   docker compose up
   ```

4. Run the database migrations
   ```sh
   vendor/bin/sail artisan migrate
   ```
5. Run the database migrations
   ```sh
   vendor/bin/sail artisan db:seed
   ```

## License

Distributed under the AGPL v3 License. See `LICENSE.txt` for more information.

[Laravel.php]: https://img.shields.io/badge/Laravel-20232A?style=for-the-badge&logo=laravel&logoColor=61DAFB

[Laravel-url]: https://laravel.com/


