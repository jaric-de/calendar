<?php
include_once '../sys/core/init.inc.php';

$obj = new Admin($dbo);

// без использования соли
$hash1 = $obj->testSaltedHash('test');
echo "Hash1 without salt: <br /> $hash1 <br /><br />";
sleep(1);

// без использования соли
$hash2 = $obj->testSaltedHash('test');
echo "Hash2 without salt: <br /> $hash2 <br /><br />";
sleep(1);

// с использованием существующей соли
$hash3 = $obj->testSaltedHash('test', $hash2);
echo "Hash3 with salt from hash2: <br /> $hash3 <br /><br />";
sleep(1);

// без использования соли для админа
$hash4 = $obj->testSaltedHash('admin');
echo "Hash4 without salt for admin: <br /> $hash4 <br /><br />";