<?php
include_once(INCLUDE_DIR.'staff/login.header.php');
defined('OSTSCPINC') or die('Invalid path');
$info = ($_POST && $errors)?Format::htmlchars($_POST):array();
?>

<div id="loginBox">
    <h1 id="logo"><a href="index.php">TRC&SKW <?php echo __('รีเซ็ทรหัสผ่านเจ้าหน้าที่'); ?></a></h1>
    <h3><?php echo __('ส่งอีเมลยืนยันแล้ว'); ?></h3>
    <h3 style="color:black;"><em><?php echo __(
    'อีเมลรีเซ็ตรหัสผ่านได้ถูกส่งให้คุณเรียบร้อยแล้ว ทำตามคำแนะนำในอีเมลเพื่อรีเซ็ทรหัสผ่าน'
    ); ?>
    </em></h3>

    <form action="index.php" method="get">
        <input class="submit" type="submit" name="submit" value="Login"/>
    </form>
</div>

<div id="copyRights">ลิขสิทธิ์ &copy; <a href='http://www.trc-con.com' target="_blank">trc-con.com</a></div>
</body>
</html>
