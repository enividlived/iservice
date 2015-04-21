<?php
/*********************************************************************
    login.php

    Handles staff authentication/logins

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require_once('../main.inc.php');
if(!defined('INCLUDE_DIR')) die('เกิดข้อผิดพลาด กรุณาติดต่อผู้ดูแลระบบ');

// Bootstrap gettext translations. Since no one is yet logged in, use the
// system or browser default
TextDomain::configureForUser();

require_once(INCLUDE_DIR.'class.staff.php');
require_once(INCLUDE_DIR.'class.csrf.php');

$content = Page::lookup(Page::getIdByType('banner-staff'));

$dest = $_SESSION['_staff']['auth']['dest'];
$msg = $_SESSION['_staff']['auth']['msg'];
$msg = $msg ?: ($content ? $content->getName() : __('กรุณาเข้าสู่ระบบ'));
$dest=($dest && (!strstr($dest,'login.php') && !strstr($dest,'ajax.php')))?$dest:'index.php';
$show_reset = false;
if($_POST) {
    // Lookup support backends for this staff
    $username = trim($_POST['userid']);
    if ($user = StaffAuthenticationBackend::process($username,
            $_POST['passwd'], $errors)) {
        session_write_close();
        Http::redirect($dest);
        require_once('index.php'); //Just incase header is messed up.
        exit;
    }

    $msg = $errors['err']?$errors['err']:__('ล็อคอินไม่ถูกต้อง');
    $show_reset = true;
}
elseif ($_GET['do']) {
    switch ($_GET['do']) {
    case 'ext':
        // Lookup external backend
        if ($bk = StaffAuthenticationBackend::getBackend($_GET['bk']))
            $bk->triggerAuth();
    }
    Http::redirect('login.php');
}
// Consider single sign-on authentication backends
elseif (!$thisstaff || !($thisstaff->getId() || $thisstaff->isValid())) {
    if (($user = StaffAuthenticationBackend::processSignOn($errors, false))
            && ($user instanceof StaffSession))
       @header("Location: $dest");
}

define("OSTSCPINC",TRUE); //Make includes happy!
include_once(INCLUDE_DIR.'staff/login.tpl.php');
?>
