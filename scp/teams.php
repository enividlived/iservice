<?php
/*********************************************************************
    teams.php

    Evertything about teams

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');

$team=null;
if($_REQUEST['id'] && !($team=Team::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: ผิดพลาด'), __('ทีม'));

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$team){
                $errors['err']=sprintf(__('%s: ผิดพลาด'), __('ทีม'));
            }elseif($team->update($_POST,$errors)){
                $msg=sprintf(__('ปรับปรุง %s เรียบร้อย'),
                    __('ทีมนี้'));
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('ไม่สามารถปรับปรุง %s ได้ กรุณาแก้ไขและลองใหม่'),
                    __('ทีมนี้'));
            }
            break;
        case 'create':
            if(($id=Team::create($_POST,$errors))){
                $msg=sprintf(__('เพิ่ม %s เรียบร้อย'),Format::htmlchars($_POST['team']));
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('ไม่สามารถเพิ่ม %s ได้ กรุณาแก้ไขและลองใหม่'),
                    __('ทีมนี้'));
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err']=sprintf(__('คุณต้องเลือกอย่างน้อย %s'), __('หนึ่งทีม'));
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.TEAM_TABLE.' SET isenabled=1 '
                            .' WHERE team_id IN ('.implode(',', db_input($_POST['ids'])).')';

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('เปิดใช้งาน %s เรียบร้อย'),
                                    _N('ทีมที่เลือก', 'ทีมที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %3$s เปิดใช้งาน'), $num, $count,
                                    _N('ทีมที่เลือก', 'ทีมที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่สามารถเปิดใช้งาน %s ได้'),
                                _N('ทีมที่เลือก', 'ทีมที่เลือก', $count));
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.TEAM_TABLE.' SET isenabled=0 '
                            .' WHERE team_id IN ('.implode(',', db_input($_POST['ids'])).')';

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('ปิดใช้งาน %s เรียบร้อย'),
                                    _N('ทีมที่เลือก', 'ทีมที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %3$s ปิดการใช้งาน'), $num, $count,
                                    _N('ทีมที่เลือก', 'ทีมที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่สามารถปิดการใช้งาน %s ได้'),
                                _N('ทีมที่เลือก', 'ทีมที่เลือก', $count));
                        }
                        break;
                    case 'delete':
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($t=Team::lookup($v)) && $t->delete())
                                $i++;
                        }
                        if($i && $i==$count)
                            $msg = sprintf(__('ลบ %s เรียบร้อย'),
                                _N('ทีมที่เลือก', 'ทีมที่เลือก', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d จาก %2$d %3$s ถูกลบ'), $i, $count,
                                _N('ทีมที่เลือก', 'ทีมที่เลือก', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('ไม่สามารถลบ %s ได้'),
                                _N('ทีมที่เลือก', 'ทีมที่เลือก', $count));
                        break;
                    default:
                        $errors['err'] = __('เกิดข้อผิดพลาด กรุณาติดต่อผู้ดูแลระบบ');
                }
            }
            break;
        default:
            $errors['err']=__('เกิดข้อผิดพลาด');
            break;
    }
}

$page='teams.inc.php';
$tip_namespace = 'staff.team';
if($team || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='team.inc.php';
}

$nav->setTabActive('staff');
$ost->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
