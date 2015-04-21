<h2><?php echo __('ตั้งค่าการแจ้งเตือน'); ?>
    <i class="help-tip icon-question-sign" href="#page_title"></i></h2>
<form action="settings.php?t=alerts" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="alerts" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th>
                <h4><?php echo __('การแจ้งเตือนจากระบบที่ส่งไปยังเจ้าหน้าที่เมื่อเกิดเหตุการณ์ต่างๆ'); ?></h4>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr><th><em><b><?php echo __('คำขอใช้บริการใหม่'); ?></b>:
            <i class="help-tip icon-question-sign" href="#ticket_alert"></i>
            </em></th></tr>
        <tr>
            <td><em><b><?php echo __('สถานะ'); ?>:</b></em> &nbsp;
                <input type="radio" name="ticket_alert_active"  value="1"
                <?php echo $config['ticket_alert_active']?'checked':''; ?>
                /> <?php echo __('เปิดใช้งาน'); ?>
                <input type="radio" name="ticket_alert_active"  value="0"   <?php echo !$config['ticket_alert_active']?'checked':''; ?> />
                 <?php echo __('ปิดใช้งาน'); ?>
                &nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['ticket_alert_active']; ?></font></em>
             </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="ticket_alert_admin" <?php echo $config['ticket_alert_admin']?'checked':''; ?>>
                <?php echo __('อีเมลผู้ดูแลระบบ'); ?> <em>(<?php echo $cfg->getAdminEmail(); ?>)</em>
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="ticket_alert_dept_manager" <?php echo $config['ticket_alert_dept_manager']?'checked':''; ?>>
                <?php echo __('ผู้จัดการแผนก'); ?>
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="ticket_alert_dept_members" <?php echo $config['ticket_alert_dept_members']?'checked':''; ?>>
                <?php echo __('เจ้าหน้าที่แผนก'); ?>
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="ticket_alert_acct_manager" <?php echo $config['ticket_alert_acct_manager']?'checked':''; ?>>
                <?php echo __('ผู้จัดการบริษัท'); ?>
            </td>
        </tr>
        <tr><th><em><b><?php echo __('ข้อความใหม่'); ?></b>:
            <i class="help-tip icon-question-sign" href="#message_alert"></i>
            </em></th></tr>
        <tr>
            <td><em><b><?php echo __('สถานะ'); ?>:</b></em> &nbsp;
              <input type="radio" name="message_alert_active"  value="1"
              <?php echo $config['message_alert_active']?'checked':''; ?>
              /> <?php echo __('เปิดใช้งาน'); ?>
              &nbsp;&nbsp;
              <input type="radio" name="message_alert_active"  value="0"   <?php echo !$config['message_alert_active']?'checked':''; ?> />
              <?php echo __('ปิดใช้งาน'); ?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="message_alert_laststaff" <?php echo $config['message_alert_laststaff']?'checked':''; ?>>
                <?php echo __('ผู้ตอบล่าสุด'); ?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="message_alert_assigned" <?php
              echo $config['message_alert_assigned']?'checked':''; ?>>
              <?php echo __('เจ้าหน้าที่/ทีม ที่ได้รับมอบหมาย'); ?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="message_alert_dept_manager" <?php
              echo $config['message_alert_dept_manager']?'checked':''; ?>>
              <?php echo __('ผู้จัดการแผนก'); ?>
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="message_alert_acct_manager" <?php echo $config['message_alert_acct_manager']?'checked':''; ?>>
                <?php echo __('ผู้จัดการบริษัท'); ?>
            </td>
        </tr>
        <tr><th><em><b><?php echo __('บันทึกภายในใหม่'); ?></b>:
            <i class="help-tip icon-question-sign" href="#internal_note_alert"></i>
            </em></th></tr>
        <tr>
            <td><em><b><?php echo __('สถานะ'); ?>:</b></em> &nbsp;
              <input type="radio" name="note_alert_active"  value="1"   <?php echo $config['note_alert_active']?'checked':''; ?> />
                <?php echo __('เปิดใช้งาน'); ?>
              &nbsp;&nbsp;
              <input type="radio" name="note_alert_active"  value="0"   <?php echo !$config['note_alert_active']?'checked':''; ?> />
                <?php echo __('ปิดใช้งาน'); ?>
              &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['note_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="note_alert_laststaff" <?php echo
              $config['note_alert_laststaff']?'checked':''; ?>> <?php echo __('ผู้ตอบล่าสุด'); ?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="note_alert_assigned" <?php echo $config['note_alert_assigned']?'checked':''; ?>>
                <?php echo __('เจ้าหน้าที่/ทีม ที่ได้รับมอบหมาย'); ?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="note_alert_dept_manager" <?php echo $config['note_alert_dept_manager']?'checked':''; ?>>
                <?php echo __('ผู้จัดการแผนก'); ?>
            </td>
        </tr>
        <tr><th><em><b><?php echo __('มอบหมายคำขอใช้บริการ'); ?></b>:
            <i class="help-tip icon-question-sign" href="#assignment_alert"></i>
            </em></th></tr>
        <tr>
            <td><em><b><?php echo __('สถานะ'); ?>: </b></em> &nbsp;
              <input name="assigned_alert_active" value="1" type="radio"
                <?php echo $config['assigned_alert_active']?'checked="checked"':''; ?>> <?php echo __('เปิดใช้งาน'); ?>
              &nbsp;&nbsp;
              <input name="assigned_alert_active" value="0" type="radio"
                <?php echo !$config['assigned_alert_active']?'checked="checked"':''; ?>> <?php echo __('ปิดใช้งาน'); ?>
               &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['assigned_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="assigned_alert_staff" <?php echo
              $config['assigned_alert_staff']?'checked':''; ?>> <?php echo __('เจ้าหน้าที่/ทีม ที่ได้รับมอบหมาย'); ?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox"name="assigned_alert_team_lead" <?php
              echo $config['assigned_alert_team_lead']?'checked':''; ?>> <?php echo __('หัวหน้าทีม'); ?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox"name="assigned_alert_team_members" <?php echo $config['assigned_alert_team_members']?'checked':''; ?>>
                <?php echo __('สมาชิกทีม'); ?>
            </td>
        </tr>
        <tr><th><em><b><?php echo __('โอนย้ายคำขอใช้บริการ'); ?></b>:
            <i class="help-tip icon-question-sign" href="#transfer_alert"></i>
            </em></th></tr>
        <tr>
            <td><em><b><?php echo __('สถานะ'); ?>:</b></em> &nbsp;
            <input type="radio" name="transfer_alert_active"  value="1"   <?php echo $config['transfer_alert_active']?'checked':''; ?> />
                <?php echo __('เปิดใช้งาน'); ?>
            <input type="radio" name="transfer_alert_active"  value="0"   <?php echo !$config['transfer_alert_active']?'checked':''; ?> />
                <?php echo __('ปิดใช้งาน'); ?>
              &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['alert_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="transfer_alert_assigned" <?php echo $config['transfer_alert_assigned']?'checked':''; ?>>
                <?php echo __('เจ้าหน้าที่/ทีม ที่ได้รับมอบหมาย'); ?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="transfer_alert_dept_manager" <?php echo $config['transfer_alert_dept_manager']?'checked':''; ?>>
                <?php echo __('ผู้จัดการแผนก'); ?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="transfer_alert_dept_members" <?php echo $config['transfer_alert_dept_members']?'checked':''; ?>>
                <?php echo __('เจ้าหน้าที่แผนก'); ?>
            </td>
        </tr>
        <tr><th><em><b><?php echo __('คำขอใช้บริการเลยกำหนด'); ?></b>:
            <i class="help-tip icon-question-sign" href="#overdue_alert"></i>
            </em></th></tr>
        <tr>
            <td><em><b><?php echo __('สถานะ'); ?>:</b></em> &nbsp;
              <input type="radio" name="overdue_alert_active"  value="1"
                <?php echo $config['overdue_alert_active']?'checked':''; ?> /> <?php echo __('เปิดใช้งาน'); ?>
              <input type="radio" name="overdue_alert_active"  value="0"
                <?php echo !$config['overdue_alert_active']?'checked':''; ?> /> <?php echo __('ปิดใช้งาน'); ?>
              &nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['overdue_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="overdue_alert_assigned" <?php
                echo $config['overdue_alert_assigned']?'checked':''; ?>> <?php echo __('เจ้าหน้าที่/ทีม ที่ได้รับมอบหมาย'); ?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="overdue_alert_dept_manager" <?php
                echo $config['overdue_alert_dept_manager']?'checked':''; ?>> <?php echo __('ผู้จัดการแผนก'); ?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="overdue_alert_dept_members" <?php
                echo $config['overdue_alert_dept_members']?'checked':''; ?>> <?php echo __('เจ้าหน้าที่แผนก'); ?>
            </td>
        </tr>
        <tr><th>
            <em><b><?php echo __('แจ้งเตือนระบบ'); ?></b>: <i class="help-tip icon-question-sign" href="#system_alerts"></i></em></th></tr>
        <tr>
            <td>
              <input type="checkbox" name="send_sys_errors" checked="checked" disabled="disabled">
                <?php echo __('ข้อผิดพลาดระบบ'); ?>
              <em><?php echo __('(เปิดใช้อัตโนมัติ)'); ?></em>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="send_sql_errors" <?php echo $config['send_sql_errors']?'checked':''; ?>>
                <?php echo __('ข้อผิดพลาด SQL'); ?>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="send_login_errors" <?php echo $config['send_login_errors']?'checked':''; ?>>
                <?php echo __('พยายามเข้าสู่ระบบผิดพลาดมากเกินไป'); ?>
            </td>
        </tr>
    </tbody>
</table>
<p style="text-align:center;">
    <input class="button" type="submit" name="submit" value="<?php echo __('บันทึก'); ?>">
    <input class="button" type="reset" name="reset" value="<?php echo __('รีเซ็ต'); ?>">
</p>
</form>
