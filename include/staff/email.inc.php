<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('ปฏิเสธการเข้าถึง');
$info=array();
$qstr='';
if($email && $_REQUEST['a']!='add'){
    $title=__('อัพเดทอีเมล');
    $action='update';
    $submit_text=__('บันทึก');
    $info=$email->getInfo();
    $info['id']=$email->getId();
    if($info['mail_delete'])
        $info['postfetch']='delete';
    elseif($info['mail_archivefolder'])
        $info['postfetch']='archive';
    else
        $info['postfetch']=''; //nothing.
    if($info['userpass'])
        $passwdtxt=__('หากต้องการเปลี่ยนรหัสผ่าน กรุณากรอกรหัสผ่านใหม่ที่ต้องการด้านบน');

    $qstr.='&id='.$email->getId();
}else {
    $title=__('เพิ่มอีเมลใหม่');
    $action='create';
    $submit_text=__('ดำเนินการ');
    $info['ispublic']=isset($info['ispublic'])?$info['ispublic']:1;
    $info['ticket_auto_response']=isset($info['ticket_auto_response'])?$info['ticket_auto_response']:1;
    $info['message_auto_response']=isset($info['message_auto_response'])?$info['message_auto_response']:1;
    if (!$info['mail_fetchfreq'])
        $info['mail_fetchfreq'] = 5;
    if (!$info['mail_fetchmax'])
        $info['mail_fetchmax'] = 10;
    if (!isset($info['smtp_auth']))
        $info['smtp_auth'] = 1;
    $qstr.='&a='.$_REQUEST['a'];
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<h2><?php echo __('อีเมลแอดเดรส');?></h2>
<form action="emails.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><strong><?php echo __('ข้อมูลและการตั้งค่าอีเมล');?></strong></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
                <?php echo __('อีเมลแอดเดรส');?>
            </td>
            <td>
                <input type="text" size="35" name="email" value="<?php echo $info['email']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['email']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo __('ชื่ออีเมล');?>
            </td>
            <td>
                <input type="text" size="35" name="name" value="<?php echo $info['name']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['name']; ?>&nbsp;</span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('ตั้งค่าคำขอใช้บริการใหม่'); ?></strong></em>
            </th>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('แผนก');?>
            </td>
            <td>
        <span>
			<select name="dept_id">
			    <option value="0" selected="selected">&mdash; <?php
                echo __('ค่าเริ่มต้นระบบ'); ?> &mdash;</option>
			    <?php
			    $sql='SELECT dept_id, dept_name FROM '.DEPT_TABLE.' dept ORDER by dept_name';
			    if(($res=db_query($sql)) && db_num_rows($res)){
				while(list($id,$name)=db_fetch_row($res)){
				    $selected=($info['dept_id'] && $id==$info['dept_id'])?'selected="selected"':'';
				    echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
				}
			    }
			    ?>
			</select>
			<i class="help-tip icon-question-sign" href="#new_ticket_department"></i>
        </span>
			&nbsp;<span class="error"><?php echo $errors['dept_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('ความสำคัญ'); ?>
            </td>
            <td>
		<span>
			<select name="priority_id">
			    <option value="0" selected="selected">&mdash; <?php
                echo __('ค่าเริ่มต้นระบบ'); ?> &mdash;</option>
			    <?php
			    $sql='SELECT priority_id, priority_desc FROM '.PRIORITY_TABLE.' pri ORDER by priority_urgency DESC';
			    if(($res=db_query($sql)) && db_num_rows($res)){
				while(list($id,$name)=db_fetch_row($res)){
				    $selected=($info['priority_id'] && $id==$info['priority_id'])?'selected="selected"':'';
				    echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
				}
			    }
			    ?>
			</select>
			<i class="help-tip icon-question-sign" href="#new_ticket_priority"></i>
		</span>
		&nbsp;<span class="error"><?php echo $errors['priority_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('บริการ'); ?>
            </td>
            <td>
		<span>
			<select name="topic_id">
                <option value="0" selected="selected">&mdash; <?php echo __('ค่าเริ่มต้นระบบ'); ?> &mdash;</option>
			    <?php
                    $topics = Topic::getHelpTopics();
                    while (list($id,$topic) = each($topics)) { ?>
                        <option value="<?php echo $id; ?>"<?php echo ($info['topic_id']==$id)?'selected':''; ?>><?php echo $topic; ?></option>
                    <?php
                    } ?>
			</select>
			<i class="help-tip icon-question-sign" href="#new_ticket_help_topic"></i>
		</span>
                <span class="error">
			<?php echo $errors['topic_id']; ?>
		</span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('Auto-Response'); ?>
            </td>
            <td>
                <label><input type="checkbox" name="noautoresp" value="1" <?php echo $info['noautoresp']?'checked="checked"':''; ?> >
                <?php echo __('<strong>ปิดใช้งาน</strong> สำหรับอีเมลแอดเดรสนี้'); ?>
                </label>
                <i class="help-tip icon-question-sign" href="#auto_response"></i>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('ข้อมูลล็อคอินอีเมล'); ?></strong>
                &nbsp;<i class="help-tip icon-question-sign" href="#login_information"></i></em>
            </th>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('ชื่อผู้ใช้'); ?>
            </td>
            <td>
                <input type="text" size="35" name="userid" value="<?php echo $info['userid']; ?>"
                    autocomplete="off" autocorrect="off">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['userid']; ?>&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td width="180">
               <?php echo __('รหัสผ่าน'); ?>
            </td>
            <td>
                <input type="password" size="35" name="passwd" value="<?php echo $info['passwd']; ?>"
                    autocomplete="off">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['passwd']; ?>&nbsp;</span>
                <br><em><?php echo $passwdtxt; ?></em>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('ดึงอีเมลด้วย IMAP หรือ POP'); ?></strong>
                &nbsp;<i class="help-tip icon-question-sign" href="#mail_account"></i>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['mail']; ?></font></em>
            </th>
        </tr>
        <tr>
            <td><?php echo __('สถานะ'); ?></td>
            <td>
                <label><input type="radio" name="mail_active"  value="1"   <?php echo $info['mail_active']?'checked="checked"':''; ?> />&nbsp;<?php echo __('เปิดใช้งาน'); ?></label>
                &nbsp;&nbsp;
                <label><input type="radio" name="mail_active"  value="0"   <?php echo !$info['mail_active']?'checked="checked"':''; ?> />&nbsp;<?php echo __('ปิดใช้งาน'); ?></label>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['mail_active']; ?></font>
            </td>
        </tr>
        <tr><td><?php echo __('ชื่อโฮสต์'); ?></td>
            <td>
		<span>
			<input type="text" name="mail_host" size=35 value="<?php echo $info['mail_host']; ?>">
			&nbsp;<font class="error">&nbsp;<?php echo $errors['mail_host']; ?></font>
			<i class="help-tip icon-question-sign" href="#host_and_port"></i>
		</span>
            </td>
        </tr>
        <tr><td><?php echo __('หมายเลขพอร์ต'); ?></td>
            <td><input type="text" name="mail_port" size=6 value="<?php echo $info['mail_port']?$info['mail_port']:''; ?>">
		<span>
			&nbsp;<font class="error">&nbsp;<?php echo $errors['mail_port']; ?></font>
			<i class="help-tip icon-question-sign" href="#host_and_port"></i>
		</span>
            </td>
        </tr>
        <tr><td><?php echo __('โปรโตคอลกล่องอีเมล'); ?></td>
            <td>
		<span>
			<select name="mail_proto">
                <option value=''>&mdash; <?php __('เลือกโปรโตคอล'); ?> &mdash;</option>
<?php
    foreach (MailFetcher::getSupportedProtos() as $proto=>$desc) { ?>
                <option value="<?php echo $proto; ?>" <?php
                    if ($info['mail_proto'] == $proto) echo 'selected="selected"';
                    ?>><?php echo $desc; ?></option>
<?php } ?>
			</select>
			<font class="error">&nbsp;<?php echo $errors['mail_protocol']; ?></font>
			<i class="help-tip icon-question-sign" href="#protocol"></i>
		</span>
            </td>
        </tr>

        <tr><td><?php echo __('ความถี่ในการดึงอีเมล'); ?></td>
            <td>
		<span>
            <input type="text" name="mail_fetchfreq" size=4 value="<?php echo $info['mail_fetchfreq']?$info['mail_fetchfreq']:''; ?>"> <?php echo __('นาที'); ?>
			<i class="help-tip icon-question-sign" href="#fetch_frequency"></i>
			&nbsp;<font class="error">&nbsp;<?php echo $errors['mail_fetchfreq']; ?></font>
		</span>
            </td>
        </tr>
        <tr><td><?php echo __('อีเมลต่อการดึงในแต่ละครั้ง'); ?></td>
            <td>
		<span>
			<input type="text" name="mail_fetchmax" size=4 value="<?php echo
            $info['mail_fetchmax']?$info['mail_fetchmax']:''; ?>">
            <?php echo __('emails'); ?>
			<i class="help-tip icon-question-sign" href="#emails_per_fetch"></i>
			&nbsp;<font class="error">&nbsp;<?php echo $errors['mail_fetchmax']; ?></font>
		</span>
            </td>
        </tr>
        <tr><td valign="top"><?php echo __('อีเมลที่ถูกดึง');?></td>
             <td>
                <label><input type="radio" name="postfetch" value="archive" <?php echo ($info['postfetch']=='archive')? 'checked="checked"': ''; ?> >
                <?php echo __('ย้ายไปโฟลเดอร์'); ?>:
                <input type="text" name="mail_archivefolder" size="20" value="<?php echo $info['mail_archivefolder']; ?>"/></label>
                    &nbsp;<font class="error"><?php echo $errors['mail_folder']; ?></font>
                    <i class="help-tip icon-question-sign" href="#fetched_emails"></i>
                <br/>
                <label><input type="radio" name="postfetch" value="delete" <?php echo ($info['postfetch']=='delete')? 'checked="checked"': ''; ?> >
                <?php echo __('ลบอีเมล'); ?></label>
                <br/>
                <label><input type="radio" name="postfetch" value="" <?php echo (isset($info['postfetch']) && !$info['postfetch'])? 'checked="checked"': ''; ?> >
                <?php echo __('ไม่ต้องทำอะไร <em>(ไม่แนะนำ)</em>'); ?></label>
              <br /><font class="error"><?php echo $errors['postfetch']; ?></font>
            </td>
        </tr>

        <tr>
            <th colspan="2">
                <em><strong><?php echo __('ส่งอีเมลด้วย SMTP'); ?></strong>
                &nbsp;<i class="help-tip icon-question-sign" href="#smtp_settings"></i>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['smtp']; ?></font></em>
            </th>
        </tr>
        <tr><td><?php echo __('สถานะ');?></td>
            <td>
                <label><input type="radio" name="smtp_active" value="1" <?php echo $info['smtp_active']?'checked':''; ?> />&nbsp;<?php echo __('เปิดใช้งาน');?></label>
                &nbsp;
                <label><input type="radio" name="smtp_active" value="0" <?php echo !$info['smtp_active']?'checked':''; ?> />&nbsp;<?php echo __('ปิดใช้งาน');?></label>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['smtp_active']; ?></font>
            </td>
        </tr>
        <tr><td><?php echo __('ชื่อโฮสต์'); ?></td>
            <td><input type="text" name="smtp_host" size=35 value="<?php echo $info['smtp_host']; ?>">
                &nbsp;<font class="error"><?php echo $errors['smtp_host']; ?></font>
			<i class="help-tip icon-question-sign" href="#host_and_port"></i>
            </td>
        </tr>
        <tr><td><?php echo __('หมายเลขพอร์ต'); ?></td>
            <td><input type="text" name="smtp_port" size=6 value="<?php echo $info['smtp_port']?$info['smtp_port']:''; ?>">
                &nbsp;<font class="error"><?php echo $errors['smtp_port']; ?></font>
			<i class="help-tip icon-question-sign" href="#host_and_port"></i>
            </td>
        </tr>
        <tr><td><?php echo __('กรุณายืนยันตัวตน'); ?></td>
            <td>

                 <label><input type="radio" name="smtp_auth"  value="1"
                     <?php echo $info['smtp_auth']?'checked':''; ?> /> <?php echo __('ใช่'); ?></label>
                 &nbsp;
                 <label><input type="radio" name="smtp_auth"  value="0"
                     <?php echo !$info['smtp_auth']?'checked':''; ?> /> <?php echo __('ไม่ใช่'); ?></label>
                <font class="error">&nbsp;<?php echo $errors['smtp_auth']; ?></font>
            </td>
        </tr>
        <tr>
            <td><?php echo __('การปลอมเฮดเดอร์'); ?></td>
            <td>
                <label><input type="checkbox" name="smtp_spoofing" value="1" <?php echo $info['smtp_spoofing'] ?'checked="checked"':''; ?>>
                <?php echo __('อนุญาตสำหรับอีเมลนี้'); ?></label>
                <i class="help-tip icon-question-sign" href="#header_spoofing"></i>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('บันทึกภายใน');?></strong>: <?php
                echo __("บันทึกภายในสำหรับเจ้าหน้าที่");?> &nbsp;<span class="error">&nbsp;<?php echo $errors['notes']; ?></span></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea class="richtext no-bar" name="notes" cols="21"
                    rows="5" style="width: 60%;"><?php echo $info['notes']; ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="text-align:center;">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="<?php echo __('รีเซ็ต');?>">
    <input type="button" name="cancel" value="<?php echo __('ยกเลิก');?>" onclick='window.location.href="emails.php"'>
</p>
</form>
