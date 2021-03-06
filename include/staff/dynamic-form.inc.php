<?php

$info=array();
if($form && $_REQUEST['a']!='add') {
    $title = __('อัพเดทแบบฟอร์มกำหนดเอง');
    $action = 'update';
    $url = "?id=".urlencode($_REQUEST['id']);
    $submit_text=__('บันทึก');
    $info = $form->ht;
    $newcount=2;
} else {
    $title = __('เพิ่มแบบฟอร์มกำหนดเอง');
    $action = 'add';
    $url = '?a=add';
    $submit_text=__('เพิ่มแบบฟอร์ม');
    $newcount=4;
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);

?>
<form class="manage-form" action="<?php echo $url ?>" method="post" id="save">
    <?php csrf_token(); ?>
    <input type="hidden" name="do" value="<?php echo $action; ?>">
    <input type="hidden" name="a" value="<?php echo $action; ?>">
    <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
    <h2><?php echo __('แบบฟอร์มที่กำหนดเอง'); ?></h2>
    <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><?php echo __(
                'แบบฟอร์มที่กำหนดเองคือการสร้างแบบฟอร์มขึ้นมาแนบไปกับคำขอใช้บริการเพื่อขอข้อมูลเพิ่มเติมจากผู้ใชได้'
                ); ?></em>
            </th>
        </tr>
    </thead>
    <tbody style="vertical-align:top">
        <tr>
            <td width="180" class="required"><?php echo __('หัวข้อ'); ?>:</td>
            <td><input type="text" name="title" size="40" value="<?php
                echo $info['title']; ?>"/>
                <i class="help-tip icon-question-sign" href="#form_title"></i>
                <font class="error"><?php
                    if ($errors['title']) echo '<br/>'; echo $errors['title']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180"><?php echo __('วิธีใช้'); ?>:</td>
            <td><textarea name="instructions" rows="3" cols="40"><?php
                echo $info['instructions']; ?></textarea>
                <i class="help-tip icon-question-sign" href="#form_instructions"></i>
            </td>
        </tr>
    </tbody>
    </table>
    <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <?php if ($form && $form->get('type') == 'T') { ?>
    <thead>
        <tr>
            <th colspan="7">
                <em><strong><?php echo __('ฟิลด์ข้อมูลผู้ใช้'); ?></strong>
                <?php echo sprintf(__('(ฟิลด์นี้ใช้สำหรับรับข้อมูลผู้ใช้จาก
                แบบฟอร์ม %s)'),
                UserForm::objects()->one()->get('title')); ?></em>
            </th>
        </tr>
        <tr>
            <th></th>
            <th><?php echo __('ชื่อ'); ?></th>
            <th><?php echo __('ประเภท'); ?></th>
            <th><?php echo __('การมองเห็น'); ?></th>
            <th><?php echo __('ตัวแปร'); ?></th>
            <th><?php echo __('ลบทิ้ง'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php
        $uform = UserForm::objects()->all();
        $ftypes = FormField::allTypes();
        foreach ($uform[0]->getFields() as $f) {
            if ($f->get('private')) continue;
        ?>
        <tr>
            <td></td>
            <td><?php echo $f->get('label'); ?></td>
            <td><?php $t=FormField::getFieldType($f->get('type')); echo __($t[0]); ?></td>
            <td><?php
                $rmode = $f->getRequirementMode();
                $modes = $f->getAllRequirementModes();
                echo $modes[$rmode]['desc'];
            ?></td>
            <td><?php echo $f->get('name'); ?></td>
            <td><input type="checkbox" disabled="disabled"/></td></tr>

        <?php } ?>
    </tbody>
    <?php } # form->type == 'T' ?>
    <thead>
        <tr>
            <th colspan="7">
                <em><strong><?php echo __('ฟิลด์แบบฟอร์ม'); ?></strong>
                <?php echo __('ฟิลด์ที่เปิดใช้เมื่อฟอร์มนี้มีการใช้งาน'); ?></em>
            </th>
        </tr>
        <tr>
            <th nowrap
                ><i class="help-tip icon-question-sign" href="#field_sort"></i></th>
            <th nowrap><?php echo __('ชื่อ'); ?>
                <i class="help-tip icon-question-sign" href="#field_label"></i></th>
            <th nowrap><?php echo __('ประเภท'); ?>
                <i class="help-tip icon-question-sign" href="#field_type"></i></th>
            <th nowrap><?php echo __('การมองเห็น'); ?>
                <i class="help-tip icon-question-sign" href="#field_visibility"></i></th>
            <th nowrap><?php echo __('ตัวแปร'); ?>
                <i class="help-tip icon-question-sign" href="#field_variable"></i></th>
            <th nowrap><?php echo __('ลบทิ้ง'); ?>
                <i class="help-tip icon-question-sign" href="#field_delete"></i></th>
        </tr>
    </thead>
    <tbody class="sortable-rows" data-sort="sort-">
    <?php if ($form) foreach ($form->getDynamicFields() as $f) {
        $id = $f->get('id');
        $deletable = !$f->isDeletable() ? 'disabled="disabled"' : '';
        $force_name = $f->isNameForced() ? 'disabled="disabled"' : '';
        $rmode = $f->getRequirementMode();
        $fi = $f->getImpl();
        $ferrors = $f->errors(); ?>
        <tr>
            <td><i class="icon-sort"></i></td>
            <td><input type="text" size="32" name="label-<?php echo $id; ?>"
                value="<?php echo Format::htmlchars($f->get('label')); ?>"/>
                <font class="error"><?php
                    if ($ferrors['label']) echo '<br/>'; echo $ferrors['label']; ?>
            </td>
            <td nowrap><select style="max-width:150px" name="type-<?php echo $id; ?>" <?php
                if (!$fi->isChangeable()) echo 'disabled="disabled"'; ?>>
                <?php foreach (FormField::allTypes() as $group=>$types) {
                        ?><optgroup label="<?php echo Format::htmlchars(__($group)); ?>"><?php
                        foreach ($types as $type=>$nfo) {
                            if ($f->get('type') != $type
                                    && isset($nfo[2]) && !$nfo[2]) continue; ?>
                <option value="<?php echo $type; ?>" <?php
                    if ($f->get('type') == $type) echo 'selected="selected"'; ?>>
                    <?php echo __($nfo[0]); ?></option>
                    <?php } ?>
                </optgroup>
                <?php } ?>
            </select>
            <?php if ($f->isConfigurable()) { ?>
                <a class="action-button field-config" style="overflow:inherit"
                    href="#ajax.php/form/field-config/<?php
                        echo $f->get('id'); ?>"
                    onclick="javascript:
                        $.dialog($(this).attr('href').substr(1), [201]);
                        return false;
                    "><i class="icon-edit"></i> <?php echo __('Config'); ?></a>
            <?php } ?></td>
            <td>
                <select name="visibility-<?php echo $id; ?>">
<?php foreach ($f->getAllRequirementModes() as $m=>$I) { ?>
    <option value="<?php echo $m; ?>" <?php if ($rmode == $m)
         echo 'selected="selected"'; ?>><?php echo $I['desc']; ?></option>
<?php } ?>
                <select>
            </td>
            <td>
                <input type="text" size="20" name="name-<?php echo $id; ?>"
                    value="<?php echo Format::htmlchars($f->get('name'));
                    ?>" <?php echo $force_name ?>/>
                <font class="error"><?php
                    if ($ferrors['name']) echo '<br/>'; echo $ferrors['name'];
                ?></font>
                </td>
            <td><input class="delete-box" type="checkbox" name="delete-<?php echo $id; ?>"
                    data-field-label="<?php echo $f->get('label'); ?>"
                    data-field-id="<?php echo $id; ?>"
                    <?php echo $deletable; ?>/>
                <input type="hidden" name="sort-<?php echo $id; ?>"
                    value="<?php echo $f->get('sort'); ?>"/>
                </td>
        </tr>
    <?php
    }
    for ($i=0; $i<$newcount; $i++) { ?>
            <td><em>+</em>
                <input type="hidden" name="sort-new-<?php echo $i; ?>"
                    value="<?php echo $info["sort-new-$i"]; ?>"/></td>
            <td><input type="text" size="32" name="label-new-<?php echo $i; ?>"
                value="<?php echo $info["label-new-$i"]; ?>"/></td>
            <td><select style="max-width:150px" name="type-new-<?php echo $i; ?>">
                <?php foreach (FormField::allTypes() as $group=>$types) {
                    ?><optgroup label="<?php echo Format::htmlchars(__($group)); ?>"><?php
                    foreach ($types as $type=>$nfo) {
                        if (isset($nfo[2]) && !$nfo[2]) continue; ?>
                <option value="<?php echo $type; ?>"
                    <?php if ($info["type-new-$i"] == $type) echo 'selected="selected"'; ?>>
                    <?php echo __($nfo[0]); ?>
                </option>
                    <?php } ?>
                </optgroup>
                <?php } ?>
            </select></td>
            <td>
                <select name="visibility-new-<?php echo $i; ?>">
<?php
    $rmode = $info['visibility-new-'.$i];
    foreach (DynamicFormField::allRequirementModes() as $m=>$I) { ?>
    <option value="<?php echo $m; ?>" <?php if ($rmode == $m)
         echo 'selected="selected"'; ?>><?php echo $I['desc']; ?></option>
<?php } ?>
                <select>
            <td><input type="text" size="20" name="name-new-<?php echo $i; ?>"
                value="<?php echo $info["name-new-$i"]; ?>"/>
                <font class="error"><?php
                    if ($errors["new-$i"]['name']) echo '<br/>'; echo $errors["new-$i"]['name'];
                ?></font>
            <td></td>
        </tr>
    <?php } ?>
    </tbody>
    <tbody>
        <tr>
            <th colspan="7">
                <em><strong><?php echo __('บันทึกภายใน'); ?>:</strong>
                <?php echo __("บันทึกภายในสำหรับเจ้าหน้าที่"); ?></em>
            </th>
        </tr>
        <tr>
            <td colspan="7"><textarea class="richtext no-bar" name="notes"
                rows="6" cols="80"><?php
                echo $info['notes']; ?></textarea>
            </td>
        </tr>
    </tbody>
    </table>
<p class="centered">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="<?php echo __('รีเซ็ต'); ?>">
    <input type="button" name="cancel" value="<?php echo __('ยกเลิก'); ?>" onclick='window.location.href="?"'>
</p>

<div style="display:none;" class="draggable dialog" id="delete-confirm">
    <h3><i class="icon-trash"></i> <?php echo __('ลบข้อมูลที่มีอยู่แล้ว?'); ?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p>
    <strong><?php echo sprintf(__('คุณกำลังจะลบฟิลด์ %s'),
        '<span id="deleted-count"></span>'); ?></strong>
        <?php echo __('คุณต้องการจะลบข้อมูลที่มีอยู่ในฟิลด์นี้เลยหรือไม่? <em>ถ้าคุณตัดสินใจยังไม่ลบข้อมูลตอนนี้ คุณมีสิทธิลบข้อมูลภายหลังได้ในขั้นตอนการแก้ไขฟิลด์</em>'); ?>
    </p><p style="color:red">
        <?php echo __('ข้อมูลที่ถูกลบจะไม่สามารถกู้คืนได้!'); ?>
    </p>
    <hr>
    <div id="deleted-fields"></div>
    <hr style="margin-top:1em"/>
    <p class="full-width">
        <span class="buttons pull-left">
            <input type="button" value="<?php echo __('ยกเลิก'); ?>" class="close">
        </span>
        <span class="buttons pull-right">
            <input type="submit" value="<?php echo __('ดำเนินการ'); ?>" class="confirm">
        </span>
     </p>
    <div class="clear"></div>
</div>
</form>

<div style="display:none;" class="dialog draggable" id="field-config">
    <div id="popup-loading">
        <h1><i class="icon-spinner icon-spin icon-large"></i>
        <?php echo __('กำลังดำเนินการ ...');?></h1>
    </div>
    <div class="body"></div>
</div>

<script type="text/javascript">
$('form.manage-form').on('submit.inline', function(e) {
    var formObj = this, deleted = $('input.delete-box:checked', this);
    if (deleted.length) {
        e.stopImmediatePropagation();
        $('#overlay').show();
        $('#deleted-fields').empty();
        deleted.each(function(i, e) {
            $('#deleted-fields').append($('<p></p>')
                .append($('<input/>').attr({type:'checkbox',name:'delete-data-'
                    + $(e).data('fieldId')})
                ).append($('<strong>').html(
                    ' <?php echo __('ลบข้อมูลทั้งหมดที่กรอกไว้ใน <u> %s </u>?');
                        ?>'.replace('%s', $(e).data('fieldLabel'))
                ))
            );
        });
        $('#delete-confirm').show().delegate('input.confirm', 'click.confirm', function() {
            $('.dialog#delete-confirm').hide();
            $(formObj).unbind('submit.inline');
            $(window).unbind('beforeunload');
            $('#loading').show();
        })
        return false;
    }
    // TODO: Popup the 'please wait' dialog
    $(window).unbind('beforeunload');
    $('#loading').show();
});
</script>
