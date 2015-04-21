<?php
/*********************************************************************
    filters.php

    Email Filters

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.filter.php');
require_once(INCLUDE_DIR.'class.canned.php');

$filter=null;
if($_REQUEST['id'] && !($filter=Filter::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('ตัวกรองคำขอใช้บริการ'));

/* NOTE: Banlist has its own interface*/
if($filter && $filter->isSystemBanlist())
    Http::redirect('banlist.php');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$filter){
                $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('ตัวกรองคำขอใช้บริการ'));
            }elseif($filter->update($_POST,$errors)){
                $msg=sprintf(__('ปรับปรุง %s เรียบร้อย'), __('ตัวกรองคำขอใช้บริการนี้'));
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('ไม่สามารถปรับปรุง %s ได้ กรุณาแก้ไขปัญหาและลองใหม่อีกครั้ง'),
                    __('ตัวกรองคำขอใช้บริการนี้'));
            }
            break;
        case 'add':
            if((Filter::create($_POST,$errors))){
                $msg=sprintf(__('ปรับปรุง %s เรียบร้อย'), __('ตัวกรองคำขอใช้บริการนี้'));
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('ไม่สามารถเพิ่ม %s ได้ กรุณาแก้ไขปัญหาและลองใหม่อีกครั้'),
                    __('ตัวกรองคำขอใช้บริการนี้'));
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = sprintf(__('คุณต้องเลือกอย่างน้อย %s เพื่อดำเนินการ'),
                    __('หนึ่งตัวกรอง'));
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.FILTER_TABLE.' SET isactive=1 '
                            .' WHERE id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('เปิดใช้งาน %s เรียบร้อย'),
                                    _N('ตัวกรองคำขอใช้บริการที่เลือก', 'ตัวกรองคำขอใช้บริการที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %3$s เปิดใช้งาน'), $num, $count,
                                    _N('ตัวกรองคำขอใช้บริการที่เลือก', 'ตัวกรองคำขอใช้บริการที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่สามารถเปิดใช้งาน %s ได้'),
                                _N('ตัวกรองคำขอใช้บริการที่เลือก', 'ตัวกรองคำขอใช้บริการที่เลือก', $count));
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.FILTER_TABLE.' SET isactive=0 '
                            .' WHERE id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('ปิดใช้งาน %s เรียบร้อย'),
                                    _N('ตัวกรองคำขอใช้บริการที่เลือก', 'ตัวกรองคำขอใช้บริการที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %3$s ถูกปิดใช้งาน'), $num, $count,
                                    _N('ตัวกรองคำขอใช้บริการที่เลือก', 'ตัวกรองคำขอใช้บริการที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่สามารถปิดใช้งาน %s ได้'),
                                _N('ตัวกรองคำขอใช้บริการที่เลือก', 'ตัวกรองคำขอใช้บริการที่เลือก', $count));
                        }
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($f=Filter::lookup($v)) && !$f->isSystemBanlist() && $f->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = sprintf(__('ลบ %s เรียบร้อย'),
                                _N('ตัวกรองคำขอใช้บริการที่เลือก', 'ตัวกรองคำขอใช้บริการที่เลือก', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d จาก %2$d %s ถูกลบ'), $i, $count,
                                _N('ตัวกรองคำขอใช้บริการที่เลือก', 'ตัวกรองคำขอใช้บริการที่เลือก', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('ไม่สามารถลบ %s ได้'),
                                 _N('ตัวกรองคำขอใช้บริการที่เลือก', 'ตัวกรองคำขอใช้บริการที่เลือก', $count));
                        break;
                    default:
                        $errors['err']=__('เกิดข้อผิดพลาด กรุณาติดต่อผู้ดูแลระบบ');
                }
            }
            break;
        default:
            $errors['err']=__('คำสั่งไม่ถูกต้อง');
            break;
    }
}

$page='filters.inc.php';
$tip_namespace = 'manage.filter';
if($filter || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='filter.inc.php';
}

$nav->setTabActive('manage');
$ost->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
