<?php
//Note that ticket obj is initiated in tickets.php.
if(!defined('OSTSCPINC') || !$thisstaff || !is_object($ticket) || !$ticket->getId()) die('ตำแหน่งไม่ถูกต้อง');

//Make sure the staff is allowed to access the page.
if(!@$thisstaff->isStaff() || !$ticket->checkStaffAccess($thisstaff)) die('ปฏิเสธการเข้าถึง');

//Re-use the post info on error...savekeyboards.org (Why keyboard? -> some people care about objects than users!!)
$info=($_POST && $errors)?Format::input($_POST):array();

//Auto-lock the ticket if locking is enabled.. If already locked by the user then it simply renews.
if($cfg->getLockTime() && !$ticket->acquireLock($thisstaff->getId(),$cfg->getLockTime()))
    $warn.=__('ไม่สามารถเข้าถึงคำขอใช้บริการ');

//Get the goodies.
$dept  = $ticket->getDept();  //Dept
$staff = $ticket->getStaff(); //Assigned or closed by..
$user  = $ticket->getOwner(); //Ticket User (EndUser)
$team  = $ticket->getTeam();  //Assigned team.
$sla   = $ticket->getSLA();
$lock  = $ticket->getLock();  //Ticket lock obj
$id    = $ticket->getId();    //Ticket ID.

//Useful warnings and errors the user might want to know!
if ($ticket->isClosed() && !$ticket->isReopenable())
    $warn = sprintf(
            __('สถานะคำขอใช้บริการปัจจุบัน (%s) ไม่อนุญาติให้ผู้ใช้ตอบกลับ'),
            $ticket->getStatus());
elseif ($ticket->isAssigned()
        && (($staff && $staff->getId()!=$thisstaff->getId())
            || ($team && !$team->hasMember($thisstaff))
        ))
    $warn.= sprintf('&nbsp;&nbsp;<span class="Icon assignedTicket">%s</span>',
            sprintf(__('คำขอใช้บริการมอบหมายให้ %s'),
                implode('/', $ticket->getAssignees())
                ));

if (!$errors['err']) {

    if ($lock && $lock->getStaffId()!=$thisstaff->getId())
        $errors['err'] = sprintf(__('คำขอใช้บริการนี้กำลังถูกใช้งานโดย %s'),
                $lock->getStaffName());
    elseif (($emailBanned=TicketFilter::isBanned($ticket->getEmail())))
        $errors['err'] = __('อีเมลอยู่ในรายการที่ถูกแบน! ต้องปลดแบนก่อนจึงจะตอบกลับได้');
}

$unbannable=($emailBanned) ? BanList::includes($ticket->getEmail()) : false;

if($ticket->isOverdue())
    $warn.='&nbsp;&nbsp;<span class="Icon overdueTicket">'.__('คำขอใช้บริการเลยกำหนด!').'</span>';

?>
<table width="940" cellpadding="2" cellspacing="0" border="0">
    <tr>
        <td width="20%" class="has_bottom_border">
             <h2><a href="tickets.php?id=<?php echo $ticket->getId(); ?>"
             title="<?php echo __('รีเฟรช'); ?>"><i class="icon-refresh"></i>
             <?php echo sprintf(__('เลขที่ %s'), $ticket->getNumber()); ?></a></h2>
        </td>
        <td width="auto" class="flush-right has_bottom_border">
            <?php
            if ($thisstaff->canBanEmails()
                    || $thisstaff->canEditTickets()
                    || ($dept && $dept->isManager($thisstaff))) { ?>
            <span class="action-button pull-right" data-dropdown="#action-dropdown-more">
                <i class="icon-caret-down pull-right"></i>
                <span ><i class="icon-cog"></i> <?php echo __('อื่นๆ');?></span>
            </span>
            <?php
            }
            // Status change options
            echo TicketStatus::status_options();

            if ($thisstaff->canEditTickets()) { ?>
                <a class="action-button pull-right" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=edit"><i class="icon-edit"></i> <?php
                    echo __('แก้ไข'); ?></a>
            <?php
            }
            if ($ticket->isOpen() && !$ticket->isAssigned() && $thisstaff->canAssignTickets()) {?>
                <a id="ticket-claim" class="action-button pull-right confirm-action" href="#claim"><i class="icon-user"></i> <?php
                    echo __('อ้างสิทธิ์'); ?></a>

            <?php
            }?>
            <span class="action-button pull-right" data-dropdown="#action-dropdown-print">
                <i class="icon-caret-down pull-right"></i>
                <a id="ticket-print" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print"><i class="icon-print"></i> <?php
                    echo __('พิมพ์'); ?></a>
            </span>
            <div id="action-dropdown-print" class="action-dropdown anchor-right">
              <ul>
                 <li><a class="no-pjax" target="_blank" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print&notes=0"><i
                 class="icon-file-alt"></i> <?php echo __('เฉพาะบทสนทนา'); ?></a>
                 <li><a class="no-pjax" target="_blank" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print&notes=1"><i
                 class="icon-file-text-alt"></i> <?php echo __('บทสนทนา + บันทึกระบบ'); ?></a>
              </ul>
            </div>
            <div id="action-dropdown-more" class="action-dropdown anchor-right">
              <ul>
                <?php
                 if($thisstaff->canEditTickets()) { ?>
                    <li><a class="change-user" href="#tickets/<?php
                    echo $ticket->getId(); ?>/change-user"><i class="icon-user"></i> <?php
                    echo __('เปลี่ยนผู้สร้าง'); ?></a></li>
                <?php
                 }
                 if($thisstaff->canDeleteTickets()) {
                     ?>
                    <li><a class="ticket-action" href="#tickets/<?php
                    echo $ticket->getId(); ?>/status/delete"
                    data-href="tickets.php"><i class="icon-trash"></i> <?php
                    echo __('ลบคำขอใช้บริการ'); ?></a></li>
                <?php
                 }
                if($ticket->isOpen() && ($dept && $dept->isManager($thisstaff))) {

                    if($ticket->isAssigned()) { ?>
                        <li><a  class="confirm-action" id="ticket-release" href="#release"><i class="icon-user"></i> <?php
                            echo __('ปลดผู้ดำเนินงาน'); ?></a></li>
                    <?php
                    }

                    if(!$ticket->isOverdue()) { ?>
                        <li><a class="confirm-action" id="ticket-overdue" href="#overdue"><i class="icon-bell"></i> <?php
                            echo __('เลยกำหนด'); ?></a></li>
                    <?php
                    }

                    if($ticket->isAnswered()) { ?>
                    <li><a class="confirm-action" id="ticket-unanswered" href="#unanswered"><i class="icon-circle-arrow-left"></i> <?php
                            echo __('ยังไม่ได้ตอบกลับ'); ?></a></li>
                    <?php
                    } else { ?>
                    <li><a class="confirm-action" id="ticket-answered" href="#answered"><i class="icon-circle-arrow-right"></i> <?php
                            echo __('กำลังดำเนินการ'); ?></a></li>
                    <?php
                    }
                } ?>
                <li><a href="#ajax.php/tickets/<?php echo $ticket->getId();
                    ?>/forms/manage" onclick="javascript:
                    $.dialog($(this).attr('href').substr(1), 201);
                    return false"
                    ><i class="icon-paste"></i> <?php echo __('จัดการฟอร์ม'); ?></a></li>

<?php           if($thisstaff->canBanEmails()) {
                     if(!$emailBanned) {?>
                        <li><a class="confirm-action" id="ticket-banemail"
                            href="#banemail"><i class="icon-ban-circle"></i> <?php echo sprintf(
                                Format::htmlchars(__('แบนอีเมล <%s>')),
                                $ticket->getEmail()); ?></a></li>
                <?php
                     } elseif($unbannable) { ?>
                        <li><a  class="confirm-action" id="ticket-banemail"
                            href="#unbanemail"><i class="icon-undo"></i> <?php echo sprintf(
                                Format::htmlchars(__('ปลดแบนอีเมล <%s>')),
                                $ticket->getEmail()); ?></a></li>
                    <?php
                     }
                }?>
              </ul>
            </div>
        </td>
    </tr>
</table>
<table class="ticket_info" cellspacing="0" cellpadding="0" width="940" border="0">
    <tr>
        <td width="50%">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th width="100"><?php echo __('สถานะ');?>:</th>
                    <td><?php echo $ticket->getStatus(); ?></td>
                </tr>
                <tr>
                    <th><?php echo __('ความสำคัญ');?>:</th>
                    <td><?php echo $ticket->getPriority(); ?></td>
                </tr>
                <tr>
                    <th><?php echo __('แผนก');?>:</th>
                    <td><?php echo Format::htmlchars($ticket->getDeptName()); ?></td>
                </tr>
                <tr>
                    <th><?php echo __('วันที่สร้าง');?>:</th>
                    <td><?php echo Format::db_datetime($ticket->getCreateDate()); ?></td>
                </tr>
            </table>
        </td>
        <td width="50%" style="vertical-align:top">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th width="100"><?php echo __('ผู้ใช้'); ?>:</th>
                    <td><a href="#tickets/<?php echo $ticket->getId(); ?>/user"
                        onclick="javascript:
                            $.userLookup('ajax.php/tickets/<?php echo $ticket->getId(); ?>/user',
                                    function (user) {
                                        $('#user-'+user.id+'-name').text(user.name);
                                        $('#user-'+user.id+'-email').text(user.email);
                                        $('#user-'+user.id+'-phone').text(user.phone);
                                        $('select#emailreply option[value=1]').text(user.name+' <'+user.email+'>');
                                    });
                            return false;
                            "><i class="icon-user"></i> <span id="user-<?php echo $ticket->getOwnerId(); ?>-name"
                            ><?php echo Format::htmlchars($ticket->getName());
                        ?></span></a>
                        <?php
                        if($user) {
                            echo sprintf('&nbsp;&nbsp;<a href="tickets.php?a=search&uid=%d" title="%s" data-dropdown="#action-dropdown-stats">(<b>%d</b>)</a>',
                                    urlencode($user->getId()), __('คำขอใช้บริการที่เกี่ยวข้อง'), $user->getNumTickets());
                        ?>
                            <div id="action-dropdown-stats" class="action-dropdown anchor-right">
                                <ul>
                                    <?php
                                    if(($open=$user->getNumOpenTickets()))
                                        echo sprintf('<li><a href="tickets.php?a=search&status=open&uid=%s"><i class="icon-folder-open-alt icon-fixed-width"></i> %s</a></li>',
                                                $user->getId(), sprintf(_N('%d คำขอใช้บริการใหม่', '%d คำขอใช้บริการใหม่', $open), $open));

                                    if(($closed=$user->getNumClosedTickets()))
                                        echo sprintf('<li><a href="tickets.php?a=search&status=closed&uid=%d"><i
                                                class="icon-folder-close-alt icon-fixed-width"></i> %s</a></li>',
                                                $user->getId(), sprintf(_N('%d ดำเนินการแล้ว', '%d ดำเนินการแล้ว', $closed), $closed));
                                    ?>
                                    <li><a href="tickets.php?a=search&uid=<?php echo $ticket->getOwnerId(); ?>"><i class="icon-double-angle-right icon-fixed-width"></i> <?php echo __('คำขอใช้บริการทั้งหมด'); ?></a></li>
                                    <li><a href="users.php?id=<?php echo
                                    $user->getId(); ?>"><i class="icon-user
                                    icon-fixed-width"></i> <?php echo __('จัดการผู้ใช้'); ?></a></li>
<?php if ($user->getOrgId()) { ?>
                                    <li><a href="orgs.php?id=<?php echo $user->getOrgId(); ?>"><i
                                        class="icon-building icon-fixed-width"></i> <?php
                                        echo __('จัดการบริษัท'); ?></a></li>
<?php } ?>
                                </ul>
                            </div>
                    <?php
                        }
                    ?>
                    </td>
                </tr>
                <tr>
                    <th><?php echo __('อีเมล'); ?>:</th>
                    <td>
                        <span id="user-<?php echo $ticket->getOwnerId(); ?>-email"><?php echo $ticket->getEmail(); ?></span>
                    </td>
                </tr>
                <tr>
                    <th><?php echo __('โทรศัพท์'); ?>:</th>
                    <td>
                        <span id="user-<?php echo $ticket->getOwnerId(); ?>-phone"><?php echo $ticket->getPhoneNumber(); ?></span>
                    </td>
                </tr>
                <tr>
                    <th><?php echo __('แหล่งที่มา'); ?>:</th>
                    <td><?php
                        echo Format::htmlchars($ticket->getSource());

                        if($ticket->getIP())
                            echo '&nbsp;&nbsp; <span class="faded">('.$ticket->getIP().')</span>';
                        ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<table class="ticket_info" cellspacing="0" cellpadding="0" width="940" border="0">
    <tr>
        <td width="50%">
            <table cellspacing="0" cellpadding="4" width="100%" border="0">
                <?php
                if($ticket->isOpen()) { ?>
                <tr>
                    <th width="100"><?php echo __('มอบหมาย');?>:</th>
                    <td>
                        <?php
                        if($ticket->isAssigned())
                            echo Format::htmlchars(implode('/', $ticket->getAssignees()));
                        else
                            echo '<span class="faded">&mdash; '.__('ยังไม่ได้มอบหมาย').' &mdash;</span>';
                        ?>
                    </td>
                </tr>
                <?php
                } else { ?>
                <tr>
                    <th width="100"><?php echo __('ผู้ดำเนินงาน');?>:</th>
                    <td>
                        <?php
                        if(($staff = $ticket->getStaff()))
                            echo Format::htmlchars($staff->getName());
                        else
                            echo '<span class="faded">&mdash; '.__('ไม่ทราบ').' &mdash;</span>';
                        ?>
                    </td>
                </tr>
                <?php
                } ?>
                <tr>
                    <th><?php echo __('แผน SLA');?>:</th>
                    <td><?php echo $sla?Format::htmlchars($sla->getName()):'<span class="faded">&mdash; '.__('ไม่มี').' &mdash;</span>'; ?></td>
                </tr>
                <?php
                if($ticket->isOpen()){ ?>
                <tr>
                    <th><?php echo __('ครบกำหนด');?>:</th>
                    <td><?php echo Format::db_datetime($ticket->getEstDueDate()); ?></td>
                </tr>
                <?php
                }else { ?>
                <tr>
                    <th><?php echo __('สิ้นสุดการดำเนินงาน');?>:</th>
                    <td><?php echo Format::db_datetime($ticket->getCloseDate()); ?></td>
                </tr>
                <?php
                }
                ?>
            </table>
        </td>
        <td width="50%">
            <table cellspacing="0" cellpadding="4" width="100%" border="0">
                <tr>
                    <th width="100"><?php echo __('ประเภทบริการ');?>:</th>
                    <td><?php echo Format::htmlchars($ticket->getHelpTopic()); ?></td>
                </tr>
                <tr>
                    <th nowrap><?php echo __('ข้อความล่าสุดโดยผู้ใช้');?>:</th>
                    <td><?php echo Format::db_datetime($ticket->getLastMsgDate()); ?></td>
                </tr>
                <tr>
                    <th nowrap><?php echo __('ตอบกลับล่าสุดโดยเจ้าหน้าที่');?>:</th>
                    <td><?php echo Format::db_datetime($ticket->getLastRespDate()); ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<table class="ticket_info" cellspacing="0" cellpadding="0" width="940" border="0">
<?php
$idx = 0;
foreach (DynamicFormEntry::forTicket($ticket->getId()) as $form) {
    // Skip core fields shown earlier in the ticket view
    // TODO: Rewrite getAnswers() so that one could write
    //       ->getAnswers()->filter(not(array('field__name__in'=>
    //           array('email', ...))));
    $answers = array_filter($form->getAnswers(), function ($a) {
        return !in_array($a->getField()->get('name'),
                array('email','subject','name','priority'));
        });
    if (count($answers) == 0)
        continue;
    ?>
        <tr>
        <td colspan="2">
            <table cellspacing="0" cellpadding="4" width="100%" border="0">
            <?php foreach($answers as $a) {
                if (!($v = $a->display())) continue; ?>
                <tr>
                    <th width="100"><?php
    echo $a->getField()->get('label');
                    ?>:</th>
                    <td><?php
    echo $v;
                    ?></td>
                </tr>
                <?php } ?>
            </table>
        </td>
        </tr>
    <?php
    $idx++;
    } ?>
</table>
<div class="clear"></div>
<h2 style="padding:10px 0 5px 0; font-size:11pt;"><?php echo Format::htmlchars($ticket->getSubject()); ?></h2>
<?php
$tcount = $ticket->getThreadCount();
$tcount+= $ticket->getNumNotes();
?>
<ul id="threads">
    <li><a class="active" id="toggle_ticket_thread" href="#"><?php echo sprintf(__('บทสนทนา (%d)'), $tcount); ?></a></li>
</ul>
<div id="ticket_thread">
    <?php
    $threadTypes=array('M'=>'message','R'=>'response', 'N'=>'note');
    /* -------- Messages & Responses & Notes (if inline)-------------*/
    $types = array('M', 'R', 'N');
    if(($thread=$ticket->getThreadEntries($types))) {
       foreach($thread as $entry) { ?>
        <table class="thread-entry <?php echo $threadTypes[$entry['thread_type']]; ?>" cellspacing="0" cellpadding="1" width="940" border="0">
            <tr>
                <th colspan="4" width="100%">
                <div>
                    <span class="pull-left">
                    <span style="display:inline-block"><?php
                        echo Format::db_datetime($entry['created']);?></span>
                    <span style="display:inline-block;padding:0 1em" class="faded title"><?php
                        echo Format::truncate($entry['title'], 100); ?></span>
                    </span>
                    <span class="pull-right" style="white-space:no-wrap;display:inline-block">
                        <span style="vertical-align:middle;" class="textra"></span>
                        <span style="vertical-align:middle;"
                            class="tmeta faded title"><?php
                            echo Format::htmlchars($entry['name'] ?: $entry['poster']); ?></span>
                    </span>
                </div>
                </th>
            </tr>
            <tr><td colspan="4" class="thread-body" id="thread-id-<?php
                echo $entry['id']; ?>"><div><?php
                echo $entry['body']->toHtml(); ?></div></td></tr>
            <?php
            if($entry['attachments']
                    && ($tentry = $ticket->getThreadEntry($entry['id']))
                    && ($urls = $tentry->getAttachmentUrls())
                    && ($links = $tentry->getAttachmentsLinks())) {?>
            <tr>
                <td class="info" colspan="4"><?php echo $tentry->getAttachmentsLinks(); ?></td>
            </tr> <?php
            }
            if ($urls) { ?>
                <script type="text/javascript">
                    $('#thread-id-<?php echo $entry['id']; ?>')
                        .data('urls', <?php
                            echo JsonDataEncoder::encode($urls); ?>)
                        .data('id', <?php echo $entry['id']; ?>);
                </script>
<?php
            } ?>
        </table>
        <?php
        if($entry['thread_type']=='M')
            $msgId=$entry['id'];
       }
    } else {
        echo '<p>'.__('ไม่สามารถเรียกบทสนทนาได้ กรุณาติดต่อผู้ดูแลระบบ').'</p>';
    }?>
</div>
<div class="clear" style="padding-bottom:10px;"></div>
<?php if($errors['err']) { ?>
    <div id="msg_error"><?php echo $errors['err']; ?></div>
<?php }elseif($msg) { ?>
    <div id="msg_notice"><?php echo $msg; ?></div>
<?php }elseif($warn) { ?>
    <div id="msg_warning"><?php echo $warn; ?></div>
<?php } ?>

<div id="response_options">
    <ul class="tabs">
        <?php
        if($thisstaff->canPostReply()) { ?>
        <li><a id="reply_tab" href="#reply"><?php echo __('ตอบกลับ');?></a></li>
        <?php
        } ?>
        <li><a id="note_tab" href="#note"><?php echo __('เพิ่มบันทึกภายใน');?></a></li>
        <?php
        if($thisstaff->canTransferTickets()) { ?>
        <li><a id="transfer_tab" href="#transfer"><?php echo __('โอนย้ายไปแผนกอื่น');?></a></li>
        <?php
        }

        if($thisstaff->canAssignTickets()) { ?>
        <li><a id="assign_tab" href="#assign"><?php echo $ticket->isAssigned()?__('มอบหมายคำขอใช้บริการใหม่'):__('มอบหมายคำขอใช้บริการ'); ?></a></li>
        <?php
        } ?>
    </ul>
    <?php
    if($thisstaff->canPostReply()) { ?>
    <form id="reply" action="tickets.php?id=<?php echo $ticket->getId(); ?>#reply" name="reply" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="msgId" value="<?php echo $msgId; ?>">
        <input type="hidden" name="a" value="reply">
        <span class="error"></span>
        <table style="width:100%" border="0" cellspacing="0" cellpadding="3">
           <tbody id="to_sec">
            <tr>
                <td width="120">
                    <label><strong><?php echo __('ข้อความถึง'); ?>:</strong></label>
                </td>
                <td>
                    <?php
                    # XXX: Add user-to-name and user-to-email HTML ID#s
                    $to =sprintf('%s &lt;%s&gt;',
                            Format::htmlchars($ticket->getName()),
                            $ticket->getReplyToEmail());
                    $emailReply = (!isset($info['emailreply']) || $info['emailreply']);
                    ?>
                    <select id="emailreply" name="emailreply">
                        <option value="1" <?php echo $emailReply ?  'selected="selected"' : ''; ?>><?php echo $to; ?></option>
                        <option value="0" <?php echo !$emailReply ? 'selected="selected"' : ''; ?>
                        >&mdash; <?php echo __('ไม่ต้องส่งอีเมล'); ?> &mdash;</option>
                    </select>
                </td>
            </tr>
            </tbody>
            <?php
            if(1) { //Make CC optional feature? NO, for now.
                ?>
            <tbody id="cc_sec"
                style="display:<?php echo $emailReply?  'table-row-group':'none'; ?>;">
             <tr>
                <td width="120">
                    <label><strong><?php echo __('ผู้เกี่ยวข้อง'); ?>:</strong></label>
                </td>
                <td>
                    <input type='checkbox' value='1' name="emailcollab" id="emailcollab"
                        <?php echo ((!$info['emailcollab'] && !$errors) || isset($info['emailcollab']))?'checked="checked"':''; ?>
                        style="display:<?php echo $ticket->getNumCollaborators() ? 'inline-block': 'none'; ?>;"
                        >
                    <?php
                    $recipients = __('เพิ่มผู้เกี่ยวข้อง');
                    if ($ticket->getNumCollaborators())
                        $recipients = sprintf(__('ผู้รับ (%d จาก %d)'),
                                $ticket->getNumActiveCollaborators(),
                                $ticket->getNumCollaborators());

                    echo sprintf('<span><a class="collaborators preview"
                            href="#tickets/%d/collaborators"><span id="recipients">%s</span></a></span>',
                            $ticket->getId(),
                            $recipients);
                   ?>
                </td>
             </tr>
            </tbody>
            <?php
            } ?>
            <tbody id="resp_sec">
            <?php
            if($errors['response']) {?>
            <tr><td width="120">&nbsp;</td><td class="error"><?php echo $errors['response']; ?>&nbsp;</td></tr>
            <?php
            }?>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong><?php echo __('ตอบกลับ');?>:</strong></label>
                </td>
                <td>
<?php if ($cfg->isCannedResponseEnabled()) { ?>
                    <select id="cannedResp" name="cannedResp">
                        <option value="0" selected="selected"><?php echo __('เลือกคำตอบสำเร็จรูป');?></option>
                        <option value='original'><?php echo __('ข้อความต้นฉบับของผู้ใช้'); ?></option>
                        <option value='lastmessage'><?php echo __('ข้อความล่าสุดจากผู้ใช้'); ?></option>
                        <?php
                        if(($cannedResponses=Canned::responsesByDeptId($ticket->getDeptId()))) {
                            echo '<option value="0" disabled="disabled">
                                ------------- '.__('คำตอบสำเร็จรูป').' ------------- </option>';
                            foreach($cannedResponses as $id =>$title)
                                echo sprintf('<option value="%d">%s</option>',$id,$title);
                        }
                        ?>
                    </select>
                    <br>
				<?php } # endif (canned-resonse-enabled)
                    $signature = '';
                    switch ($thisstaff->getDefaultSignatureType()) {
                    case 'dept':
                        if ($dept && $dept->canAppendSignature())
                           $signature = $dept->getSignature();
                       break;
                    case 'mine':
                        $signature = $thisstaff->getSignature();
                        break;
                    } ?>
                    <input type="hidden" name="draft_id" value=""/>
                    <textarea name="response" id="response" cols="50"
                        data-draft-namespace="ticket.response"
                        data-signature-field="signature" data-dept-id="<?php echo $dept->getId(); ?>"
                        data-signature="<?php
                            echo Format::htmlchars(Format::viewableImages($signature)); ?>"
                        placeholder="<?php echo __(
                        'เขียนข้อความตอบกลับที่คุณต้องการที่นี่'
                        ); ?>"
                        data-draft-object-id="<?php echo $ticket->getId(); ?>"
                        rows="9" wrap="soft"
                        class="richtext ifhtml draft draft-delete"><?php
                        echo $info['response']; ?></textarea>
                <div id="reply_form_attachments" class="attachments">
<?php
print $response_form->getField('attachments')->render();
?>
                </div>
                </td>
            </tr>
            <tr>
                <td width="120">
                    <label for="signature" class="left"><?php echo __('ลายเซ็นต์');?>:</label>
                </td>
                <td>
                    <?php
                    $info['signature']=$info['signature']?$info['signature']:$thisstaff->getDefaultSignatureType();
                    ?>
                    <label><input type="radio" name="signature" value="none" checked="checked"> <?php echo __('ไม่มี');?></label>
                    <?php
                    if($thisstaff->getSignature()) {?>
                    <label><input type="radio" name="signature" value="mine"
                        <?php echo ($info['signature']=='mine')?'checked="checked"':''; ?>> <?php echo __('ลายเซ็นต์ของฉัน');?></label>
                    <?php
                    } ?>
                    <?php
                    if($dept && $dept->canAppendSignature()) { ?>
                    <label><input type="radio" name="signature" value="dept"
                        <?php echo ($info['signature']=='dept')?'checked="checked"':''; ?>>
                        <?php echo sprintf(__('ลายเซ็นต์แผนก (%s)'), Format::htmlchars($dept->getName())); ?></label>
                    <?php
                    } ?>
                </td>
            </tr>
            <tr>
                <td width="120">
                    <label><strong><?php echo __('สถานะ');?>:</strong></label>
                </td>
                <td>
                    <select name="reply_status_id">
                    <?php
                    $statusId = $info['reply_status_id'] ?: $ticket->getStatusId();
                    $states = array('open');
                    if ($thisstaff->canCloseTickets())
                        $states = array_merge($states, array('closed'));

                    foreach (TicketStatusList::getStatuses(
                                array('states' => $states)) as $s) {
                        if (!$s->isEnabled()) continue;
                        $selected = ($statusId == $s->getId());
                        echo sprintf('<option value="%d" %s>%s%s</option>',
                                $s->getId(),
                                $selected
                                 ? 'selected="selected"' : '',
                                __($s->getName()),
                                $selected
                                ? (' ('.__('ปัจจุบัน').')') : ''
                                );
                    }
                    ?>
                    </select>
                </td>
            </tr>
         </tbody>
        </table>
        <p  style="padding:0 165px;">
            <input class="btn_sm" type="submit" value="<?php echo __('ตอบกลับ');?>">
            <input class="btn_sm" type="reset" value="<?php echo __('รีเซ็ต');?>">
        </p>
    </form>
    <?php
    } ?>
    <form id="note" action="tickets.php?id=<?php echo $ticket->getId(); ?>#note" name="note" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="locktime" value="<?php echo $cfg->getLockTime(); ?>">
        <input type="hidden" name="a" value="postnote">
        <table width="100%" border="0" cellspacing="0" cellpadding="3">
            <?php
            if($errors['postnote']) {?>
            <tr>
                <td width="120">&nbsp;</td>
                <td class="error"><?php echo $errors['postnote']; ?></td>
            </tr>
            <?php
            } ?>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong><?php echo __('บันทึกภายใน'); ?>:</strong><span class='error'>&nbsp;*</span></label>
                </td>
                <td>
                    <div>
                        <div class="faded" style="padding-left:0.15em"><?php
                        echo __('หัวเรื่องบันทึกย่อ (ถ้ามี)'); ?></div>
                        <input type="text" name="title" id="title" size="60" value="<?php echo $info['title']; ?>" >
                        <br/>
                        <span class="error">&nbsp;<?php echo $errors['title']; ?></span>
                    </div>
                    <br/>
                    <div class="error"><?php echo $errors['note']; ?></div>
                    <textarea name="note" id="internal_note" cols="80"
                        placeholder="<?php echo __('รายละเอียดบันทึกย่อ (ต้องระบุ)'); ?>"
                        rows="9" wrap="soft" data-draft-namespace="ticket.note"
                        data-draft-object-id="<?php echo $ticket->getId(); ?>"
                        class="richtext ifhtml draft draft-delete"><?php echo $info['note'];
                        ?></textarea>
                <div class="attachments">
<?php
print $note_form->getField('attachments')->render();
?>
                </div>
                </td>
            </tr>
            <tr><td colspan="2">&nbsp;</td></tr>
            <tr>
                <td width="120">
                    <label><?php echo __('สถานะ');?>:</label>
                </td>
                <td>
                    <div class="faded"></div>
                    <select name="note_status_id">
                        <?php
                        $statusId = $info['note_status_id'] ?: $ticket->getStatusId();
                        $states = array('open');
                        if ($thisstaff->canCloseTickets())
                            $states = array_merge($states, array('closed'));
                        foreach (TicketStatusList::getStatuses(
                                    array('states' => $states)) as $s) {
                            if (!$s->isEnabled()) continue;
                            $selected = $statusId == $s->getId();
                            echo sprintf('<option value="%d" %s>%s%s</option>',
                                    $s->getId(),
                                    $selected ? 'selected="selected"' : '',
                                    __($s->getName()),
                                    $selected ? (' ('.__('ปัจจุบัน').')') : ''
                                    );
                        }
                        ?>
                    </select>
                    &nbsp;<span class='error'>*&nbsp;<?php echo $errors['note_status_id']; ?></span>
                </td>
            </tr>
        </table>

       <p  style="padding-left:165px;">
           <input class="btn_sm" type="submit" value="<?php echo __('สร้างบันทึก');?>">
           <input class="btn_sm" type="reset" value="<?php echo __('รีเซ็ต');?>">
       </p>
   </form>
    <?php
    if($thisstaff->canTransferTickets()) { ?>
    <form id="transfer" action="tickets.php?id=<?php echo $ticket->getId(); ?>#transfer" name="transfer" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="ticket_id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="transfer">
        <table width="100%" border="0" cellspacing="0" cellpadding="3">
            <?php
            if($errors['transfer']) {
                ?>
            <tr>
                <td width="120">&nbsp;</td>
                <td class="error"><?php echo $errors['transfer']; ?></td>
            </tr>
            <?php
            } ?>
            <tr>
                <td width="120">
                    <label for="deptId"><strong><?php echo __('แผนก');?>:</strong></label>
                </td>
                <td>
                    <?php
                        echo sprintf('<span class="faded">'.__('ขณะนี้คำขอใช้บริการอยู่ในแผนก <b>%s</b> ').'</span>', $ticket->getDeptName());
                    ?>
                    <br>
                    <select id="deptId" name="deptId">
                        <option value="0" selected="selected">&mdash; <?php echo __('เลือกแผนกที่ต้องการ');?> &mdash;</option>
                        <?php
                        if($depts=Dept::getDepartments()) {
                            foreach($depts as $id =>$name) {
                                if($id==$ticket->getDeptId()) continue;
                                echo sprintf('<option value="%d" %s>%s</option>',
                                        $id, ($info['deptId']==$id)?'selected="selected"':'',$name);
                            }
                        }
                        ?>
                    </select>&nbsp;<span class='error'>*&nbsp;<?php echo $errors['deptId']; ?></span>
                </td>
            </tr>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong><?php echo __('ข้อคิดเห็น'); ?>:</strong><span class='error'>&nbsp;*</span></label>
                </td>
                <td>
                    <textarea name="transfer_comments" id="transfer_comments"
                        placeholder="<?php echo __('ต้องระบุเหตุผลในการโอนย้ายคำขอใช้บริการ'); ?>"
                        class="richtext ifhtml no-bar" cols="80" rows="7" wrap="soft"><?php
                        echo $info['transfer_comments']; ?></textarea>
                    <span class="error"><?php echo $errors['transfer_comments']; ?></span>
                </td>
            </tr>
        </table>
        <p style="padding-left:165px;">
           <input class="btn_sm" type="submit" value="<?php echo __('โอนย้าย');?>">
           <input class="btn_sm" type="reset" value="<?php echo __('รีเซ็ต');?>">
        </p>
    </form>
    <?php
    } ?>
    <?php
    if($thisstaff->canAssignTickets()) { ?>
    <form id="assign" action="tickets.php?id=<?php echo $ticket->getId(); ?>#assign" name="assign" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="assign">
        <table style="width:100%" border="0" cellspacing="0" cellpadding="3">

            <?php
            if($errors['assign']) {
                ?>
            <tr>
                <td width="120">&nbsp;</td>
                <td class="error"><?php echo $errors['assign']; ?></td>
            </tr>
            <?php
            } ?>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label for="assignId"><strong><?php echo __('ผู้ได้รับมอบหมาย');?>:</strong></label>
                </td>
                <td>
                    <select id="assignId" name="assignId">
                        <option value="0" selected="selected">&mdash; <?php echo __('เลือกเจ้าหน้าที่หรือทีม');?> &mdash;</option>
                        <?php
                        if($ticket->isOpen() && !$ticket->isAssigned())
                            echo sprintf('<option value="%d">'.__('อ้างสิทธิ์คำขอใช้บริการ').'</option>', $thisstaff->getId());

                        $sid=$tid=0;

                        if ($dept->assignMembersOnly())
                            $users = $dept->getAvailableMembers();
                        else
                            $users = Staff::getAvailableStaffMembers();

                        if ($users) {
                            echo '<OPTGROUP label="'.sprintf(__('เจ้าหน้าที่ (%d)'), count($users)).'">';
                            $staffId=$ticket->isAssigned()?$ticket->getStaffId():0;
                            foreach($users as $id => $name) {
                                if($staffId && $staffId==$id)
                                    continue;

                                if (!is_object($name))
                                    $name = new PersonsName($name);

                                $k="s$id";
                                echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['assignId']==$k)?'selected="selected"':''), $name);
                            }
                            echo '</OPTGROUP>';
                        }

                        if(($teams=Team::getActiveTeams())) {
                            echo '<OPTGROUP label="'.sprintf(__('ทีม (%d)'), count($teams)).'">';
                            $teamId=(!$sid && $ticket->isAssigned())?$ticket->getTeamId():0;
                            foreach($teams as $id => $name) {
                                if($teamId && $teamId==$id)
                                    continue;

                                $k="t$id";
                                echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['assignId']==$k)?'selected="selected"':''),$name);
                            }
                            echo '</OPTGROUP>';
                        }
                        ?>
                    </select>&nbsp;<span class='error'>*&nbsp;<?php echo $errors['assignId']; ?></span>
                    <?php
                    if ($ticket->isAssigned() && $ticket->isOpen()) { ?>
                        <div class="faded"><?php echo sprintf(__('คำขอใช้บริการถูกมอบหมายให้ %s'),
                            sprintf('<b>%s</b>', $ticket->getAssignee())); ?></div> <?php
                    } elseif ($ticket->isClosed()) { ?>
                        <div class="faded"><?php echo __('มอบหมายเจ้าหน้าที่ให้คำขอใช้บริการที่ปิดไปแล้วจะเป็นการ <b>เปิดคำขอใช้บริการ</b> ขึ้นใหม่!'); ?></div>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong><?php echo __('ข้อคิดเห็น');?>:</strong><span class='error'>&nbsp;</span></label>
                </td>
                <td>
                    <textarea name="assign_comments" id="assign_comments"
                        cols="80" rows="7" wrap="soft"
                        placeholder="<?php echo __('ระบุเหตุผลที่มอบหมายคำขอใช้บริการ หรือคำแนะนำสำหรับเจ้าหน้าที่ที่ได้รับมอบหมาย'); ?>"
                        class="richtext ifhtml no-bar"><?php echo $info['assign_comments']; ?></textarea>
                    <span class="error"><?php echo $errors['assign_comments']; ?></span><br>
                </td>
            </tr>
        </table>
        <p  style="padding-left:165px;">
            <input class="btn_sm" type="submit" value="<?php echo $ticket->isAssigned()?__('มอบหมายใหม่'):__('มอบหมาย'); ?>">
            <input class="btn_sm" type="reset" value="<?php echo __('รีเซ็ต');?>">
        </p>
    </form>
    <?php
    } ?>
</div>
<div style="display:none;" class="dialog" id="print-options">
    <h3><?php echo __('ตั้งค่าการพิมพ์คำขอใช้บริการ');?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>" method="post" id="print-form" name="print-form">
        <?php csrf_token(); ?>
        <input type="hidden" name="a" value="print">
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <fieldset class="notes">
            <label class="fixed-size" for="notes"><?php echo __('พิมพ์บันทึกย่อ');?>:</label>
            <input type="checkbox" id="notes" name="notes" value="1"> <?php echo __('พิมพ์บันทึกย่อ <b>ภายใน</b>');?>
        </fieldset>
        <fieldset>
            <label class="fixed-size" for="psize"><?php echo __('ขนาดกระดาษ');?>:</label>
            <select id="psize" name="psize">
                <option value="">&mdash; <?php echo __('เลือกขนาดกระดาษ');?> &mdash;</option>
                <?php
                  $psize =$_SESSION['PAPER_SIZE']?$_SESSION['PAPER_SIZE']:$thisstaff->getDefaultPaperSize();
                  foreach(Export::$paper_sizes as $v) {
                      echo sprintf('<option value="%s" %s>%s</option>',
                                $v,($psize==$v)?'selected="selected"':'', __($v));
                  }
                ?>
            </select>
        </fieldset>
        <hr style="margin-top:3em"/>
        <p class="full-width">
            <span class="buttons pull-left">
                <input type="reset" value="<?php echo __('รีเซ็ต');?>">
                <input type="button" value="<?php echo __('ยกเลิก');?>" class="close">
            </span>
            <span class="buttons pull-right">
                <input type="submit" value="<?php echo __('พิมพ์');?>">
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>
<div style="display:none;" class="dialog" id="confirm-action">
    <h3><?php echo __('โปรดยืนยัน');?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="claim-confirm">
        <?php echo __('คุณแน่ใจหรือว่าต้องการ <b>อ้างสิทธิ์</b> (มอบหมายให้ตัวเอง) คำขอใช้บริการนี้?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="answered-confirm">
        <?php echo __('คุณแน่ใจหรือว่าต้องการตั้งสถานะคำขอใช้บริการเป็น <b>กำลังดำเนินการ</b>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="unanswered-confirm">
        <?php echo __('คุณแน่ใจหรือว่าต้องการตั้งสถานะคำขอใช้บริการเป็น <b>ยังไม่ได้ตอบกลับ</b>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="overdue-confirm">
        <?php echo __('คุณแน่ใจหรือว่าต้องการตั้งสถานะคำขอใช้บริการเป็น <font color="red"><b>เลยกำหนด</b></font>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="banemail-confirm">
        <?php echo sprintf(__('คุณแน่ใจหรือว่าต้องการ <b>แบน</b> %s?'), $ticket->getEmail());?> <br><br>
        <?php echo __('เมื่อมีการสร้างคำขอใช้บริการใหม่จากอีเมลนี้ ระบบจะปฏิเสธโดยอัตโนมัติ');?>
    </p>
    <p class="confirm-action" style="display:none;" id="unbanemail-confirm">
        <?php echo sprintf(__('คุณแน่ใจหรือว่าต้องการ <b>ลบ</b> %s จากรายการที่ถูกแบน?'), $ticket->getEmail()); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="release-confirm">
        <?php echo sprintf(__('คุณแน่ใจหรือว่าต้องการ <b>ปลดเจ้าหน้าที่</b> ออกจากคำขอใช้บริการ <b>%s</b>?'), $ticket->getAssigned()); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="changeuser-confirm">
        <span id="msg_warning" style="display:block;vertical-align:top">
        <?php echo sprintf(Format::htmlchars(__('%s <%s> จะไม่มีสิทธิเข้าถึงคำขอใช้บริการอีกต่อไป')),
            '<b>'.Format::htmlchars($ticket->getName()).'</b>', Format::htmlchars($ticket->getEmail())); ?>
        </span>
        <?php echo sprintf(__('คุณแน่ใจหรือว่าต้องการ <b>เปลี่ยน</b> เจ้าของคำขอใช้บริการเป็น %s?'),
            '<b><span id="newuser">คนนี้</span></b>'); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong><?php echo __('คุณแน่ใจหรือว่าต้องการลบคำขอใช้บริการนี้?');?></strong></font>
        <br><br><?php echo __('ข้อมูลที่ถูกลบจะไม่สามารถกู้คืนได้ รวมถึงไฟล์แนบด้วย');?>
    </p>
    <div><?php echo __('โปรดยืนยันเพื่อดำเนินการต่อ');?></div>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>" method="post" id="confirm-form" name="confirm-form">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="process">
        <input type="hidden" name="do" id="action" value="">
        <hr style="margin-top:1em"/>
        <p class="full-width">
            <span class="buttons pull-left">
                <input type="button" value="<?php echo __('ยกเลิก');?>" class="close">
            </span>
            <span class="buttons pull-right">
                <input type="submit" value="<?php echo __('ใช่');?>">
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>
<script type="text/javascript">
$(function() {
    $(document).on('click', 'a.change-user', function(e) {
        e.preventDefault();
        var tid = <?php echo $ticket->getOwnerId(); ?>;
        var cid = <?php echo $ticket->getOwnerId(); ?>;
        var url = 'ajax.php/'+$(this).attr('href').substr(1);
        $.userLookup(url, function(user) {
            if(cid!=user.id
                    && $('.dialog#confirm-action #changeuser-confirm').length) {
                $('#newuser').html(user.name +' &lt;'+user.email+'&gt;');
                $('.dialog#confirm-action #action').val('changeuser');
                $('#confirm-form').append('<input type=hidden name=user_id value='+user.id+' />');
                $('#overlay').show();
                $('.dialog#confirm-action .confirm-action').hide();
                $('.dialog#confirm-action p#changeuser-confirm')
                .show()
                .parent('div').show().trigger('click');
            }
        });
    });
});
</script>
