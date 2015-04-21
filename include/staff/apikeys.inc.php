<?php
if(!defined('OSTADMININC') || !$thisstaff->isAdmin()) die('ปฏิเสธการเข้าถึง');

$qstr='';
$sql='SELECT * FROM '.API_KEY_TABLE.' WHERE 1';
$sortOptions=array('key'=>'apikey','status'=>'isactive','ip'=>'ipaddr','date'=>'created','created'=>'created','updated'=>'updated');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
$sort=($_REQUEST['sort'] && $sortOptions[strtolower($_REQUEST['sort'])])?strtolower($_REQUEST['sort']):'key';
//Sorting options...
if($sort && $sortOptions[$sort]) {
    $order_column =$sortOptions[$sort];
}
$order_column=$order_column?$order_column:'key.created';

if($_REQUEST['order'] && $orderWays[strtoupper($_REQUEST['order'])]) {
    $order=$orderWays[strtoupper($_REQUEST['order'])];
}
$order=$order?$order:'DESC';

if($order_column && strpos($order_column,',')){
    $order_column=str_replace(','," $order,",$order_column);
}
$x=$sort.'_sort';
$$x=' class="'.strtolower($order).'" ';
$order_by="$order_column $order ";

$total=db_count('SELECT count(*) FROM '.API_KEY_TABLE.' ');
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total,$page,PAGE_LIMIT);
$pageNav->setURL('apikeys.php',$qstr.'&sort='.urlencode($_REQUEST['sort']).'&order='.urlencode($_REQUEST['order']));
//Ok..lets roll...create the actual query
$qstr.='&order='.($order=='DESC'?'ASC':'DESC');
$query="$sql ORDER BY $order_by LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
$res=db_query($query);
if($res && ($num=db_num_rows($res)))
    $showing=$pageNav->showing().' '.__('รหัส API');
else
    $showing=__('ไม่พบรหัส API!');

?>

<div class="pull-left" style="width:700px;padding-top:5px;">
 <h2><?php echo __('รหัส API');?></h2>
</div>
<div class="pull-right flush-right" style="padding-top:5px;padding-right:5px;">
 <b><a href="apikeys.php?a=add" class="Icon newapi"><?php echo __('เพิ่มรหัส API ใหม่');?></a></b></div>
<div class="clear"></div>
<form action="apikeys.php" method="POST" name="keys">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="mass_process" >
<input type="hidden" id="action" name="a" value="" >
 <table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <caption><?php echo $showing; ?></caption>
    <thead>
        <tr>
            <th width="7">&nbsp;</th>
            <th width="320"><a <?php echo $key_sort; ?> href="apikeys.php?<?php echo $qstr; ?>&sort=key"><?php echo __('รหัส API');?></a></th>
            <th width="120"><a <?php echo $ip_sort; ?> href="apikeys.php?<?php echo $qstr; ?>&sort=ip"><?php echo __('หมายเลข IP');?></a></th>
            <th width="100"><a  <?php echo $status_sort; ?> href="apikeys.php?<?php echo $qstr; ?>&sort=status"><?php echo __('สถานะ');?></a></th>
            <th width="150" nowrap><a  <?php echo $date_sort; ?>href="apikeys.php?<?php echo $qstr; ?>&sort=date"><?php echo __('วันที่สร้าง');?></a></th>
            <th width="150" nowrap><a  <?php echo $updated_sort; ?>href="apikeys.php?<?php echo $qstr; ?>&sort=updated"><?php echo __('อัพเดทล่าสุด');?></a></th>
        </tr>
    </thead>
    <tbody>
    <?php
        $total=0;
        $ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
        if($res && db_num_rows($res)):
            while ($row = db_fetch_array($res)) {
                $sel=false;
                if($ids && in_array($row['id'],$ids))
                    $sel=true;
                ?>
            <tr id="<?php echo $row['id']; ?>">
                <td width=7px>
                  <input type="checkbox" class="ckb" name="ids[]" value="<?php echo $row['id']; ?>"
                            <?php echo $sel?'checked="checked"':''; ?>> </td>
                <td>&nbsp;<a href="apikeys.php?id=<?php echo $row['id']; ?>"><?php echo Format::htmlchars($row['apikey']); ?></a></td>
                <td><?php echo $row['ipaddr']; ?></td>
                <td><?php echo $row['isactive']?__('ใช้งาน'):'<b>'.__('ไม่ได้ใช้งาน').'</b>'; ?></td>
                <td>&nbsp;<?php echo Format::db_date($row['created']); ?></td>
                <td>&nbsp;<?php echo Format::db_datetime($row['updated']); ?></td>
            </tr>
            <?php
            } //end of while.
        endif; ?>
    <tfoot>
     <tr>
        <td colspan="7">
            <?php if($res && $num){ ?>
            <?php echo __('Select');?>:&nbsp;
            <a id="selectAll" href="#ckb"><?php echo __('ทั้งหมด');?></a>&nbsp;&nbsp;
            <a id="selectNone" href="#ckb"><?php echo __('ไม่เลือก');?></a>&nbsp;&nbsp;
            <a id="selectToggle" href="#ckb"><?php echo __('สลับ');?></a>&nbsp;&nbsp;
            <?php }else{
                echo __('ไม่พบรหัส API');
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
    <input class="button" type="submit" name="enable" value="<?php echo __('เปิดใช้งาน');?>" >
    <input class="button" type="submit" name="disable" value="<?php echo __('ปิดใช้งาน');?>">
    <input class="button" type="submit" name="delete" value="<?php echo __('ลบ');?>">
</p>
<?php
endif;
?>
</form>
<div style="display:none;" class="dialog" id="confirm-action">
    <h3><?php echo __('โปรดยืนยัน');?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="enable-confirm">
        <?php echo sprintf(__('คุณแน่ใจหรือว่าจะ <b>เปิดใช้งาน</b> %s?'),
            _N('selected API key', 'selected API keys', 2));?>
    </p>
    <p class="confirm-action" style="display:none;" id="disable-confirm">
        <?php echo sprintf(__('คุณแน่ใจหรือว่าจะ <b>ปิดใช้งาน</b> %s?'),
            _N('selected API key', 'selected API keys', 2)); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong><?php echo sprintf(__('คุณแน่ใจหรือว่าจะลบ %s?'),
            _N('selected API key', 'selected API keys', 2));?></strong></font>
        <br><br><?php echo __('ข้อมูลที่ถูกลบจะไม่สามารถกู้คืนได้'); ?>
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
