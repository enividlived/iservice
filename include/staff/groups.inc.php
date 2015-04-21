<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('ปฏิเสธการเข้าถึง');

$qstr='';

$sql='SELECT grp.*,count(DISTINCT staff.staff_id) as users, count(DISTINCT dept.dept_id) as depts '
     .' FROM '.GROUP_TABLE.' grp '
     .' LEFT JOIN '.STAFF_TABLE.' staff ON(staff.group_id=grp.group_id) '
     .' LEFT JOIN '.GROUP_DEPT_TABLE.' dept ON(dept.group_id=grp.group_id) '
     .' WHERE 1';
$sortOptions=array('name'=>'grp.group_name','status'=>'grp.group_enabled',
                   'users'=>'users', 'depts'=>'depts', 'created'=>'grp.created','updated'=>'grp.updated');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
$sort=($_REQUEST['sort'] && $sortOptions[strtolower($_REQUEST['sort'])])?strtolower($_REQUEST['sort']):'name';
//Sorting options...
if($sort && $sortOptions[$sort]) {
    $order_column =$sortOptions[$sort];
}
$order_column=$order_column?$order_column:'grp.group_name';

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

$qstr.='&order='.($order=='DESC'?'ASC':'DESC');
$query="$sql GROUP BY grp.group_id ORDER BY $order_by";
$res=db_query($query);
if($res && ($num=db_num_rows($res)))
    $showing=sprintf(__('Showing 1-%1$d of %2$d groups'), $num, $num);
else
    $showing=__('ไม่พบกลุ่ม!');

?>
<div class="pull-left" style="width:700px;padding-top:5px;">
 <h2><?php echo __('Agent Groups');?>
    <i class="help-tip icon-question-sign" href="#groups"></i>
    </h2>
 </div>
<div class="pull-right flush-right" style="padding-top:5px;padding-right:5px;">
    <b><a href="groups.php?a=add" class="Icon newgroup"><?php echo __('สร้างกลุ่มใหม่');?></a></b></div>
<div class="clear"></div>
<form action="groups.php" method="POST" name="groups">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="mass_process" >
 <input type="hidden" id="action" name="a" value="" >
 <table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <caption><?php echo $showing; ?></caption>
    <thead>
        <tr>
            <th width="7px">&nbsp;</th>
            <th width="200"><a <?php echo $name_sort; ?> href="groups.php?<?php echo $qstr; ?>&sort=name"><?php echo __('ชื่อกลุ่ม');?></a></th>
            <th width="80"><a  <?php echo $status_sort; ?> href="groups.php?<?php echo $qstr; ?>&sort=status"><?php echo __('สถานะ');?></a></th>
            <th width="80" style="text-align:center;"><a  <?php echo $users_sort; ?>href="groups.php?<?php echo $qstr; ?>&sort=users"><?php echo __('สมาชิก');?></a></th>
            <th width="80" style="text-align:center;"><a  <?php echo $depts_sort; ?>href="groups.php?<?php echo $qstr; ?>&sort=depts"><?php echo __('แผนก');?></a></th>
            <th width="100"><a  <?php echo $created_sort; ?> href="groups.php?<?php echo $qstr; ?>&sort=created"><?php echo __('สร้างเมื่อ');?></a></th>
            <th width="120"><a  <?php echo $updated_sort; ?> href="groups.php?<?php echo $qstr; ?>&sort=updated"><?php echo __('อัพเดทล่าสุด');?></a></th>
        </tr>
    </thead>
    <tbody>
    <?php
        $total=0;
        $ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
        if($res && db_num_rows($res)) {
            while ($row = db_fetch_array($res)) {
                $sel=false;
                if($ids && in_array($row['group_id'],$ids))
                    $sel=true;
                ?>
            <tr id="<?php echo $row['group_id']; ?>">
                <td width=7px>
                  <input type="checkbox" class="ckb" name="ids[]" value="<?php echo $row['group_id']; ?>"
                            <?php echo $sel?'checked="checked"':''; ?>> </td>
                <td><a href="groups.php?id=<?php echo $row['group_id']; ?>"><?php echo $row['group_name']; ?></a> &nbsp;</td>
                <td>&nbsp;<?php echo $row['group_enabled']?__('เปิดใช้งาน'):'<b>'.__('ปิดใช้งาน').'</b>'; ?></td>
                <td style="text-align:right;padding-right:30px">&nbsp;&nbsp;
                    <?php if($row['users']>0) { ?>
                        <a href="staff.php?gid=<?php echo $row['group_id']; ?>"><?php echo $row['users']; ?></a>
                    <?php }else{ ?> 0
                    <?php } ?>
                    &nbsp;
                </td>
                <td style="text-align:right;padding-right:30px">&nbsp;&nbsp;
                    <?php echo $row['depts']; ?>
                </td>
                <td><?php echo Format::db_date($row['created']); ?>&nbsp;</td>
                <td><?php echo Format::db_datetime($row['updated']); ?>&nbsp;</td>
            </tr>
            <?php
            } //end of while.
        } ?>
    <tfoot>
     <tr>
        <td colspan="7">
            <?php if($res && $num){ ?>
            <?php echo __('Select');?>:&nbsp;
            <a id="selectAll" href="#ckb"><?php echo __('ทั้งหมด');?></a>&nbsp;&nbsp;
            <a id="selectNone" href="#ckb"><?php echo __('ไม่เลือก');?></a>&nbsp;&nbsp;
            <a id="selectToggle" href="#ckb"><?php echo __('สลับ');?></a>&nbsp;&nbsp;
            <?php }else{
                echo __('ไม่พบกลุ่ม!');
            } ?>
        </td>
     </tr>
    </tfoot>
</table>
<?php
if($res && $num): //Show options..
?>
<p class="centered" id="actions">
    <input class="button" type="submit" name="enable" value="<?php echo __('เปิดใช้งาน');?>" >
    <input class="button" type="submit" name="disable" value="<?php echo __('ปิดใช้งาน');?>" >
    <input class="button" type="submit" name="delete" value="<?php echo __('ลบทิ้ง');?>">
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
        <?php echo sprintf(__('คุณแน่ใจหรือว่าต้องการ <b>เปิดใช้งาน</b> %s?'),
            _N('กลุ่มที่เลือก', 'กลุ่มที่เลือก', 2));?>
    </p>
    <p class="confirm-action" style="display:none;" id="disable-confirm">
        <?php echo sprintf(__('คุณแน่ใจหรือว่าต้องการ <b>ปิดใช้งาน</b> %s?'),
            _N('กลุ่มที่เลือก', 'กลุ่มที่เลือก', 2));?>
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong><?php echo sprintf(__('คุณแน่ใจหรือว่าต้องการลบ %s?'),
            _N('กลุ่มที่เลือก', 'กลุ่มที่เลือก', 2));?></strong></font>
        <br><br><?php echo __("ข้อมูลที่ถูกลบจะไม่สามารถกู้คืนได้ และอาจส่งผลกระทบต่อการเข้าใช้งานของเจ้าหน้าที่");?>
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
