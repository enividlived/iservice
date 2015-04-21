<?php
require('admin.inc.php');
require_once(INCLUDE_DIR."/class.plugin.php");

if($_REQUEST['id'] && !($plugin=Plugin::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: เกิดข้อผิดพลาด'),
        __('ปลั๊กอิน'));

if($_POST) {
    switch(strtolower($_POST['do'])) {
    case 'update':
        if ($plugin) {
            $plugin->getConfig()->commit($errors);
        }
        break;
    case 'mass_process':
        if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
            $errors['err'] = sprintf(__('คุณต้องเลือกอย่างน้อย %s'),
                __('หนึ่งปลั๊กอิน'));
        } else {
            $count = count($_POST['ids']);
            switch(strtolower($_POST['a'])) {
            case 'enable':
                foreach ($_POST['ids'] as $id) {
                    if ($p = Plugin::lookup($id)) {
                        $p->enable();
                    }
                }
                break;
            case 'disable':
                foreach ($_POST['ids'] as $id) {
                    if ($p = Plugin::lookup($id)) {
                        $p->disable();
                    }
                }
                break;
            case 'delete':
                foreach ($_POST['ids'] as $id) {
                    if ($p = Plugin::lookup($id)) {
                        $p->uninstall();
                    }
                }
                break;
            }
        }
        break;
    case 'install':
        if ($ost->plugins->install($_POST['install_path']))
            $msg = sprintf(__('ติดตั้ง %s เรียบร้อย'),
                __('ปลั๊กอิน'));
        break;
    }
}

$page = 'plugins.inc.php';
if ($plugin)
    $page = 'plugin.inc.php';
elseif ($_REQUEST['a']=='add')
    $page = 'plugin-add.inc.php';

$nav->setTabActive('manage');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
