<?php
/*********************************************************************
    orgs.php

    Peter Rotich <peter@osticket.com>
    Jared Hancock <jared@osticket.com>
    Copyright (c)  2006-2014 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('staff.inc.php');
require_once INCLUDE_DIR . 'class.note.php';

$org = null;
if ($_REQUEST['id'] || $_REQUEST['org_id'])
    $org = Organization::lookup($_REQUEST['org_id'] ?: $_REQUEST['id']);

if ($_POST) {
    switch ($_REQUEST['a']) {
    case 'import-users':
        if (!$org) {
            $errors['err'] = __('กรุณาระบุ ID ของบริษัท ก่อนนำข้อมูลเข้า');
            break;
        }
        $status = User::importFromPost($_FILES['import'] ?: $_POST['pasted'],
            array('org_id'=>$org->getId()));
        if (is_numeric($status))
            $msg = sprintf(__('นำเข้า %1$d %2$s เรียบร้อย'), $status,
                _N('ผู้ใช้', 'ผู้ใช้', $status));
        else
            $errors['err'] = $status;
        break;
    case 'remove-users':
        if (!$org)
            $errors['err'] = __('พยายามนำผู้ใช้ออกจากบริษัทที่ไม่ได้กำหนด');
        elseif (!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
            $errors['err'] = sprintf(__('คุณต้องเลือกอย่างน้อย %s'),
                __('หนึ่งผู้ใช้'));
        } else {
            $i = 0;
            foreach ($_POST['ids'] as $k=>$v) {
                if (($u=User::lookup($v)) && $org->removeUser($u))
                    $i++;
            }
            $num = count($_POST['ids']);
            if ($i && $i == $num)
                $msg = sprintf(__('ลบ %s เรียบร้อย'),
                    _N('ผู้ใช้ที่เลือก', 'ผู้ใช้ที่เลือก', $count));
            elseif ($i > 0)
                $warn = sprintf(__('%1$d จาก %2$d %3$s ถูกลบ'), $i, $count,
                    _N('ผู้ใช้ที่เลือก', 'ผู้ใช้ที่เลือก', $count));
            elseif (!$errors['err'])
                $errors['err'] = sprintf(__('ไม่สามารถลบ %s ได้'),
                    _N('ผู้ใช้ที่เลือก', 'ผู้ใช้ที่เลือก', $count));
        }
        break;
    default:
        $errors['err'] = __('เกิดข้อผิดพลาด');
    }
} elseif ($_REQUEST['a'] == 'export') {
    require_once(INCLUDE_DIR.'class.export.php');
    $ts = strftime('%Y%m%d');
    if (!($token=$_REQUEST['qh']))
        $errors['err'] = __('ต้องการ Query token');
    elseif (!($query=$_SESSION['orgs_qs_'.$token]))
        $errors['err'] = __('ไม่พบ Query token');
    elseif (!Export::saveOrganizations($query, __('organizations')."-$ts.csv", 'csv'))
        $errors['err'] = __('เกิดข้อผิดพลาด ไม่สามารถนำข้อมูลออกได้');
}

$page = $org? 'org-view.inc.php' : 'orgs.inc.php';
$nav->setTabActive('users');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
