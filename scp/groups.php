<?php
/*********************************************************************
    groups.php

    User Groups.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');

$group=null;
if($_REQUEST['id'] && !($group=Group::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('กลุ่ม'));

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$group){
                $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('กลุ่ม'));
            }elseif($group->update($_POST,$errors)){
                $msg=sprintf(__('ปรับปรุง %s เรียบร้อย'),
                    __('กลุ่มนี้'));
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('ไม่สามารถปรับปรุง %s ได้ กรุณาแก้ไขปัญหาและลองใหม่อีกครั้ง!'),
                    __('กลุ่มนี้'));
            }
            break;
        case 'create':
            if(($id=Group::create($_POST,$errors))){
                $msg=sprintf(__('เพิ่ม %s เรียบร้อย'),Format::htmlchars($_POST['name']));
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('ไม่สามารถเพิ่ม %s ได้ กรุณาแก้ไขปัญหาและลองใหม่อีกครั้ง'),
                    __('กลุ่มนี้'));
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = sprintf(__('คุณต้องเลือกอย่างน้อย %s.'), __('หนึ่งกลุ่ม'));
            } elseif(in_array($thisstaff->getGroupId(), $_POST['ids'])) {
                $errors['err'] = __("เนื่องจากคุณเป็นผู้ดูแลระบบ คุณไม่สามารถลบกลุ่มที่ตัวเองเป็นสมาชิกอยู่ได้ ไม่งั้นผู้ดูแลระบบคนอื่นจะเข้าสู่ระบบไม่ได้!");
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.GROUP_TABLE.' SET group_enabled=1, updated=NOW() '
                            .' WHERE group_id IN ('.implode(',', db_input($_POST['ids'])).')';

                        if(db_query($sql) && ($num=db_affected_rows())){
                            if($num==$count)
                                $msg = sprintf(__('เปิดใช้งาน %s เรียบร้อย'),
                                    _N('กลุ่มที่เลือก', 'กลุ่มที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %3$s เปิดใช้งาน'), $num, $count,
                                    _N('กลุ่มที่เลือก', 'กลุ่มที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่สามารถเปิดใช้งาน %s ได้'),
                                _N('กลุ่มที่เลือก', 'กลุ่มที่เลือก', $count));
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.GROUP_TABLE.' SET group_enabled=0, updated=NOW() '
                            .' WHERE group_id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('ปิดใช้งาน %s เรียบร้อย'),
                                    _N('กลุ่มที่เลือก', 'กลุ่มที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %3$s ปิดใช้งาน'), $num, $count,
                                    _N('กลุ่มที่เลือก', 'กลุ่มที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่สามารถปิดใช้งาน %s ได้'),
                                _N('กลุ่มที่เลือก', 'กลุ่มที่เลือก', $count));
                        }
                        break;
                    case 'delete':
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($g=Group::lookup($v)) && $g->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = sprintf(__('ลบ %s เรียบร้อย'),
                                _N('กลุ่มที่เลือก', 'กลุ่มที่เลือก', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d จาก %2$d %3$s ถูกลบ'), $i, $count,
                                _N('กลุ่มที่เลือก', 'กลุ่มที่เลือก', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('ไม่สามารถลบ %s ได้'),
                                _N('กลุ่มที่เลือก', 'กลุ่มที่เลือก', $count));
                        break;
                    default:
                        $errors['err']  = __('เกิดข้อผิดพลาด กรุณาติดต่อผู้ดูแลระบบ');
                }
            }
            break;
        default:
            $errors['err']=__('เกิดข้อผิดพลาด');
            break;
    }
}

$page='groups.inc.php';
$tip_namespace = 'staff.groups';
if($group || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='group.inc.php';
}

$nav->setTabActive('staff');
$ost->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
