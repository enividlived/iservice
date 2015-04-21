<?php
/*********************************************************************
    emails.php

    Emails

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.email.php');

$email=null;
if($_REQUEST['id'] && !($email=Email::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('อีเมล'));

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$email){
                $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('อีเมล'));
            }elseif($email->update($_POST,$errors)){
                $msg=sprintf(__('ปรับปรุง %s เรียบร้อย'),
                    __('อีเมลนี้'));
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('มีปัญหาในการปรับปรุง %s กรุณาลองใหม่อีกครั้ง!'), __('อีเมลนี้'));
            }
            break;
        case 'create':
            if(($id=Email::create($_POST,$errors))){
                $msg=sprintf(__('เพิ่ม %s เรียบร้อย'), Format::htmlchars($_POST['name']));
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('ไม่สามารถเพิ่ม %s ได้ กรุณาแก้ไขปัญหาและลองใหม่อีกครั้ง'),
                    __('อีเมลนี้'));
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = sprintf(__('คุณต้องเลือกอย่างน้อย %s'),
                    __('หนึ่งอีเมล'));
            } else {
                $count=count($_POST['ids']);

                $sql='SELECT count(dept_id) FROM '.DEPT_TABLE.' dept '
                    .' WHERE email_id IN ('.implode(',', db_input($_POST['ids'])).') '
                    .' OR autoresp_email_id IN ('.implode(',', db_input($_POST['ids'])).')';

                list($depts)=db_fetch_row(db_query($sql));
                if($depts>0) {
                    $errors['err'] = __('อีเมลนี้กำลังถูกใช้งานอยู่โดยแผนกใดแผนกหนึ่ง กรุณาปลดออกก่อนทำการลบ');
                } elseif(!strcasecmp($_POST['a'], 'delete')) {
                    $i=0;
                    foreach($_POST['ids'] as $k=>$v) {
                        if($v!=$cfg->getDefaultEmailId() && ($e=Email::lookup($v)) && $e->delete())
                            $i++;
                    }

                    if($i && $i==$count)
                        $msg = sprintf(__('ลบ %s เรียบร้อย'),
                            _N('อีเมลที่เลือก', 'อีเมลที่เลือก', $count));
                    elseif($i>0)
                        $warn = sprintf(__('%1$d จาก %2$d %3$s ถูกลบ'), $i, $count,
                            _N('อีเมลที่เลือก', 'อีเมลที่เลือก', $count));
                    elseif(!$errors['err'])
                        $errors['err'] = sprintf(__('ไม่สามารถลบ %s ได้'),
                            _N('อีเมลที่เลือก', 'อีเมลที่เลือก', $count));

                } else {
                    $errors['err'] = __('เกิดข้อผิดพลาด กรุณาติดต่อผู้ดูแลระบบ');
                }
            }
            break;
        default:
            $errors['err'] = __('เกิดข้อผิดพลาด');
            break;
    }
}

$page='emails.inc.php';
$tip_namespace = 'emails.email';
if($email || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='email.inc.php';
}

$nav->setTabActive('emails');
$ost->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
