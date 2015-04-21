<?php
/*********************************************************************
    banlist.php

    List of banned email addresses

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.banlist.php');

/* Get the system ban list filter */
if(!($filter=Banlist::getFilter()))
    $warn = __('ยังไม่มีรายการที่ถูกแบน');
elseif(!$filter->isActive())
    // XXX: This should never happen and can no longer be enabled via
    // this link
    $warn = __('ขณะนี้การแบนถูก <b>ปิดการใช้งาน</b>').' - <a href="filters.php">'.__('เปิดใช้งานใหม่!').'</a>.';

$rule=null; //ban rule obj.
if($filter && $_REQUEST['id'] && !($rule=$filter->getRule($_REQUEST['id'])))
    $errors['err'] = sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('รายการแบน'));

if($_POST && !$errors && $filter){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$rule){
                $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('กฎการแบน'));
            }elseif(!$_POST['val'] || !Validator::is_email($_POST['val'])){
                $errors['err']=$errors['val']=__('กรุณาระบุอีเมลที่ถูกต้อง');
            }elseif(!$errors){
                $vars=array('what'=>'email',
                            'how'=>'equal',
                            'val'=>trim($_POST['val']),
                            'filter_id'=>$filter->getId(),
                            'isactive'=>$_POST['isactive'],
                            'notes'=>$_POST['notes']);
                if($rule->update($vars,$errors)){
                    $msg=sprintf(__('ปรับปรุง %s เรียบร้อย'), Format::htmlchars($_POST['val']));
                }elseif(!$errors['err']){
                    $errors['err']=sprintf(__('ไม่สามารถปรับปรุง %s ได้ กรุณาลองใหม่!'), __('กฎการแบนนี้'));
                }
            }
            break;
        case 'add':
            if(!$filter) {
                $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('กฎการแบน'));
            }elseif(!$_POST['val'] || !Validator::is_email($_POST['val'])) {
                $errors['err']=$errors['val']=__('กรุณาระบุอีเมลที่ถูกต้อง');
            }elseif(BanList::includes(trim($_POST['val']))) {
                $errors['err']=$errors['val']=__('อีเมลนี้อยู่ในรายการแล้ว');
            }elseif($filter->addRule('email','equal',trim($_POST['val']),array('isactive'=>$_POST['isactive'],'notes'=>$_POST['notes']))) {
                $msg=__('เพิ่มอีเมลในรายการเรียบร้อยแล้ว');
                $_REQUEST['a']=null;
                //Add filter rule here.
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('ไม่สามารถสร้าง %s ได้ กรุณาลองใหม่!'), __('กฎการแบน'));
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = __('คุณต้องเลือกอย่างน้อยหนึ่งอีเมล');
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.FILTER_RULE_TABLE.' SET isactive=1 '
                            .' WHERE filter_id='.db_input($filter->getId())
                            .' AND id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())){
                            if($num==$count)
                                $msg = sprintf(__('เปิดใช้งาน %s เรียบร้อย'),
                                    _N('กฎการแบนที่เลือก', 'กฎการแบนที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %3$s เปิดใช้งาน'), $num, $count,
                                    _N('กฎการแบนที่เลือก', 'กฎการแบนที่เลือก', $count));
                        } else  {
                            $errors['err'] = sprintf(__('ไม่สามารถเปิดใช้งาน %s ได้'),
                                _N('กฎการแบนที่เลือก', 'กฎการแบนที่เลือก', $count));
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.FILTER_RULE_TABLE.' SET isactive=0 '
                            .' WHERE filter_id='.db_input($filter->getId())
                            .' AND id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('ปิดใช้งาน %s เรียบร้อย'),
                                    _N('กฎการแบนที่เลือก', 'กฎการแบนที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %3$s ปิดใช้งาน'), $num, $count,
                                    _N('กฎการแบนที่เลือก', 'กฎการแบนที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่สามารถปิดใช้งาน %s ได้'),
                                _N('กฎการแบนที่เลือก', 'กฎการแบนที่เลือก', $count));
                        }
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($r=FilterRule::lookup($v)) && $r->getFilterId()==$filter->getId() && $r->delete())
                                $i++;
                        }
                        if($i && $i==$count)
                            $msg = sprintf(__('ไม่สามารถลบ %s ได้'),
                                _N('กฎการแบนที่เลือก', 'กฎการแบนที่เลือก', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d จาก %2$d %3$s ถูกลบ'), $i, $count,
                                _N('กฎการแบนที่เลือก', 'กฎการแบนที่เลือก', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('ไม่สามารถลบ %s ได้'),
                                _N('กฎการแบนที่เลือก', 'กฎการแบนที่เลือก', $count));

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

$page='banlist.inc.php';
$tip_namespace = 'emails.banlist';
if(!$filter || ($rule || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))) {
    $page='banrule.inc.php';
}

$nav->setTabActive('emails');
$ost->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
