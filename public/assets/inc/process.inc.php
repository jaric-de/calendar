<?php
session_start();

require_once '../../../sys/config/db-cred.inc.php';


foreach ($C as $name => $val) {
    define($name, $val);
}

$actions = [
    'event_edit' => [
        'object' => 'Calendar',
        'method' => 'processForm',
        'header' => 'Location: ../../'
    ]
];

if ($_POST['token'] === $_SESSION['token'] && isset($actions[$_POST['action']])) {
    $useArray = $actions[$_POST['action']];
    $obj = new $useArray['object']($dbo);
    $msg = $obj->$useArray['method']();
    if ($msg === TRUE) {
        header($useArray['header']);
        exit;
    } else {
        exit($msg);
    }
} else {
    header("Location: ../../"); // header на index.php
    exit;
}

function __autoload($className)
{
    $filename = "../../../sys/class/class." . strtolower($className) . ".inc.php";
    if (file_exists($filename)) {
        include_once $filename;
    }
}