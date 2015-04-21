<?php
if(!defined('OSTCLIENTINC')) die('ปฏิเสธการเข้าถึง');

$userid=Format::input($_POST['userid']);
?>
<h1><?php echo __('ลืมรหัสผ่าน'); ?></h1>
<p><?php echo __(
'กรอกชื่อผู้ใช้หรืออีเมลลงในแบบฟอร์มด้านล่างและกดปุ่ม <strong>ส่งอีเมล</strong> เพื่อสร้างลิงค์ล้างรหัสผ่านและส่งไปที่อีเมลของคุณ');
?>

<form action="pwreset.php" method="post" id="clientLogin">
    <div style="width:50%;display:inline-block">
    <?php csrf_token(); ?>
    <input type="hidden" name="do" value="sendmail"/>
    <strong><?php echo Format::htmlchars($banner); ?></strong>
    <br>
    <div>
        <label for="username"><?php echo __('ชื่อผู้ใช้'); ?>:</label>
        <input id="username" type="text" name="userid" size="30" value="<?php echo $userid; ?>">
    </div>
    <p>
        <input class="btn" type="submit" value="<?php echo __('ส่งอีเมล'); ?>">
    </p>
    </div>
</form>
