<?php
if(!defined('OSTSTAFFINC') || !$staff || !$thisstaff) die('ปฏิเสธการเข้าถึง');

$info=$staff->getInfo();
$info['signature'] = Format::viewableImages($info['signature']);
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
$info['id']=$staff->getId();
?>
<form action="profile.php" method="post" id="save" autocomplete="off">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="update">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?php echo __('โปรไฟล์ของฉัน');?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo __('ข้อมูลผู้ใช้');?></h4>
                <em><?php echo __('ข้อมูลติดต่อ');?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
                <?php echo __('ชื่อผู้ใช้');?>:
            </td>
            <td><b><?php echo $staff->getUserName(); ?></b>&nbsp;<i class="help-tip icon-question-sign" href="#username"></i></td>
        </tr>

        <tr>
            <td width="180" class="required">
                <?php echo __('ชื่อ');?>:
            </td>
            <td>
                <input type="text" size="34" name="firstname" value="<?php echo $info['firstname']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['firstname']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo __('นามสกุล');?>:
            </td>
            <td>
                <input type="text" size="34" name="lastname" value="<?php echo $info['lastname']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['lastname']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo __('อีเมลแอดเดรส');?>:
            </td>
            <td>
                <input type="text" size="34" name="email" value="<?php echo $info['email']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['email']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('โทรศัพท์');?>:
            </td>
            <td>
                <input type="text" size="22" name="phone" value="<?php echo $info['phone']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['phone']; ?></span>
                Ext <input type="text" size="5" name="phone_ext" value="<?php echo $info['phone_ext']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['phone_ext']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('โทรศัพท์มือถือ');?>:
            </td>
            <td>
                <input type="text" size="22" name="mobile" value="<?php echo $info['mobile']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['mobile']; ?></span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('การตั้งค่า');?></strong>: <?php echo __('การตั้งค่าส่วนตัว');?></em>
            </th>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo __('เขตเวลา');?>:
            </td>
            <td>
                <select name="timezone_id" id="timezone_id">
                    <option value="0">&mdash; <?php echo __('เลือกเขตเวลา');?> &mdash;</option>
                    <?php
                    $sql='SELECT id, offset,timezone FROM '.TIMEZONE_TABLE.' ORDER BY id';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$offset, $tz)=db_fetch_row($res)){
                            $sel=($info['timezone_id']==$id)?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>GMT %s - %s</option>',$id,$sel,$offset,$tz);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['timezone_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('ภาษาสำหรับแสดงผล'); ?>:
            </td>
            <td>
        <?php
        $langs = Internationalization::availableLanguages(); ?>
                <select name="lang">
                    <option value="">&mdash; <?php echo __('ใช้ค่าของเบราว์เซอร์'); ?> &mdash;</option>
<?php foreach($langs as $l) {
    $selected = ($info['lang'] == $l['code']) ? 'selected="selected"' : ''; ?>
                    <option value="<?php echo $l['code']; ?>" <?php echo $selected;
                        ?>><?php echo Internationalization::getLanguageDescription($l['code']); ?></option>
<?php } ?>
                </select>
                <span class="error">&nbsp;<?php echo $errors['lang']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
               <?php echo __('เวลาออมแสง');?>:
            </td>
            <td>
                <input type="checkbox" name="daylight_saving" value="1" <?php echo $info['daylight_saving']?'checked="checked"':''; ?>>
                <?php echo __('เปิดใช้การนับเวลาออมแสง');?>
                <em>(<?php echo __('วันเวลาปัจจุบัน');?>: <strong><?php echo Format::date($cfg->getDateTimeFormat(),Misc::gmtime(),$info['tz_offset'],$info['daylight_saving']); ?></strong>)</em>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo __('รายการสูงสุดต่อหน้า');?>:</td>
            <td>
                <select name="max_page_size">
                    <option value="0">&mdash; <?php echo __('ค่าเริ่มต้นระบบ');?> &mdash;</option>
                    <?php
                    $pagelimit=$info['max_page_size']?$info['max_page_size']:$cfg->getPageSize();
                    for ($i = 5; $i <= 50; $i += 5) {
                        $sel=($pagelimit==$i)?'selected="selected"':'';
                         echo sprintf('<option value="%d" %s>'.__('%s รายการ').'</option>',$i,$sel,$i);
                    } ?>
                </select> <?php echo __('ต่อหน้า');?>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo __('รีเฟรชอัตโนมัติ');?>:</td>
            <td>
                <select name="auto_refresh_rate">
                  <option value="0">&mdash; <?php echo __('ปิดใช้งาน');?> &mdash;</option>
                  <?php
                  $y=1;
                   for($i=1; $i <=30; $i+=$y) {
                     $sel=($info['auto_refresh_rate']==$i)?'selected="selected"':'';
                     echo sprintf('<option value="%1$d" %2$s>'
                        .sprintf(
                            _N('ทุกนาที', 'ทุก %d นาที', $i), $i)
                         .'</option>',$i,$sel);
                     if($i>9)
                        $y=2;
                   } ?>
                </select>
                <em><?php echo __('อัตราการรีเฟรชหน้าคำขอใช้บริการต่อนาที');?></em>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo __('ลายเซ็นต์เริ่มต้น');?>:</td>
            <td>
                <select name="default_signature_type">
                  <option value="none" selected="selected">&mdash; <?php echo __('ไม่มี');?> &mdash;</option>
                  <?php
                   $options=array('mine'=>__('ลายเซ็นต์ของฉัน'),'dept'=>sprintf(__('ลายเซ็นต์แผนก (%s)'),
                 __('ถ้ามี' /* This is used in 'Department Signature (>if set<)' */)));
                  foreach($options as $k=>$v) {
                      echo sprintf('<option value="%s" %s>%s</option>',
                                $k,($info['default_signature_type']==$k)?'selected="selected"':'',$v);
                  }
                  ?>
                </select>
                <em><?php echo __('ใช้เพื่อแนบท้ายเวลาตอบกลับคำขอใช้บริการ');?></em>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['default_signature_type']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo __('ขนาดกระดาษสำหรับพิมพ์');?>:</td>
            <td>
                <select name="default_paper_size">
                  <option value="none" selected="selected">&mdash; <?php echo __('ไม่มี');?> &mdash;</option>
                  <?php

                  foreach(Export::$paper_sizes as $v) {
                      echo sprintf('<option value="%s" %s>%s</option>',
                                $v,($info['default_paper_size']==$v)?'selected="selected"':'',__($v));
                  }
                  ?>
                </select>
                <em><?php echo __('ขนาดกระดาษเมื่อพิมพ์คำขอใช้บริการออกเป็น PDF');?></em>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['default_paper_size']; ?></span>
            </td>
        </tr>
        <tr>
            <td><?php echo __('แสดงชื่อแผนกแทนชื่อตนเอง');?>:</td>
            <td>
                <input type="checkbox" name="show_assigned_tickets" <?php echo $info['show_assigned_tickets']?'checked="checked"':''; ?>>
                <em><?php echo __('แสดงชื่อแผนกแทนชื่อตนเองเมื่อรับคำขอใช้บริการ');?></em>
                &nbsp;<i class="help-tip icon-question-sign" href="#show_assigned_tickets"></i></em>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('รหัสผ่าน');?></strong>: <?php echo __('ถ้าต้องการตั้งรหัสผ่านใหม่ ให้กรอกรหัสผ่านปัจจุบันและรหัสผ่านใหม่ที่ต้องการในช่องด้านล่าง');?>&nbsp;<span class="error">&nbsp;<?php echo $errors['passwd']; ?></span></em>
            </th>
        </tr>
        <?php if (!isset($_SESSION['_staff']['reset-token'])) { ?>
        <tr>
            <td width="180">
                <?php echo __('รหัสผ่านปัจจุบัน');?>:
            </td>
            <td>
                <input type="password" size="18" name="cpasswd" value="<?php echo $info['cpasswd']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['cpasswd']; ?></span>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <td width="180">
                <?php echo __('รหัสผ่านใหม่');?>:
            </td>
            <td>
                <input type="password" size="18" name="passwd1" value="<?php echo $info['passwd1']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['passwd1']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('ยืนยันรหัสผ่านใหม่');?>:
            </td>
            <td>
                <input type="password" size="18" name="passwd2" value="<?php echo $info['passwd2']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['passwd2']; ?></span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('ลายเซ็นต์');?></strong>: <?php echo __('ลายเซ็นต์ที่ต้องการแนบเวลาส่งอีเมล');?>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['signature']; ?></span>&nbsp;<i class="help-tip icon-question-sign" href="#signature"></i></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea class="richtext no-bar" name="signature" cols="21"
                    rows="5" style="width: 60%;"><?php echo $info['signature']; ?></textarea>
                <br><em><?php __('สามารถกำหนดตอนตอบกลับคำขอใช้บริการได้ ว่าจะให้แนบลายเซ็นต์ด้วยหรือไม่');?></em>
            </td>
        </tr>
    </tbody>
</table>
<p style="text-align:center;">
    <input type="submit" name="submit" value="<?php echo __('บันทึก');?>">
    <input type="reset"  name="reset"  value="<?php echo __('รีเซ็ต');?>">
    <input type="button" name="cancel" value="<?php echo __('ยกเลิก');?>" onclick='window.location.href="index.php"'>
</p>
</form>
