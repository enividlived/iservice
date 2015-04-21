<div class="pull-left" style="width:700;padding-top:5px;">
 <h2><?php echo __('รายการที่กำหนดเอง'); ?></h2>
</div>
<div class="pull-right flush-right" style="padding-top:5px;padding-right:5px;">
 <b><a href="lists.php?a=add" class="Icon list-add"><?php
 echo __('เพิ่มรายการที่กำหนดเอง'); ?></a></b></div>
<div class="clear"></div>

<?php
$page = ($_GET['p'] && is_numeric($_GET['p'])) ? $_GET['p'] : 1;
$count = DynamicList::objects()->count();
$pageNav = new Pagenate($count, $page, PAGE_LIMIT);
$pageNav->setURL('lists.php');
$showing=$pageNav->showing().' '._N('custom list', 'custom lists', $count);

?>
<form action="lists.php" method="POST" name="lists">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="mass_process" >
<input type="hidden" id="action" name="a" value="" >
<table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <caption>Custom Lists</caption>
    <thead>
        <tr>
            <th width="7">&nbsp;</th>
            <th><?php echo __('ชื่อรายการ'); ?></th>
            <th><?php echo __('วันที่สร้าง') ?></th>
            <th><?php echo __('อัพเดทล่าสุด'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach (DynamicList::objects()->order_by('-type', 'name')
                ->limit($pageNav->getLimit())
                ->offset($pageNav->getStart()) as $list) {
            $sel = false;
            if ($ids && in_array($form->get('id'),$ids))
                $sel = true; ?>
        <tr>
            <td>
                <?php
                if ($list->isDeleteable()) { ?>
                <input width="7" type="checkbox" class="ckb" name="ids[]"
                value="<?php echo $list->getId(); ?>"
                    <?php echo $sel?'checked="checked"':''; ?>>
                <?php
                } else {
                    echo '&nbsp;';
                }
                ?>
            </td>
            <td><a href="?id=<?php echo $list->getId(); ?>"><?php echo
            $list->getPluralName() ?: $list->getName(); ?></a></td>
            <td><?php echo $list->get('created'); ?></td>
            <td><?php echo $list->get('updated'); ?></td>
        </tr>
    <?php }
    ?>
    </tbody>
    <tfoot>
     <tr>
        <td colspan="4">
            <?php if($count){ ?>
            <?php echo __('เลือก'); ?>:&nbsp;
            <a id="selectAll" href="#ckb"><?php echo __('ทั้งหมด'); ?></a>&nbsp;&nbsp;
            <a id="selectNone" href="#ckb"><?php echo __('ไม่เลือก'); ?></a>&nbsp;&nbsp;
            <a id="selectToggle" href="#ckb"><?php echo __('สลับ'); ?></a>&nbsp;&nbsp;
            <?php } else {
                echo sprintf(__('ยังไม่มีรายการที่กำหนดเอง &mdash; %s เพิ่มรายการใหม่ %s!'),
                    '<a href="lists.php?a=add">','</a>');
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
</p>
</form>

<div style="display:none;" class="dialog" id="confirm-action">
    <h3><?php echo __('โปรดยืนยัน'); ?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong><?php echo sprintf(
        __('คุณแน่ใจหรือว่าต้องการลบ %s?'),
        _N('รายการที่กำหนดเองที่เลือก', 'รายการที่กำหนดเองที่เลือก', 2)); ?></strong></font>
        <br><br><?php echo __('ข้อมูลที่ถูกลบจะไม่สามารถกู้คืนได้'); ?>
    </p>
    <div><?php echo __('โปรดยืนยันก่อนดำเนินการต่อ'); ?></div>
    <hr style="margin-top:1em"/>
    <p class="full-width">
        <span class="buttons pull-left">
            <input type="button" value="ยกเลิก" class="close">
        </span>
        <span class="buttons pull-right">
            <input type="button" value="ดำเนินการ" class="confirm">
        </span>
    </p>
    <div class="clear"></div>
</div>