<?php
/*********************************************************************
    staff.php

    Evertything about staff members.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');

$staff=null;
if($_REQUEST['id'] && !($staff=Staff::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: ผิดพลาด'), __('เจ้าหน้าที่'));

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$staff){
                $errors['err']=sprintf(__('%s: ผิดพลาด'), __('เจ้าหน้าที่'));
            }elseif($staff->update($_POST,$errors)){
                $msg=sprintf(__('ปรับปรุง %s เรียบร้อย'),
                    __('เจ้าหน้าที่นี้'));
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('ไม่สามารถปรับปรุง %s ได้ กรุณาแก้ไขและลองใหม่อีกครั้ง!'),
                    __('เจ้าหน้าที่นี้'));
            }
            break;
        case 'create':
            if(($id=Staff::create($_POST,$errors))){
                $msg=sprintf(__('เพิ่ม %s เรียบร้อย'),Format::htmlchars($_POST['firstname']));
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('ไม่สามารถเพิ่ม %s ได้ กรุณาแก้ไขและลองใหม่อีกครั้ง'),
                    __('เจ้าหน้าที่นี้'));
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = sprintf(__('คุณต้องเลือกอย่างน้อย %s '),
                    __('หนึ่งเจ้าหน้าที่'));
            } elseif(in_array($thisstaff->getId(),$_POST['ids'])) {
                $errors['err'] = __('คุณไม่สามารถลบ/ปิดการใช้งานตัวเองได้ เพราะขณะนี้เหลือคุณที่เป็นผู้ดูแลระบบคนเดียวเท่านั้น!');
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.STAFF_TABLE.' SET isactive=1 '
                            .' WHERE staff_id IN ('.implode(',', db_input($_POST['ids'])).')';

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf('เปิดการใช้งาน %s เรียบร้อย',
                                    _N('เจ้าหน้าที่ที่เลือก', 'เจ้าหน้าที่ที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %3$s เปิดใช้งาน'), $num, $count,
                                    _N('เจ้าหน้าที่ที่เลือก', 'เจ้าหน้าที่ที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่สามารถเปิดใช้งาน %s ได้'),
                                _N('เจ้าหน้าที่ที่เลือก', 'เจ้าหน้าที่ที่เลือก', $count));
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.STAFF_TABLE.' SET isactive=0 '
                            .' WHERE staff_id IN ('.implode(',', db_input($_POST['ids'])).') AND staff_id!='.db_input($thisstaff->getId());

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf('ปิดใช้งาน %s เรียบร้อย',
                                    _N('เจ้าหน้าที่ที่เลือก', 'เจ้าหน้าที่ที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %3$s ปิดใช้งาน'), $num, $count,
                                    _N('เจ้าหน้าที่ที่เลือก', 'เจ้าหน้าที่ที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่สามารถปิดใช้งาน %s ได้'),
                                _N('เจ้าหน้าที่ที่เลือก', 'เจ้าหน้าที่ที่เลือก', $count));
                        }
                        break;
                    case 'delete':
                        foreach($_POST['ids'] as $k=>$v) {
                            if($v!=$thisstaff->getId() && ($s=Staff::lookup($v)) && $s->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = sprintf(__('ลบ %s เรียบร้อย'),
                                _N('เจ้าหน้าที่ที่เลือก', 'เจ้าหน้าที่ที่เลือก', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d จาก %2$d %3$s ถูกลบ'), $i, $count,
                                _N('เจ้าหน้าที่ที่เลือก', 'เจ้าหน้าที่ที่เลือก', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('ไม่สามารถลบ %s ได้'),
                                _N('เจ้าหน้าที่ที่เลือก', 'เจ้าหน้าที่ที่เลือก', $count));
                        break;
                    default:
                        $errors['err'] = __('เกิดข้อผิดพลาด ติดต่อผู้ดูแลระบบ.');
                }

            }
            break;
        default:
            $errors['err']=__('เกิดข้อผิดพลาด');
            break;
    }
}

$page='staffmembers.inc.php';
$tip_namespace = 'staff.agent';
if($staff || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='staff.inc.php';
}

$nav->setTabActive('staff');
$ost->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
