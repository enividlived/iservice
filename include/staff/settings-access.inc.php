<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('ปฏิเสธการเข้าถึง');

?>
<h2><?php echo __('ตั้งค่าการเข้าถึง'); ?></h2>
<form action="settings.php?t=access" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="access" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo __('ตั้งค่าการเข้าถึงระบบ iService'); ?></h4>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th colspan="2">
                <em><b><?php echo __('ตั้งค่าการเข้าสู่ระบบของเจ้าหน้าที่'); ?></b></em>
            </th>
        </tr>
        <tr><td><?php echo __('การหมดอายุของรหัสผ่าน'); ?>:</th>
            <td>
                <select name="passwd_reset_period">
                   <option value="0"> &mdash; <?php echo __('ไม่มีหมดอายุ'); ?> &mdash;</option>
                  <?php
                    for ($i = 1; $i <= 12; $i++) {
                        echo sprintf('<option value="%d" %s>%s</option>',
                                $i,(($config['passwd_reset_period']==$i)?'selected="selected"':''),
                                sprintf(_N('ทุกเดือน', 'ทุก %d เดือน', $i), $i));
                    }
                    ?>
                </select>
                <font class="error"><?php echo $errors['passwd_reset_period']; ?></font>
                <i class="help-tip icon-question-sign" href="#password_expiration_policy"></i>
            </td>
        </tr>
        <tr><td><?php echo __('สามารถรีเซ็ตรหัสผ่าน'); ?>:</th>
            <td>
              <input type="checkbox" name="allow_pw_reset" <?php echo $config['allow_pw_reset']?'checked="checked"':''; ?>>
              &nbsp;<i class="help-tip icon-question-sign" href="#allow_password_resets"></i>
            </td>
        </tr>
        <tr><td><?php echo __('ระยะเวลาหมดอายุของอีเมลรีเซ็ตรหัสผ่าน'); ?>:</th>
            <td>
              <input type="text" name="pw_reset_window" size="6" value="<?php
                    echo $config['pw_reset_window']; ?>">
                    <em><?php echo __('minutes'); ?></em>
                    <i class="help-tip icon-question-sign" href="#reset_token_expiration"></i>
                &nbsp;<font class="error"><?php echo $errors['pw_reset_window']; ?></font>
            </td>
        </tr>
        <tr><td><?php echo __('ความพยายามเข้าสู่ระบบของเจ้าหน้าที่'); ?>:</td>
            <td>
                <select name="staff_max_logins">
                  <?php
                    for ($i = 1; $i <= 10; $i++) {
                        echo sprintf('<option value="%d" %s>%d</option>', $i,(($config['staff_max_logins']==$i)?'selected="selected"':''), $i);
                    }
                    ?>
                </select> <?php echo __(
                'จำนวนครั้งการล็อคอินสูงสุด ก่อนโดนล็อคจากระบบ'); ?>
                <br/>
                <select name="staff_login_timeout">
                  <?php
                    for ($i = 1; $i <= 10; $i++) {
                        echo sprintf('<option value="%d" %s>%d</option>', $i,(($config['staff_login_timeout']==$i)?'selected="selected"':''), $i);
                    }
                    ?>
                </select> <?php echo __('นาที'); ?>
            </td>
        </tr>
        <tr><td><?php echo __('ระยะเวลาการเข้าสู่ระบบหมดอายุ'); ?>:</td>
            <td>
              <input type="text" name="staff_session_timeout" size=6 value="<?php echo $config['staff_session_timeout']; ?>">
                <?php echo __('นาที'); ?> <em><?php echo __('(กรอก 0 เพื่อปิดการใช้งาน)'); ?></em>. <i class="help-tip icon-question-sign" href="#staff_session_timeout"></i>
            </td>
        </tr>
        <tr><td><?php echo __('ล็อคเซสชั่นการล็อคอินเจ้าหน้าที่เข้ากับ IP'); ?>:</td>
            <td>
              <input type="checkbox" name="staff_ip_binding" <?php echo $config['staff_ip_binding']?'checked="checked"':''; ?>>
              <i class="help-tip icon-question-sign" href="#bind_staff_session_to_ip"></i>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><b><?php echo __('ตั้งค่าการเข้าสู่ระบบของผู้ใช้'); ?></b></em>
            </th>
        </tr>
        <tr><td><?php echo __('ต้องสมัครสมาชิก'); ?>:</td>
            <td><input type="checkbox" name="clients_only" <?php
                if ($config['clients_only'])
                    echo 'checked="checked"'; ?>/> <?php echo __(
                    'ต้องสมัครสมาชิกก่อนถึงจะสร้างคำขอใช้บริการได้'); ?>
            <i class="help-tip icon-question-sign" href="#registration_method"></i>
            </td>
        <tr><td><?php echo __('วิธีการสมัครสมาชิก'); ?>:</td>
            <td><select name="client_registration">
<?php foreach (array(
    'disabled' => __('ปิดการใช้งาน — ผู้ใช้ทุกคนเป็นแค่ผู้เยี่ยมชม'),
    'public' => __('สาธารณะ — ทุกคนสามารถสมัครสมาชิกได้'),
    'closed' => __('ส่วนตัว — ต้องให้เจ้าหน้าที่สมัครสมาชิกให้เท่านั้น'),)
    as $key=>$val) { ?>
        <option value="<?php echo $key; ?>" <?php
        if ($config['client_registration'] == $key)
            echo 'selected="selected"'; ?>><?php echo $val;
        ?></option><?php
    } ?>
            </select>
            <i class="help-tip icon-question-sign" href="#registration_method"></i>
            </td>
        </tr>
        <tr><td><?php echo __('ความพยายามเข้าสู่ระบบของผู้ใช้'); ?>:</td>
            <td>
                <select name="client_max_logins">
                  <?php
                    for ($i = 1; $i <= 10; $i++) {
                        echo sprintf('<option value="%d" %s>%d</option>', $i,(($config['client_max_logins']==$i)?'selected="selected"':''), $i);
                    }

                    ?>
                </select> <?php echo __(
                'จำนวนครั้งการล็อคอินสูงสุด ก่อนโดนล็อคจากระบบ'); ?>
                <br/>
                <select name="client_login_timeout">
                  <?php
                    for ($i = 1; $i <= 10; $i++) {
                        echo sprintf('<option value="%d" %s>%d</option>', $i,(($config['client_login_timeout']==$i)?'selected="selected"':''), $i);
                    }
                    ?>
                </select> <?php echo __('นาที'); ?>
            </td>
        </tr>
        <tr><td><?php echo __('ระยะเวลาการเข้าสู่ระบบหมดอายุ'); ?>:</td>
            <td>
              <input type="text" name="client_session_timeout" size=6 value="<?php echo $config['client_session_timeout']; ?>">
              <i class="help-tip icon-question-sign" href="#client_session_timeout"></i>
            </td>
        </tr>
        <tr><td><?php echo __('การเข้าถึงอย่างรวดเร็วสำหรับผู้ใช้'); ?>:</td>
            <td><input type="checkbox" name="client_verify_email" <?php
                if ($config['client_verify_email'])
                    echo 'checked="checked"'; ?>/> <?php echo __(
                'ต้องใช้อีเมลยืนยันเพื่อเข้าสู่หน้า "ตรวจสอบคำขอใช้บริการ" '); ?>
            <i class="help-tip icon-question-sign" href="#client_verify_email"></i>
            </td>
        </tr>
    </tbody>
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo __('เท็มเพลตการเข้าสู่ระบบและสมัครสมาชิก'); ?></h4>
            </th>
        </tr>
    </thead>
    <tbody>
<?php
$res = db_query('select distinct(`type`), content_id, notes, name, updated from '
    .PAGE_TABLE
    .' where isactive=1 group by `type`');
$contents = array();
while (list($type, $id, $notes, $name, $u) = db_fetch_row($res))
    $contents[$type] = array($id, $name, $notes, $u);

$manage_content = function($title, $content) use ($contents) {
    list($id, $name, $notes, $upd) = $contents[$content];
    $notes = explode('. ', $notes);
    $notes = $notes[0];
    ?><tr><td colspan="2">
    <a href="#ajax.php/content/<?php echo $id; ?>/manage"
    onclick="javascript:
        $.dialog($(this).attr('href').substr(1), 200);
    return false;"><i class="icon-file-text pull-left icon-2x"
        style="color:#bbb;"></i> <?php
    echo Format::htmlchars($title); ?></a><br/>
        <span class="faded" style="display:inline-block;width:90%"><?php
        echo Format::display($notes); ?>
    <em>(<?php echo sprintf(__('อัพเดทล่าสุด %s'), Format::db_datetime($upd));
        ?>)</em></span></td></tr><?php
}; ?>
        <tr>
            <th colspan="2">
                <em><b><?php echo __(
                'เท็มเพลตการเข้าสู่ระบบและสมัครสมาชิก'); ?></b></em>
            </th>
        </tr>
        <?php $manage_content(__('เจ้าหน้าที่'), 'pwreset-staff'); ?>
        <?php $manage_content(__('ผู้ใช้'), 'pwreset-client'); ?>
        <?php $manage_content(__('ผู้เยี่ยมชม'), 'access-link'); ?>
        <tr>
            <th colspan="2">
                <em><b><?php echo __('หน้าเข้าสู่ระบบ'); ?></b></em>
            </th>
        </tr>
        <?php $manage_content(__('โลโก้หน้าเข้าสู่ระบบเจ้าหน้าที่'), 'banner-staff'); ?>
        <?php $manage_content(__('หน้าเข้าสู่ระบบผู้ใช้'), 'banner-client'); ?>
        <tr>
            <th colspan="2">
                <em><b><?php echo __('ผู้ใช้สมัครสมาชิก'); ?></b></em>
            </th>
        </tr>
        <?php $manage_content(__('หน้ายืนยันการสมัครสมาชิกในอีเมล'), 'registration-confirm'); ?>
        <?php $manage_content(__('หน้ายืนยันเมื่อผู้ใช้สมัครสมาชิก'), 'registration-client'); ?>
        <?php $manage_content(__('หน้าเมื่อผู้ใช้ยืนยันการสมัครสมาชิกแล้ว'), 'registration-thanks'); ?>
        <tr>
            <th colspan="2">
                <em><b><?php echo __('ลงทะเบียนเจ้าหน้าที่'); ?></b></em>
            </th>
        </tr>
        <?php $manage_content(__('อีเมลต้อนรับเจ้าหน้าที่'), 'registration-staff'); ?>
</tbody>
</table>
<p style="text-align:center">
    <input class="button" type="submit" name="submit" value="<?php echo __('บันทึก'); ?>">
    <input class="button" type="reset" name="reset" value="<?php echo __('รีเซ็ต'); ?>">
</p>
</form>
