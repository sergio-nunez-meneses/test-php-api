# Simple REST API in pure PHP
Simple REST API built in pure PHP using [Okta](https://www.okta.com/) as authorization server, inspired by this [Okta tutorial](https://developer.okta.com/blog/2019/03/08/simple-rest-api-php), for pedagogical purposes only.

First, create a new database and user through the ```mysql cli```:
```sql
mysql -uroot -p
CREATE DATABASE database_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'username'@'localhost' identified by 'password';
GRANT ALL on database_name.* to 'username'@'localhost';
quit
```

Next, create the folder ```tools```, and create inside the file ```sql.php``` and fill it with the following information:
```php
<?php
define('DB_NAME', 'database_name');
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_CHAR', 'utf8mb4');
define('DB_USER', 'username');
define('DB_PWD', 'password');
define('PDO_OPTIONS', []);
```

Having already created an [Okta account](https://developer.okta.com/signup/), create the file ```okta.php``` in the same folder and fill it with your personal account information:
```php
<?php
define('OKTAAUDIENCE', '');
define('OKTAISSUER', '');
define('SCOPE', '');
define('OKTACLIENTID', '');
define('OKTASECRET', '');
```

Finally, to test the Okta authorization, copy and paste the following command lines in your terminal:
```
cd public
php client.php
```
