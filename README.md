## Symfony Blog
### Prerequisites
What things you need to install the software and how to install them
```
Git
Composer
```
### Install
```
$ git clone https://gitlab.noveogroup.com/ekusainov/blog.git
$ composer install
```
Create database
```
$ bin/console doctrine:database:create
$ bin/console doctrine:migrations:migrate
```
### Run application
```
$ bin/console server:start
```
### Stop application
```
bin/console server:stop
```
### Fill database with test data
```
$ bin/console doctrine:fixtures:load
```