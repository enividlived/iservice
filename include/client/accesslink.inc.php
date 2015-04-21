<?php
if(!defined('OSTCLIENTINC')) die('ปฏิเสธการเข้าถึง');

$email=Format::input($_POST['lemail']?$_POST['lemail']:$_GET['e']);
$ticketid=Format::input($_POST['lticket']?$_POST['lticket']:$_GET['t']);

if ($cfg->isClientEmailVerificationRequired())
    $button = __("อีเมลลิงค์คำขอใช้บริการ");
else
    $button = __("ดูคำขอใช้บริการ");
?>
<h1><?php echo __('ตรวจสอบสถานะคำขอใช้บริการ'); ?></h1>
<p><?php
echo __('โปรดกรอกอีเมลแอดเดรสและหมายเลขคำขอใช้บริการ');
if ($cfg->isClientEmailVerificationRequired())
    echo ' '.__('ลิงค์อ่านคำขอใช้บริการจะถูกส่งไปหาคุณ');
else
    echo ' '.__('และทำให้คุณสามารถเข้าสู่ระบบเพื่อดูข้อมูลได้');
?></p>
<form action="login.php" method="post" id="clientLogin">
    <?php csrf_token(); ?>
<div style="display:table-row">
    <div class="login-box">
    <div><strong><?php echo Format::htmlchars($errors['login']); ?></strong></div>
    <div>
        <label for="email"><?php echo __('อีเมลแอดเดรส'); ?>:
        <input id="email" placeholder="<?php echo __('เช่น test@trc-con.com'); ?>" type="text"
            name="lemail" size="30" value="<?php echo $email; ?>"></label>
    </div>
    <div>
        <label for="ticketno"><?php echo __('หมายเลขคำขอใช้บริการ'); ?>:
        <input id="ticketno" type="text" name="lticket" placeholder="<?php echo __('เช่น H/O-14030'); ?>"
            size="30" value="<?php echo $ticketid; ?>"></label>
    </div>
    <p>
        <input class="btn" type="submit" value="<?php echo $button; ?>">
    </p>
    </div>
    <div class="instructions">
<?php if ($cfg && $cfg->getClientRegistrationMode() !== 'disabled') { ?>
        <?php echo __('เคยสมัครสมาชิกแล้ว?'); ?>
        <a href="login.php"><?php echo __('เข้าสู่ระบบ'); ?></a> <?php
    if ($cfg->isClientRegistrationEnabled()) { ?>
<?php echo sprintf(__('หรือ %s สมัครสมาชิก %s เพื่อสร้างคำขอใช้บริการ'),
    '<a href="account.php?do=create">','</a>');
    }
}?>
    </div>
</div>
</form>
<br>
<p>
<?php echo sprintf(
__("ถ้านี่คือครั้งในการใช้งานระบบ สามารถ %s สร้างคำขอใช้บริการ %s ได้ที่นี่"),
    '<a href="open.php">','</a>'); ?>
</p>
