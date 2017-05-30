<?php
require_once '../sys/core/init.inc.php';

$pageTitle = "Registration";
$cssFiles = ["style.css", "admin.css"];

require_once 'assets/common/header.inc.php';
?>
    <div id="content">
        <form action="assets/inc/process.inc.php" method="post">
            <fieldset>
                <legend>Registration</legend>
                <label for="uname">Username</label>
                <input type="text" name="uname" id="uname" value="" />
                <label for="pword">Password</label>
                <input type="password" name="pword" id="pword" value="" />
                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>" />
                <input type="hidden" name="action" value="user_login" />
                <input type="submit" name="login_submit" value="Exit" />
                or <a href="./">Cancel</a>
            </fieldset>
        </form>
    </div>
<?php
require_once 'assets/common/footer.inc.php';
