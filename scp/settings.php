<?php
/*********************************************************************
    settings.php

    Handles all admin settings.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

require('admin.inc.php');
$errors=array();
$settingOptions=array(
    'system' =>
        array(__('ตั้งค่าระบบ'), 'settings.system'),
    'tickets' =>
        array(__('ตั้งค่าคำขอใช้บริการ'), 'settings.ticket'),
    'emails' =>
        array(__('ตั้งค่าอีเมล'), 'settings.email'),
    'pages' =>
        array(__('ตั้งค่าเพจ'), 'settings.pages'),
    'access' =>
        array(__('ตั้งค่าการเข้าใช้งาน'), 'settings.access'),
    'kb' =>
        array(__('ตั้งค่าถาม-ตอบ'), 'settings.kb'),
    'autoresp' =>
        array(__('ตั้งค่าการตอบรับอัตโนมัติ'), 'settings.autoresponder'),
    'alerts' =>
        array(__('ตั้งค่าการแจ้งเตือน'), 'settings.alerts'),
);
//Handle a POST.
$target=($_REQUEST['t'] && $settingOptions[$_REQUEST['t']])?$_REQUEST['t']:'system';
$page = false;
if (isset($settingOptions[$target]))
    $page = $settingOptions[$target];

if($page && $_POST && !$errors) {
    if($cfg && $cfg->updateSettings($_POST,$errors)) {
        $msg=sprintf(__('ปรับปรุง %s เรียบร้อย'), Format::htmlchars($page[0]));
    } elseif(!$errors['err']) {
        $errors['err']=__('ไม่สามารถปรับปรุงได้ - กรุณาแก้ไขและลองใหม่อีกครั้ง');
    }
}

$config=($errors && $_POST)?Format::input($_POST):Format::htmlchars($cfg->getConfigInfo());
$ost->addExtraHeader('<meta name="tip-namespace" content="'.$page[1].'" />',
    "$('#content').data('tipNamespace', '".$page[1]."');");

$nav->setTabActive('settings', ('settings.php?t='.$target));
require_once(STAFFINC_DIR.'header.inc.php');
include_once(STAFFINC_DIR."settings-$target.inc.php");
include_once(STAFFINC_DIR.'footer.inc.php');
?>
