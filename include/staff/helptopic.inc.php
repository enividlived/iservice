<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('ปฏิเสธการเข้าถึง');
$info=array();
$qstr='';
if($topic && $_REQUEST['a']!='add') {
    $title=__('อัพเดทบริการ');
    $action='update';
    $submit_text=__('บันทึก');
    $info=$topic->getInfo();
    $info['id']=$topic->getId();
    $info['pid']=$topic->getPid();
    $qstr.='&id='.$topic->getId();
} else {
    $title=__('สร้างบริการใหม่');
    $action='create';
    $submit_text=__('สร้างบริการ');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $info['ispublic']=isset($info['ispublic'])?$info['ispublic']:1;
    $info['form_id'] = Topic::FORM_USE_PARENT;
    $qstr.='&a='.$_REQUEST['a'];
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="helptopics.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?php echo __('บริการ');?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><?php echo __('ข้อมูลบริการ');?>
                &nbsp;<i class="help-tip icon-question-sign" href="#help_topic_information"></i></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
               <?php echo __('บริการ');?>:
            </td>
            <td>
                <input type="text" size="30" name="topic" value="<?php echo $info['topic']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['topic']; ?></span> <i class="help-tip icon-question-sign" href="#topic"></i>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo __('สถานะ');?>:
            </td>
            <td>
                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>><?php echo __('เปิดใช้งาน'); ?>
                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>><?php echo __('ปิดใช้งาน'); ?>
                &nbsp;<span class="error">*&nbsp;</span> <i class="help-tip icon-question-sign" href="#status"></i>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo __('ประเภท');?>:
            </td>
            <td>
                <input type="radio" name="ispublic" value="1" <?php echo $info['ispublic']?'checked="checked"':''; ?>><?php echo __('สาธารณะ'); ?>
                <input type="radio" name="ispublic" value="0" <?php echo !$info['ispublic']?'checked="checked"':''; ?>><?php echo __('ส่วนตัว/ภายใน'); ?>
                &nbsp;<span class="error">*&nbsp;</span> <i class="help-tip icon-question-sign" href="#type"></i>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('หัวข้อใหญ่');?>:
            </td>
            <td>
                <select name="topic_pid">
                    <option value="">&mdash; <?php echo __('บริการบนสุด'); ?> &mdash;</option><?php
                    $topics = Topic::getAllHelpTopics();
                    while (list($id,$topic) = each($topics)) {
                        if ($id == $info['topic_id'])
                            continue; ?>
                        <option value="<?php echo $id; ?>"<?php echo ($info['topic_pid']==$id)?'selected':''; ?>><?php echo $topic; ?></option>
                    <?php
                    } ?>
                </select> <i class="help-tip icon-question-sign" href="#parent_topic"></i>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['pid']; ?></span>
            </td>
        </tr>

        <tr><th colspan="2"><em><?php echo __('การตั้งค่าบริการ');?></em></th></tr>
        <tr>
            <td><strong><?php echo __('ฟอร์มแบบกำหนดเอง'); ?></strong>:</td>
           <td><select name="form_id">
                <option value="0" <?php
if ($info['form_id'] == '0') echo 'selected="selected"';
                    ?>>&mdash; <?php echo __('ไม่มี'); ?> &mdash;</option>
                <option value="<?php echo Topic::FORM_USE_PARENT; ?>"  <?php
if ($info['form_id'] == Topic::FORM_USE_PARENT) echo 'selected="selected"';
                    ?>>&mdash; <?php echo __('ใช้ฟอร์มจากหัวข้อใหญ่'); ?> &mdash;</option>
               <?php foreach (DynamicForm::objects()->filter(array('type'=>'G')) as $group) { ?>
                <option value="<?php echo $group->get('id'); ?>"
                       <?php if ($group->get('id') == $info['form_id'])
                            echo 'selected="selected"'; ?>>
                       <?php echo $group->get('title'); ?>
                   </option>
               <?php } ?>
               </select>
               &nbsp;<span class="error">&nbsp;<?php echo $errors['form_id']; ?></span>
               <i class="help-tip icon-question-sign" href="#custom_form"></i>
           </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo __('แผนก'); ?>:
            </td>
            <td>
                <select name="dept_id">
                    <option value="0">&mdash; <?php echo __('ค่าเริ่มต้นระบบ'); ?> &mdash;</option>
                    <?php
                    $sql='SELECT dept_id,dept_name FROM '.DEPT_TABLE.' dept ORDER by dept_name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$name)=db_fetch_row($res)){
                            $selected=($info['dept_id'] && $id==$info['dept_id'])?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['dept_id']; ?></span>
                <i class="help-tip icon-question-sign" href="#department"></i>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('สถานะ'); ?>:
            </td>
            <td>
                <span>
                <select name="status_id">
                    <option value="">&mdash; <?php echo __('ค่าเริ่มต้นระบบ'); ?> &mdash;</option>
                    <?php
                    foreach (TicketStatusList::getStatuses(array('states'=>array('open'))) as $status) {
                        $name = $status->getName();
                        if (!($isenabled = $status->isEnabled()))
                            $name.=' '.__('(disabled)');

                        echo sprintf('<option value="%d" %s %s>%s</option>',
                                $status->getId(),
                                ($info['status_id'] == $status->getId())
                                 ? 'selected="selected"' : '',
                                 $isenabled ? '' : 'disabled="disabled"',
                                 $name
                                );
                    }
                    ?>
                </select>
                &nbsp;
                <span class="error"><?php echo $errors['status_id']; ?></span>
                <i class="help-tip icon-question-sign" href="#status"></i>
                </span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('ความสำคัญ'); ?>:
            </td>
            <td>
                <select name="priority_id">
                    <option value="">&mdash; <?php echo __('ค่าเริ่มต้นระบบ'); ?> &mdash;</option>
                    <?php
                    $sql='SELECT priority_id,priority_desc FROM '.PRIORITY_TABLE.' pri ORDER by priority_urgency DESC';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$name)=db_fetch_row($res)){
                            $selected=($info['priority_id'] && $id==$info['priority_id'])?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['priority_id']; ?></span>
                <i class="help-tip icon-question-sign" href="#priority"></i>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('แผน SLA');?>:
            </td>
            <td>
                <select name="sla_id">
                    <option value="0">&mdash; <?php echo __("ค่าเริ่มต้นของแผนก");?> &mdash;</option>
                    <?php
                    if($slas=SLA::getSLAs()) {
                        foreach($slas as $id =>$name) {
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id, ($info['sla_id']==$id)?'selected="selected"':'',$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['sla_id']; ?></span>
                <i class="help-tip icon-question-sign" href="#sla_plan"></i>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo __('หน้าขอบคุณ'); ?>:</td>
            <td>
                <select name="page_id">
                    <option value="">&mdash; <?php echo __('ค่าเริ่มต้นระบบ'); ?> &mdash;</option>
                    <?php
                    if(($pages = Page::getActiveThankYouPages())) {
                        foreach($pages as $page) {
                            if(strcasecmp($page->getType(), 'thank-you')) continue;
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $page->getId(),
                                    ($info['page_id']==$page->getId())?'selected="selected"':'',
                                    $page->getName());
                        }
                    }
                    ?>
                </select>&nbsp;<font class="error"><?php echo $errors['page_id']; ?></font>
                <i class="help-tip icon-question-sign" href="#thank_you_page"></i>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('มอบหมายอัตโนมัติ');?>:
            </td>
            <td>
                <select name="assign">
                    <option value="0">&mdash; <?php echo __('ไม่ได้มอบหมาย'); ?> &mdash;</option>
                    <?php
                    if (($users=Staff::getStaffMembers())) {
                        echo sprintf('<OPTGROUP label="%s">', sprintf(__('เจ้าหน้าที่ (%d)'), count($user)));
                        foreach ($users as $id => $name) {
                            $name = new PersonsName($name);
                            $k="s$id";
                            $selected = ($info['assign']==$k || $info['staff_id']==$id)?'selected="selected"':'';
                            ?>
                            <option value="<?php echo $k; ?>"<?php echo $selected; ?>><?php echo $name; ?></option>

                        <?php
                        }
                        echo '</OPTGROUP>';
                    }
                    $sql='SELECT team_id, name, isenabled FROM '.TEAM_TABLE.' ORDER BY name';
                    if(($res=db_query($sql)) && ($cteams = db_num_rows($res))) {
                        echo sprintf('<OPTGROUP label="%s">', sprintf(__('Teams (%d)'), $cteams));
                        while (list($id, $name, $isenabled) = db_fetch_row($res)){
                            $k="t$id";
                            $selected = ($info['assign']==$k || $info['team_id']==$id)?'selected="selected"':'';

                            if (!$isenabled)
                                $name .= ' '.__('(disabled)');
                            ?>
                            <option value="<?php echo $k; ?>"<?php echo $selected; ?>><?php echo $name; ?></option>
                        <?php
                        }
                        echo '</OPTGROUP>';
                    }
                    ?>
                </select>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['assign']; ?></span>
                <i class="help-tip icon-question-sign" href="#auto_assign_to"></i>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('ตอบกลับอัตโนมัติ'); ?>:
            </td>
            <td>
                <input type="checkbox" name="noautoresp" value="1" <?php echo $info['noautoresp']?'checked="checked"':''; ?> >
                    <?php echo __('<strong>ปิดใช้งาน</strong> การตอบกลับอัตโนมัติเมื่อสร้างคำขอใช้บริการ'); ?>
                    <i class="help-tip icon-question-sign" href="#ticket_auto_response"></i>
            </td>
        </tr>
        <tr>
            <td>
                <?php echo __('รูปแบบลำดับคำขอใช้บริการ'); ?>:
            </td>
            <td>
                <label>
                <input type="radio" name="custom-numbers" value="0" <?php echo !$info['custom-numbers']?'checked="checked"':''; ?>
                    onchange="javascript:$('#custom-numbers').hide();"> <?php echo __('ค่าเริ่มต้นระบบ'); ?>
                </label>&nbsp;<label>
                <input type="radio" name="custom-numbers" value="1" <?php echo $info['custom-numbers']?'checked="checked"':''; ?>
                    onchange="javascript:$('#custom-numbers').show(200);"> <?php echo __('กำหนดเอง'); ?>
                </label>&nbsp; <i class="help-tip icon-question-sign" href="#custom_numbers"></i>
            </td>
        </tr>
    </tbody>
    <tbody id="custom-numbers" style="<?php if (!$info['custom-numbers']) echo 'display:none'; ?>">
        <tr>
            <td style="padding-left:20px">
                <?php echo __('รูปแบบ'); ?>:
            </td>
            <td>
                <input type="text" name="number_format" value="<?php echo $info['number_format']; ?>"/>
                <span class="faded"><?php echo __('เช่น'); ?> <span id="format-example"><?php
                    if ($info['custom-numbers']) {
                        if ($info['sequence_id'])
                            $seq = Sequence::lookup($info['sequence_id']);
                        if (!isset($seq))
                            $seq = new RandomSequence();
                        echo $seq->current($info['number_format']);
                    } ?></span></span>
                <div class="error"><?php echo $errors['number_format']; ?></div>
            </td>
        </tr>
        <tr>
<?php $selected = 'selected="selected"'; ?>
            <td style="padding-left:20px">
                <?php echo __('การเรียงลำดับ'); ?>:
            </td>
            <td>
                <select name="sequence_id">
                <option value="0" <?php if ($info['sequence_id'] == 0) echo $selected;
                    ?>>&mdash; <?php echo __('แบบสุ่ม'); ?> &mdash;</option>
<?php foreach (Sequence::objects() as $s) { ?>
                <option value="<?php echo $s->id; ?>" <?php
                    if ($info['sequence_id'] == $s->id) echo $selected;
                    ?>><?php echo $s->name; ?></option>
<?php } ?>
                </select>
                <button class="action-button pull-right" onclick="javascript:
                $.dialog('ajax.php/sequence/manage', 205);
                return false;
                "><i class="icon-gear"></i> <?php echo __('จัดการ'); ?></button>
            </td>
        </tr>
    </tbody>
    <tbody>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('บันทึกภายใน');?></strong>: <?php echo __("บันทึกภายในสำหรับเจ้าหน้าที่");?></em>
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
    <input type="button" name="cancel" value="<?php echo __('ยกเลิก');?>" onclick='window.location.href="helptopics.php"'>
</p>
</form>
<script type="text/javascript">
$(function() {
    var request = null,
      update_example = function() {
      request && request.abort();
      request = $.get('ajax.php/sequence/'
        + $('[name=sequence_id] :selected').val(),
        {'format': $('[name=number_format]').val()},
        function(data) { $('#format-example').text(data); }
      );
    };
    $('[name=sequence_id]').on('change', update_example);
    $('[name=number_format]').on('keyup', update_example);
});
</script>
