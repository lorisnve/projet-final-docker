<?php
define('DB_DSN', sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8',
    getenv('DB_HOST') ?: 'db',
    getenv('DB_PORT') ?: '3306',
    getenv('MYSQL_DATABASE')
));
define('DB_USER', getenv('MYSQL_USER'));
define('DB_PASS', trim(file_get_contents(getenv('DB_PASSWORD_FILE'))));
$options = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", // encodage utf-8
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // gérer les erreurs en tant qu'exception
    PDO::ATTR_EMULATE_PREPARES => false // faire des vrais requêtes préparées et non une émulation
);