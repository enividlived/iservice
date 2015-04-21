<?php
/*********************************************************************
    canned.php

    Canned Responses aka Premade Responses.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('staff.inc.php');
include_once(INCLUDE_DIR.'class.canned.php');

/* check permission */
if(!$thisstaff || !$thisstaff->canManageCannedResponses()
        || !$cfg->isCannedResponseEnabled()) {
    header('Location: kb.php');
    exit;
}

//TODO: Support attachments!

$canned=null;
if($_REQUEST['id'] && !($canned=Canned::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('คำตอบที่กำหนดไว้'));

$canned_form = new Form(array(
    'attachments' => new FileUploadField(array('id'=>'attach',
        'configuration'=>array('extensions'=>false,
            'size'=>$cfg->getMaxFileSize())
   )),
));

if($_POST && $thisstaff->canManageCannedResponses()) {
    switch(strtolower($_POST['do'])) {
        case 'update':
            if(!$canned) {
                $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('คำตอบที่กำหนดไว้'));
            } elseif($canned->update($_POST, $errors)) {
                $msg=sprintf(__('ปรับปรุง %s เรียบร้อย'),
                    __('คำตอบที่กำหนดไว้'));
                //Delete removed attachments.
                //XXX: files[] shouldn't be changed under any circumstances.
                $keepers = $canned_form->getField('attachments')->getClean();
                $attachments = $canned->attachments->getSeparates(); //current list of attachments.
                foreach($attachments as $k=>$file) {
                    if($file['id'] && !in_array($file['id'], $keepers)) {
                        $canned->attachments->delete($file['id']);
                    }
                }

                //Upload NEW attachments IF ANY - TODO: validate attachment types??
                if ($keepers)
                    $canned->attachments->upload($keepers);

                // Attach inline attachments from the editor
                if (isset($_POST['draft_id'])
                        && ($draft = Draft::lookup($_POST['draft_id']))) {
                    $canned->attachments->deleteInlines();
                    $canned->attachments->upload(
                        $draft->getAttachmentIds($_POST['response']),
                        true);
                }

                $canned->reload();

                // XXX: Handle nicely notifying a user that the draft was
                // deleted | OR | show the draft for the user on the name
                // page refresh or a nice bar popup immediately with
                // something like "This page is out-of-date", and allow the
                // user to voluntarily delete their draft
                //
                // Delete drafts for all users for this canned response
                Draft::deleteForNamespace('canned.'.$canned->getId());
            } elseif(!$errors['err']) {
                $errors['err']=sprintf(__('ไม่สามารถปรับปรุง %s ได้ กรุณาลองใหม่อีกครั้ง!'), __('คำตอบที่กำหนดไว้'));
            }
            break;
        case 'create':
            if(($id=Canned::create($_POST, $errors))) {
                $msg=sprintf(__('เพิ่ม %s เรียบร้อย'), Format::htmlchars($_POST['title']));
                $_REQUEST['a']=null;
                //Upload attachments
                $keepers = $canned_form->getField('attachments')->getClean();
                if (($c=Canned::lookup($id)) && $keepers)
                    $c->attachments->upload($keepers);

                // Attach inline attachments from the editor
                if ($c && isset($_POST['draft_id'])
                        && ($draft = Draft::lookup($_POST['draft_id'])))
                    $c->attachments->upload(
                        $draft->getAttachmentIds($_POST['response']), true);

                // Delete this user's drafts for new canned-responses
                Draft::deleteForNamespace('canned', $thisstaff->getId());
            } elseif(!$errors['err']) {
                $errors['err']=sprintf(__('ไม่สามารถเพิ่ม %s ได้ กรุณาแก้ไขปัญหาแล้วลองใหม่'),
                    __('คำตอบที่กำหนดไว้'));
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err']=sprintf(__('คุณต้องเลือกอย่างน้อย %s'), __('หนึ่งคำตอบที่กำหนดไว้'));
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.CANNED_TABLE.' SET isenabled=1 '
                            .' WHERE canned_id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('เปิดใช้งาน %s เรียบร้อย'),
                                    _N('คำตอบที่กำหนดไว้ที่เลือก', 'คำตอบที่กำหนดไว้ที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %s เปิดใช้งาน'), $num, $count,
                                    _N('คำตอบที่กำหนดไว้ที่เลือก', 'คำตอบที่กำหนดไว้ที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่สามารถเปิดใช้งาน %s ได้'),
                                _N('คำตอบที่กำหนดไว้ที่เลือก', 'คำตอบที่กำหนดไว้ที่เลือก', $count));
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.CANNED_TABLE.' SET isenabled=0 '
                            .' WHERE canned_id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('ปิดใช้งาน %s เรียบร้อย'),
                                    _N('คำตอบที่กำหนดไว้ที่เลือก', 'คำตอบที่กำหนดไว้ที่เลือก', $count));
                            else
                                $warn = sprintf(__('%1$d จาก %2$d %s ปิดใช้งาน'), $num, $count,
                                    _N('คำตอบที่กำหนดไว้ที่เลือก', 'คำตอบที่กำหนดไว้ที่เลือก', $count));
                        } else {
                            $errors['err'] = sprintf(__('ไม่สามารถปิดใช้งาน %s ได้'),
                                _N('คำตอบที่กำหนดไว้ที่เลือก', 'คำตอบที่กำหนดไว้ที่เลือก', $count));
                        }
                        break;
                    case 'delete':

                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($c=Canned::lookup($v)) && $c->delete())
                                $i++;
                        }

                        if($i==$count)
                            $msg = sprintf(__('ลบ %s เรียบร้อย'),
                                _N('คำตอบที่กำหนดไว้ที่เลือก', 'คำตอบที่กำหนดไว้ที่เลือก', $count));
                        elseif($i>0)
                            $warn=sprintf(__('%1$d จาก %2$d %3$s ถูกลบ'), $i, $count,
                                _N('คำตอบที่กำหนดไว้ที่เลือก', 'คำตอบที่กำหนดไว้ที่เลือก', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('ไม่สามารถลบ %s ได้'),
                                _N('คำตอบที่กำหนดไว้ที่เลือก', 'คำตอบที่กำหนดไว้ที่เลือก', $count));
                        break;
                    default:
                        $errors['err']=__('คำสั่งผิดพลาด');
                }
            }
            break;
        default:
            $errors['err']=__('เกิดข้อผิดพลาด');
            break;
    }
}

$page='cannedresponses.inc.php';
$tip_namespace = 'knowledgebase.canned_response';
if($canned || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='cannedresponse.inc.php';
}

$nav->setTabActive('kbase');
$ost->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
print $canned_form->getMedia();
include(STAFFINC_DIR.'footer.inc.php');
?>
