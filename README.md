# 70000OTONS OF METAL Booking Engine - Backend

This is Booking Engine Admin. 
[![Laravel Forge Site Deployment Status](https://img.shields.io/endpoint?url=https%3A%2F%2Fforge.laravel.com%2Fsite-badges%2F4746fb0e-08cb-47d3-adba-23bcff554e8e%3Fdate%3D1%26label%3D1%26commit%3D1&style=plastic)](https://forge.laravel.com/servers/893391/sites/2641209)

[![Laravel Forge Site Deployment Status](https://img.shields.io/endpoint?url=https%3A%2F%2Fforge.laravel.com%2Fsite-badges%2F3b71f0e7-8dc3-488a-9b48-017be65c6a0b%3Fdate%3D1%26commit%3D1&style=plastic)](https://forge.laravel.com/servers/826351/sites/2475243)

#### Boilerplate - boilerplate-70k-back
[boilerplate-70k-back](https://github.com/70000TONS-IT/boilerplate-70k-back)

## Requirements

```bash
PHP >= 8.2
Composer
Node >= 20.x
NPM >= 6.x
```

## Installation

Clone the repository:

```bash
git clone https://github.com/70000TONS-IT/boilerplate-70k-back
cd boilerplate-70k-back
```
#### Install PHP dependencies:

```bash
composer install
```
#### Install Node.js dependencies:

```bash
npm install
```
#### Configure the .env file:

```bash
cp .env.example .env
php artisan key:generate
```

#### Env file - IMPORTANT
We are using ENV file from keepass. 
Always use latest version, while copying **do not copy APP_KEY** keep APP_KEY as it is generated on your local environment

Run the following command to verify all variables:
```bash
php artisan env:check
```

#### Set up the database in the .env file and run the migrations:

```bash
php artisan migrate
```

#### Run seeders to fill database data:
```bash
php artisan db:seed
```

## Start the App:

```bash
npm run single
```

> To handle error message with no existing file manifest.json in public folder. You have to create manifest file first.
Run:

```bash
npm run build
```

## Testing
To run the tests, use the following command:
```bash
npm run test
```
