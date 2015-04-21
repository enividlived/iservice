<?php
require('admin.inc.php');
require_once(INCLUDE_DIR.'class.list.php');


$list=null;
$criteria=array();
if ($_REQUEST['id'])
    $criteria['id'] = $_REQUEST['id'];
elseif ($_REQUEST['type'])
    $criteria['type'] = $_REQUEST['type'];

if ($criteria) {
    $list = DynamicList::lookup($criteria);

    if ($list)
         $form = $list->getForm();
    else
        $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'),
            __('รายการที่กำหนดเอง'));
}

$errors = array();
$max_isort = 0;

if($_POST) {
    switch(strtolower($_POST['do'])) {
        case 'update':
            if (!$list)
                $errors['err']=sprintf(__('%s: ผิดพลาดหรือไม่ถูกต้อง'),
                    __('รายการที่กำหนดเอง'));
            elseif ($list->update($_POST, $errors)) {
                // Update items
                $items = array();
                foreach ($list->getAllItems() as $item) {
                    $id = $item->getId();
                    if ($_POST["delete-item-$id"] == 'on' && $item->isDeletable()) {
                        $item->delete();
                        continue;
                    }

                    $ht = array(
                            'value' => $_POST["value-$id"],
                            'abbrev' => $_POST["abbrev-$id"],
                            'sort' => $_POST["sort-$id"],
                            );
                    $value = mb_strtolower($ht['value']);
                    if (!$value)
                        $errors["value-$id"] = __('กรุณากรอกค่า');
                    elseif (in_array($value, $items))
                        $errors["value-$id"] = __('ค่านี้มีการใช้งานแล้ว');
                    elseif ($item->update($ht, $errors)) {
                        if ($_POST["disable-$id"] == 'on')
                            $item->disable();
                        elseif(!$item->isEnabled() && $item->isEnableable())
                            $item->enable();

                        $item->save();
                        $items[] = $value;
                    }

                    $max_isort = max($max_isort, $_POST["sort-$id"]);
                }

                // Update properties
                if (!$errors && ($form = $list->getForm())) {
                    $names = array();
                    foreach ($form->getDynamicFields() as $field) {
                        $id = $field->get('id');
                        if ($_POST["delete-prop-$id"] == 'on' && $field->isDeletable()) {
                            $field->delete();
                            // Don't bother updating the field
                            continue;
                        }
                        if (isset($_POST["type-$id"]) && $field->isChangeable())
                            $field->set('type', $_POST["type-$id"]);
                        if (isset($_POST["name-$id"]) && !$field->isNameForced())
                            $field->set('name', $_POST["name-$id"]);

                        foreach (array('sort','label') as $f) {
                            if (isset($_POST["prop-$f-$id"])) {
                                $field->set($f, $_POST["prop-$f-$id"]);
                            }
                        }
                        if (in_array($field->get('name'), $names))
                            $field->addError(__('ตัวแปรนี้มีในระบบแล้ว'), 'name');
                        if (preg_match('/[.{}\'"`; ]/u', $field->get('name')))
                            $field->addError(__('ตัวแปรไม่ถูกต้อง กรุณาใช้ตัวอักษรหรือตัวเลขเท่านั้น'), 'name');
                        if ($field->get('name'))
                            $names[] = $field->get('name');
                        if ($field->isValid())
                            $field->save();
                        else
                            # notrans (not shown)
                            $errors["field-$id"] = 'ฟิลด์มีค่าที่ไม่ถูกต้อง';
                        // Keep track of the last sort number
                        $max_sort = max($max_sort, $field->get('sort'));
                    }
                }

                if ($errors)
                     $errors['err'] = $errors['err'] ?: sprintf(__('ไม่สามารถปรับปรุง %s ได้ กรุณาแก้ไขและลองใหม่อีกครั้ง'),
                        __('รายการที่กำหนดเองนี้'));
                else {
                    $list->_items = null;
                    $msg = sprintf(__('ไม่สามารถปรับปรุง %s ได้'),
                        __('รายการที่กำหนดเองนี้'));
                }

            } elseif ($errors)
                $errors['err'] = $errors['err'] ?: sprintf(__('ไม่สามารถปรับปรุง %s ได้ กรุณาแก้ไขและลองใหม่อีกครั้ง!'),
                    __('รายการที่กำหนดเองนี้'));
            else
                $errors['err']=sprintf(__('ไม่สามารถปรับปรุง %s ได้'), __('รายการที่กำหนดเองนี้'))
                    .' '.__('เกิดข้อผิดพลาดภายใน');

            break;
        case 'add':
            if ($list=DynamicList::add($_POST, $errors)) {
                 $msg = sprintf(__('เพิ่ม %s เรียบร้อย'),
                    __('รายการที่กำหนดเองนี้'));
            } elseif ($errors) {
                $errors['err']=sprintf(__('ไม่สามารถเพิ่ม %s ได้ กรุณาแก้ไขและลองใหม่อีกครั้ง'),
                    __('รายการที่กำหนดเองนี้'));
            } else {
                $errors['err']=sprintf(__('ไม่สามารถเพิ่ม %s ได้'), __('รายการที่กำหนดเองนี้'))
                    .' '.__('เกิดข้อผิดพลาดภายใน');
            }
            break;

        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = sprintf(__('คุณต้องเลือกอย่างน้อย %s'),
                    __('หนึ่งรายการที่กำหนดเอง'));
            } else {
                $count = count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($t=DynamicList::lookup($v)) && $t->delete())
                                $i++;
                        }
                        if ($i && $i==$count)
                            $msg = sprintf(__('ลบ %s เรียบร้อย'),
                                _N('รายการที่กำหนดเอง', 'รายการที่กำหนดเอง', $count));
                        elseif ($i > 0)
                            $warn = sprintf(__('%1$d จาก %2$d %3$s ถูกลบ'), $i, $count,
                                _N('รายการที่กำหนดเอง', 'รายการที่กำหนดเอง', $count));
                        elseif (!$errors['err'])
                            $errors['err'] = sprintf(__('ไม่สามารถลบ %s ได้ อาจมีการใช้งานอยู่ในฟอร์มอื่น'),
                                _N('รายการที่กำหนดเอง', 'รายการที่กำหนดเอง', $count));
                        break;
                }
            }
            break;
    }

    if ($list && $list->allowAdd()) {
        for ($i=0; isset($_POST["sort-new-$i"]); $i++) {
            if (!$_POST["value-new-$i"])
                continue;

            $list->addItem(array(
                        'value' => $_POST["value-new-$i"],
                        'abbrev' =>$_POST["abbrev-new-$i"],
                        'sort' => $_POST["sort-new-$i"] ?: ++$max_isort,
                        ), $errors);
        }
    }

    if ($form) {
        for ($i=0; isset($_POST["prop-sort-new-$i"]); $i++) {
            if (!$_POST["prop-label-new-$i"])
                continue;
            $field = DynamicFormField::create(array(
                'form_id' => $form->get('id'),
                'sort' => $_POST["prop-sort-new-$i"] ?: ++$max_sort,
                'label' => $_POST["prop-label-new-$i"],
                'type' => $_POST["type-new-$i"],
                'name' => $_POST["name-new-$i"],
            ));
            $field->setForm($form);
            if ($field->isValid())
                $field->save();
            else
                $errors["new-$i"] = $field->errors();
        }
        // XXX: Move to an instrumented list that can handle this better
        if (!$errors)
            $form->_dfields = $form->_fields = null;
    }
}

$page='dynamic-lists.inc.php';
if($list || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='dynamic-list.inc.php';
    $ost->addExtraHeader('<meta name="tip-namespace" content="manage.custom_list" />',
        "$('#content').data('tipNamespace', 'manage.custom_list');");
}

$nav->setTabActive('manage');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
