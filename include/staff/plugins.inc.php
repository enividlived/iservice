<div class="pull-left" style="width:700;padding-top:5px;">
 <h2><?php echo __('ปลั๊กอินที่ถูกติดตั้ง'); ?></h2>
</div>
<div class="pull-right flush-right" style="padding-top:5px;padding-right:5px;">
 <b><a href="plugins.php?a=add" class="Icon form-add"><?php
 echo __('เพิ่มปลั๊กอินใหม่'); ?></a></b></div>
<div class="clear"></div>

<?php
$page = ($_GET['p'] && is_numeric($_GET['p'])) ? $_GET['p'] : 1;
$count = count($ost->plugins->allInstalled());
$pageNav = new Pagenate($count, $page, PAGE_LIMIT);
$pageNav->setURL('forms.php');
$showing=$pageNav->showing().' '._N('plugin', 'plugins', $count);
?>

<form action="plugins.php" method="POST" name="forms">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="mass_process" >
<input type="hidden" id="action" name="a" value="" >
<table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <thead>
        <tr>
            <th width="7">&nbsp;</th>
            <th><?php echo __('ชื่อปลั๊กอิน'); ?></th>
            <th><?php echo __('สถานะ'); ?></td>
            <th><?php echo __('วันที่ติดตั้ง'); ?></th>
        </tr>
    </thead>
    <tbody>
<?php
foreach ($ost->plugins->allInstalled() as $p) {
    if ($p instanceof Plugin) { ?>
    <tr>
        <td><input type="checkbox" class="ckb" name="ids[]" value="<?php echo $p->getId(); ?>"
                <?php echo $sel?'checked="checked"':''; ?>></td>
        <td><a href="plugins.php?id=<?php echo $p->getId(); ?>"
            ><?php echo $p->getName(); ?></a></td>
        <td><?php echo ($p->isActive())
            ? 'เปิดใช้งาน' : '<strong>ปิดใช้งาน</strong>'; ?></td>
        <td><?php echo Format::db_datetime($p->getInstallDate()); ?></td>
    </tr>
    <?php } else {} ?>
<?php } ?>
    </tbody>
    <tfoot>
     <tr>
        <td colspan="4">
            <?php if($count){ ?>
            <?php echo __('เลือก'); ?>:&nbsp;
            <a id="selectAll" href="#ckb"><?php echo __('ทั้งหมด'); ?></a>&nbsp;&nbsp;
            <a id="selectNone" href="#ckb"><?php echo __('ไม่เลือก'); ?></a>&nbsp;&nbsp;
            <a id="selectToggle" href="#ckb"><?php echo __('สลับ'); ?></a>&nbsp;&nbsp;
            <?php }else{
                echo sprintf(__('ไม่มีปลั๊กอินติดตั้งในระบบ &mdash; %s เพิ่มใหม่สิ %s!'),
                    '<a href="?a=add">','</a>');
            } ?>
        </td>
     </tr>
    </tfoot>
</table>
<?php
if ($count) //Show options..
    echo '<div>&nbsp;'.__('หน้า').':'.$pageNav->getPageLinks().'&nbsp;</div>';
?>
<p class="centered" id="actions">
    <input class="button" type="submit" name="delete" value="<?php echo __('ลบทิ้ง'); ?>">
    <input class="button" type="submit" name="enable" value="<?php echo __('เปิดใช้งาน'); ?>">
    <input class="button" type="submit" name="disable" value="<?php echo __('ปิดใช้งาน'); ?>">
</p>
</form>

<div style="display:none;" class="dialog" id="confirm-action">
    <h3><?php echo __('โปรดยืนยัน'); ?></h3>
    <a class="close" href="">&times;</a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong><?php echo sprintf(
        __('คุณแน่ใจหรือว่าต้องการลบ %s?'),
        _N('ปลั๊กอินที่เลือก', 'ปลั๊กอินที่เลือก', 2)); ?></strong></font>
        <br><br><?php echo __(
        'การตั้งค่าของปลั๊กอินจะถูกลบทั้งหมด'); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="enable-confirm">
        <font color="green"><?php echo sprintf(
        __('คุณแน่ใจหรือว่าต้องการ <b>เปิดใช้งาน</b> %s?'),
        _N('ปลั๊กอินที่เลือก', 'ปลั๊กอินที่เลือก', 2)); ?></font>
    </p>
    <p class="confirm-action" style="display:none;" id="disable-confirm">
        <font color="red"><?php echo sprintf(
        __('คุณแน่ใจหรือว่าต้องการ <b>ปิดใช้งาน</b> %s?'),
        _N('ปลั๊กอินที่เลือก', 'ปลั๊กอินที่เลือก', 2)); ?></font>
    </p>
    <div><?php echo __('โปรดยืนยันเพื่อดำเนินการต่อ'); ?></div>
    <hr style="margin-top:1em"/>
    <p class="full-width">
        <span class="buttons pull-left">
            <input type="button" value="<?php echo __('ยกเลิก'); ?>" class="close">
        </span>
        <span class="buttons pull-right">
            <input type="button" value="<?php echo __('ดำเนินการ'); ?>" class="confirm">
        </span>
     </p>
    <div class="clear"></div>
</div>
