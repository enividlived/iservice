<?php
if(!defined('OSTSCPINC') || !$thisstaff) die('ปฏิเสธการเข้าถึง');
$info=array();
$qstr='';
if($canned && $_REQUEST['a']!='add'){
    $title=__('ปรับปรุงคำตอบสำเร็จรูป');
    $action='update';
    $submit_text=__('บันทึก');
    $info=$canned->getInfo();
    $info['id']=$canned->getId();
    $qstr.='&id='.$canned->getId();
    // Replace cid: scheme with downloadable URL for inline images
    $info['response'] = $canned->getResponseWithImages();
    $info['notes'] = Format::viewableImages($info['notes']);
}else {
    $title=__('เพิ่มคำตอบสำเร็จรูป');
    $action='create';
    $submit_text=__('เพิ่มคำตอบ');
    $info['isenabled']=isset($info['isenabled'])?$info['isenabled']:1;
    $qstr.='&a='.$_REQUEST['a'];
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);

?>
<form action="canned.php?<?php echo $qstr; ?>" method="post" id="save" enctype="multipart/form-data">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?php echo __('คำตอบสำเร็จรูป')?>
 &nbsp;<i class="help-tip icon-question-sign" href="#canned_response"></i></h2>
 <table class="form_table fixed" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr><td></td><td></td></tr> <!-- For fixed table layout -->
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><?php echo __('การตั้งค่าคำตอบสำเร็จรูป');?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required"><?php echo __('สถานะ');?>:</td>
            <td>
                <label><input type="radio" name="isenabled" value="1" <?php
                    echo $info['isenabled']?'checked="checked"':''; ?>>&nbsp;<?php echo __('เปิดใช้งาน'); ?>&nbsp;</label>
                <label><input type="radio" name="isenabled" value="0" <?php
                        echo !$info['isenabled']?'checked="checked"':''; ?>>&nbsp;<?php echo __('ปิดใช้งาน'); ?>&nbsp;</label>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['isenabled']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required"><?php echo __('แผนก');?>:</td>
            <td>
                <select name="dept_id">
                    <option value="0">&mdash; <?php echo __('ทุกแผนก');?> &mdash;</option>
                    <?php
                    $sql='SELECT dept_id, dept_name FROM '.DEPT_TABLE.' dept ORDER by dept_name';
                    if(($res=db_query($sql)) && db_num_rows($res)) {
                        while(list($id,$name)=db_fetch_row($res)) {
                            $selected=($info['dept_id'] && $id==$info['dept_id'])?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['dept_id']; ?></span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('คำตอบสำเร็จรูป');?></strong>: <?php echo __('กรุณากรอกรายละเอียดให้เข้าใจง่าย');?>&nbsp;</em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <div><b><?php echo __('เรื่อง');?></b><span class="error">*&nbsp;<?php echo $errors['title']; ?></span></div>
                <input type="text" size="70" name="title" value="<?php echo $info['title']; ?>">
                <br><br>
                <div style="margin-bottom:0.5em"><b><?php echo __('คำตอบสำเร็จรูป'); ?></b>
                    <font class="error">*&nbsp;<?php echo $errors['response']; ?></font>
                    &nbsp;&nbsp;&nbsp;(<a class="tip" href="#ticket_variables"><?php echo __('ตัวแปรที่ใช้งานได้'); ?></a>)
                    </div>
                <textarea name="response" class="richtext draft draft-delete" cols="21" rows="12"
                    data-draft-namespace="canned"
                    data-draft-object-id="<?php if (isset($canned)) echo $canned->getId(); ?>"
                    style="width:98%;" class="richtext draft"><?php
                        echo $info['response']; ?></textarea>
                <div><h3><?php echo __('ไฟล์แนบสำเร็จรูป'); ?> <?php echo __('(หากต้องการแนบไฟล์ไปพร้อมกับคำตอบสำเร็จรูป)'); ?>
                &nbsp;<i class="help-tip icon-question-sign" href="#canned_attachments"></i></h3>
                <div class="error"><?php echo $errors['files']; ?></div>
                </div>
                <?php
                $attachments = $canned_form->getField('attachments');
                if ($canned && ($files=$canned->attachments->getSeparates())) {
                    $ids = array();
                    foreach ($files as $f)
                        $ids[] = $f['id'];
                    $attachments->value = $ids;
                }
                print $attachments->render(); ?>
                <br/>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('บันทึกภายใน');?></strong>: <?php echo __('บันทึกย่อของคำตอบสำเร็จรูป');?>&nbsp;</em>
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
 <?php if ($canned && $canned->getFilters()) { ?>
    <br/>
    <div id="msg_warning"><?php echo __('คำตอบสำเร็จรูปกำลังถูกใช้งานโดยฟิลเตอร์อีเมล');?>: <?php
    echo implode(', ', $canned->getFilters()); ?></div>
 <?php } ?>
<p style="padding-left:225px;">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="<?php echo __('รีเซ็ต'); ?>" onclick="javascript:
        $(this.form).find('textarea.richtext')
            .redactor('deleteDraft');
        location.reload();" />
    <input type="button" name="cancel" value="<?php echo __('ยกเลิก'); ?>" onclick='window.location.href="canned.php"'>
</p>
</form>
