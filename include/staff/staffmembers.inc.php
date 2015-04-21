<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('ปฏิเสธการเข้าถึง');
$qstr='';
$select='SELECT staff.*,CONCAT_WS(" ",firstname,lastname) as name, grp.group_name, dept.dept_name as dept,count(m.team_id) as teams ';
$from='FROM '.STAFF_TABLE.' staff '.
      'LEFT JOIN '.GROUP_TABLE.' grp ON(staff.group_id=grp.group_id) '.
      'LEFT JOIN '.DEPT_TABLE.' dept ON(staff.dept_id=dept.dept_id) '.
      'LEFT JOIN '.TEAM_MEMBER_TABLE.' m ON(m.staff_id=staff.staff_id) ';
$where='WHERE 1 ';

if($_REQUEST['did'] && is_numeric($_REQUEST['did'])) {
    $where.=' AND staff.dept_id='.db_input($_REQUEST['did']);
    $qstr.='&did='.urlencode($_REQUEST['did']);
}

if($_REQUEST['gid'] && is_numeric($_REQUEST['gid'])) {
    $where.=' AND staff.group_id='.db_input($_REQUEST['gid']);
    $qstr.='&gid='.urlencode($_REQUEST['gid']);
}

if($_REQUEST['tid'] && is_numeric($_REQUEST['tid'])) {
    $where.=' AND m.team_id='.db_input($_REQUEST['tid']);
    $qstr.='&tid='.urlencode($_REQUEST['tid']);
}

$sortOptions=array('name'=>'staff.firstname,staff.lastname','username'=>'staff.username','status'=>'isactive',
                   'group'=>'grp.group_name','dept'=>'dept.dept_name','created'=>'staff.created','login'=>'staff.lastlogin');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
$sort=($_REQUEST['sort'] && $sortOptions[strtolower($_REQUEST['sort'])])?strtolower($_REQUEST['sort']):'name';
//Sorting options...
if($sort && $sortOptions[$sort]) {
    $order_column =$sortOptions[$sort];
}
$order_column=$order_column?$order_column:'staff.firstname,staff.lastname';

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

$total=db_count('SELECT count(DISTINCT staff.staff_id) '.$from.' '.$where);
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total,$page,PAGE_LIMIT);
$pageNav->setURL('staff.php',$qstr.'&sort='.urlencode($_REQUEST['sort']).'&order='.urlencode($_REQUEST['order']));
//Ok..lets roll...create the actual query
$qstr.='&order='.($order=='DESC'?'ASC':'DESC');
$query="$select $from $where GROUP BY staff.staff_id ORDER BY $order_by LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
//echo $query;
?>
<h2><?php echo __('เจ้าหน้าที่');?></h2>
<div class="pull-left" style="width:700px;">
    <form action="staff.php" method="GET" name="filter">
     <input type="hidden" name="a" value="filter" >
        <select name="did" id="did">
             <option value="0">&mdash; <?php echo __('ทุกแผนก');?> &mdash;</option>
             <?php
             $sql='SELECT dept.dept_id, dept.dept_name,count(staff.staff_id) as users  '.
                  'FROM '.DEPT_TABLE.' dept '.
                  'INNER JOIN '.STAFF_TABLE.' staff ON(staff.dept_id=dept.dept_id) '.
                  'GROUP By dept.dept_id HAVING users>0 ORDER BY dept_name';
             if(($res=db_query($sql)) && db_num_rows($res)){
                 while(list($id,$name, $users)=db_fetch_row($res)){
                     $sel=($_REQUEST['did'] && $_REQUEST['did']==$id)?'selected="selected"':'';
                     echo sprintf('<option value="%d" %s>%s (%s)</option>',$id,$sel,$name,$users);
                 }
             }
             ?>
        </select>
        <select name="gid" id="gid">
            <option value="0">&mdash; <?php echo __('ทุกกลุ่ม');?> &mdash;</option>
             <?php
             $sql='SELECT grp.group_id, group_name,count(staff.staff_id) as users '.
                  'FROM '.GROUP_TABLE.' grp '.
                  'INNER JOIN '.STAFF_TABLE.' staff ON(staff.group_id=grp.group_id) '.
                  'GROUP BY grp.group_id ORDER BY group_name';
             if(($res=db_query($sql)) && db_num_rows($res)){
                 while(list($id,$name,$users)=db_fetch_row($res)){
                     $sel=($_REQUEST['gid'] && $_REQUEST['gid']==$id)?'selected="selected"':'';
                     echo sprintf('<option value="%d" %s>%s (%s)</option>',$id,$sel,$name,$users);
                 }
             }
             ?>
        </select>
        <select name="tid" id="tid">
            <option value="0">&mdash; <?php echo __('ทุกทีม');?> &mdash;</option>
             <?php
             $sql='SELECT team.team_id, team.name, count(member.staff_id) as users FROM '.TEAM_TABLE.' team '.
                  'INNER JOIN '.TEAM_MEMBER_TABLE.' member ON(member.team_id=team.team_id) '.
                  'GROUP BY team.team_id ORDER BY team.name';
             if(($res=db_query($sql)) && db_num_rows($res)){
                 while(list($id,$name,$users)=db_fetch_row($res)){
                     $sel=($_REQUEST['tid'] && $_REQUEST['tid']==$id)?'selected="selected"':'';
                     echo sprintf('<option value="%d" %s>%s (%s)</option>',$id,$sel,$name,$users);
                 }
             }
             ?>
        </select>
        &nbsp;&nbsp;
        <input type="submit" name="submit" value="<?php echo __('นำไปใช้');?>"/>
    </form>
 </div>
<div class="pull-right flush-right" style="padding-right:5px;"><b><a href="staff.php?a=add" class="Icon newstaff"><?php echo __('เพิ่มเจ้าหน้าที่ใหม่');?></a></b></div>
<div class="clear"></div>
<?php
$res=db_query($query);
if($res && ($num=db_num_rows($res)))
    $showing=$pageNav->showing() . ' ' . _N('agent', 'agents', $num);
else
    $showing=__('ไม่พบเจ้าหน้าที่!');
?>
<form action="staff.php" method="POST" name="staff" >
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="mass_process" >
 <input type="hidden" id="action" name="a" value="" >
 <table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <caption><?php echo $showing; ?></caption>
    <thead>
        <tr>
            <th width="7px">&nbsp;</th>
            <th width="200"><a <?php echo $name_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=name"><?php echo __('ชื่อ');?></a></th>
            <th width="100"><a <?php echo $username_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=username"><?php echo __('ชื่อผู้ใช้');?></a></th>
            <th width="100"><a  <?php echo $status_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=status"><?php echo __('สถานะ');?></a></th>
            <th width="120"><a  <?php echo $group_sort; ?>href="staff.php?<?php echo $qstr; ?>&sort=group"><?php echo __('กลุ่ม');?></a></th>
            <th width="150"><a  <?php echo $dept_sort; ?>href="staff.php?<?php echo $qstr; ?>&sort=dept"><?php echo __('แผนก');?></a></th>
            <th width="100"><a <?php echo $created_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=created"><?php echo __('สร้างเมื่อ');?></a></th>
            <th width="145"><a <?php echo $login_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=login"><?php echo __('เข้าสู่ระบบล่าสุด');?></a></th>
        </tr>
    </thead>
    <tbody>
    <?php
        if($res && db_num_rows($res)):
            $ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
            while ($row = db_fetch_array($res)) {
                $sel=false;
                if($ids && in_array($row['staff_id'],$ids))
                    $sel=true;
                ?>
               <tr id="<?php echo $row['staff_id']; ?>">
                <td width=7px>
                  <input type="checkbox" class="ckb" name="ids[]" value="<?php echo $row['staff_id']; ?>" <?php echo $sel?'checked="checked"':''; ?> >
                <td><a href="staff.php?id=<?php echo $row['staff_id']; ?>"><?php echo Format::htmlchars($row['name']); ?></a>&nbsp;</td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['isactive']?__('เปิดใช้งาน'):'<b>'.__('ถูกล็อค').'</b>'; ?>&nbsp;<?php echo $row['onvacation']?'<small>(<i>'.__('พักร้อน').'</i>)</small>':''; ?></td>
                <td><a href="groups.php?id=<?php echo $row['group_id']; ?>"><?php echo Format::htmlchars($row['group_name']); ?></a></td>
                <td><a href="departments.php?id=<?php echo $row['dept_id']; ?>"><?php echo Format::htmlchars($row['dept']); ?></a></td>
                <td><?php echo Format::db_date($row['created']); ?></td>
                <td><?php echo Format::db_datetime($row['lastlogin']); ?>&nbsp;</td>
               </tr>
            <?php
            } //end of while.
        endif; ?>
    <tfoot>
     <tr>
        <td colspan="8">
            <?php if($res && $num){ ?>
            <?php echo __('Select');?>:&nbsp;
            <a id="selectAll" href="#ckb"><?php echo __('ทั้งหมด');?></a>&nbsp;&nbsp;
            <a id="selectNone" href="#ckb"><?php echo __('ไม่เลือก');?></a>&nbsp;&nbsp;
            <a id="selectToggle" href="#ckb"><?php echo __('สลับ');?></a>&nbsp;&nbsp;
            <?php }else{
                echo __('ไม่พบเจ้าหน้าที่!');
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
    &nbsp;&nbsp;
    <input class="button" type="submit" name="disable" value="<?php echo __('ล็อค');?>" >
    &nbsp;&nbsp;
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
        <?php echo sprintf(__('คุณแน่ใจหรือว่าต้องการ <b>เปิดใช้งาน</b> (ปลดล็อค) %s?'),
            _N('เจ้าหน้าที่นี้', 'เจ้าหน้าที่นี้', 2));?>
    </p>
    <p class="confirm-action" style="display:none;" id="disable-confirm">
        <?php echo sprintf(__('คุณแน่ใจหรือว่าต้องการ <b>ปิดใช้งาน</b> (ล็อค) %s?'),
            _N('เจ้าหน้าที่นี้', 'เจ้าหน้าที่นี้', 2));?>
        <br><br><?php echo __("เจ้าหน้าที่ที่ถูกล็อคจะไม่สามารถเข้าถึงแผงควบคุมเจ้าหน้าที่ได้");?>
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong><?php echo sprintf(__('คุณแน่ใจหรือว่าต้องการลบ %s?'),
            _N('เจ้าหน้าที่นี้', 'เจ้าหน้าที่นี้', 2));?></strong></font>
        <br><br><?php echo __('ข้อมูลที่ถูกลบจะไม่สามารถกู้คืนได้');?>
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
