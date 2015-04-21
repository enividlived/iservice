<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('ปฏิเสธการเข้าถึง');
?>
<h2><?php echo __('ตั้งค่าการใช้งานบทความถาม-ตอบ');?></h2>
<form action="settings.php?t=kb" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="kb" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo __('ตั้งค่าบทความถาม-ตอบ');?></h4>
                <em><?php echo __("ปิดการใช้งานบทความถาม-ตอบสำหรับผู้ใช้");?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180"><?php echo __('ระบบบทความถาม-ตอบ'); ?>:</td>
            <td>
                <input type="checkbox" name="enable_kb" value="1" <?php echo $config['enable_kb']?'checked="checked"':''; ?>>
                <?php echo __('เปิดใช้งานระบบ'); ?>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['enable_kb']; ?></font>
                <i class="help-tip icon-question-sign" href="#knowledge_base_status"></i>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo __('คำตอบที่กำหนดไว้');?>:</td>
            <td>
                <input type="checkbox" name="enable_premade" value="1" <?php echo $config['enable_premade']?'checked="checked"':''; ?> >
                <?php echo __('เปิดใช้งานระบบ'); ?>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['enable_premade']; ?></font>
                <i class="help-tip icon-question-sign" href="#canned_responses"></i>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:210px;">
    <input class="button" type="submit" name="submit" value="<?php echo __('บันทึก'); ?>">
    <input class="button" type="reset" name="reset" value="<?php echo __('รีเซ็ต'); ?>">
</p>
</form>
