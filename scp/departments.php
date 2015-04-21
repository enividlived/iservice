<?php
/*********************************************************************
    departments.php

    Departments

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');

$dept=null;
if($_REQUEST['id'] && !($dept=Dept::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('แผนก'));

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$dept){
                $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('แผนก'));
            }elseif($dept->update($_POST,$errors)){
                $msg=sprintf(__('ปรับปรุง %s เรียบร้อย'),
                    __('แผนกนี้'));
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('ไม่สามารถปรับปรุง %s ได้ กรุณาลองอีกครั้ง!'),
                    __('แผนกนี้'));
            }
            break;
        case 'create':
            if(($id=Dept::create($_POST,$errors))){
                $msg=sprintf(__('เพิ่ม "%s" เรียบร้อย'),Format::htmlchars($_POST['name']));
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('ไม่สามารถเพิ่ม %s ได้ กรุณาแก้ไขปัญหาและลองใหม่อีกครั้ง'),
                    __('แผนกนี้'));
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = sprintf(__('คุณต้องเลือกอย่างน้อย %s'),
                    __('หนึ่งแผนก'));
            }elseif(in_array($cfg->getDefaultDeptId(),$_POST['ids'])) {
                $errors['err'] = __('ไม่สามารถปิดการใช้งานหรือลบแผนกเริ่มต้นได้ เลือกแผนกเริ่มต้นใหม่และลองอีกครั้ง');
            }else{
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'make_public':
                        $sql='UPDATE '.DEPT_TABLE.' SET ispublic=1 '
                            .' WHERE dept_id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())){
                            if($num==$count)
                                $msg=sprintf(__('ทำให้ %s เป็นสาธารณะเรียบร้อยแล้ว'),
                                    _N('แผนกที่เลือก', 'แผนกที่เลือก', $count));
                            else
                                $warn=sprintf(__(
                                    /* Phrase will read:
                                       <a> of <b> <selected objects> made PUBLIC */
                                    '%1$d จาก %2$d %s ถูกทำให้เป็นสาธารณะ'), $num, $count,
                                    _N('แผนกที่เลือก', 'แผนกที่เลือก', $count));
                        } else {
                            $errors['err']=sprintf(__('ไม่สามารถทำให้ %s เป็นสาธารณะได้'),
                                _N('แผนกที่เลือก', 'แผนกที่เลือก', $count));
                        }
                        break;
                    case 'make_private':
                        $sql='UPDATE '.DEPT_TABLE.' SET ispublic=0  '
                            .' WHERE dept_id IN ('.implode(',', db_input($_POST['ids'])).') '
                            .' AND dept_id!='.db_input($cfg->getDefaultDeptId());
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('ทำให้ %s เป็นส่วนตัวแล้ว'),
                                    _N('แผนกที่เลือก', 'แผนกที่เลือก', $count));
                            else
                                $warn = sprintf(__(
                                    /* Phrase will read:
                                       <a> of <b> <selected objects> made PRIVATE */
                                    '%1$d จาก %2$d %3$s ถูกทำให้เป็นส่วนตัว'), $num, $count,
                                    _N('แผนกที่เลือก', 'แผนกที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่สามารถทำให้ %s เป็นส่วนตัวได้ แผนกอาจตั้งเป็นส่วนตัวอยู่แล้ว!'),
                                _N('แผนกที่เลือก', 'แผนกที่เลือก', $count));
                        }
                        break;
                    case 'delete':
                        //Deny all deletes if one of the selections has members in it.
                        $sql='SELECT count(staff_id) FROM '.STAFF_TABLE
                            .' WHERE dept_id IN ('.implode(',', db_input($_POST['ids'])).')';
                        list($members)=db_fetch_row(db_query($sql));
                        if($members)
                            $errors['err']=__('แผนกที่มีเจ้าหน้าที่ไม่สามารถลบได้ ย้ายเจ้าหน้าที่ออกจากแผนกก่อน');
                        else {
                            $i=0;
                            foreach($_POST['ids'] as $k=>$v) {
                                if($v!=$cfg->getDefaultDeptId() && ($d=Dept::lookup($v)) && $d->delete())
                                    $i++;
                            }
                            if($i && $i==$count)
                                $msg = sprintf(__('ลบ %s เรียบร้อย'),
                                    _N('แผนกที่เลือก', 'แผนกที่เลือก', $count));
                            elseif($i>0)
                                $warn = sprintf(__(
                                    /* Phrase will read:
                                       <a> of <b> <selected objects> deleted */
                                    '%1$d จาก %2$d %3$s ถูกลบ'), $i, $count,
                                    _N('แผนกที่เลือก', 'แผนกที่เลือก', $count));
                            elseif(!$errors['err'])
                                $errors['err'] = sprintf(__('ไม่สามารถลบ %s ได้'),
                                    _N('แผนกที่เลือก', 'แผนกที่เลือก', $count));
                        }
                        break;
                    default:
                        $errors['err']=__('เกิดข้อผิดพลาด กรุณาติดต่อผู้ดูแลระบบ');
                }
            }
            break;
        default:
            $errors['err']=__('เกิดข้อผิดพลาด');
            break;
    }
}

$page='departments.inc.php';
$tip_namespace = 'staff.department';
if($dept || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='department.inc.php';
}

$nav->setTabActive('staff');
$ost->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
