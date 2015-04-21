<h1><?php echo __('จัดการข้อมูลส่วนตัว'); ?></h1>
<p><?php echo __(
'ใช้แบบฟอร์มด้านล่างเพื่อปรับปรุงข้อมูลส่วนตัวของคุณ'
); ?>
</p>
<form action="profile.php" method="post">
  <?php csrf_token(); ?>
<table width="800" class="padded">
<?php
foreach ($user->getForms() as $f) {
    $f->render(false);
}
if ($acct = $thisclient->getAccount()) {
    $info=$acct->getInfo();
    $info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<tr>
    <td colspan="2">
        <div><hr><h3><?php echo __('การตั้งค่า'); ?></h3>
        </div>
    </td>
</tr>
    <td><?php echo __('เขตเวลา'); ?>:</td>
    <td>
        <select name="timezone_id" id="timezone_id">
            <option value="0">&mdash; <?php echo __('เลือกเขตเวลา'); ?> &mdash;</option>
            <?php
            $sql='SELECT id, offset,timezone FROM '.TIMEZONE_TABLE.' ORDER BY id';
            if(($res=db_query($sql)) && db_num_rows($res)){
                while(list($id,$offset, $tz)=db_fetch_row($res)){
                    $sel=($info['timezone_id']==$id)?'selected="selected"':'';
                    echo sprintf('<option value="%d" %s>GMT %s - %s</option>',$id,$sel,$offset,$tz);
                }
            }
            ?>
        </select>
        &nbsp;<span class="error"><?php echo $errors['timezone_id']; ?></span>
    </td>
</tr>
<tr>
    <td width="180">
        <?php echo __('เวลาออมแสง') ?>:
    </td>
    <td>
        <input type="checkbox" name="dst" value="1" <?php echo $info['dst']?'checked="checked"':''; ?>>
        <?php echo __('เปิดใช้การนับเวลาออมแสง'); ?>
        <em>(<?php __('เวลาปัจจุบัน'); ?>:
            <strong><?php echo Format::date($cfg->getDateTimeFormat(),Misc::gmtime(),$info['tz_offset'],$info['dst']); ?></strong>)</em>
    </td>
</tr>
    <tr>
        <td width="180">
            <?php echo __('ภาษาที่ต้องการ'); ?>:
        </td>
        <td>
    <?php
    $langs = Internationalization::availableLanguages(); ?>
            <select name="lang">
                <option value="">&mdash; <?php echo __('ใช้การตั้งค่าของเบราเซอร์'); ?> &mdash;</option>
<?php foreach($langs as $l) {
$selected = ($info['lang'] == $l['code']) ? 'selected="selected"' : ''; ?>
                <option value="<?php echo $l['code']; ?>" <?php echo $selected;
                    ?>><?php echo Internationalization::getLanguageDescription($l['code']); ?></option>
<?php } ?>
            </select>
            <span class="error">&nbsp;<?php echo $errors['lang']; ?></span>
        </td>
    </tr>
<?php if ($acct->isPasswdResetEnabled()) { ?>
<tr>
    <td colspan=2">
        <div><hr><h3><?php echo __('ข้อมูลเข้าสู่ระบบ'); ?></h3></div>
    </td>
</tr>
<?php if (!isset($_SESSION['_client']['reset-token'])) { ?>
<tr>
    <td width="180">
        <?php echo __('รหัสผ่านปัจจุบัน'); ?>:
    </td>
    <td>
        <input type="password" size="18" name="cpasswd" value="<?php echo $info['cpasswd']; ?>">
        &nbsp;<span class="error">&nbsp;<?php echo $errors['cpasswd']; ?></span>
    </td>
</tr>
<?php } ?>
<tr>
    <td width="180">
        <?php echo __('รหัสผ่านใหม่'); ?>:
    </td>
    <td>
        <input type="password" size="18" name="passwd1" value="<?php echo $info['passwd1']; ?>">
        &nbsp;<span class="error">&nbsp;<?php echo $errors['passwd1']; ?></span>
    </td>
</tr>
<tr>
    <td width="180">
        <?php echo __('รหัสผ่านใหม่อีกครั้ง'); ?>:
    </td>
    <td>
        <input type="password" size="18" name="passwd2" value="<?php echo $info['passwd2']; ?>">
        &nbsp;<span class="error">&nbsp;<?php echo $errors['passwd2']; ?></span>
    </td>
</tr>
<?php } ?>
<?php } ?>
</table>
<hr>
<p style="text-align: center;">
    <input type="submit" value="ปรับปรุง"/>
    <input type="reset" value="รีเซ็ต"/>
    <input type="button" value="ยกเลิก" onclick="javascript:
        window.location.href='index.php';"/>
</p>
</form>
