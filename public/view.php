<?php
if (isset($_GET['event_id'])) {
    $id = preg_replace('/[^0-9]/', '', $_GET['event_id']);

    // вернуть на основную страницу, если $id  - недействителен
    if (empty($id)) {
        header("Location: ./");
        exit;
    }
} else {
    header("Location: ./");
    exit;
}

require_once '../sys/core/init.inc.php';

$pageTitle = "Event";
$cssFiles = ["style.css", "admin.css"];

require_once 'assets/common/header.inc.php';
$cal = new Calendar($dbo);
?>

<div id="content">
    <?php echo $cal->displayEvent($id) ?>
    <a href="./">&laquo; Back to the calendar</a>
</div>
<?php
require_once 'assets/common/footer.inc.php';