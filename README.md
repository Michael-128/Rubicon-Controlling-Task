# Rubicon-Controlling-Task

## Setup
- Clone the repository
```bash
git clone https://github.com/Michael-128/Rubicon-Controlling-Task && cd Rubicon-Controlling-Task
```
- Set up dependecies
```bash
composer install 
```
- Set up database
```bash
php bin/console make:migration --env=dev && php bin/console doctrine:migrations:migrate --env=dev
```
- Load data into the database
```bash
php bin/console doctrine:fixtures:load --env=dev
```
- Serve
```bash
symfony serve
```
