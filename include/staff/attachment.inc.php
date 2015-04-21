<?php
if(!defined('OSTADMININC') || !$thisstaff->isAdmin()) die('ปฏิเสธการเข้าถึง');
//Get the config info.
$config=($errors && $_POST)?Format::input($_POST):$cfg->getConfigInfo();
?>
<table width="100%" border="0" cellspacing=0 cellpadding=0>
    <form action="admin.php?t=attach" method="post">
    <input type="hidden" name="t" value="attach">
    <tr>
      <td>
        <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
          <tr class="header">
            <td colspan=2>&nbsp;ตั้งค่าระบบแนบไฟล์</td>
          </tr>
          <tr class="subheader">
            <td colspan=2">
                ก่อนเปิดใช้งานระบบแนบไฟล์ กรุณาศึกษาการตั้งค่าความปลอดภัยรวมถึงปัญหาที่อาจเกิดขึ้นจากระบบนี้</td>
          </tr>
          <tr>
            <th width="165">อนุญาตให้แนบไฟล์:</th>
            <td>
              <input type="checkbox" name="allow_attachments" <?php echo $config['allow_attachments'] ?'checked':''; ?>><b>อนุญาตให้แนบไฟล์</b>
                &nbsp; (<i>การตั้งค่า</i>)
                &nbsp;<font class="error">&nbsp;<?php echo $errors['allow_attachments']; ?></font>
            </td>
          </tr>
          <tr>
            <th>แนบไฟล์ทางอีเมล:</th>
            <td>
                <input type="checkbox" name="allow_email_attachments" <?php echo $config['allow_email_attachments'] ? 'checked':''; ?> > รับไฟล์แนบจากอีเมล
                    &nbsp;<font class="warn">&nbsp;<?php echo $warn['allow_email_attachments']; ?></font>
            </td>
          </tr>
         <tr>
            <th>แนบไฟล์ออนไลน์:</th>
            <td>
                <input type="checkbox" name="allow_online_attachments" <?php echo $config['allow_online_attachments'] ?'checked':''; ?> >
                    อนุญาตให้อัพโหลดไฟล์แนบแบบออนไลน์<br/>&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="checkbox" name="allow_online_attachments_onlogin" <?php echo $config['allow_online_attachments_onlogin'] ?'checked':''; ?> >
                    เฉพาะผู้ใช้งานที่เข้าสู่ระบบแล้วเท่านั้น (<i>ผู้ใช้งานต้องเข้าสู่ระบบก่อนทำการอัพโหลดไฟล์ </i>)
                    <font class="warn">&nbsp;<?php echo $warn['allow_online_attachments']; ?></font>
            </td>
          </tr>
          <tr>
            <th>ไฟล์แนบตอบกลับของเจ้าหน้าที่</th>
            <td>
                <input type="checkbox" name="email_attachments" <?php echo $config['email_attachments']?'checked':''; ?> >อีเมลไฟล์แนบให้กับผู้ใช้
            </td>
          </tr>
          <tr>
            <th nowrap>ขนาดไฟล์แนบสูงสุด:</th>
            <td>
              <input type="text" name="max_file_size" value="<?php echo $config['max_file_size']; ?>"> <i>bytes</i>
                <font class="error">&nbsp;<?php echo $errors['max_file_size']; ?></font>
            </td>
          </tr>
          <tr>
            <th>โฟลเดอร์ไฟล์แนบ:</th>
            <td>
                ต้องมีสิทธิในการเขียนข้อมูลบนเซิร์ฟเวอร์ด้วย &nbsp;<font class="error">&nbsp;<?php echo $errors['upload_dir']; ?></font><br>
              <input type="text" size=60 name="upload_dir" value="<?php echo $config['upload_dir']; ?>">
              <font color=red>
              <?php echo $attwarn; ?>
              </font>
            </td>
          </tr>
          <tr>
            <th valign="top"><br/>นามสกุลไฟล์ที่อนุมัติ:</th>
            <td>
                กรอกนามสกุลไฟล์อนุมติคั่นด้วยเครื่องหมายคอมม่า เช่น <i>.doc, .pdf, </i> <br>
                ต้องการอนุมัติทุกไฟล์ ให้ใส่เครื่องหมายดอกจันทร์ <b><i>.*</i></b>&nbsp;&nbsp;i.e dotStar (ไม่แนะนำ).
                <textarea name="allowed_filetypes" cols="21" rows="4" style="width: 65%;" wrap=HARD ><?php echo $config['allowed_filetypes']; ?></textarea>
            </td>
          </tr>
        </table>
    </td></tr>
    <tr><td style="padding:10px 0 10px 200px">
        <input class="button" type="submit" name="submit" value="บันทึก">
        <input class="button" type="reset" name="reset" value="ล้าง">
    </td></tr>
  </form>
</table>
