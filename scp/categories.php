<?php
/*********************************************************************
    categories.php

    FAQ categories

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('staff.inc.php');
include_once(INCLUDE_DIR.'class.category.php');

/* check permission */
if(!$thisstaff || !$thisstaff->canManageFAQ()) {
    header('Location: kb.php');
    exit;
}


$category=null;
if($_REQUEST['id'] && !($category=Category::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('หัวข้อบทความ'));

if($_POST){
    switch(strtolower($_POST['do'])) {
        case 'update':
            if(!$category) {
                $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('หัวข้อบทความ'));
            } elseif($category->update($_POST,$errors)) {
                $msg=sprintf(__('ปรับปรุง %s เรียบร้อย'),
                    __('หัวข้อบทความที่เลือก'));
            } elseif(!$errors['err']) {
                $errors['err']=sprintf(__('ไม่สามารถปรับปรุง %s ได้ กรุณาแก้ไขปัญหาและลองใหม่อีกครั้ง'), __('หัวข้อบทความที่เลือก'));
            }
            break;
        case 'create':
            if(($id=Category::create($_POST,$errors))) {
                $msg=sprintf(__('เพิ่ม %s เรียบร้อย'), Format::htmlchars($_POST['name']));
                $_REQUEST['a']=null;
            } elseif(!$errors['err']) {
                $errors['err']=sprintf(__('ไม่สามารถเพิ่ม %s ได้ กรุณาแก้ไขปัญหาและลองใหม่อีกครั้ง'),
                    __('หัวข้อบทความนี้'));
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err']=sprintf(__('คุณต้องเลือกอย่างน้อย %s'), __('หนึ่งหัวข้อบทความ'));
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'make_public':
                        $sql='UPDATE '.FAQ_CATEGORY_TABLE.' SET ispublic=1 '
                            .' WHERE category_id IN ('.implode(',', db_input($_POST['ids'])).')';

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('ทำให้ %s เป็นสาธารณะเรียบร้อยแล้ว'),
                                    _N('หัวข้อบทความที่เลือก', 'หัวข้อบทความที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %3$s ถูกทำให้เป็นสาธารณะ'), $num, $count,
                                    _N('หัวข้อบทความที่เลือก', 'หัวข้อบทความที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่สามารถทำให้ %s เป็นสาธารณะได้'),
                                _N('หัวข้อบทความที่เลือก', 'หัวข้อบทความที่เลือก', $count));
                        }
                        break;
                    case 'make_private':
                        $sql='UPDATE '.FAQ_CATEGORY_TABLE.' SET ispublic=0 '
                            .' WHERE category_id IN ('.implode(',', db_input($_POST['ids'])).')';

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('ทำให้ %s เป็นส่วนตัวเรียบร้อยแล้ว'),
                                    _N('หัวข้อบทความที่เลือก', 'หัวข้อบทความที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %3$s ถูกทำให้เป็นส่วนตัว'), $num, $count,
                                    _N('หัวข้อบทความที่เลือก', 'หัวข้อบทความที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่่สามารถทำให้ %s เป็นส่วนตัวได้'),
                                _N('หัวข้อบทความที่เลือก', 'หัวข้อบทความที่เลือก', $count));
                        }
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($c=Category::lookup($v)) && $c->delete())
                                $i++;
                        }

                        if($i==$count)
                            $msg = sprintf(__('ลบ %s เรียบร้อย'),
                                _N('หัวข้อบทความที่เลือก', 'หัวข้อบทความที่เลือก', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d จาก %2$d %3$s ถูกลบ'), $i, $count,
                                _N('หัวข้อบทความที่เลือก', 'หัวข้อบทความที่เลือก', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('ไม่สามารถลบ %s ได้'),
                                _N('หัวข้อบทความที่เลือก', 'หัวข้อบทความที่เลือก', $count));
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

$page='categories.inc.php';
$tip_namespace = 'knowledgebase.category';
if($category || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='category.inc.php';
}

$nav->setTabActive('kbase');
$ost->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
