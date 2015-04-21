<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('ปฏิเสธการเข้าถึง');
?>
<h2><?php echo __('ตั้งค่าอีเมล');?></h2>
<form action="settings.php?t=emails" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="emails" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo __('ตั้งค่าอีเมล');?></h4>
                <em><?php echo __('โปรดจำไว้ว่าการตั้งค่าระบบบางส่วนสามารถถูกเขียนทับได้ที่การตั้งค่าระดับแผนกหรืออีเมล');?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required"><?php echo __('เท็มเพลตเริ่มต้น'); ?>:</td>
            <td>
                <select name="default_template_id">
                    <option value="">&mdash; <?php echo __('เลือกเท็มเพลตอีเมลเริ่มต้น'); ?> &mdash;</option>
                    <?php
                    $sql='SELECT tpl_id, name FROM '.EMAIL_TEMPLATE_GRP_TABLE
                        .' WHERE isactive =1 ORDER BY name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while (list($id, $name) = db_fetch_row($res)){
                            $selected = ($config['default_template_id']==$id)?'selected="selected"':''; ?>
                            <option value="<?php echo $id; ?>"<?php echo $selected; ?>><?php echo $name; ?></option>
                        <?php
                        }
                    } ?>
                </select>&nbsp;<font class="error">*&nbsp;<?php echo $errors['default_template_id']; ?></font>
                <i class="help-tip icon-question-sign" href="#default_email_templates"></i>
            </td>
        </tr>
        <tr>
            <td width="180" class="required"><?php echo __('อีเมลเริ่มต้นของระบบ');?>:</td>
            <td>
                <select name="default_email_id">
                    <option value=0 disabled><?php echo __('เลือกอย่างน้อยหนึ่งรายการ');?></option>
                    <?php
                    $sql='SELECT email_id,email,name FROM '.EMAIL_TABLE;
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while (list($id,$email,$name) = db_fetch_row($res)){
                            $email=$name?"$name &lt;$email&gt;":$email;
                            ?>
                            <option value="<?php echo $id; ?>"<?php echo ($config['default_email_id']==$id)?'selected="selected"':''; ?>><?php echo $email; ?></option>
                        <?php
                        }
                    } ?>
                 </select>
                 &nbsp;<font class="error">*&nbsp;<?php echo $errors['default_email_id']; ?></font>
                <i class="help-tip icon-question-sign" href="#default_system_email"></i>
            </td>
        </tr>
        <tr>
            <td width="180" class="required"><?php echo __('อีเมลแจ้งเตือนเริ่มต้น');?>:</td>
            <td>
                <select name="alert_email_id">
                    <option value="0" selected="selected"><?php echo __('ใช้ค่าจากอีเมลเริ่มต้นระบบ (ด้านบน)');?></option>
                    <?php
                    $sql='SELECT email_id,email,name FROM '.EMAIL_TABLE.' WHERE email_id != '.db_input($config['default_email_id']);
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while (list($id,$email,$name) = db_fetch_row($res)){
                            $email=$name?"$name &lt;$email&gt;":$email;
                            ?>
                            <option value="<?php echo $id; ?>"<?php echo ($config['alert_email_id']==$id)?'selected="selected"':''; ?>><?php echo $email; ?></option>
                        <?php
                        }
                    } ?>
                 </select>
                 &nbsp;<font class="error">*&nbsp;<?php echo $errors['alert_email_id']; ?></font>
                <i class="help-tip icon-question-sign" href="#default_alert_email"></i>
            </td>
        </tr>
        <tr>
            <td width="180" class="required"><?php echo __("อีเมลผู้ดูแลระบบ");?>:</td>
            <td>
                <input type="text" size=40 name="admin_email" value="<?php echo $config['admin_email']; ?>">
                    &nbsp;<font class="error">*&nbsp;<?php echo $errors['admin_email']; ?></font>
                <i class="help-tip icon-question-sign" href="#admins_email_address"></i>
            </td>
        </tr>
        <tr><th colspan=2><em><strong><?php echo __('อีเมลขาเข้า'); ?>:</strong>&nbsp;
            </em></th>
        <tr>
            <td width="180"><?php echo __('การดึงอีเมล'); ?>:</td>
            <td><input type="checkbox" name="enable_mail_polling" value=1 <?php echo $config['enable_mail_polling']? 'checked="checked"': ''; ?>>
                <?php echo __('เปิดใช้งาน'); ?>
                <i class="help-tip icon-question-sign" href="#email_fetching"></i>
                &nbsp;
                 <input type="checkbox" name="enable_auto_cron" <?php echo $config['enable_auto_cron']?'checked="checked"':''; ?>>
                <?php echo __('ดึงตามคำสั่ง cron'); ?>&nbsp;
                <i class="help-tip icon-question-sign" href="#enable_autocron_fetch"></i>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo __('ลบการอ้างอิงจากอีเมล');?>:</td>
            <td>
                <input type="checkbox" name="strip_quoted_reply" <?php echo $config['strip_quoted_reply'] ? 'checked="checked"':''; ?>>
                <?php echo __('เปิดใช้งาน'); ?>
                <i class="help-tip icon-question-sign" href="#strip_quoted_reply"></i>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['strip_quoted_reply']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo __('ตัวแบ่งข้อความตอบกลับ');?>:</td>
            <td><input type="text" name="reply_separator" value="<?php echo $config['reply_separator']; ?>">
                &nbsp;<font class="error">&nbsp;<?php echo $errors['reply_separator']; ?></font>&nbsp;<i class="help-tip icon-question-sign" href="#reply_separator_tag"></i>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo __('ความสำคัญกำหนดตามอีเมล'); ?>:</td>
            <td>
                <input type="checkbox" name="use_email_priority" value="1" <?php echo $config['use_email_priority'] ?'checked="checked"':''; ?>>
                &nbsp;<?php echo __('เปิดใช้งาน'); ?>&nbsp;
                <i class="help-tip icon-question-sign" href="#emailed_tickets_priority"></i>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo __('รับทุกอีเมล'); ?>:</td>
            <td><input type="checkbox" name="accept_unregistered_email" <?php
                echo $config['accept_unregistered_email'] ? 'checked="checked"' : ''; ?>/>
                <?php echo __('รับอีเมลจากผู้ใช้ที่ไม่มีชื่อในระบบ'); ?>
                <i class="help-tip icon-question-sign" href="#accept_all_emails"></i>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo __('รับผู้เข้าร่วมจากอีเมล'); ?>:</td>
            <td><input type="checkbox" name="add_email_collabs" <?php
            echo $config['add_email_collabs'] ? 'checked="checked"' : ''; ?>/>
            <?php echo __('รับผู้เข้าร่วมจากช่องอีเมล'); ?>&nbsp;
            <i class="help-tip icon-question-sign" href="#accept_email_collaborators"></i>
        </tr>
        <tr><th colspan=2><em><strong><?php echo __('อีเมลส่งออก');?></strong>: <?php echo __('อีเมลนี้จะถูกใช้งานต่อเมื่อทำการส่งอีเมลโดยไม่ตั้งค่า SMTP');?></em></th></tr>
        <tr><td width="180"><?php echo __('Default MTA'); ?>:</td>
            <td>
                <select name="default_smtp_id">
                    <option value=0 selected="selected"><?php echo __('ไม่มี: ใชัฟังชันต์ PHP mail');?></option>
                    <?php
                    $sql=' SELECT email_id, email, name, smtp_host '
                        .' FROM '.EMAIL_TABLE.' WHERE smtp_active = 1';
                    if(($res=db_query($sql)) && db_num_rows($res)) {
                        while (list($id, $email, $name, $host) = db_fetch_row($res)){
                            $email=$name?"$name &lt;$email&gt;":$email;
                            ?>
                            <option value="<?php echo $id; ?>"<?php echo ($config['default_smtp_id']==$id)?'selected="selected"':''; ?>><?php echo $email; ?></option>
                        <?php
                        }
                    } ?>
                 </select>&nbsp;<font class="error">&nbsp;<?php echo $errors['default_smtp_id']; ?></font>
                 <i class="help-tip icon-question-sign" href="#default_mta"></i>
           </td>
       </tr>
        <tr>
            <td width="180"><?php echo __('ไฟล์แนบ');?>:</td>
            <td>
                <input type="checkbox" name="email_attachments" <?php echo $config['email_attachments']?'checked="checked"':''; ?>>
                <?php echo __('ส่งอีเมลไฟล์แนบให้ผู้ใช้'); ?>
                <i class="help-tip icon-question-sign" href="#ticket_response_files"></i>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:250px;">
    <input class="button" type="submit" name="submit" value="<?php echo __('บันทึก');?>">
    <input class="button" type="reset" name="reset" value="<?php echo __('รีเซ็ต');?>">
</p>
</form>
