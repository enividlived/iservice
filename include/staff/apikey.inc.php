<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('ปฏิเสธการเข้าถึง');

$info=array();
$qstr='';
if($api && $_REQUEST['a']!='add'){
    $title=__('ปรับปรุงรหัส API');
    $action='update';
    $submit_text=__('บันทึกข้อมูล');
    $info=$api->getHashtable();
    $qstr.='&id='.$api->getId();
}else {
    $title=__('สร้างรหัส API');
    $action='add';
    $submit_text=__('สร้างรหัส');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $qstr.='&a='.urlencode($_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="apikeys.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?php echo __('รหัส API');?>
    <i class="help-tip icon-question-sign" href="#api_key"></i>
    </h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><?php echo __('รหัส API จะสุ่มสร้างอัตโนมัติ ลบหรือสร้างใหม่เพื่อเปลี่ยนรหัส');?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="150" class="required">
                <?php echo __('สถานะ');?>:
            </td>
            <td>
                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>><strong><?php echo __('ใช้งาน');?></strong>
                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>><?php echo __('ปิดการใช้งาน');?>
                &nbsp;<span class="error">*&nbsp;</span>
            </td>
        </tr>
        <?php if($api){ ?>
        <tr>
            <td width="150">
                <?php echo __('หมายเลข IP');?>:
            </td>
            <td>
                <span>
                <?php echo $api->getIPAddr(); ?>
                <i class="help-tip icon-question-sign" href="#ip_addr"></i>
                </span>
            </td>
        </tr>
        <tr>
            <td width="150">
                <?php echo __('รหัส API');?>:
            </td>
            <td><?php echo $api->getKey(); ?> &nbsp;</td>
        </tr>
        <?php }else{ ?>
        <tr>
            <td width="150" class="required">
               <?php echo __('หมายเลข IP');?>:
            </td>
            <td>
                <span>
                <input type="text" size="30" name="ipaddr" value="<?php echo $info['ipaddr']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['ipaddr']; ?></span>
                <i class="help-tip icon-question-sign" href="#ip_addr"></i>
                </span>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('เซอร์วิส');?>:</strong> <?php echo __('เลือกบริการที่รหัส API สามารถเข้าถึงได้');?></em>
            </th>
        </tr>
        <tr>
            <td colspan=2 style="padding-left:5px">
                <label>
                    <input type="checkbox" name="can_create_tickets" value="1" <?php echo $info['can_create_tickets']?'checked="checked"':''; ?> >
                    <?php echo __('สามารถสร้างคำขอใช้บริการ<em>(XML/JSON/EMAIL)</em>');?>
                </label>
            </td>
        </tr>
        <tr>
            <td colspan=2 style="padding-left:5px">
                <label>
                    <input type="checkbox" name="can_exec_cron" value="1" <?php echo $info['can_exec_cron']?'checked="checked"':''; ?> >
                    <?php echo __('สามารถใช้คำสั่ง Cron');?>
                </label>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('บันทึกผู้ดูแลระบบ');?></strong>: <?php echo __('บันทึกภายใน');?>&nbsp;</em>
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
<p style="padding-left:225px;">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="<?php echo __('รีเซ็ต');?>">
    <input type="button" name="cancel" value="<?php echo __('ยกเลิก');?>" onclick='window.location.href="apikeys.php"'>
</p>
</form>
