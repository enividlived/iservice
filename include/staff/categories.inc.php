<?php
if(!defined('OSTSCPINC') || !$thisstaff) die('ปฏิเสธการเข้าถึง');

$qstr='';
$sql='SELECT cat.category_id, cat.name, cat.ispublic, cat.updated, count(faq.faq_id) as faqs '.
     ' FROM '.FAQ_CATEGORY_TABLE.' cat '.
     ' LEFT JOIN '.FAQ_TABLE.' faq ON (faq.category_id=cat.category_id) ';
$sql.=' WHERE 1';
$sortOptions=array('name'=>'cat.name','type'=>'cat.ispublic','faqs'=>'faqs','updated'=>'cat.updated');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
$sort=($_REQUEST['sort'] && $sortOptions[strtolower($_REQUEST['sort'])])?strtolower($_REQUEST['sort']):'name';
//Sorting options...
if($sort && $sortOptions[$sort]) {
    $order_column =$sortOptions[$sort];
}
$order_column=$order_column?$order_column:'cat.name';

if($_REQUEST['order'] && $orderWays[strtoupper($_REQUEST['order'])]) {
    $order=$orderWays[strtoupper($_REQUEST['order'])];
}
$order=$order?$order:'ASC';

if($order_column && strpos($order_column,',')){
    $order_column=str_replace(','," $order,",$order_column);
}
$x=$sort.'_sort';
$$x=' class="'.strtolower($order).'" ';
$order_by="$order_column $order ";

$total=db_count('SELECT count(*) FROM '.FAQ_CATEGORY_TABLE.' cat ');
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total, $page, PAGE_LIMIT);
$pageNav->setURL('categories.php',$qstr.'&sort='.urlencode($_REQUEST['sort']).'&order='.urlencode($_REQUEST['order']));
$qstr.='&order='.($order=='DESC'?'ASC':'DESC');
$query="$sql GROUP BY cat.category_id ORDER BY $order_by LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
$res=db_query($query);
if($res && ($num=db_num_rows($res)))
    $showing=$pageNav->showing().' '.__('categories');
else
    $showing=__('ไม่พบหัวข้อบทความ!');

?>
<div class="pull-left" style="width:700px;padding-top:5px;">
 <h2><?php echo __('หัวข้อบทความ');?></h2>
 </div>
<div class="pull-right flush-right" style="padding-top:5px;padding-right:5px;">
    <b><a href="categories.php?a=add" class="Icon newCategory"><?php echo __('เพิ่มหัวข้อบทความใหม่');?></a></b></div>
<div class="clear"></div>
<form action="categories.php" method="POST" name="cat">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="mass_process" >
 <input type="hidden" id="action" name="a" value="" >
 <table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <caption><?php echo $showing; ?></caption>
    <thead>
        <tr>
            <th width="7">&nbsp;</th>
            <th width="500"><a <?php echo $name_sort; ?> href="categories.php?<?php echo $qstr; ?>&sort=name"><?php echo __('ชื่อ');?></a></th>
            <th width="150"><a  <?php echo $type_sort; ?> href="categories.php?<?php echo $qstr; ?>&sort=type"><?php echo __('ประเภท');?></a></th>
            <th width="80"><a  <?php echo $faqs_sort; ?> href="categories.php?<?php echo $qstr; ?>&sort=faqs"><?php echo __('บทความ');?></a></th>
            <th width="150" nowrap><a  <?php echo $updated_sort; ?>href="categories.php?<?php echo $qstr; ?>&sort=updated"><?php echo __('ปรับปรุงล่าสุด');?></a></th>
        </tr>
    </thead>
    <tbody>
    <?php
        $total=0;
        $ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
        if($res && db_num_rows($res)):
            while ($row = db_fetch_array($res)) {
                $sel=false;
                if($ids && in_array($row['category_id'],$ids))
                    $sel=true;

                $faqs=0;
                if($row['faqs'])
                    $faqs=sprintf('<a href="faq.php?cid=%d">%d</a>',$row['category_id'],$row['faqs']);
                ?>
            <tr id="<?php echo $row['category_id']; ?>">
                <td width=7px>
                  <input type="checkbox" name="ids[]" value="<?php echo $row['category_id']; ?>" class="ckb"
                            <?php echo $sel?'checked="checked"':''; ?>>
                </td>
                <td><a href="categories.php?id=<?php echo $row['category_id']; ?>"><?php echo Format::truncate($row['name'],200); ?></a>&nbsp;</td>
                <td><?php echo $row['ispublic']?'<b>'.__('สาธารณะ').'</b>':__('ภายใน'); ?></td>
                <td style="text-align:right;padding-right:25px;"><?php echo $faqs; ?></td>
                <td>&nbsp;<?php echo Format::db_datetime($row['updated']); ?></td>
            </tr>
            <?php
            } //end of while.
        endif; ?>
    <tfoot>
     <tr>
        <td colspan="5">
            <?php if($res && $num){ ?>
            <?php echo __('เลือก');?>:&nbsp;
            <a id="selectAll" href="#ckb"><?php echo __('ทั้งหมด');?></a>&nbsp;&nbsp;
            <a id="selectNone" href="#ckb"><?php echo __('ไม่เลือก');?></a>&nbsp;&nbsp;
            <a id="selectToggle" href="#ckb"><?php echo __('สลับ');?></a>&nbsp;&nbsp;
            <?php }else{
                echo __('ไม่พบหัวข้อบทความ');
            } ?>
        </td>
     </tr>
    </tfoot>
</table>
<?php
if($res && $num): //Show options..
    echo '<div>&nbsp;'.__('หน้า').':'.$pageNav->getPageLinks().'&nbsp;</div>';
?>
<p class="centered" id="actions">
    <input class="button" type="submit" name="make_public" value="<?php echo __('ทำให้เป็นสาธารณะ');?>">
    <input class="button" type="submit" name="make_private" value="<?php echo __('ทำให้เป็นภายใน');?>">
    <input class="button" type="submit" name="delete" value="<?php echo __('ลบทิ้ง');?>" >
</p>
<?php
endif;
?>
</form>
<div style="display:none;" class="dialog" id="confirm-action">
    <h3><?php echo __('โปรดยืนยัน');?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="make_public-confirm">
        <?php echo sprintf(__('คุณแน่ใจหรือว่าต้องการทำให้ %s เป็น <b>สาธารณะ</b>?'),
            _N('หัวข้อบทความที่เลือก', 'หัวข้อบทความที่เลือก', 2));?>
    </p>
    <p class="confirm-action" style="display:none;" id="make_private-confirm">
        <?php echo sprintf(__('คุณแน่ใจหรือว่าต้องการทำให้ %s เป็น <b>ส่วนตัว</b> (ภายใน)?'),
            _N('หัวข้อบทความที่เลือก', 'หัวข้อบทความที่เลือก', 2));?>
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong><?php echo sprintf(__('คุณแน่ใจหรือว่าต้องการลบทิ้ง %s?'),
            _N('หัวข้อบทความที่เลือก', 'หัวข้อบทความที่เลือก', 2));?></strong></font>
        <br><br><?php echo __('หัวข้อที่ถูกลบจะไม่สามารถกู้คืนได้ รวมถึงบทความถาม-ตอบที่อยู่ภายใต้หัวข้อนั้นๆด้วย'); ?>
    </p>
    <div><?php echo __('โปรดยืนยันเพื่อดำเนินการต่อ');?></div>
    <hr style="margin-top:1em"/>
    <p class="full-width">
        <span class="buttons pull-left">
            <input type="button" value="<?php echo __('ยกเลิก');?>" class="close">
        </span>
        <span class="buttons pull-right">
            <input type="button" value="<?php echo __('ดำเนินการ');?>" class="confirm">
        </span>
     </p>
    <div class="clear"></div>
</div>
