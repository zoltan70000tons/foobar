# 70K Tons Booking Engine - Backend

This is Booking Engine Admin. 

#### Boilerplate - boilerplate-70k-back
[boilerplate-70k-back](https://github.com/70000TONS-IT/boilerplate-70k-back)

#### Requirements

```bash
PHP >= 8.2
Composer
Node >= 20.x
NPM >= 6.x
```



#### Installation

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

#### Set up the database in the .env file and run the migrations:

```bash
php artisan migrate
```

#### Run seeders to fill database data:
```bash
php artisan migrate
```

#### Start the server:

```bash
php artisan serve
```

> To handle error message with no existing file manifest.json in public folder. You have to create manifest file first.
Run:

```bash
npm run build
```

#### Testing
To run the tests, use the following command:
```bash
npm run test
```