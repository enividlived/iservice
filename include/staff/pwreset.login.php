<?php
include_once(INCLUDE_DIR.'staff/login.header.php');
defined('OSTSCPINC') or die('Invalid path');
$info = ($_POST)?Format::htmlchars($_POST):array();
?>

<div id="loginBox">
    <h1 id="logo"><a href="index.php">TRC&SKW <?php echo __('รีเซ็ทรหัสผ่านเจ้าหน้าที่'); ?></a></h1>
    <h3><?php echo Format::htmlchars($msg); ?></h3>

    <form action="pwreset.php" method="post">
        <?php csrf_token(); ?>
        <input type="hidden" name="do" value="newpasswd"/>
        <input type="hidden" name="token" value="<?php echo Format::htmlchars($_REQUEST['token']); ?>"/>
        <fieldset>
            <input type="text" name="userid" id="name" value="<?php echo
                $info['userid']; ?>" placeholder="<?php echo __('ชือผู้ใช้'); ?>"
                autocorrect="off" autocapitalize="off"/>
        </fieldset>
        <input class="submit" type="submit" name="submit" value="Login"/>
    </form>
</div>

<div id="copyRights">ลิขสิทธิ์ &copy; <a href='http://www.trc-con.com' target="_blank">trc-con.com</a></div>
</body>
</html>
