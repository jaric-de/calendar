<?php
session_start();
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = sha1(uniqid(mt_rand(), TRUE)); // генерация случайного хеш-кода (маркер сеанса), если это не было сделано ранее для защиты от CSRF
}

include_once '../sys/config/db-cred.inc.php';
foreach ($C as $name => $val) {
    define($name, $val);
}

/**
 * Создать PDO объект
 */
$dsn = "mysql:host=". DB_HOST .";dbname=". DB_NAME .";charset=" . DB_CHARSET;
$dbo = new PDO($dsn, DB_USER, DB_PASS, $GLOBALS['pdoOptions']);

function __autoload($class)
{
    $filename = "../sys/class/class." . $class . ".inc.php";
    if (file_exists($filename)) {
        include_once $filename;
    }
}