<?php
if(!defined('OSTSCPINC') || !$thisstaff || !$thisstaff->canManageFAQ()) die('ปฏิเสธการเข้าถึง');
$info=array();
$qstr='';
if($faq){
    $title=__('อัพเดทบทความ').': '.$faq->getQuestion();
    $action='update';
    $submit_text=__('บันทึก');
    $info=$faq->getHashtable();
    $info['id']=$faq->getId();
    $info['topics']=$faq->getHelpTopicsIds();
    $info['answer']=Format::viewableImages($faq->getAnswer());
    $info['notes']=Format::viewableImages($faq->getNotes());
    $qstr='id='.$faq->getId();
}else {
    $title=__('สร้างบทความใหม่');
    $action='create';
    $submit_text=__('สร้างบทความ');
    if($category) {
        $qstr='cid='.$category->getId();
        $info['category_id']=$category->getId();
    }
}
//TODO: Add attachment support.
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="faq.php?<?php echo $qstr; ?>" method="post" id="save" enctype="multipart/form-data">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?php echo __('คำถามที่พบบ่อย');?></h2>
 <table class="form_table fixed" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr><td></td><td></td></tr> <!-- For fixed table layout -->
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th colspan="2">
                <em><?php echo __('ข้อมูลคำถามที่พบบ่อย');?></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <div style="padding-top:3px;"><b><?php echo __('คำถาม');?></b>&nbsp;<span class="error">*&nbsp;<?php echo $errors['question']; ?></span></div>
                    <input type="text" size="70" name="question" value="<?php echo $info['question']; ?>">
            </td>
        </tr>
        <tr>
            <td colspan=2>
                <div><b><?php echo __('หัวข้อหลักที่เกี่ยวข้อง');?></b>:&nbsp;<span class="faded"><?php echo __('หัวข้อหลักที่บทความนี้มีส่วนเกี่ยวข้อง');?></span></div>
                <select name="category_id" style="width:350px;">
                    <option value="0"><?php echo __('เลือกหัวข้อหลัก');?> </option>
                    <?php
                    $sql='SELECT category_id, name, ispublic FROM '.FAQ_CATEGORY_TABLE;
                    if(($res=db_query($sql)) && db_num_rows($res)) {
                        while($row=db_fetch_array($res)) {
                            echo sprintf('<option value="%d" %s>%s (%s)</option>',
                                    $row['category_id'],
                                    (($info['category_id']==$row['category_id'])?'selected="selected"':''),
                                    $row['name'],
                                    ($info['ispublic']?__('สาธารณะ'):__('ภายใน')));
                        }
                    }
                   ?>
                </select>
                <span class="error">*&nbsp;<?php echo $errors['category_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td colspan=2>
                <div><b><?php echo __('ประเภทบทความ');?></b>:
                &nbsp;<i class="help-tip icon-question-sign" href="#listing_type"></i></div>
                <input type="radio" name="ispublished" value="1" <?php echo $info['ispublished']?'checked="checked"':''; ?>><?php echo __('สาธารณะ (เผยแพร่)');?>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" name="ispublished" value="0" <?php echo !$info['ispublished']?'checked="checked"':''; ?>><?php echo __('ภายใน (ส่วนตัว)');?>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['ispublished']; ?></span>
            </td>
        </tr>
        <tr>
            <td colspan=2>
                <div style="margin-bottom:0.5em;margin-top:0.5em">
                    <b><?php echo __('คำตอบ');?></b>&nbsp;<font class="error">*&nbsp;<?php echo $errors['answer']; ?></font></div>
                </div>
                <textarea name="answer" cols="21" rows="12"
                    style="width:98%;" class="richtext draft"
                    data-draft-namespace="faq"
                    data-draft-object-id="<?php if (is_object($faq)) echo $faq->getId(); ?>"
                    ><?php echo $info['answer']; ?></textarea>
            </td>
        </tr>
        <tr>
            <td colspan=2>
                <div><h3><?php echo __('ไฟล์แนบ');?>
                    <span class="faded">(<?php echo __('ถ้ามี');?>)</span></h3>
                    <div class="error"><?php echo $errors['files']; ?></div>
                </div>
                <?php
                $attachments = $faq_form->getField('attachments');
                if ($faq && ($files=$faq->attachments->getSeparates())) {
                    $ids = array();
                    foreach ($files as $f)
                        $ids[] = $f['id'];
                    $attachments->value = $ids;
                }
                print $attachments->render(); ?>
                <br/>
            </td>
        </tr>
        <?php
        if ($topics = Topic::getAllHelpTopics()) { ?>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('บริการ');?></strong>: <?php echo __('เลือกบริการที่เกี่ยวข้องกับบทความนี้');?></em>
            </th>
        </tr>
        <tr><td colspan="2">
            <?php
            while (list($topicId,$topic) = each($topics)) {
                echo sprintf('<input type="checkbox" name="topics[]" value="%d" %s>%s<br>',
                        $topicId,
                        (($info['topics'] && in_array($topicId,$info['topics']))?'checked="checked"':''),
                        $topic);
            }
             ?>
            </td>
        </tr>
        <?php
        } ?>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('บันทึกภายใน');?></strong>: &nbsp;</em>
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
    <input type="reset"  name="reset"  value="<?php echo __('รีเซ็ต'); ?>" onclick="javascript:
        $(this.form).find('textarea.richtext')
            .redactor('deleteDraft');
        location.reload();" />
    <input type="button" name="cancel" value="<?php echo __('ยกเลิก'); ?>" onclick='window.location.href="faq.php?<?php echo $qstr; ?>"'>
</p>
</form>
