<?php

if(!defined('OSTCLIENTINC') || !$thisclient || !$ticket || !$ticket->checkUserAccess($thisclient)) die('ปฏิเสธการเข้าถึง');

?>

<h1>
    <?php echo sprintf(__('แก้ไขคำขอใช้บริการที่ %s'), $ticket->getNumber()); ?>
</h1>

<form action="tickets.php" method="post">
    <?php echo csrf_token(); ?>
    <input type="hidden" name="a" value="edit"/>
    <input type="hidden" name="id" value="<?php echo Format::htmlchars($_REQUEST['id']); ?>"/>
<table width="800">
    <tbody id="dynamic-form">
    <?php if ($forms)
        foreach ($forms as $form) {
            $form->render(false);
    } ?>
    </tbody>
</table>
<hr>
<p style="text-align: center;">
    <input type="submit" value="บันทึก"/>
    <input type="reset" value="รีเซ็ต"/>
    <input type="button" value="ยกเลิก" onclick="javascript:
        window.location.href='index.php';"/>
</p>
</form>
