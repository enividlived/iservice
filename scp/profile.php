<?php
/*********************************************************************
    profile.php

    Staff's profile handle

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

require_once('staff.inc.php');
require_once(INCLUDE_DIR.'class.export.php');       // For paper sizes

$msg='';
$staff=Staff::lookup($thisstaff->getId());
if($_POST && $_POST['id']!=$thisstaff->getId()) { //Check dummy ID used on the form.
 $errors['err']=__('เกิดปัญหา กรุณาติดต่อผู้ดูแลระบบ');
} elseif(!$errors && $_POST) { //Handle post

    if(!$staff)
        $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('เจ้าหน้าที่'));
    elseif($staff->updateProfile($_POST,$errors)){
        $msg=__('ปรับปรุงข้อมูลส่วนตัวเรียบร้อย');
        $thisstaff->reload();
        $staff->reload();
        $_SESSION['TZ_OFFSET']=$thisstaff->getTZoffset();
        $_SESSION['TZ_DST']=$thisstaff->observeDaylight();
    }elseif(!$errors['err'])
        $errors['err']=__('ไม่สามารถปรับปรุงข้อมูลส่วนตัวได้ กรุณาตรวจสอบและลองใหม่อีกครั้ง');
}

//Forced password Change.
if($thisstaff->forcePasswdChange() && !$errors['err'])
    $errors['err']=sprintf(__('<b>สวัสดี %s</b> - กรุณาเปลี่ยนรหัสผ่านของคุณก่อนดำเนินการต่อ!'),$thisstaff->getFirstName());
elseif($thisstaff->onVacation() && !$warn)
    $warn=sprintf(__("<b>ยินดีต้อนรับ %s</b>! ตอนนี้สถานะของคุณคือ 'ลาพักร้อน' กรุณาแจ้งให้หัวหน้าของคุณทราบเพื่อเปิดการใช้งาน"),$thisstaff->getFirstName());

$inc='profile.inc.php';
$nav->setTabActive('dashboard');
$ost->addExtraHeader('<meta name="tip-namespace" content="dashboard.my_profile" />',
    "$('#content').data('tipNamespace', 'dashboard.my_profile');");
require_once(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$inc);
require_once(STAFFINC_DIR.'footer.inc.php');
?>
