<?php
/*********************************************************************
    faq.php

    FAQs.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('staff.inc.php');
require_once(INCLUDE_DIR.'class.faq.php');

$faq=$category=null;
if($_REQUEST['id'] && !($faq=FAQ::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('บทความ'));

if($_REQUEST['cid'] && !$faq && !($category=Category::lookup($_REQUEST['cid'])))
    $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('หัวข้อบทความ'));

$faq_form = new Form(array(
    'attachments' => new FileUploadField(array('id'=>'attach',
        'configuration'=>array('extensions'=>false,
            'size'=>$cfg->getMaxFileSize())
   )),
));

if($_POST):
    $errors=array();
    $_POST['files'] = $faq_form->getField('attachments')->getClean();
    switch(strtolower($_POST['do'])) {
        case 'create':
        case 'add':
            if(($faq=FAQ::add($_POST,$errors))) {
                $msg=sprintf(__('เพิ่ม %s เรียบร้อย'), Format::htmlchars($faq->getQuestion()));
                // Delete draft for this new faq
                Draft::deleteForNamespace('faq', $thisstaff->getId());
            } elseif(!$errors['err'])
                $errors['err'] = sprintf(__('ไม่สามารถเพิ่ม %s ได้ กรุณาแก้ไขปัญหาและลองใหม่'),
                     __('บทความนี้'));
        break;
        case 'update':
        case 'edit';
            if(!$faq)
                $errors['err'] = sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('บทความ'));
            elseif($faq->update($_POST,$errors)) {
                $msg=sprintf(__('ปรับปรุง %s เรียบร้อย'), __('บทความนี้'));
                $_REQUEST['a']=null; //Go back to view
                $faq->reload();
                // Delete pending draft updates for this faq (for ALL users)
                Draft::deleteForNamespace('faq.'.$faq->getId());
            } elseif(!$errors['err'])
                $errors['err'] = sprintf(__('ไม่สามารถปรับปรุง %s ได้ กรุณาแก้ไขปัญหาและลองใหม่'),
                    __('บทความนี้'));
            break;
        case 'manage-faq':
            if(!$faq) {
                $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('บทความ'));
            } else {
                switch(strtolower($_POST['a'])) {
                    case 'edit':
                        $_GET['a']='edit';
                        break;
                    case 'publish';
                        if($faq->publish())
                            $msg=sprintf(__('เผยแพร่ %s เรียบร้อย'), __('บทความนี้'));
                        else
                            $errors['err']=sprintf(__('ไม่สามารถเผยแพร่ %s ได้ กรุณาแก้ไขบทความแล้วลองอีกที'),
                                __('this FAQ article'));
                        break;
                    case 'unpublish';
                        if($faq->unpublish())
                            $msg=sprintf(__('เลิกเผยแพร่ %s เรียบร้อย'), __('บทความนี้'));
                        else
                            $errors['err']=sprintf(__('ไม่สามารถเลิกเผยแพร่ %s ได้ กรุณาแก้ไขบทความแล้วลองอีกที'), __('บทความนี้'));
                        break;
                    case 'delete':
                        $category = $faq->getCategory();
                        if($faq->delete()) {
                            $msg=sprintf(__('ลบ %s เรียบร้อย'), Format::htmlchars($faq->getQuestion()));
                            $faq=null;
                        } else {
                            $errors['err']=sprintf(__('ไม่สามารถลบ %s ได้'), __('บทความนี้'));
                        }
                        break;
                    default:
                        $errors['err']=__('การกระทำไม่ถูกต้อง');
                }
            }
            break;
        default:
            $errors['err']=__('เกิดข้อผิดพลาด');

    }
endif;


$inc='faq-categories.inc.php'; //FAQs landing page.
if($faq) {
    $inc='faq-view.inc.php';
    if($_REQUEST['a']=='edit' && $thisstaff->canManageFAQ())
        $inc='faq.inc.php';
}elseif($_REQUEST['a']=='add' && $thisstaff->canManageFAQ()) {
    $inc='faq.inc.php';
} elseif($category && $_REQUEST['a']!='search') {
    $inc='faq-category.inc.php';
}
$tip_namespace = 'knowledgebase.faq';
$nav->setTabActive('kbase');
$ost->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require_once(STAFFINC_DIR.'header.inc.php');
require_once(STAFFINC_DIR.$inc);
print $faq_form->getMedia();
require_once(STAFFINC_DIR.'footer.inc.php');
?>
