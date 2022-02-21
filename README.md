<a href="https://codeclimate.com/github/jabardigitalservice/pikobar-logistik-api/maintainability"><img src="https://api.codeclimate.com/v1/badges/78ce000cc84f4304d57d/maintainability" /></a>
<a href="https://codeclimate.com/github/jabardigitalservice/pikobar-logistik-api/test_coverage"><img src="https://api.codeclimate.com/v1/badges/78ce000cc84f4304d57d/test_coverage" /></a>


# PIKOBAR LOGISTIK API

## Description
API for Pikobar Logistik Alkes and Pikobar Logistik Vaccine.

## Specification
Build with:
- Laravel 7.x
- MySQL 5.7
- Composer 2.x
- PHP 7.4
- XDEBUG_MODE = ON (optional. Set this up on your `php.ini`)

## Installation
```sh
$ git clone git@github.com:jabardigitalservice/pikobar-logistik-api.git
$ cd pikobar-logistik-api
$ cp .env.example .env
```
### Default
```sh
$ php composer install
$ php artisan key:generate
$ php artisan jwt:secret
$ php artisan migrate
$ php artisan db:seed
$ php artisan serve
```

### Installation with Docker
```sh
$ docker-compose up -d
$ docker exec -it api_logistik bash
$ php composer install
$ php artisan key:generate
$ php artisan jwt:secret
$ php artisan migrate
$ php artisan db:seed
```

## Testing on Development (Local)
**Caution!** You need another database at local development to avoid empty database after execute test.
- Create env for testing. run `cp .env .env.testing`
- Create another database for testing, example `logistik_testing`
- Change database from `env.testing` to `logistik_testing` (or what you create before)
- run `php artisan test`

## Coding Style
- Naming conventions :
https://github.com/alexeymezenin/laravel-best-practices#follow-laravel-naming-conventions
- PSR standards :
https://www.php-fig.org/psr/psr-2/

## License
Pikobar Logistik API is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
