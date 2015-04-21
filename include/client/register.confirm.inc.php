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
<strong><?php echo __('ขอบคุณที่ลงทะเบียน'); ?></strong>
</p>
<p><?php echo __(
"เราได้ทำการส่งลิงค์ไปยังอีเมลที่คุณกำหนดแล้ว โปรดทำตามขั้นตอนในอีเมลเพื่อลงทะเบียนและเข้าสู่ระบบต่อไป"
); ?>
</p>
<?php } ?>
