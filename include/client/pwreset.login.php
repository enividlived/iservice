<?php
if(!defined('OSTCLIENTINC')) die('ปฏิเสธการเข้าถึง');

$userid=Format::input($_POST['userid']);
?>
<h1><?php echo __('ลืมรหัสผ่าน'); ?></h1>
<p><?php echo __(
'กรอกชื่อผู้ใช้ในช่องด้านล่างและ <strong>เข้าสู่ระบบ</strong> เพื่อเข้าสู่ขั้นตอนการรีเซ็ตรหัสผ่านของคุณ');
?>
<form action="pwreset.php" method="post" id="clientLogin">
    <div style="width:50%;display:inline-block">
    <?php csrf_token(); ?>
    <input type="hidden" name="do" value="reset"/>
    <input type="hidden" name="token" value="<?php echo Format::htmlchars($_REQUEST['token']); ?>"/>
    <strong><?php echo Format::htmlchars($banner); ?></strong>
    <br>
    <div>
        <label for="username"><?php echo __('ชื่อผู้ใช้'); ?>:</label>
        <input id="username" type="text" name="userid" size="30" value="<?php echo $userid; ?>">
    </div>
    <p>
        <input class="btn" type="submit" value="เข้าสู่ระบบ">
    </p>
    </div>
</form>
