<?php
require_once '../sys/core/init.inc.php';

$pageTitle = "Add/Edit Form";
$cssFiles = ["style.css", "admin.css"];

require_once 'assets/common/header.inc.php';
$cal = new Calendar($dbo);
?>
    <div id="content">
        <?php echo $cal->displayForm() ?>
    </div>
<?php
require_once 'assets/common/footer.inc.php';