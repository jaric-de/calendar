<?php
$C = [];
$C['DB_HOST'] = 'localhost';
$C['DB_USER'] = 'root';
$C['DB_PASS'] = '';
$C['DB_NAME'] = 'php-jquery_example';
$C['DB_CHARSET'] = 'utf8';
$pdoOptions = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];