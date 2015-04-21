<?php
/*********************************************************************
    users.php

    Peter Rotich <peter@osticket.com>
    Jared Hancock <jared@osticket.com>
    Copyright (c)  2006-2014 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('staff.inc.php');

require_once INCLUDE_DIR.'class.note.php';

$user = null;
if ($_REQUEST['id'] && !($user=User::lookup($_REQUEST['id'])))
    $errors['err'] = sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), _N('ผู้ใช้', 'ผู้ใช้', 1));

if ($_POST) {
    switch(strtolower($_REQUEST['do'])) {
        case 'update':
            if (!$user) {
                $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), _N('ผู้ใช้', 'ผู้ใช้', 1));
            } elseif(($acct = $user->getAccount())
                    && !$acct->update($_POST, $errors)) {
                 $errors['err']=__('ไม่สามารถปรับปรุงข้อมูลผู้ใช้ได้');
            } elseif($user->updateInfo($_POST, $errors)) {
                $msg=sprintf(__('ปรับปรุงข้อมูลของ %s เรียบร้อย'), __('ผู้ใช้นี้'));
                $_REQUEST['a'] = null;
            } elseif(!$errors['err']) {
                $errors['err']=sprintf(__('ไม่สามารถปรับปรุงข้อมูลของ %s ได้ กรุณาแก้ไขและลองใหม่อีกครั้ง!'),
                    __('ผู้ใช้นี้'));
            }
            break;
        case 'create':
            $form = UserForm::getUserForm()->getForm($_POST);
            if (($user = User::fromForm($form))) {
                $msg = Format::htmlchars(sprintf(__('เพิ่ม %s เรียบร้อย'), $user->getName()));
                $_REQUEST['a'] = null;
            } elseif (!$errors['err']) {
                $errors['err'] = sprintf(__('ไม่สามารถเพิ่ม %s ได้กรุณาแก้ไขและลองใหม่อีกครั้ง'),
                    __('ผู้ใช้นี้'));
            }
            break;
        case 'confirmlink':
            if (!$user || !$user->getAccount())
                $errors['err'] = sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'),
                    __('แอคเคาท์ผู้ใช้'));
            elseif ($user->getAccount()->isConfirmed())
                $errors['err'] = __('ยืนยันการลงทะเบียนแล้ว');
            elseif ($user->getAccount()->sendConfirmEmail())
                $msg = sprintf(__('อีเมลยืนยันการลงทะเบียนถูกส่งไปที่ %s'),$user->getEmail());
            else
                $errors['err'] = __('ไม่สามารถส่งอีเมลยืนยันการลงทะเบียนได้ กรุณาลองใหม่อีกครั้ง!');
            break;
        case 'pwreset':
            if (!$user || !$user->getAccount())
                $errors['err'] = sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('แอคเคาท์ผู้ใช้'));
            elseif ($user->getAccount()->sendResetEmail())
                $msg = sprintf(__('อีเมลรีเซ็ตรหัสผ่านถูกส่งไปที่ %s'),$user->getEmail());
            else
                $errors['err'] = __('ไม่สามารถส่งอีเมลรีเซ็ตรหัสผ่านได้ กรุณาลองใหม่อีกครั้ง!');
            break;
        case 'mass_process':
            if (!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = sprintf(__('คุณต้องเลือกอย่างน้อย %s.'),
                    __('หนึ่งผู้ใช้'));
            } else {
                $errors['err'] = "ยังไม่เสร็จ!";
            }
            break;
        case 'import-users':
            $status = User::importFromPost($_FILES['import'] ?: $_POST['pasted']);
            if (is_numeric($status))
                $msg = sprintf(__('นำเข้าข้อมูล %1$d %2$s เรียบร้อย'), $status,
                    _N('ผู้ใช้', 'ผู้ใช้', $status));
            else
                $errors['err'] = $status;
            break;
        default:
            $errors['err'] = __('การดำเนินการล้มเหลว');
            break;
    }
} elseif($_REQUEST['a'] == 'export') {
    require_once(INCLUDE_DIR.'class.export.php');
    $ts = strftime('%Y%m%d');
    if (!($token=$_REQUEST['qh']))
        $errors['err'] = __('ต้องการ Query Token');
    elseif (!($query=$_SESSION['users_qs_'.$token]))
        $errors['err'] = __('ไม่พบ Query');
    elseif (!Export::saveUsers($query, __("users")."-$ts.csv", 'csv'))
        $errors['err'] = __('ปัญหาระบบ: ไม่สามารถแสดงผลคิวรีได้');
}

$page = $user? 'user-view.inc.php' : 'users.inc.php';

$nav->setTabActive('users');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
