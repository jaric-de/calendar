<?php
/**
 * Включить необходимые файлы
 */

require_once '../sys/core/init.inc.php'; // файл инициализации

//$cal = new Calendar($dbo, "2010-01-01 12:00:00");
//$cal = new Calendar($dbo, "2017-05-17 12:00:00");
$cal = new Calendar($dbo);

/**
 * Отобразить календарь
 */
echo $cal->buildCalendar();