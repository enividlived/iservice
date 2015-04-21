<?php
/*********************************************************************
    slas.php

    SLA - Service Level Agreements

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.sla.php');

$sla=null;
if($_REQUEST['id'] && !($sla=SLA::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'),
        __('แผน SLA'));

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$sla){
                $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'),
                    __('แผน SLA'));
            }elseif($sla->update($_POST,$errors)){
                $msg=sprintf(__('ปรับปรุง %s เรียบร้อย'),
                    __('แผน SLA นี้'));
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('เกิดปัญหาในการปรับปรุง %s. กรุณาลองใหม่อีกครั้ง!'),
                    __('แผน SLA นี้'));
            }
            break;
        case 'add':
            if(($id=SLA::create($_POST,$errors))){
                $msg=sprintf(__('เพิ่ม %s เรียบร้อย'),
                    __('แผน SLA'));
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('ไม่สามารถเพิ่ม %s ได้ กรุณาแก้ไขและลองใหม่อีกครั้ง'),
                    __('แผน SLA นี้'));
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = sprintf(__('คุณต้องเลือกอย่างน้อย %s'),
                    __('หนึ่งแผน SLA'));
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.SLA_TABLE.' SET isactive=1 '
                            .' WHERE id IN ('.implode(',', db_input($_POST['ids'])).')';

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('เปิดใช้งาน %s เรียบร้อย'),
                                    _N('แผน SLA ที่เลือก', 'แผน SLA ที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %3$s เปิดใช้งาน'), $num, $count,
                                    _N('แผน SLA ที่เลือก', 'แผน SLA ที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่สามารถเปิดใช้งาน %s ได้'),
                                _N('แผน SLA ที่เลือก', 'แผน SLA ที่เลือก', $count));
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.SLA_TABLE.' SET isactive=0 '
                            .' WHERE id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('ปิดใช้งาน %s เรียบร้อย'),
                                    _N('แผน SLA ที่เลือก', 'แผน SLA ที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %3$s ปิดใช้งาน'), $num, $count,
                                    _N('แผน SLA ที่เลือก', 'แผน SLA ที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่สามารถปิดใช้งาน %s ได้'),
                                _N('แผน SLA ที่เลือก', 'แผน SLA ที่เลือก', $count));
                        }
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if (($p=SLA::lookup($v))
                                && $p->getId() != $cfg->getDefaultSLAId()
                                && $p->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = sprintf(__('ลบ %s เรียบร้อย'),
                                _N('แผน SLA ที่เลือก', 'แผน SLA ที่เลือก', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d จาก %2$d %3$s ถูกลบ'), $i, $count,
                                _N('แผน SLA ที่เลือก', 'แผน SLA ที่เลือก', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('ไม่สามารถลบ %s ได้'),
                                _N('แผน SLA ที่เลือก', 'แผน SLA ที่เลือก', $count));
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

$page='slaplans.inc.php';
if($sla || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='slaplan.inc.php';
    $ost->addExtraHeader('<meta name="tip-namespace" content="manage.sla" />',
            "$('#content').data('tipNamespace', 'manage.sla');");
}

$nav->setTabActive('manage');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
