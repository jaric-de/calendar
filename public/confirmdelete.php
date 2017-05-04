<?php
if (isset($_POST['event_id'])) {
    $id = $_POST['event_id'];
} else {
    header("Location: ./");
    exit;
}

require_once '../sys/core/init.inc.php';

$cal = new Calendar($dbo);
$markup = $cal->confirmDelete($id);
$pageTitle = "Event view";
$cssFiles = ['style.css', 'admin.css'];
require_once 'assets/common/header.inc.php';
?>

<div id="content">
    <?php echo $markup; ?>
</div>

<?php
require_once 'assets/common/footer.inc.php';
