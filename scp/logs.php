<?php
/*********************************************************************
    logs.php

    System Logs

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = sprintf(__('คุณต้องเลือกอย่างน้อย %s'),
                    __('หนึ่งรายการล็อก'));
            } else {
                $count=count($_POST['ids']);
                if($_POST['a'] && !strcasecmp($_POST['a'], 'delete')) {

                    $sql='DELETE FROM '.SYSLOG_TABLE
                        .' WHERE log_id IN ('.implode(',', db_input($_POST['ids'])).')';
                    if(db_query($sql) && ($num=db_affected_rows())){
                        if($num==$count)
                            $msg=sprintf(__('ลบ %s เรียบร้อย'),
                                _N('รายการล็อกที่เลือก', 'รายการล็อกที่เลือก', $count));
                        else
                            $warn=sprintf(__('%1$d จาก %2$d %3$s ถูกลบ'), $num, $count,
                                _N('รายการล็อกที่เลือก', 'รายการล็อกที่เลือก', $count));
                    } elseif(!$errors['err'])
                        $errors['err']=sprintf(__('ไม่สามารถลบ %s ได้'),
                            _N('รายการล็อกที่เลือก', 'รายการล็อกที่เลือก', $count));
                } else {
                    $errors['err']=__('เกิดข้อผิดพลาด กรุณาติดต่อผู้ดูแลระบบ');
                }
            }
            break;
        default:
            $errors['err']=__('เกิดข้อผิดพลาด');
            break;
    }
}

$page='syslogs.inc.php';
$nav->setTabActive('dashboard');
$ost->addExtraHeader('<meta name="tip-namespace" content="dashboard.system_logs" />',
    "$('#content').data('tipNamespace', 'dashboard.system_logs');");
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
