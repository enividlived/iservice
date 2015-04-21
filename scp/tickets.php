<?php
/*************************************************************************
    tickets.php

    Handles all tickets related actions.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

require('staff.inc.php');
require_once(INCLUDE_DIR.'class.ticket.php');
require_once(INCLUDE_DIR.'class.dept.php');
require_once(INCLUDE_DIR.'class.filter.php');
require_once(INCLUDE_DIR.'class.canned.php');
require_once(INCLUDE_DIR.'class.json.php');
require_once(INCLUDE_DIR.'class.dynamic_forms.php');
require_once(INCLUDE_DIR.'class.export.php');       // For paper sizes

$page='';
$ticket = $user = null; //clean start.
//LOCKDOWN...See if the id provided is actually valid and if the user has access.
if($_REQUEST['id']) {
    if(!($ticket=Ticket::lookup($_REQUEST['id'])))
         $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'), __('คำขอใช้บริการ'));
    elseif(!$ticket->checkStaffAccess($thisstaff)) {
        $errors['err']=__('ปฏิเสธการเข้าถึง กรุณาติดต่อผู้ดูแลระบบ');
        $ticket=null; //Clear ticket obj.
    }
}

//Lookup user if id is available.
if ($_REQUEST['uid'])
    $user = User::lookup($_REQUEST['uid']);

// Configure form for file uploads
$response_form = new Form(array(
    'attachments' => new FileUploadField(array('id'=>'attach',
        'name'=>'attach:response',
        'configuration' => array('extensions'=>'')))
));
$note_form = new Form(array(
    'attachments' => new FileUploadField(array('id'=>'attach',
        'name'=>'attach:note',
        'configuration' => array('extensions'=>'')))
));

//At this stage we know the access status. we can process the post.
if($_POST && !$errors):

    if($ticket && $ticket->getId()) {
        //More coffee please.
        $errors=array();
        $lock=$ticket->getLock(); //Ticket lock if any
        switch(strtolower($_POST['a'])):
        case 'reply':
            if(!$thisstaff->canPostReply())
                $errors['err'] = __('ปฏิเสธการดำเนินการ กรุณาติดต่อผู้ดูแลระบบ');
            else {

                if(!$_POST['response'])
                    $errors['response']=__('กรุณาระบุข้อความตอบกลับ');
                //Use locks to avoid double replies
                if($lock && $lock->getStaffId()!=$thisstaff->getId())
                    $errors['err']=__('ปฏิเสธการดำเนินการ คำขอใช้บริการกำลังถูกใช้งานอยู่!');

                //Make sure the email is not banned
                if(!$errors['err'] && TicketFilter::isBanned($ticket->getEmail()))
                    $errors['err']=__('อีเมลถูกแบน กรุณาปลดแบนก่อน');
            }

            //If no error...do the do.
            $vars = $_POST;
            $vars['cannedattachments'] = $response_form->getField('attachments')->getClean();

            if(!$errors && ($response=$ticket->postReply($vars, $errors, $_POST['emailreply']))) {
                $msg = sprintf(__('%s: ตอบกลับข้อความเรียบร้อย'),
                        sprintf(__('คำขอใช้บริการที่ %s'),
                            sprintf('<a href="tickets.php?id=%d"><b>%s</b></a>',
                                $ticket->getId(), $ticket->getNumber()))
                        );

                // Clear attachment list
                $response_form->setSource(array());
                $response_form->getField('attachments')->reset();

                // Remove staff's locks
                TicketLock::removeStaffLocks($thisstaff->getId(),
                        $ticket->getId());

                // Cleanup response draft for this user
                Draft::deleteForNamespace(
                    'ticket.response.' . $ticket->getId(),
                    $thisstaff->getId());

                // Go back to the ticket listing page on reply
                $ticket = null;

            } elseif(!$errors['err']) {
                $errors['err']=__('ไม่สามารถตอบกลับได้ กรุณาแก้ไขข้อมูลให้ถูกต้อง!');
            }
            break;
        case 'transfer': /** Transfer ticket **/
            //Check permission
            if(!$thisstaff->canTransferTickets())
                $errors['err']=$errors['transfer'] = __('ปฏิเสธการดำเนินการ คุณไม่ได้รับอนุญาตให้โอนย้ายคำขอใช้บริการ');
            else {

                //Check target dept.
                if(!$_POST['deptId'])
                    $errors['deptId'] = __('เลือกแผนก');
                elseif($_POST['deptId']==$ticket->getDeptId())
                    $errors['deptId'] = __('คำขอใช้บริการอยู่ในแผนกนั้นๆแล้ว');
                elseif(!($dept=Dept::lookup($_POST['deptId'])))
                    $errors['deptId'] = __('แผนกไม่ถูกต้องหรือมีข้อผิดพลาด');

                //Transfer message - required.
                if(!$_POST['transfer_comments'])
                    $errors['transfer_comments'] = __('กรุณาระบุเหตุผลในการโอนย้าย');
                elseif(strlen($_POST['transfer_comments'])<5)
                    $errors['transfer_comments'] = __('เหตุผลในการโอนย้ายสั้นเกินไป!');

                //If no errors - them attempt the transfer.
                if(!$errors && $ticket->transfer($_POST['deptId'], $_POST['transfer_comments'])) {
                    $msg = sprintf(__('คำขอใช้บริการได้ถูกโอนย้ายไปที่ %s'),$ticket->getDeptName());
                    //Check to make sure the staff still has access to the ticket
                    if(!$ticket->checkStaffAccess($thisstaff))
                        $ticket=null;

                } elseif(!$errors['transfer']) {
                    $errors['err'] = __('ไม่สามารถโอนย้ายได้');
                    $errors['transfer']=__('กรุณาแก้ไขข้อมูลใหม่!');
                }
            }
            break;
        case 'assign':

             if(!$thisstaff->canAssignTickets())
                 $errors['err']=$errors['assign'] = __('ปฏิเสธการดำเนินการ คุณไม่ได้รับอนุญาตให้มอบหมายคำขอใช้บริการ');
             else {

                 $id = preg_replace("/[^0-9]/", "",$_POST['assignId']);
                 $claim = (is_numeric($_POST['assignId']) && $_POST['assignId']==$thisstaff->getId());

                 if(!$_POST['assignId'] || !$id)
                     $errors['assignId'] = __('เลือกเจ้าหน้าที่');
                 elseif($_POST['assignId'][0]!='s' && $_POST['assignId'][0]!='t' && !$claim)
                     $errors['assignId']=__('ไอดีเจ้าหน้าที่ไม่ถูกต้อง ติดต่อผู้ดูแลระบบ');
                 elseif($ticket->isAssigned()) {
                     if($_POST['assignId'][0]=='s' && $id==$ticket->getStaffId())
                         $errors['assignId']=__('คำขอใช้บริการถูกมอบหมายให้เจ้าหน้าที่นี้แล้ว');
                     elseif($_POST['assignId'][0]=='t' && $id==$ticket->getTeamId())
                         $errors['assignId']=__('คำขอใช้บริการถูกมอบหมายให้ทีมนี้แล้ว');
                 }

                 //Comments are not required on self-assignment (claim)
                 if($claim && !$_POST['assign_comments'])
                     $_POST['assign_comments'] = sprintf(__('คำขอใช้บริการถูกมอบให้ %s'),$thisstaff->getName());
                 elseif(!$_POST['assign_comments'])
                     $errors['assign_comments'] = __('กรุณาระบุเหตุผลในการมอบหมาย');
                 elseif(strlen($_POST['assign_comments'])<5)
                         $errors['assign_comments'] = __('เหตุผลในการมอบหมายสั้นเกินไป');

                 if(!$errors && $ticket->assign($_POST['assignId'], $_POST['assign_comments'], !$claim)) {
                     if($claim) {
                         $msg = __('มอบหมายคำขอใช้บริการให้ตัวเองแล้ว');
                     } else {
                         $msg=sprintf(__('มอบหมายคำขอใช้บริการให้ %s เรียบร้อย'), $ticket->getAssigned());
                         TicketLock::removeStaffLocks($thisstaff->getId(), $ticket->getId());
                         $ticket=null;
                     }
                 } elseif(!$errors['assign']) {
                     $errors['err'] = __('ไม่สามารถมอบหมายคำขอใช้บริการได้');
                     $errors['assign'] = __('กรุณาแก้ไขข้อมูลใหม่!');
                 }
             }
            break;
        case 'postnote': /* Post Internal Note */
            $vars = $_POST;
            $attachments = $note_form->getField('attachments')->getClean();
            $vars['cannedattachments'] = array_merge(
                $vars['cannedattachments'] ?: array(), $attachments);

            $wasOpen = ($ticket->isOpen());
            if(($note=$ticket->postNote($vars, $errors, $thisstaff))) {

                $msg=__('เพิ่มบันทึกย่อเรียบร้อย');
                // Clear attachment list
                $note_form->setSource(array());
                $note_form->getField('attachments')->reset();

                if($wasOpen && $ticket->isClosed())
                    $ticket = null; //Going back to main listing.
                else
                    // Ticket is still open -- clear draft for the note
                    Draft::deleteForNamespace('ticket.note.'.$ticket->getId(),
                        $thisstaff->getId());

            } else {

                if(!$errors['err'])
                    $errors['err'] = __('ไม่สามารถเพิ่มบันทึกย่อ เนื่องจากข้อมูลไม่ถูกต้อง');

                $errors['postnote'] = __('ไม่สามารถเพิ่มบันทึกย่อ กรุณาแก้ไขข้อมูลใหม่');
            }
            break;
        case 'edit':
        case 'update':
            if(!$ticket || !$thisstaff->canEditTickets())
                $errors['err']=__('ปฏิเสธการดำเนินการ คุณไม่มีสิทธิแก้ไขคำขอใช้บริการ');
            elseif($ticket->update($_POST,$errors)) {
                $msg=__('แก้ไขคำขอใช้บริการเรียบร้อย');
                $_REQUEST['a'] = null; //Clear edit action - going back to view.
                //Check to make sure the staff STILL has access post-update (e.g dept change).
                if(!$ticket->checkStaffAccess($thisstaff))
                    $ticket=null;
            } elseif(!$errors['err']) {
                $errors['err']=__('ไม่สามารถแก้ไขคำขอใช้บริการ กรุณาแก้ไขข้อมูลใหม่');
            }
            break;
        case 'process':
            switch(strtolower($_POST['do'])):
                case 'release':
                    if(!$ticket->isAssigned() || !($assigned=$ticket->getAssigned())) {
                        $errors['err'] = __('คำขอใช้บริการยังไม่ได้มอบหมาย!');
                    } elseif($ticket->release()) {
                        $msg=sprintf(__(
                            /* 1$ is the current assignee, 2$ is the agent removing the assignment */
                            'ผู้รับผิดชอบคำขอใช้บริการ %1$s ถูกปลดโดย %2$s'),
                            $assigned, $thisstaff->getName());
                        $ticket->logActivity(__('คำขอใช้บริการไม่ได้มอบหมาย'),$msg);
                    } else {
                        $errors['err'] = __('พบปัญหาในการปลดคำขอใช้บริการ กรุณาลองใหม่');
                    }
                    break;
                case 'claim':
                    if(!$thisstaff->canAssignTickets()) {
                        $errors['err'] = __('ปฏิเสธการดำเนินการ คุณไม่ได้รับอนุญาตให้มอบหมายหรือรับคำขอใช้บริการ!');
                    } elseif(!$ticket->isOpen()) {
                        $errors['err'] = __('เฉพาะคำขอใช้บริการที่อยู่ในสถานะดำเนินการเท่านั้น จึงจะมอบหมายได้');
                    } elseif($ticket->isAssigned()) {
                        $errors['err'] = sprintf(__('คำขอใช้บริการได้ถูกมอบหมายให้ %s แล้ว'),$ticket->getAssigned());
                    } elseif($ticket->assignToStaff($thisstaff->getId(), (sprintf(__('คำขอใช้บริการถูกรับโดย %s'),$thisstaff->getName())), false)) {
                        $msg = __('มอบคำขอใช้บริการให้กับตัวเองแล้ว');
                    } else {
                        $errors['err'] = __('พบปัญหาในการมอบหมายคำขอใช้บริการ กรุณาลองใหม่อีกครั้ง');
                    }
                    break;
                case 'overdue':
                    $dept = $ticket->getDept();
                    if(!$dept || !$dept->isManager($thisstaff)) {
                        $errors['err']=__('ปฏิเสธการดำเนินการ คุณไม่มีสิทธิตั้งสถานะคำขอใช้บริการเป็นเลยกำหนด');
                    } elseif($ticket->markOverdue()) {
                        $msg=sprintf(__('คำขอใช้บริการถูกตั้งเป็นเลยกำหนดโดย %s'),$thisstaff->getName());
                        $ticket->logActivity(__('คำขอใช้บริการเลยกำหนด'),$msg);
                    } else {
                        $errors['err']=__('พบปัญหาในการตั้งสถานะคำขอใช้บริการ กรุณาลองใหม่อีกครั้ง');
                    }
                    break;
                case 'answered':
                    $dept = $ticket->getDept();
                    if(!$dept || !$dept->isManager($thisstaff)) {
                        $errors['err']=__('ปฏิเสธการดำเนินการ คุณไม่มีสิทธิเปลี่ยนสถานะคำขอใช้บริการ');
                    } elseif($ticket->markAnswered()) {
                        $msg=sprintf(__('สถานะคำขอใช้บริการถูกเปลี่ยนเป็นกำลังดำเนินการโดย %s'),$thisstaff->getName());
                        $ticket->logActivity(__('คำขอใช้บริการกำลังดำเนินการ'),$msg);
                    } else {
                        $errors['err']=__('พบปัญหาในการตั้งสถานะคำขอใช้บริการ กรุณาลองใหม่อีกครั้ง');
                    }
                    break;
                case 'unanswered':
                    $dept = $ticket->getDept();
                    if(!$dept || !$dept->isManager($thisstaff)) {
                        $errors['err']=__('ปฏิเสธการดำเนินการ คุณไม่มีสิทธิเปลี่ยนสถานะคำขอใช้บริการ');
                    } elseif($ticket->markUnAnswered()) {
                        $msg=sprintf(__('สถานะคำขอใช้บริการถูกเปลี่ยนเป็นยังไม่ดำเนินการโดย %s'),$thisstaff->getName());
                        $ticket->logActivity(__('คำขอใช้บริการยังไม่ดำเนินการ'),$msg);
                    } else {
                        $errors['err']=__('พบปัญหาในการตั้งสถานะคำขอใช้บริการ กรุณาลองใหม่อีกครั้ง');
                    }
                    break;
                case 'banemail':
                    if(!$thisstaff->canBanEmails()) {
                        $errors['err']=__('ปฏิเสธการดำเนินการ คุณไม่มีสิทธิแบนอีเมล');
                    } elseif(BanList::includes($ticket->getEmail())) {
                        $errors['err']=__('มีอีเมลนี้ในรายชื่อที่ถูกแบนแล้ว');
                    } elseif(Banlist::add($ticket->getEmail(),$thisstaff->getName())) {
                        $msg=sprintf(__('อีเมล %s ถูกเพิ่มในรายการที่ถูกแบน'),$ticket->getEmail());
                    } else {
                        $errors['err']=__('ไม่สามารถเพิ่มอีเมลในรายการแบนได้');
                    }
                    break;
                case 'unbanemail':
                    if(!$thisstaff->canBanEmails()) {
                        $errors['err'] = __('ปฏิเสธการดำเนินการ คุณไม่มีสิทธิปลดอีเมลจากการแบน');
                    } elseif(Banlist::remove($ticket->getEmail())) {
                        $msg = __('อีเมลถูกถอดจากรายการที่ถูกแบน');
                    } elseif(!BanList::includes($ticket->getEmail())) {
                        $warn = __('อีเมลไม่ได้อยู่ในรายการที่ถูกแบน');
                    } else {
                        $errors['err']=__('ไม่สามารถปลดอีเมลจากการแบนได้ กรุณาลองใหม่อีกครั้ง');
                    }
                    break;
                case 'changeuser':
                    if (!$thisstaff->canEditTickets()) {
                        $errors['err']=__('ปฏิเสธการดำเนินการ คุณไม่สามารถแก้ไขคำขอใช้บริการได้');
                    } elseif (!$_POST['user_id'] || !($user=User::lookup($_POST['user_id']))) {
                        $errors['err'] = __('ผู้ใช้ไม่ถูกต้อง');
                    } elseif ($ticket->changeOwner($user)) {
                        $msg = sprintf(__('ผู้สร้างคำขอใช้บริการถูกเปลี่ยนเป็น %s'),
                            Format::htmlchars($user->getName()));
                    } else {
                        $errors['err'] = __('ไม่สามารถเปลี่ยนผู้สร้างคำขอใช้บริการได้ กรุณาลองใหม่อีกครั้ง');
                    }
                    break;
                default:
                    $errors['err']=__('คุณต้องเลือกอย่างน้อย 1 รายการ');
            endswitch;
            break;
        default:
            $errors['err']=__('เกิดข้อผิดพลาด');
        endswitch;
        if($ticket && is_object($ticket))
            $ticket->reload();//Reload ticket info following post processing
    }elseif($_POST['a']) {

        switch($_POST['a']) {
            case 'open':
                $ticket=null;
                if(!$thisstaff || !$thisstaff->canCreateTickets()) {
                     $errors['err'] = sprintf('%s %s',
                             sprintf(__('คุณไม่มีสิทธิ %s.'),
                                 __('สร้างคำขอใช้บริการ')),
                             __('กรุณาติดต่อผู้ดูแลระบบ'));
                } else {
                    $vars = $_POST;
                    $vars['uid'] = $user? $user->getId() : 0;

                    $vars['cannedattachments'] = $response_form->getField('attachments')->getClean();

                    if(($ticket=Ticket::open($vars, $errors))) {
                        $msg=__('สร้างคำขอใช้บริการเรียบร้อย');
                        $_REQUEST['a']=null;
                        if (!$ticket->checkStaffAccess($thisstaff) || $ticket->isClosed())
                            $ticket=null;
                        Draft::deleteForNamespace('ticket.staff%', $thisstaff->getId());
                        // Drop files from the response attachments widget
                        $response_form->setSource(array());
                        $response_form->getField('attachments')->reset();
                        unset($_SESSION[':form-data']);
                    } elseif(!$errors['err']) {
                        $errors['err']=__('ไม่สามารถสร้างคำขอใช้บริการได้ กรุณาแก้ไขข้อมูลให้ถูกต้อง');
                    }
                }
                break;
        }
    }
    if(!$errors)
        $thisstaff ->resetStats(); //We'll need to reflect any changes just made!
endif;

/*... Quick stats ...*/
$stats= $thisstaff->getTicketsStats();

//Navigation
$nav->setTabActive('tickets');
$open_name = _P('queue-name',
    /* This is the name of the open ticket queue */
    'คำขอใช้บริการใหม่');
if($cfg->showAnsweredTickets()) {
    $nav->addSubMenu(array('desc'=>$open_name.' ('.number_format($stats['open']+$stats['answered']).')',
                            'title'=>__('คำขอใช้บริการใหม่'),
                            'href'=>'tickets.php',
                            'iconclass'=>'Ticket'),
                        (!$_REQUEST['status'] || $_REQUEST['status']=='open'));
} else {

    if ($stats) {

        $nav->addSubMenu(array('desc'=>$open_name.' ('.number_format($stats['open']).')',
                               'title'=>__('คำขอใช้บริการใหม่'),
                               'href'=>'tickets.php',
                               'iconclass'=>'Ticket'),
                            (!$_REQUEST['status'] || $_REQUEST['status']=='open'));
    }

    if($stats['answered']) {
        $nav->addSubMenu(array('desc'=>__('กำลังดำเนินการ').' ('.number_format($stats['answered']).')',
                               'title'=>__('กำลังดำเนินการ'),
                               'href'=>'tickets.php?status=answered',
                               'iconclass'=>'answeredTickets'),
                            ($_REQUEST['status']=='answered'));
    }
}

if($stats['assigned']) {

    $nav->addSubMenu(array('desc'=>__('คำขอใช้บริการของฉัน').' ('.number_format($stats['assigned']).')',
                           'title'=>__('คำขอใช้บริการของฉัน'),
                           'href'=>'tickets.php?status=assigned',
                           'iconclass'=>'assignedTickets'),
                        ($_REQUEST['status']=='assigned'));
}

if($stats['overdue']) {
    $nav->addSubMenu(array('desc'=>__('เลยกำหนด').' ('.number_format($stats['overdue']).')',
                           'title'=>__('เลยกำหนด'),
                           'href'=>'tickets.php?status=overdue',
                           'iconclass'=>'overdueTickets'),
                        ($_REQUEST['status']=='overdue'));

    if(!$sysnotice && $stats['overdue']>10)
        $sysnotice=sprintf(__('%d คำขอใช้บริการเลยกำหนด!'),$stats['overdue']);
}

if($thisstaff->showAssignedOnly() && $stats['closed']) {
    $nav->addSubMenu(array('desc'=>__('คำขอที่ดำเนินการแล้วของฉัน').' ('.number_format($stats['closed']).')',
                           'title'=>__('คำขอที่ดำเนินการแล้วของฉัน'),
                           'href'=>'tickets.php?status=closed',
                           'iconclass'=>'closedTickets'),
                        ($_REQUEST['status']=='closed'));
} else {

    $nav->addSubMenu(array('desc' => __('ดำเนินการแล้ว').' ('.number_format($stats['closed']).')',
                           'title'=>__('ดำเนินการแล้ว'),
                           'href'=>'tickets.php?status=closed',
                           'iconclass'=>'closedTickets'),
                        ($_REQUEST['status']=='closed'));
}

if($thisstaff->canCreateTickets()) {
    $nav->addSubMenu(array('desc'=>__('สร้างคำขอใช้บริการ'),
                           'title'=> __('สร้างคำขอใช้บริการ'),
                           'href'=>'tickets.php?a=open',
                           'iconclass'=>'newTicket',
                           'id' => 'new-ticket'),
                        ($_REQUEST['a']=='open'));
}


$ost->addExtraHeader('<script type="text/javascript" src="js/ticket.js?c18eac4"></script>');
$ost->addExtraHeader('<meta name="tip-namespace" content="tickets.queue" />',
    "$('#content').data('tipNamespace', 'tickets.queue');");

$inc = 'tickets.inc.php';
if($ticket) {
    $ost->setPageTitle(sprintf(__('คำขอใช้บริการ %s'),$ticket->getNumber()));
    $nav->setActiveSubMenu(-1);
    $inc = 'ticket-view.inc.php';
    if($_REQUEST['a']=='edit' && $thisstaff->canEditTickets()) {
        $inc = 'ticket-edit.inc.php';
        if (!$forms) $forms=DynamicFormEntry::forTicket($ticket->getId());
        // Auto add new fields to the entries
        foreach ($forms as $f) $f->addMissingFields();
    } elseif($_REQUEST['a'] == 'print' && !$ticket->pdfExport($_REQUEST['psize'], $_REQUEST['notes']))
        $errors['err'] = __('ปัญหาระบบ: ไม่สามารถแปลงคำขอใช้บริการเป็น PDF ได้');
} else {
	$inc = 'tickets.inc.php';
    if($_REQUEST['a']=='open' && $thisstaff->canCreateTickets())
        $inc = 'ticket-open.inc.php';
    elseif($_REQUEST['a'] == 'export') {
        $ts = strftime('%Y%m%d');
        if (!($token=$_REQUEST['h']))
            $errors['err'] = __('ต้องการ Query Token');
        elseif (!($query=$_SESSION['search_'.$token]))
            $errors['err'] = __('ไม่พบ Query Token');
        elseif (!Export::saveTickets($query, "tickets-$ts.csv", 'csv'))
            $errors['err'] = __('ปัญหาระบบ: ไม่สามารถแสดงผลคิวรีได้');
    }

    //Clear active submenu on search with no status
    if($_REQUEST['a']=='search' && !$_REQUEST['status'])
        $nav->setActiveSubMenu(-1);

    //set refresh rate if the user has it configured
    if(!$_POST && !$_REQUEST['a'] && ($min=$thisstaff->getRefreshRate())) {
        $js = "clearTimeout(window.ticket_refresh);
               window.ticket_refresh = setTimeout($.refreshTicketView,"
            .($min*60000).");";
        $ost->addExtraHeader('<script type="text/javascript">'.$js.'</script>',
            $js);
    }
}

require_once(STAFFINC_DIR.'header.inc.php');
require_once(STAFFINC_DIR.$inc);
print $response_form->getMedia();
require_once(STAFFINC_DIR.'footer.inc.php');
