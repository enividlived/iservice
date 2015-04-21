<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('ปฏิเสธการเข้าถึง');
$info=array();
$qstr='';
if($group && $_REQUEST['a']!='add'){
    $title=__('อัพเดทกลุ่ม');
    $action='update';
    $submit_text=__('บันทึก');
    $info=$group->getInfo();
    $info['id']=$group->getId();
    $info['depts']=$group->getDepartments();
    $qstr.='&id='.$group->getId();
}else {
    $title=__('สร้างกลุ่มใหม่');
    $action='create';
    $submit_text=__('สร้างกลุ่ม');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $info['can_create_tickets']=isset($info['can_create_tickets'])?$info['can_create_tickets']:1;
    $qstr.='&a='.$_REQUEST['a'];
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="groups.php?<?php echo $qstr; ?>" method="post" id="save" name="group">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?php echo __('สิทธิการเข้าถึงและดำเนินการ');?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><strong><?php echo __('ข้อมูลกลุ่ม');?></strong>: <?php echo __("กลุ่มที่ปิดการใช้งานจะเป็นการจำกัดสิทธิของเจ้าหน้าที่ ยกเว้นผู้ดูแลระบบ");?></em>
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
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['name']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo __('สถานะ');?>:
            </td>
            <td>
                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>><strong><?php echo __('เปิดใช้งาน');?></strong>
                &nbsp;
                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>><strong><?php echo __('ปิดใช้งาน');?></strong>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['status']; ?></span>
                <i class="help-tip icon-question-sign" href="#status"></i>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('สิทธิของกลุ่ม');?></strong>: <?php echo __('มีผลกับสมาชิกกลุ่มทุกคน');?>&nbsp;</em>
            </th>
        </tr>
        <tr><td><?php echo __('สามารถ <b>สร้าง</b> คำขอใช้บริการ');?></td>
            <td>
                <input type="radio" name="can_create_tickets"  value="1"   <?php echo $info['can_create_tickets']?'checked="checked"':''; ?> /><?php echo __('ได้');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_create_tickets"  value="0"   <?php echo !$info['can_create_tickets']?'checked="checked"':''; ?> /><?php echo __('ไม่ได้');?>
                &nbsp;&nbsp;<i><?php echo __('สามารถสร้างคำขอใช้บริการแทนผู้ใช้');?></i>
            </td>
        </tr>
        <tr><td><?php echo __('สามารถ <b>แก้ไข</b> คำขอใช้บริการ</td>');?>
            <td>
                <input type="radio" name="can_edit_tickets"  value="1"   <?php echo $info['can_edit_tickets']?'checked="checked"':''; ?> /><?php echo __('ได้');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_edit_tickets"  value="0"   <?php echo !$info['can_edit_tickets']?'checked="checked"':''; ?> /><?php echo __('ไม่ได้');?>
                &nbsp;&nbsp;<i><?php echo __('สามารถแก้ไขคำขอใช้บริการของผู้ใช้');?></i>
            </td>
        </tr>
        <tr><td><?php echo __('สามารถ <b>ตอบกลับ</b>');?></td>
            <td>
                <input type="radio" name="can_post_ticket_reply"  value="1"   <?php echo $info['can_post_ticket_reply']?'checked="checked"':''; ?> /><?php echo __('ได้');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_post_ticket_reply"  value="0"   <?php echo !$info['can_post_ticket_reply']?'checked="checked"':''; ?> /><?php echo __('ไม่ได้');?>
                &nbsp;&nbsp;<i><?php echo __('สามารถตอบกลับคำขอใช้บริการ');?></i>
            </td>
        </tr>
        <tr><td><?php echo __('สามารถ <b>ปิด</b> คำขอใช้บริการ');?></td>
            <td>
                <input type="radio" name="can_close_tickets"  value="1" <?php echo $info['can_close_tickets']?'checked="checked"':''; ?> /><?php echo __('ได้');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_close_tickets"  value="0" <?php echo !$info['can_close_tickets']?'checked="checked"':''; ?> /><?php echo __('ไม่ได้');?>
                &nbsp;&nbsp;<i><?php echo __('สามารถปิดคำขอใช้บริการ เจ้าหน้าที่ยังสามารถตอบกลับได้ตามปกติ');?></i>
            </td>
        </tr>
        <tr><td><?php echo __('สามารถ <b>มอบหมาย</b> คำขอใช้บริการ');?></td>
            <td>
                <input type="radio" name="can_assign_tickets"  value="1" <?php echo $info['can_assign_tickets']?'checked="checked"':''; ?> /><?php echo __('ได้');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_assign_tickets"  value="0" <?php echo !$info['can_assign_tickets']?'checked="checked"':''; ?> /><?php echo __('ไม่ได้');?>
                &nbsp;&nbsp;<i><?php echo __('สามารถมอบหมายคำขอใช้บริการให้เจ้าหน้าที่');?></i>
            </td>
        </tr>
        <tr><td><?php echo __('สามารถ <b>โอนย้าย</b> คำขอใช้บริการ');?></td>
            <td>
                <input type="radio" name="can_transfer_tickets"  value="1" <?php echo $info['can_transfer_tickets']?'checked="checked"':''; ?> /><?php echo __('ได้');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_transfer_tickets"  value="0" <?php echo !$info['can_transfer_tickets']?'checked="checked"':''; ?> /><?php echo __('ไม่ได้');?>
                &nbsp;&nbsp;<i><?php echo __('สามารถโอนย้ายคำขอใช้บริการไปยังแผนกอื่น');?></i>
            </td>
        </tr>
        <tr><td><?php echo __('สามารถ <b>ลบ</b> คำขอใช้บริการ');?></td>
            <td>
                <input type="radio" name="can_delete_tickets"  value="1"   <?php echo $info['can_delete_tickets']?'checked="checked"':''; ?> /><?php echo __('ได้');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_delete_tickets"  value="0"   <?php echo !$info['can_delete_tickets']?'checked="checked"':''; ?> /><?php echo __('ไม่ได้');?>
                &nbsp;&nbsp;<i><?php echo __("สามารถลบคำขอใช้บริการ (คำขอใช้บริการที่ถูกลบจะไม่สามารถกู้คืนได้!)");?></i>
            </td>
        </tr>
        <tr><td><?php echo __('สามารถแบนอีเมล');?></td>
            <td>
                <input type="radio" name="can_ban_emails"  value="1" <?php echo $info['can_ban_emails']?'checked="checked"':''; ?> /><?php echo __('ได้');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_ban_emails"  value="0" <?php echo !$info['can_ban_emails']?'checked="checked"':''; ?> /><?php echo __('ไม่ได้');?>
                &nbsp;&nbsp;<i><?php echo __('สามารถเพิ่ม/ลบอีเมล จากรายการอีเมลที่ถูกแบน');?></i>
            </td>
        </tr>
        <tr><td><?php echo __('สามารถจัดการข้อความที่กำหนดไว้');?></td>
            <td>
                <input type="radio" name="can_manage_premade"  value="1" <?php echo $info['can_manage_premade']?'checked="checked"':''; ?> /><?php echo __('ได้');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_manage_premade"  value="0" <?php echo !$info['can_manage_premade']?'checked="checked"':''; ?> /><?php echo __('ไม่ได้');?>
                &nbsp;&nbsp;<i><?php echo __('สามารถเพิ่ม/ปรับปรุง/ปิดใช้งาน/ลบ คำตอบและไฟล์แนบที่กำหนดไว้');?></i>
            </td>
        </tr>
        <tr><td><?php echo __('สามารถจัดการบทความถาม-ตอบ');?></td>
            <td>
                <input type="radio" name="can_manage_faq"  value="1" <?php echo $info['can_manage_faq']?'checked="checked"':''; ?> /><?php echo __('ได้');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_manage_faq"  value="0" <?php echo !$info['can_manage_faq']?'checked="checked"':''; ?> /><?php echo __('ไม่ได้');?>
                &nbsp;&nbsp;<i><?php echo __('สามารถเพิ่ม/ปรับปรุง/ปิดใช้งาน/ลบ หัวข้อและบทความถาม-ตอบ');?></i>
            </td>
        </tr>
        <tr><td><?php echo __('สามารถดูสถานะของเจ้าหน้าที่');?></td>
            <td>
                <input type="radio" name="can_view_staff_stats"  value="1" <?php echo $info['can_view_staff_stats']?'checked="checked"':''; ?> /><?php echo __('ได้');?>
                &nbsp;&nbsp;
                <input type="radio" name="can_view_staff_stats"  value="0" <?php echo !$info['can_view_staff_stats']?'checked="checked"':''; ?> /><?php echo __('ไม่ได้');?>
                &nbsp;&nbsp;<i><?php echo __('สามารถดูสถานะของเจ้าหน้าที่คนอื่นที่กำหนดไว้ในแผนก');?></i>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('แผนกที่เข้าถึงได้');?></strong>:
                <i class="help-tip icon-question-sign" href="#department_access"></i>
                &nbsp;<a id="selectAll" href="#deptckb"><?php echo __('เลือกทั้งหมด');?></a>
                &nbsp;&nbsp;
                <a id="selectNone" href="#deptckb"><?php echo __('ไม่เลือก');?></a></em>
            </th>
        </tr>
        <?php
         $sql='SELECT dept_id,dept_name FROM '.DEPT_TABLE.' ORDER BY dept_name';
         if(($res=db_query($sql)) && db_num_rows($res)){
            while(list($id,$name) = db_fetch_row($res)){
                $ck=($info['depts'] && in_array($id,$info['depts']))?'checked="checked"':'';
                echo sprintf('<tr><td colspan=2>&nbsp;&nbsp;<input type="checkbox" class="deptckb" name="depts[]" value="%d" %s>%s</td></tr>',$id,$ck,$name);
            }
         }
        ?>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('บันทึกย่อสำหรับผู้ดูแลระบบ');?></strong>: <?php echo __('บันทึกย่อที่ผู้ดูแลระบบสามารถมองเห็นได้ทุกคน');?>&nbsp;</em>
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
<p style="text-align:center">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="<?php echo __('รีเซ็ต');?>">
    <input type="button" name="cancel" value="<?php echo __('ยกเลิก');?>" onclick='window.location.href="groups.php"'>
</p>
</form>
