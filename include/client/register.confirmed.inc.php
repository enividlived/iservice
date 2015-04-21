<?php if ($content) {
    list($title, $body) = $ost->replaceTemplateVariables(
        array($content->getName(), $content->getBody())); ?>
<h1><?php echo Format::display($title); ?></h1>
<p><?php
echo Format::display($body); ?>
</p>
<?php } else { ?>
<h1><?php echo __('ลงทะเบียนเข้าสู่ระบบ'); ?></h1>
<p>
<strong><?php echo __('ขอบคุณที่ลงทะเบียนกับเรา'); ?></strong>
</p>
<p><?php echo __(
"คุณได้ทำการยืนยันการลงทะเบียนและเปิดใช้งานแอคเคาท์แล้ว คุณสามารถสร้างรวมถึงตรวจสอบสถานะของคำขอใช้บริการได้ทันที"
); ?>
</p>
<p><em><?php echo __('TRC&SKW iService'); ?></em></p>
<?php } ?>
