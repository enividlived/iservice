<h2><?php echo __('ตั้งค่าตอบกลับอัตโนมัติ'); ?></h2>
<form action="settings.php?t=autoresp" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="autoresp" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo __('ตั้งค่าตอบกลับอัตโนมัติ'); ?></h4>
                <em><?php echo __('เป็นการตั้งค่าทั้งระบบ - สามารถปิดการใช้งานได้ที่ระดับแผนกหรืออีเมล'); ?></em>
            </th>
        </tr>
    </thead>
    <tbody>

        <tr>
            <td width="160"><?php echo __('สร้างคำขอใช้บริการ'); ?>:</td>
            <td>
                <input type="checkbox" name="ticket_autoresponder" <?php
echo $config['ticket_autoresponder'] ? 'checked="checked"' : ''; ?>/>
                <?php echo __('ผู้สร้างคำขอใช้บริการ'); ?>&nbsp;
                <i class="help-tip icon-question-sign" href="#new_ticket"></i>
            </td>
        </tr>
        <tr>
            <td width="160"><?php echo __('คำขอใช้บริการสร้างโดยเจ้าหน้าที่'); ?>:</td>
            <td>
                <input type="checkbox" name="ticket_notice_active" <?php
echo $config['ticket_notice_active'] ? 'checked="checked"' : ''; ?>/>
                <?php echo __('ผู้สร้างคำขอใช้บริการ'); ?>&nbsp;
                <i class="help-tip icon-question-sign" href="#new_ticket_by_staff"></i>
            </td>
        </tr>
        <tr>
            <td width="160" rowspan="2"><?php echo __('ข้อความตอบกลับใหม่'); ?>:</td>
            <td>
                <input type="checkbox" name="message_autoresponder" <?php
echo $config['message_autoresponder'] ? 'checked="checked"' : ''; ?>/>
                <?php echo __('ผู้ส่ง: ส่งข้อความยืนยันเมื่อมีการตอบกลับคำขอใช้บริการ'); ?>&nbsp;
                <i class="help-tip icon-question-sign" href="#new_message_for_submitter"></i>
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="message_autoresponder_collabs" <?php
echo $config['message_autoresponder_collabs'] ? 'checked="checked"' : ''; ?>/>
                <?php echo __('ผู้เข้าร่วม: ส่งข้อความแจ้งเหตุการณ์'); ?>&nbsp;
                <i class="help-tip icon-question-sign" href="#new_message_for_participants"></i>
                </div>
            </td>
        </tr>
        <tr>
            <td width="160"><?php echo __('สร้างคำขอใช้บริการมากเกินกำหนด'); ?>:</td>
            <td>
                <input type="checkbox" name="overlimit_notice_active" <?php
echo $config['overlimit_notice_active'] ? 'checked="checked"' : ''; ?>/>
                <?php echo __('ผู้สร้างคำขอใช้บริการ'); ?>&nbsp;
                <i class="help-tip icon-question-sign" href="#overlimit_notice"></i>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:200px;">
    <input class="button" type="submit" name="submit" value="<?php echo __('บันทึก'); ?>">
    <input class="button" type="reset" name="reset" value="<?php echo __('รีเซ็ต'); ?>">
</p>
</form>
