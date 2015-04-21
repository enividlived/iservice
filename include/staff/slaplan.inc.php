<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('ปฏิเสธการเข้าถึง');
$info=array();
$qstr='';
if($sla && $_REQUEST['a']!='add'){
$title=__('อัพเดทแผน SLA' /* SLA is abbreviation for Service Level Agreement */);
    $action='update';
    $submit_text=__('บันทึก');
    $info=$sla->getInfo();
    $info['id']=$sla->getId();
    $qstr.='&id='.$sla->getId();
}else {
$title=__('สร้างแผน SLA' /* SLA is abbreviation for Service Level Agreement */);
    $action='add';
    $submit_text=__('สร้างแผน');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $info['enable_priority_escalation']=isset($info['enable_priority_escalation'])?$info['enable_priority_escalation']:1;
    $info['disable_overdue_alerts']=isset($info['disable_overdue_alerts'])?$info['disable_overdue_alerts']:0;
    $qstr.='&a='.urlencode($_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="slas.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?php echo __('ข้อตกลงการให้บริการ');?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><?php echo __('คำขอใช้บริการจะถูกตั้งสถานะเป็นเลยกำหนด หากยังไม่เสร็จตามระยะเวลาในแผน');?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
              <?php echo __('ชื่อ');?>:
            </td>
            <td>
                <input type="text" size="30" name="name" value="<?php echo $info['name']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['name']; ?></span>&nbsp;<i class="help-tip icon-question-sign" href="#name"></i>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
              <?php echo __('ระยะเวลา');?>:
            </td>
            <td>
                <input type="text" size="10" name="grace_period" value="<?php echo $info['grace_period']; ?>">
                <em>( <?php echo __('เป็นชั่วโมง');?> )</em>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['grace_period']; ?></span>&nbsp;<i class="help-tip icon-question-sign" href="#grace_period"></i>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo __('สถานะ');?>:
            </td>
            <td>
                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>><strong><?php echo __('เปิดใช้งาน');?></strong>
                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>><?php echo __('ปิดใช้งาน');?>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['isactive']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('ไม่ตายตัว'); ?>:
            </td>
            <td>
                <input type="checkbox" name="transient" value="1" <?php echo $info['transient']?'checked="checked"':''; ?> >
                <?php echo __('แผน SLA สามารถถูกเขียนทับเมื่อโอนย้ายไปยังแผนกหรือหัวข้อบริการอื่น'); ?>
                &nbsp;<i class="help-tip icon-question-sign" href="#transient"></i>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('การแจ้งเตือนเมื่อเลยกำหนด');?>:
            </td>
            <td>
                <input type="checkbox" name="disable_overdue_alerts" value="1" <?php echo $info['disable_overdue_alerts']?'checked="checked"':''; ?> >
                    <?php echo __('<strong>เปิดใช้งาน</strong> การแจ้งเตือนคำขอใช้บริการเลยกำหนด'); ?>
                    <em><?php echo __('(เขียนทับค่าเริ่มต้นระบบ)'); ?></em>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('บันทึกผู้ดูแลระบบ');?></strong>: <?php echo __('บันทึกภายใน');?>
                &nbsp;&nbsp;<i class="help-tip icon-question-sign" href="#admin_notes"></i></em>
                </em>
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
    <input type="button" name="cancel" value="<?php echo __('ยกเลิก');?>" onclick='window.location.href="slas.php"'>
</p>
</form>
