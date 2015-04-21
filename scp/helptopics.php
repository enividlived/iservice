<?php
/*********************************************************************
    helptopics.php

    Help Topics.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.topic.php');
require_once(INCLUDE_DIR.'class.dynamic_forms.php');

$topic=null;
if($_REQUEST['id'] && !($topic=Topic::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('หัวข้อบริการ'));

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$topic){
                $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('หัวข้อบริการ'));
            }elseif($topic->update($_POST,$errors)){
                $msg=sprintf(__('ปรับปรุง %s เรียบร้อย'),
                    __('หัวข้อบริการนี้'));
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('ไม่สามารถปรับปรุง %s ได้ กรุณาลองใหม่!'),
                    __('หัวข้อบริการนี้'));
            }
            break;
        case 'create':
            if(($id=Topic::create($_POST,$errors))){
                $msg=sprintf(__('เพิ่ม %s เรียบร้อย'), Format::htmlchars($_POST['topic']));
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=sprintf(__('ไม่สามารถเพิ่ม %s ได้ กรุณาแก้ไขปัญหาและลองใหม่'),
                    __('หัวข้อบริการนี้'));
            }
            break;
        case 'mass_process':
            switch(strtolower($_POST['a'])) {
            case 'sort':
                // Pass
                break;
            default:
                if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids']))
                    $errors['err'] = sprintf(__('คุณต้องเลือกอย่างน้อย %s'),
                        __('หนึ่งหัวข้อบริการ'));
            }
            if (!$errors) {
                $count=count($_POST['ids']);

                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.TOPIC_TABLE.' SET isactive=1 '
                            .' WHERE topic_id IN ('.implode(',', db_input($_POST['ids'])).')';

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('เปิดใช้งาน %s เรียบร้อย'),
                                    _N('หัวข้อบริการที่เลือก', 'หัวข้อบริการที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %3$s เปิดใช้งานแล้ว'), $num, $count,
                                    _N('หัวข้อบริการที่เลือก', 'หัวข้อบริการที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่สามารถเปิดใช้งาน %s ได้'),
                                _N('หัวข้อบริการที่เลือก', 'หัวข้อบริการที่เลือก', $count));
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.TOPIC_TABLE.' SET isactive=0 '
                            .' WHERE topic_id IN ('.implode(',', db_input($_POST['ids'])).')'
                            .' AND topic_id <> '.db_input($cfg->getDefaultTopicId());
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('ปิดใช้งาน %s เรียบร้อย'),
                                    _N('หัวข้อบริการที่เลือก', 'หัวข้อบริการที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %3$s ปิดใช้งานแล้ว'), $num, $count,
                                    _N('หัวข้อบริการที่เลือก', 'หัวข้อบริการที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่สามารถปิดใช้งาน %s ได้'),
                                _N('หัวข้อบริการที่เลือก', 'หัวข้อบริการที่เลือก', $count));
                        }
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($t=Topic::lookup($v)) && $t->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = sprintf(__('ลบ %s เรียบร้อย'),
                                _N('หัวข้อบริการที่เลือก', 'หัวข้อบริการที่เลือก', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d จาก %2$d %3$s ลบทิ้งแล้ว'), $i, $count,
                                _N('หัวข้อบริการที่เลือก', 'หัวข้อบริการที่เลือก', $count));
                        elseif(!$errors['err'])
                            $errors['err']  = sprintf(__('ไม่สามารถลบ %s ได้'),
                                _N('หัวข้อบริการที่เลือก', 'หัวข้อบริการที่เลือก', $count));

                        break;
                    case 'sort':
                        try {
                            $cfg->setTopicSortMode($_POST['help_topic_sort_mode']);
                            if ($cfg->getTopicSortMode() == 'm') {
                                foreach ($_POST as $k=>$v) {
                                    if (strpos($k, 'sort-') === 0
                                            && is_numeric($v)
                                            && ($t = Topic::lookup(substr($k, 5))))
                                        $t->setSortOrder($v);
                                }
                            }
                            $msg = __('ตั้งค่าการเรียงลำดับเรียบร้อย');
                        }
                        catch (Exception $ex) {
                            $errors['err'] = __('ไม่สามารถตั้งค่าการเรียงลำดับได้');
                        }
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
    if ($id or $topic) {
        if (!$id) $id=$topic->getId();
    }
}

$page='helptopics.inc.php';
$tip_namespace = 'manage.helptopic';
if($topic || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='helptopic.inc.php';
}

$nav->setTabActive('manage');
$ost->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
