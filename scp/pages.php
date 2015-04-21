<?php
/*********************************************************************
    pages.php

    Site pages.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
require_once(INCLUDE_DIR.'class.page.php');

$page = null;
if($_REQUEST['id'] && !($page=Page::lookup($_REQUEST['id'])))
   $errors['err']=sprintf(__('%s: เกิดข้อผิดพลาด'), __('เพจ'));

if($_POST) {
    switch(strtolower($_POST['do'])) {
        case 'add':
            if(($pageId=Page::create($_POST, $errors))) {
                $_REQUEST['a'] = null;
                $msg=sprintf(__('เพิ่ม %s เรียบร้อย'), Format::htmlchars($_POST['name']));
                // Attach inline attachments from the editor
                if ($page = Page::lookup($pageId))
                    $page->attachments->upload(
                        Draft::getAttachmentIds($_POST['body']), true);
                Draft::deleteForNamespace('page');
            } elseif(!$errors['err'])
                $errors['err'] = sprintf(__('ไม่สามารถเพิ่ม %s ได้ กรุณาแก้ไขและลองใหม่'),
                    __('เพจนี้'));
        break;
        case 'update':
            if(!$page)
                $errors['err'] = sprintf(__('%s: เกิดข้อผิดพลาด'),
                    __('เพจ'));
            elseif($page->update($_POST, $errors)) {
                $msg=sprintf(__('ปรับปรุง %s เรียบร้อย'),
                    __('เพจนี้'));
                $_REQUEST['a']=null; //Go back to view
                // Attach inline attachments from the editor
                $page->attachments->deleteInlines();
                $page->attachments->upload(
                    Draft::getAttachmentIds($_POST['body']),
                    true);
                Draft::deleteForNamespace('page.'.$page->getId());
            } elseif(!$errors['err'])
                $errors['err'] = sprintf(__('ไม่สามารถปรับปรุง %s ได้ กรุณาแก้ไขและลองใหม่อีกครั้ง'),
                    __('เพจนี้'));
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = sprintf(__('คุณต้องเลือกอย่างน้อย %s'),
                    __('หนึ่งหน้าเพจ'));
            } elseif(array_intersect($_POST['ids'], $cfg->getDefaultPages()) && strcasecmp($_POST['a'], 'enable')) {
                $errors['err'] = sprintf(__('รายการ %s กำลังถูกใช้งาน และไม่สามารถปิดการใช้งานหรือลบออกได้'),
                    _N('เพจที่เลือก', 'เพจที่เลือก', 2));
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.PAGE_TABLE.' SET isactive=1 '
                            .' WHERE id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('เปิดใช้งาน %s เรียบร้อย'),
                                    _N('เพจที่เลือก', 'เพจที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %3$s เปิดใช้งาน'), $num, $count,
                                    _N('เพจที่เลือก', 'เพจที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่สามารถเปิดใช้งาน %s ได้'),
                                _N('เพจที่เลือก', 'เพจที่เลือก', $count));
                        }
                        break;
                    case 'disable':
                        $i = 0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($p=Page::lookup($v)) && $p->disable())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = sprintf(__('ปิดใช้งาน %s เรียบร้อย'),
                                _N('เพจที่เลือก', 'เพจที่เลือก', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d จาก %2$d %3$s ถูกปิดใช้งาน'), $i, $count,
                                _N('เพจที่เลือก', 'เพจที่เลือก', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('ไม่สามารถปิดใช้งาน %s ได้'),
                                _N('เพจที่เลือก', 'เพจที่เลือก', $count));
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($p=Page::lookup($v)) && $p->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = sprintf(__('ลบ %s เรียบร้อย'),
                                _N('เพจที่เลือก', 'เพจที่เลือก', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d จาก %2$d %3$s ถูกลบ'), $i, $count,
                                _N('เพจที่เลือก', 'เพจที่เลือก', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('ไม่สามารถลบ %s ได้'),
                                _N('เพจที่เลือก', 'เพจที่เลือก', $count));
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

$inc='pages.inc.php';
$tip_namespace = 'manage.pages';
if($page || $_REQUEST['a']=='add') {
    $inc='page.inc.php';
}

$nav->setTabActive('manage');
$ost->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require_once(STAFFINC_DIR.'header.inc.php');
require_once(STAFFINC_DIR.$inc);
require_once(STAFFINC_DIR.'footer.inc.php');
?>
