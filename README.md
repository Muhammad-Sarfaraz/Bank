# Setup

#### Automatic Setup
ensure that you have configured the database settings in your environment variables in env. Then run this code on bash.

```bash
./setup.sh
```
#### Manual Setup

- ``` composer install ```
- ```php artisan key:generate ```
- ```php artisan migrate:fresh --seed ```
- ```php artisan storage:link ```
- ``` php artisan optimize:clear ```
#### Run the application
```php artisan serve```

#### Test Coverage
```php artisan test```

#### Frontend
For the frontend views, please navigate to [link](https://github.com/Muhammad-Sarfaraz/Frontend-Bank).

