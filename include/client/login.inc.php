<?php
if(!defined('OSTCLIENTINC')) die('ปฏิเสธการเข้าสู่ระบบ');

$email=Format::input($_POST['luser']?:$_GET['e']);
$passwd=Format::input($_POST['lpasswd']?:$_GET['t']);

$content = Page::lookup(Page::getIdByType('banner-client'));

if ($content) {
    list($title, $body) = $ost->replaceTemplateVariables(
        array($content->getName(), $content->getBody()));
} else {
    $title = __('เข้าสู่ระบบ');
    $body = __('เพื่อการบริการที่ดีขึ้น เราแนะนำให้คุณล็อคอินเข้าสู่ระบบครับ');
}

?>
<h1><?php echo Format::display($title); ?></h1>
<p><?php echo Format::display($body); ?></p>
<form action="login.php" method="post" id="clientLogin">
    <?php csrf_token(); ?>
<div style="display:table-row">
    <div class="login-box">
    <strong><?php echo Format::htmlchars($errors['login']); ?></strong>
    <div>
        <input id="username" placeholder="<?php echo __('ชื่อผู้ใช้'); ?>" type="text" name="luser" size="30" value="<?php echo $email; ?>">
    </div>
    <div>
        <input id="passwd" placeholder="<?php echo __('รหัสผ่าน'); ?>" type="password" name="lpasswd" size="30" value="<?php echo $passwd; ?>"></td>
    </div>
    <p>
        <input class="btn" type="submit" value="<?php echo __('เข้าสู่ระบบ'); ?>">
    <div>
        <b><a href="manual_user.pdf"><font color=FF0000><?php echo __('คู่มือการใช้งานระบบ iService'); ?></a></font></b>
    </div>
<?php if ($suggest_pwreset) { ?>
        <a style="padding-top:4px;display:inline-block;" href="pwreset.php"><?php echo __('ลืมรหัสผ่าน'); ?></a>
<?php } ?>
    </p>
    </div>
    <div style="display:table-cell;padding: 15px;vertical-align:top">
<?php

$ext_bks = array();
foreach (UserAuthenticationBackend::allRegistered() as $bk)
    if ($bk instanceof ExternalAuthentication)
        $ext_bks[] = $bk;

if (count($ext_bks)) {
    foreach ($ext_bks as $bk) { ?>
<div class="external-auth"><?php $bk->renderExternalLink(); ?></div><?php
    }
}
if ($cfg && $cfg->isClientRegistrationEnabled()) {
    if (count($ext_bks)) echo '<hr style="width:70%"/>'; ?>
    <div style="margin-bottom: 5px">
    <?php echo __('ยังไม่ได้ลงทะเบียน?'); ?> <a href="account.php?do=create"><?php echo __('สร้างแอคเคาท์ใหม่'); ?></a>
    </div>
<?php } ?>
    <div>
    </div>
    </div>
</div>
</form>
<br>
<p>
<?php if ($cfg && !$cfg->isClientLoginRequired()) {
    echo sprintf(__('ถ้านี่ึคือครั้งแรกในการใช้บริการของท่าน กรุณา %s เข้าสู่ระบบ %s ก่อนสร้างคำขอใช้บริการครับ'),
        '<a href="open.php">', '</a>');
} ?>
</p>
