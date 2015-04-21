<div class="pull-left" style="width:700;padding-top:5px;">
 <h2><?php echo __('แบบฟอร์มที่กำหนดเอง'); ?></h2>
</div>
<div class="pull-right flush-right" style="padding-top:5px;padding-right:5px;">
<b><a href="forms.php?a=add" class="Icon form-add"><?php
    echo __('เพิ่มแบบฟอร์มที่กำหนดเอง'); ?></a></b></div>
<div class="clear"></div>

<?php
$page = ($_GET['p'] && is_numeric($_GET['p'])) ? $_GET['p'] : 1;
$count = DynamicForm::objects()->filter(array('type__in'=>array('G')))->count();
$pageNav = new Pagenate($count, $page, PAGE_LIMIT);
$pageNav->setURL('forms.php');
$showing=$pageNav->showing().' '._N('form','forms',$count);
?>

<form action="forms.php" method="POST" name="forms">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="mass_process" >
<input type="hidden" id="action" name="a" value="" >
<table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <thead>
        <tr>
            <th width="7">&nbsp;</th>
            <th><?php echo __('ฟอร์มระบบ'); ?></th>
            <th><?php echo __('อัพเดทล่าสุด'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php
    $forms = array(
        'U' => 'icon-user',
        'T' => 'icon-ticket',
        'C' => 'icon-building',
        'O' => 'icon-group',
    );
    foreach (DynamicForm::objects()
            ->filter(array('type__in'=>array_keys($forms)))
            ->order_by('type', 'title') as $form) { ?>
        <tr>
        <td><i class="<?php echo $forms[$form->get('type')]; ?>"></i></td>
            <td><a href="?id=<?php echo $form->get('id'); ?>">
                <?php echo $form->get('title'); ?></a>
            <td><?php echo $form->get('updated'); ?></td>
        </tr>
    <?php } ?>
    </tbody>
    <tbody>
    <caption><?php echo $showing; ?></caption>
    <thead>
        <tr>
            <th width="7">&nbsp;</th>
            <th><?php echo __('แบบฟอร์มที่กำหนดเอง'); ?></th>
            <th><?php echo __('อัพเดทล่าสุด'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach (DynamicForm::objects()->filter(array('type'=>'G'))
                ->order_by('title')
                ->limit($pageNav->getLimit())
                ->offset($pageNav->getStart()) as $form) {
            $sel=false;
            if($ids && in_array($form->get('id'),$ids))
                $sel=true; ?>
        <tr>
            <td><?php if ($form->isDeletable()) { ?>
                <input type="checkbox" class="ckb" name="ids[]" value="<?php echo $form->get('id'); ?>"
                    <?php echo $sel?'checked="checked"':''; ?>>
            <?php } ?></td>
            <td><a href="?id=<?php echo $form->get('id'); ?>"><?php echo $form->get('title'); ?></a></td>
            <td><?php echo $form->get('updated'); ?></td>
        </tr>
    <?php }
    ?>
    </tbody>
    <tfoot>
     <tr>
        <td colspan="3">
            <?php if($count){ ?>
            <?php echo __('Select'); ?>:&nbsp;
            <a id="selectAll" href="#ckb"><?php echo __('ทั้งหมด'); ?></a>&nbsp;&nbsp;
            <a id="selectNone" href="#ckb"><?php echo __('ไม่เลือก'); ?></a>&nbsp;&nbsp;
            <a id="selectToggle" href="#ckb"><?php echo __('สลับ'); ?></a>&nbsp;&nbsp;
            <?php }else{
                echo sprintf(__(
                    'ยังไม่มีแบบฟอร์มที่กำหนดเอง &mdash; %s สร้างใหม่! %s'),
                    '<a href="forms.php?a=add">','</a>');
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
        <font color="red"><strong><?php echo sprintf(__(
        'คุณแน่ใจหรือว่าต้องการลบ %s?'),
        _N('แบบฟอร์มที่กำหนดเองที่เลือก', 'แบบฟอร์มที่กำหนดเองที่เลือก', 2));?></strong></font>
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
