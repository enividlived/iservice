<?php
/*********************************************************************
    templates.php

    Email Templates

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.template.php');

$template=null;
if($_REQUEST['tpl_id'] &&
        !($template=EmailTemplateGroup::lookup($_REQUEST['tpl_id'])))
    $errors['err']=sprintf(__('%s: ไม่ถูกต้อง'), __('รูปแบบเท็มเพลต'));
elseif($_REQUEST['id'] &&
        !($template=EmailTemplate::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: ไม่ถูกต้อง %s'), __('เท็มเพลต'));
elseif($_REQUEST['default_for']) {
    $sql = 'SELECT id FROM '.EMAIL_TEMPLATE_TABLE
        .' WHERE tpl_id='.db_input($cfg->getDefaultTemplateId())
        .' AND code_name='.db_input($_REQUEST['default_for']);
    if ($id = db_result(db_query($sql)))
        Http::redirect('templates.php?a=manage&id='.db_input($id));
}

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'updatetpl':
            if(!$template){
                $errors['err']=sprintf(__('%s: ไม่ถูกต้อง'),
                    __('เท็มเพลตข้อความ'));
            }elseif($template->update($_POST,$errors)){
                $msg=sprintf(__('ปรับปรุง %s เรียบร้อย'),
                    __('เท็มเพลตข้อความนี้'));
                // Drop drafts for this template for ALL users
                Draft::deleteForNamespace('tpl.'.$template->getCodeName()
                    .'.'.$template->getTplId());
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('เกิดปัญหาในการปรับปรุง %s กรุณาลองใหม่!'),
                    __('เท็มเพลตนี้'));
            }
            break;
        case 'implement':
            if(!$template){
                $errors['err']=sprintf(__('%s: ไม่ถูกต้อง'), __('ชุดเท็มเพลต'));
            }elseif($new = EmailTemplate::add($_POST,$errors)){
                $template = $new;
                $msg=sprintf(__('ปรับปรุง %s เรียบร้อย'), __('เท็มเพลตข้อความนี้'));
                // Drop drafts for this user for this template
                Draft::deleteForNamespace('tpl.'.$new->getCodeName()
                    .$new->getTplId(), $thisstaff->getId());
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('เกิดปัญหาในการปรับปรุง %s กรุณาลองใหม่!'),
                    __('เท็มเพลตข้อความนี้'));
            }
            break;
        case 'update':
            if(!$template){
                $errors['err']=sprintf(__('%s: ไม่ถูกต้อง'), __('ชุดเท็มเพลต'));
            }elseif($template->update($_POST,$errors)){
                $msg=sprintf(__('ปรับปรุง %s เรียบร้อย'),
                    mb_convert_case(__('เท็มเพลตข้อความนี้'), MB_CASE_TITLE));
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('เกิดปัญหาในการปรับปรุง %s กรุณาลองใหม่!'),
                    __('เท็มเพลตข้อความนี้'));
            }
            break;
        case 'add':
            if(($new=EmailTemplateGroup::add($_POST,$errors))){
                $template=$new;
                $msg=sprintf(__('ปรับปรุง %s เรียบร้อย'),
                    mb_convert_case(__('a template set'), MB_CASE_TITLE));
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('ไม่สามารถเพิ่ม %s ได้ กรุณาแก้ไขปัญหาแล้วลองใหม่!'),
                    __('ชุดเท็มเพลตนี้'));
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err']=sprintf(__('คุณต้องเลือกอย่างน้อย %s ก่อนดำเนินการต่อ'),
                    __('หนึ่งชุดเท็มเพลต'));
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.EMAIL_TEMPLATE_GRP_TABLE.' SET isactive=1 '
                            .' WHERE tpl_id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())){
                            if($num==$count)
                                $msg = sprintf(__('เปิดใช้งาน %s เรียบร้อย'),
                                    _N('ชุดเท็มเพลตที่เลือก', 'ชุดเท็มเพลตที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %3$s เปิดใช้งาน'), $num, $count,
                                    _N('ชุดเท็มเพลตที่เลือก', 'ชุดเท็มเพลตที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่สามารถเปิดใช้งาน %s ได้'),
                                _N('ชุดเท็มเพลตที่เลือก', 'ชุดเท็มเพลตที่เลือก', $count));
                        }
                        break;
                    case 'disable':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($t=EmailTemplateGroup::lookup($v)) && !$t->isInUse() && $t->disable())
                                $i++;
                        }
                        if($i && $i==$count)
                            $msg = sprintf(__('ปิดใช้งาน %s เรียบร้อย'),
                                _N('ชุดเท็มเพลตที่เลือก', 'ชุดเท็มเพลตที่เลือก', $count));
                        elseif($i)
                            $warn = sprintf(__('%1$d จาก %2$d %3$s ปิดใช้งาน'), $i, $count,
                                _N('ชุดเท็มเพลตที่เลือก', 'ชุดเท็มเพลตที่เลือก', $count))
                               .' '.__('(กำลังใช้งานและตั้งเป็นเท็มเพลตเริ่มต้นจึงไม่สามารถปิดการใช้งานได้)');
                        else
                            $errors['err'] = sprintf(__("ไม่สามารถปิดการใช้งาน %s ได้"),
                                _N('ชุดเท็มเพลตที่เลือก', 'ชุดเท็มเพลตที่เลือก', $count))
                               .' '.__('(กำลังใช้งานและตั้งเป็นเท็มเพลตเริ่มต้นจึงไม่สามารถปิดการใช้งานได้)');
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($t=EmailTemplateGroup::lookup($v)) && !$t->isInUse() && $t->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = sprintf(__('ลบ %s เรียบร้อย'),
                                _N('ชุดเท็มเพลตที่เลือก', 'ชุดเท็มเพลตที่เลือก', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d จาก %2$d %3$s ถูกลบ'), $i, $count,
                                _N('ชุดเท็มเพลตที่เลือก', 'ชุดเท็มเพลตที่เลือก', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('ไม่สามารถลบ %s ได้'),
                                _N('ชุดเท็มเพลตที่เลือก', 'ชุดเท็มเพลตที่เลือก', $count));
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

$page='templates.inc.php';
$tip_namespace = 'emails.template';
if($template && !strcasecmp($_REQUEST['a'],'manage')){
    $page='tpl.inc.php';
}elseif($template && !strcasecmp($_REQUEST['a'],'implement')){
    $page='tpl.inc.php';
}elseif($template || !strcasecmp($_REQUEST['a'],'add')){
    $page='template.inc.php';
}

$nav->setTabActive('emails');
$ost->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
