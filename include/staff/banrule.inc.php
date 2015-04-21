<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('ปฏิเสธการใช้งาน');

$info=array();
$qstr='';
if($rule && $_REQUEST['a']!='add'){
    $title=__('อัพเดทกฎการแบน');
    $action='update';
    $submit_text=__('อัพเดท');
    $info=$rule->getInfo();
    $info['id']=$rule->getId();
    $qstr.='&id='.$rule->getId();
}else {
    $title=__('เพิ่มอีเมลใหม่ในรายชื่อที่ถูกแบน');
    $action='add';
    $submit_text=__('เพิ่ม');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $qstr.='&a='.urlencode($_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="banlist.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?php echo __('จัดการกฏการแบนอีเมล');?>
    <i class="help-tip icon-question-sign" href="#ban_list"></i>
    </h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><?php echo __('กรุณาระบุอีเมลแอดเดรสที่ถูกต้อง');?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
                <?php echo __('สถานะการแบน'); ?>:
            </td>
            <td>
                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>><strong><?php echo __('ใช้งาน');?></strong>
                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>><?php echo __('ปิดการใช้งาน');?>
                &nbsp;<span class="error">*&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo __('อีเมลแอดเดรส');?>:
            </td>
            <td>
                <input name="val" type="text" size="24" value="<?php echo $info['val']; ?>">
                 &nbsp;<span class="error">*&nbsp;<?php echo $errors['val']; ?></span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('บันทึกภายใน');?></strong>: <?php echo __('บันทึกผู้ดูแลระบบ');?>&nbsp;</em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea class="richtext no-bar" name="notes" cols="21"
                    rows="8" style="width: 80%;"><?php echo $info['notes']; ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="text-align:center;">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="<?php echo __('รีเซ็ต');?>">
    <input type="button" name="cancel" value="<?php echo __('ยกเลิก');?>" onclick='window.location.href="banlist.php"'>
</p>
</form>
