<?php

$info=array();
if($plugin && $_REQUEST['a']!='add') {
    $config = $plugin->getConfig();
    if (!($page = $config->hasCustomConfig())) {
        if ($config)
            $form = $config->getForm();
        if ($form && $_POST)
            $form->isValid();
    }
    $title = __('อัพเดทปลั๊กอิน');
    $action = 'update';
    $submit_text = __('บันทึก');
    $info = $plugin->ht;
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>

<form action="?id=<?php echo urlencode($_REQUEST['id']); ?>" method="post" id="save">
    <?php csrf_token(); ?>
    <input type="hidden" name="do" value="<?php echo $action; ?>">
    <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
    <h2><?php echo __('จัดการปลั๊กอิน'); ?>
        <br/><small><?php echo $plugin->getName(); ?></small></h2>

    <h3><?php echo __('การตั้งค่า'); ?></h3>
<?php
if ($page)
    $config->renderCustomConfig();
elseif ($form) { ?>
    <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <tbody>
<?php $form->render(); ?>
    </tbody></table>
<?php
}
else { ?>
    <tr><th><?php echo __('ปลั๊กอินนี้ไม่สามารถปรับตั้งค่าได้'); ?><br>
        <em><?php echo __('ปลั๊กอินทุกอันน่าจะถูกทำให้ใช้งานได้ง่าย'); ?></em></th></tr>
<?php }
?>
<p class="centered">
<?php if ($page || $form) { ?>
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="<?php echo __('รีเซ็ต'); ?>">
<?php } ?>
    <input type="button" name="cancel" value="<?php echo __('ยกเลิก'); ?>" onclick='window.location.href="?"'>
</p>
</form>
