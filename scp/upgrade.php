<?php
/*********************************************************************
    upgrade.php

    osTicket Upgrade Wizard

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require_once 'admin.inc.php';
require_once INCLUDE_DIR.'class.upgrader.php';

//$_SESSION['ost_upgrader']=null;
$upgrader = new Upgrader(TABLE_PREFIX, UPGRADE_DIR.'streams/');
$errors=array();
if($_POST && $_POST['s'] && !$upgrader->isAborted()) {
    switch(strtolower($_POST['s'])) {
        case 'prereq':
            if(!$ost->isUpgradePending()) {
                $errors['err']=__('ถัมเพื่อครายยย? ขณะนี้ระบบเป็นรุ่นล่าสุดอยู่แล้ว');
            } elseif(!$upgrader->isUpgradable()) {
                $errors['err']=__('ไม่สามารถอัพเกรดจากรุ่นปัจจุบันได้!');
            } elseif(!$upgrader->check_prereq()) {
                $errors['prereq']=__('ระบบไม่ตรงตามเงื่อนไขในการอัพเกรด! กรุณาอ่าน Release Note เพื่อรายละเอียดเพิ่มเติม');
            } elseif(!strcasecmp(basename(CONFIG_FILE), 'settings.php')) {
                $errors['err']=__('กรุณาแก้ไขไฟล์ config ก่อนดำเนินการต่อ!');
            } else {
                $upgrader->setState('upgrade');
            }
            break;
        case 'upgrade': //Manual upgrade.... when JS (ajax) is not supported.
            if($upgrader->getTask()) {
                $upgrader->doTask();
            } elseif($ost->isUpgradePending() && $upgrader->isUpgradable()) {
                $upgrader->upgrade();
            } elseif(!$ost->isUpgradePending()) {
                $upgrader->setState('done');
            }

            if(($errors=$upgrader->getErrors()))  {
                $upgrader->setState('aborted');
            }
            break;
        default:
            $errors['err']=__('เกิดข้อผิดพลาด');
    }
}

switch(strtolower($upgrader->getState())) {
    case 'aborted':
        $inc='aborted.inc.php';
        break;
    case 'upgrade':
        $inc='upgrade.inc.php';
        break;
    case 'done':
        $inc='done.inc.php';
        break;
    default:
        $inc='prereq.inc.php';
        if($upgrader->isAborted())
            $inc='aborted.inc.php';
        elseif(!strcasecmp(basename(CONFIG_FILE), 'settings.php'))
            $inc='rename.inc.php';
        elseif(!$ost->isUpgradePending())
            $errors['err']=sprintf(__('ถัมเพื่อครายย? ระบบได้อัพเกรดเป็น <b>%s</b>เรียบร้อยแล้ว  และไม่มีแพทช์ที่รอการติดตั้ง'),$ost->getVersion());
        elseif(!$upgrader->isUpgradable())
            $errors['err']=sprintf(__('ไม่สามารถอัพเกรดจากรุ่นปัจจุบันได้ [%s]!'), $cfg->getSchemaSignature());

}

$nav = new AdminNav($thisstaff);
$nav->setTabActive('dashboard');
$nav->addSubMenu(array('desc'=>__('อัพเกรด'),
                           'title'=>__('อัพเกรด'),
                           'href'=>'upgrade.php',
                           'iconclass'=>'preferences'),
                        true);
$ost->addExtraHeader('<script type="text/javascript" src="./js/upgrader.js?c18eac4"></script>');
require(STAFFINC_DIR.'header.inc.php');
require(UPGRADE_DIR.$inc);
require(STAFFINC_DIR.'footer.inc.php');
?>
