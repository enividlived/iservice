<?php
include_once(INCLUDE_DIR.'staff/login.header.php');
$info = ($_POST && $errors)?Format::htmlchars($_POST):array();
?>
<div id="loginBox">
    <h1 id="logo"><a href="index.php">TRC&SKW iService :: <?php echo __('แผงควบคุมเจ้าหน้าที่');?></a></h1>
    <h3><?php echo Format::htmlchars($msg); ?></h3>
    <div class="banner"><small><?php echo ($content) ? Format::display($content->getBody()) : ''; ?></small></div>
    <form action="login.php" method="post">
        <?php csrf_token(); ?>
        <input type="hidden" name="do" value="scplogin">
        <fieldset>
        <input type="text" name="userid" id="name" value="<?php echo $info['userid']; ?>" placeholder="<?php echo __('ชื่อผู้ใช้'); ?>" autocorrect="off" autocapitalize="off">
        <input type="password" name="passwd" id="pass" placeholder="<?php echo __('รหัสผ่าน'); ?>" autocorrect="off" autocapitalize="off">
            <?php if ($show_reset && $cfg->allowPasswordReset()) { ?>
            <h3 style="display:inline"><a href="pwreset.php"><?php echo __('ลืมรหัสผ่าน'); ?></a></h3>
            <?php } ?>
            <input class="submit" type="submit" name="submit" value="<?php echo __('เข้าสู่ระบบ'); ?>">
        </fieldset>
    </form>
<?php
$ext_bks = array();
foreach (StaffAuthenticationBackend::allRegistered() as $bk)
    if ($bk instanceof ExternalAuthentication)
        $ext_bks[] = $bk;

if (count($ext_bks)) { ?>
<div class="or">
    <hr/>
</div><?php
    foreach ($ext_bks as $bk) { ?>
<div class="external-auth"><?php $bk->renderExternalLink(); ?></div><?php
    }
} ?>
</div>
<div id="copyRights"><?php echo __('ลิขสิทธิ์'); ?> &copy;
<a href='http://www.trc-con.com' target="_blank">trc-con.com</a></div>
</body>
</html>
