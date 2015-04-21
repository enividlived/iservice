<?php
if(!defined('OSTSCPINC') || !$thisstaff || !$thisstaff->canCreateTickets()) die('ปฏิเสธการเข้าถึง');
$info=array();
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);

if (!$info['topicId'])
    $info['topicId'] = $cfg->getDefaultTopicId();

$form = null;
if ($info['topicId'] && ($topic=Topic::lookup($info['topicId']))) {
    $form = $topic->getForm();
    if ($_POST && $form) {
        $form = $form->instanciate();
        $form->isValid();
    }
}

if ($_POST)
    $info['duedate'] = Format::date($cfg->getDateFormat(),
       strtotime($info['duedate']));
?>
<form action="tickets.php?a=open" method="post" id="save"  enctype="multipart/form-data">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="create">
 <input type="hidden" name="a" value="open">
 <h2><?php echo __('สร้างคำขอใช้บริการใหม่');?></h2>
 <table class="form_table fixed" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
    <!-- This looks empty - but beware, with fixed table layout, the user
         agent will usually only consult the cells in the first row to
         construct the column widths of the entire toable. Therefore, the
         first row needs to have two cells -->
        <tr><td></td><td></td></tr>
        <tr>
            <th colspan="2">
                <h4><?php echo __('สร้างคำขอใช้บริการ');?></h4>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('ข้อมูลผู้ใช้'); ?></strong>: </em>
            </th>
        </tr>
        <?php
        if ($user) { ?>
        <tr><td><?php echo __('ผู้ใช้'); ?>:</td><td>
            <div id="user-info">
                <input type="hidden" name="uid" id="uid" value="<?php echo $user->getId(); ?>" />
            <a href="#" onclick="javascript:
                $.userLookup('ajax.php/users/<?php echo $user->getId(); ?>/edit',
                        function (user) {
                            $('#user-name').text(user.name);
                            $('#user-email').text(user.email);
                        });
                return false;
                "><i class="icon-user"></i>
                <span id="user-name"><?php echo Format::htmlchars($user->getName()); ?></span>
                &lt;<span id="user-email"><?php echo $user->getEmail(); ?></span>&gt;
                </a>
                <a class="action-button" style="overflow:inherit" href="#"
                    onclick="javascript:
                        $.userLookup('ajax.php/users/select/'+$('input#uid').val(),
                            function(user) {
                                $('input#uid').val(user.id);
                                $('#user-name').text(user.name);
                                $('#user-email').text('<'+user.email+'>');
                        });
                        return false;
                    "><i class="icon-edit"></i> <?php echo __('แก้ไข'); ?></a>
            </div>
        </td></tr>
        <?php
        } else { //Fallback: Just ask for email and name
            ?>
        <tr>
            <td width="160" class="required"> <?php echo __('อีเมลแอดเดรส'); ?>: </td>
            <td>
                <span style="display:inline-block;">
                    <input type="text" size=45 name="email" id="user-email"
                        autocomplete="off" autocorrect="off" value="<?php echo $info['email']; ?>" /> </span>
                <font class="error">* <?php echo $errors['email']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="160" class="required"> <?php echo __('ชื่อเต็ม'); ?>: </td>
            <td>
                <span style="display:inline-block;">
                    <input type="text" size=45 name="name" id="user-name" value="<?php echo $info['name']; ?>" /> </span>
                <font class="error">* <?php echo $errors['name']; ?></font>
            </td>
        </tr>
        <?php
        } ?>

        <?php
        if($cfg->notifyONNewStaffTicket()) {  ?>
        <tr>
            <td width="160"><?php echo __('แจ้งเตือนคำขอใช้บริการ'); ?>:</td>
            <td>
            <input type="checkbox" name="alertuser" <?php echo (!$errors || $info['alertuser'])? 'checked="checked"': ''; ?>><?php
                echo __('ส่งการแจ้งเตือนไปหาผู้ใช้'); ?>
            </td>
        </tr>
        <?php
        } ?>
    </tbody>
    <tbody>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('การตั้งค่าและข้อมูลคำขอใช้บริการ');?></strong>:</em>
            </th>
        </tr>
        <tr>
            <td width="160" class="required">
                <?php echo __('แหล่งที่มา');?>:
            </td>
            <td>
                <select name="source">
                    <option value="Phone" selected="selected"><?php echo __('โทรศัพท์'); ?></option>
                    <option value="Email" <?php echo ($info['source']=='Email')?'selected="selected"':''; ?>><?php echo __('อีเมล'); ?></option>
                    <option value="Other" <?php echo ($info['source']=='Other')?'selected="selected"':''; ?>><?php echo __('อื่นๆ'); ?></option>
                </select>
                &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['source']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="160" class="required">
                <?php echo __('บริการ'); ?>:
            </td>
            <td>
                <select name="topicId" onchange="javascript:
                        var data = $(':input[name]', '#dynamic-form').serialize();
                        $.ajax(
                          'ajax.php/form/help-topic/' + this.value,
                          {
                            data: data,
                            dataType: 'json',
                            success: function(json) {
                              $('#dynamic-form').empty().append(json.html);
                              $(document.head).append(json.media);
                            }
                          });">
                    <?php
                    if ($topics=Topic::getHelpTopics()) {
                        if (count($topics) == 1)
                            $selected = 'selected="selected"';
                        else { ?>
                        <option value="" selected >&mdash; <?php echo __('เลือกบริการ'); ?> &mdash;</option>
<?php                   }
                        foreach($topics as $id =>$name) {
                            echo sprintf('<option value="%d" %s %s>%s</option>',
                                $id, ($info['topicId']==$id)?'selected="selected"':'',
                                $selected, $name);
                        }
                        if (count($topics) == 1 && !$form) {
                            if (($T = Topic::lookup($id)))
                                $form =  $T->getForm();
                        }
                    }
                    ?>
                </select>
                &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['topicId']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="160">
                <?php echo __('แผนก'); ?>:
            </td>
            <td>
                <select name="deptId">
                    <option value="" selected >&mdash; <?php echo __('เลือกแผนก'); ?>&mdash;</option>
                    <?php
                    if($depts=Dept::getDepartments()) {
                        foreach($depts as $id =>$name) {
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id, ($info['deptId']==$id)?'selected="selected"':'',$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<font class="error"><?php echo $errors['deptId']; ?></font>
            </td>
        </tr>

         <tr>
            <td width="160">
                <?php echo __('แผน SLA');?>:
            </td>
            <td>
                <select name="slaId">
                    <option value="0" selected="selected" >&mdash; <?php echo __('ค่าเริ่มต้นระบบ');?> &mdash;</option>
                    <?php
                    if($slas=SLA::getSLAs()) {
                        foreach($slas as $id =>$name) {
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id, ($info['slaId']==$id)?'selected="selected"':'',$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['slaId']; ?></font>
            </td>
         </tr>

         <tr>
            <td width="160">
                <?php echo __('วันครบกำหนด');?>:
            </td>
            <td>
                <input class="dp" id="duedate" name="duedate" value="<?php echo Format::htmlchars($info['duedate']); ?>" size="12" autocomplete=OFF>
                &nbsp;&nbsp;
                <?php
                $min=$hr=null;
                if($info['time'])
                    list($hr, $min)=explode(':', $info['time']);

                echo Misc::timeDropdown($hr, $min, 'time');
                ?>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['duedate']; ?> &nbsp; <?php echo $errors['time']; ?></font>
                <em><?php echo __('เวลาจะตั้งค่าตามเขตเวลาของคุณ');?> (GMT <?php echo $thisstaff->getTZoffset(); ?>)</em>
            </td>
        </tr>

        <?php
        if($thisstaff->canAssignTickets()) { ?>
        <tr>
            <td width="160"><?php echo __('มอบหมายให้');?>:</td>
            <td>
                <select id="assignId" name="assignId">
                    <option value="0" selected="selected">&mdash; <?php echo __('เลือกเจ้าหน้าที่หรือทีม');?> &mdash;</option>
                    <?php
                    if(($users=Staff::getAvailableStaffMembers())) {
                        echo '<OPTGROUP label="'.sprintf(__('เจ้าหน้าที่ (%d)'), count($users)).'">';
                        foreach($users as $id => $name) {
                            $k="s$id";
                            echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['assignId']==$k)?'selected="selected"':''),$name);
                        }
                        echo '</OPTGROUP>';
                    }

                    if(($teams=Team::getActiveTeams())) {
                        echo '<OPTGROUP label="'.sprintf(__('ทีม (%d)'), count($teams)).'">';
                        foreach($teams as $id => $name) {
                            $k="t$id";
                            echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['assignId']==$k)?'selected="selected"':''),$name);
                        }
                        echo '</OPTGROUP>';
                    }
                    ?>
                </select>&nbsp;<span class='error'>&nbsp;<?php echo $errors['assignId']; ?></span>
            </td>
        </tr>
        <?php } ?>
        </tbody>
        <tbody id="dynamic-form">
        <?php
            if ($form) {
                print $form->getForm()->getMedia();
                include(STAFFINC_DIR .  'templates/dynamic-form.tmpl.php');
            }
        ?>
        </tbody>
        <tbody> <?php
        $tform = TicketForm::getInstance()->getForm($_POST);
        if ($_POST) $tform->isValid();
        $tform->render(true);
        ?>
        </tbody>
        <tbody>
        <?php
        //is the user allowed to post replies??
        if($thisstaff->canPostReply()) { ?>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('คำตอบ');?></strong>: <?php echo __('กำหนดคำตอบล่วงหน้าสำหรับคำขอใช้บริการที่จะสร้าง (ถ้ามี)');?></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
            <?php
            if(($cannedResponses=Canned::getCannedResponses())) {
                ?>
                <div style="margin-top:0.3em;margin-bottom:0.5em">
                    <?php echo __('คำตอบที่กำหนดไว้');?>:&nbsp;
                    <select id="cannedResp" name="cannedResp">
                        <option value="0" selected="selected">&mdash; <?php echo __('เลือกคำตอบที่กำหนดไว้');?> &mdash;</option>
                        <?php
                        foreach($cannedResponses as $id =>$title) {
                            echo sprintf('<option value="%d">%s</option>',$id,$title);
                        }
                        ?>
                    </select>
                    &nbsp;&nbsp;&nbsp;
                    <label><input type='checkbox' value='1' name="append" id="append" checked="checked"><?php echo __('เพิ่มเติม');?></label>
                </div>
            <?php
            }
                $signature = '';
                if ($thisstaff->getDefaultSignatureType() == 'mine')
                    $signature = $thisstaff->getSignature(); ?>
                <textarea class="richtext ifhtml draft draft-delete"
                    data-draft-namespace="ticket.staff.response"
                    data-signature="<?php
                        echo Format::htmlchars(Format::viewableImages($signature)); ?>"
                    data-signature-field="signature" data-dept-field="deptId"
                    placeholder="<?php echo __('คำตอบสำหรับคำขอใช้บริการ'); ?>"
                    name="response" id="response" cols="21" rows="8"
                    style="width:80%;"><?php echo $info['response']; ?></textarea>
                    <div class="attachments">
<?php
print $response_form->getField('attachments')->render();
?>
                    </div>

                <table border="0" cellspacing="0" cellpadding="2" width="100%">
            <tr>
                <td width="100"><?php echo __('สถานะคำขอใช้บริการ');?>:</td>
                <td>
                    <select name="statusId">
                    <?php
                    $statusId = $info['statusId'] ?: $cfg->getDefaultTicketStatusId();
                    $states = array('open');
                    if ($thisstaff->canCloseTickets())
                        $states = array_merge($states, array('closed'));
                    foreach (TicketStatusList::getStatuses(
                                array('states' => $states)) as $s) {
                        if (!$s->isEnabled()) continue;
                        $selected = ($statusId == $s->getId());
                        echo sprintf('<option value="%d" %s>%s</option>',
                                $s->getId(),
                                $selected
                                 ? 'selected="selected"' : '',
                                __($s->getName()));
                    }
                    ?>
                    </select>
                </td>
            </tr>
             <tr>
                <td width="100"><?php echo __('ลายเซ็นต์');?>:</td>
                <td>
                    <?php
                    $info['signature']=$info['signature']?$info['signature']:$thisstaff->getDefaultSignatureType();
                    ?>
                    <label><input type="radio" name="signature" value="none" checked="checked"> <?php echo __('ไม่มี');?></label>
                    <?php
                    if($thisstaff->getSignature()) { ?>
                        <label><input type="radio" name="signature" value="mine"
                            <?php echo ($info['signature']=='mine')?'checked="checked"':''; ?>> <?php echo __('ลายเซ็นต์ของฉัน');?></label>
                    <?php
                    } ?>
                    <label><input type="radio" name="signature" value="dept"
                        <?php echo ($info['signature']=='dept')?'checked="checked"':''; ?>> <?php echo sprintf(__('ลายเซ็นต์แผนก (%s)'), __('ถ้าสร้างไว้')); ?></label>
                </td>
             </tr>
            </table>
            </td>
        </tr>
        <?php
        } //end canPostReply
        ?>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('บันทึกภายใน');?></strong>
                <font class="error">&nbsp;<?php echo $errors['note']; ?></font></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea class="richtext ifhtml draft draft-delete"
                    placeholder="<?php echo __('บันทึกภายในสำหรับเจ้าหน้าที่ (แนะนำให้ใส่ถ้าเป็นการมอบหมายงาน)'); ?>"
                    data-draft-namespace="ticket.staff.note" name="note"
                    cols="21" rows="6" style="width:80%;"
                    ><?php echo $info['note']; ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="text-align:center;">
    <input type="submit" name="submit" value="<?php echo _P('action-button', 'สร้าง');?>">
    <input type="reset"  name="reset"  value="<?php echo __('รีเซ็ต');?>">
    <input type="button" name="cancel" value="<?php echo __('ยกเลิก');?>" onclick="javascript:
        $('.richtext').each(function() {
            var redactor = $(this).data('redactor');
            if (redactor && redactor.opts.draftDelete)
                redactor.deleteDraft();
        });
        window.location.href='tickets.php';
    ">
</p>
</form>
<script type="text/javascript">
$(function() {
    $('input#user-email').typeahead({
        source: function (typeahead, query) {
            $.ajax({
                url: "ajax.php/users?q="+query,
                dataType: 'json',
                success: function (data) {
                    typeahead.process(data);
                }
            });
        },
        onselect: function (obj) {
            $('#uid').val(obj.id);
            $('#user-name').val(obj.name);
            $('#user-email').val(obj.email);
        },
        property: "/bin/true"
    });

   <?php
    // Popup user lookup on the initial page load (not post) if we don't have a
    // user selected
    if (!$_POST && !$user) {?>
    setTimeout(function() {
      $.userLookup('ajax.php/users/lookup/form', function (user) {
        window.location.href = window.location.href+'&uid='+user.id;
      });
    }, 100);
    <?php
    } ?>
});
</script>
