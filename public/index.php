<?php
error_reporting(E_ALL);
/**
 * Включить необходимые файлы
 */

require_once '../sys/core/init.inc.php'; // файл инициализации

//$cal = new Calendar($dbo, "2010-01-01 12:00:00");
$cal = new Calendar($dbo);

$pageTitle = 'Calendar';
$cssFiles = ['style.css', 'admin.css'];
include_once 'assets/common/header.inc.php';

?>
<div id="content">
<?php

/**
 * Отобразить календарь
 */
echo $cal->buildCalendar();

?>
</div>
<?php
    echo isset($_SESSION['user']) ? "Вход выполнен!" : "Вход не выполнен";
?>
<?php

require_once 'assets/common/footer.inc.php';
