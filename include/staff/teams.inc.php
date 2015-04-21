<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('ปฏิเสธการเข้าถึง');

$qstr='';
$sql='SELECT team.*,count(m.staff_id) as members,CONCAT_WS(" ",lead.firstname,lead.lastname) as team_lead '.
     ' FROM '.TEAM_TABLE.' team '.
     ' LEFT JOIN '.TEAM_MEMBER_TABLE.' m ON(m.team_id=team.team_id) '.
     ' LEFT JOIN '.STAFF_TABLE.' lead ON(lead.staff_id=team.lead_id) ';
$sql.=' WHERE 1';
$sortOptions=array('name'=>'team.name','status'=>'team.isenabled','members'=>'members','lead'=>'team_lead','created'=>'team.created');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
$sort=($_REQUEST['sort'] && $sortOptions[strtolower($_REQUEST['sort'])])?strtolower($_REQUEST['sort']):'name';
//Sorting options...
if($sort && $sortOptions[$sort]) {
    $order_column =$sortOptions[$sort];
}
$order_column=$order_column?$order_column:'team.name';

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

$query="$sql GROUP BY team.team_id ORDER BY $order_by";
$res=db_query($query);
if($res && ($num=db_num_rows($res)))
    $showing=sprintf(__('กำลังแสดง 1-%1$d จาก %2$d ทีม'), $num, $num);
else
    $showing=__('ไม่พบทีม!');

?>
<div class="pull-left" style="width:700px;padding-top:5px;">
 <h2><?php echo __('ทีม');?>
    <i class="help-tip icon-question-sign" href="#teams"></i>
    </h2>
 </div>
<div class="pull-right flush-right" style="padding-top:5px;padding-right:5px;">
    <b><a href="teams.php?a=add" class="Icon newteam"><?php echo __('สร้างทีมใหม่');?></a></b></div>
<div class="clear"></div>
<form action="teams.php" method="POST" name="teams">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="mass_process" >
 <input type="hidden" id="action" name="a" value="" >
 <table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <caption><?php echo $showing; ?></caption>
    <thead>
        <tr>
            <th width="7px">&nbsp;</th>
            <th width="250"><a <?php echo $name_sort; ?> href="teams.php?<?php echo $qstr; ?>&sort=name"><?php echo __('ชื่อทีม');?></a></th>
            <th width="80"><a  <?php echo $status_sort; ?> href="teams.php?<?php echo $qstr; ?>&sort=status"><?php echo __('สถานะ');?></a></th>
            <th width="80"><a  <?php echo $members_sort; ?>href="teams.php?<?php echo $qstr; ?>&sort=members"><?php echo __('สมาชิก');?></a></th>
            <th width="200"><a  <?php echo $lead_sort; ?> href="teams.php?<?php echo $qstr; ?>&sort=lead"><?php echo __('หัวหน้าทีม');?></a></th>
            <th width="100"><a  <?php echo $created_sort; ?> href="teams.php?<?php echo $qstr; ?>&sort=created"><?php echo __('สร้างเมื่อ');?></a></th>
            <th width="130"><a  <?php echo $updated_sort; ?> href="teams.php?<?php echo $qstr; ?>&sort=updated"><?php echo __('อัพเดทล่าสุด');?></a></th>
        </tr>
    </thead>
    <tbody>
    <?php
        $total=0;
        $ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
        if($res && db_num_rows($res)):
            while ($row = db_fetch_array($res)) {
                $sel=false;
                if($ids && in_array($row['team_id'],$ids))
                    $sel=true;
                ?>
            <tr id="<?php echo $row['team_id']; ?>">
                <td width=7px>
                  <input type="checkbox" class="ckb" name="ids[]" value="<?php echo $row['team_id']; ?>"
                            <?php echo $sel?'checked="checked"':''; ?>> </td>
                <td><a href="teams.php?id=<?php echo $row['team_id']; ?>"><?php echo $row['name']; ?></a> &nbsp;</td>
                <td>&nbsp;<?php echo $row['isenabled']?__('เปิดใช้งาน'):'<b>'.__('ปิดใช้งาน').'</b>'; ?></td>
                <td style="text-align:right;padding-right:25px">&nbsp;&nbsp;
                    <?php if($row['members']>0) { ?>
                        <a href="staff.php?tid=<?php echo $row['team_id']; ?>"><?php echo $row['members']; ?></a>
                    <?php }else{ ?> 0
                    <?php } ?>
                    &nbsp;
                </td>
                <td><a href="staff.php?id=<?php echo $row['lead_id']; ?>"><?php echo $row['team_lead']; ?>&nbsp;</a></td>
                <td><?php echo Format::db_date($row['created']); ?>&nbsp;</td>
                <td><?php echo Format::db_datetime($row['updated']); ?>&nbsp;</td>
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
                echo __('ไม่พบทีม!');
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
    <p class="confirm-action" style="display:none;" id="enable-confirm">
        <?php echo sprintf(__('คุณแน่ใจหรือว่าต้องการ <b>เปิดใช้งาน</b> %s?'),
            _N('ทีมที่เลือก', 'ทีมที่เลือก', 2));?>
    </p>
    <p class="confirm-action" style="display:none;" id="disable-confirm">
        <?php echo sprintf(__('คุณแน่ใจหรือว่าต้องการ <b>ปิดใช้งาน</b> %s?'),
            _N('ทีมที่เลือก', 'ทีมที่เลือก', 2));?>
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong><?php echo sprintf(__('คุณแน่ใจหรือว่าต้องการลบ %s?'),
            _N('ทีมที่เลือก', 'ทีมที่เลือก', 2));?></strong></font>
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
