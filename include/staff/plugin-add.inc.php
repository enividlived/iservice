
<h2><?php echo __('ติดตั้งปลั๊กอิน'); ?></h2>
<p><?php echo __(
'หากต้องการติดตั้งปลั๊กอิน ให้ดาวโหลดปลั๊กอินที่ต้องการแล้วนำไปวางไว้ในโฟลเดอร์ <code>include/plugins</code> เมื่อปลั๊กอินอยู่ในโฟลเดอร์ <code>plugins/</code> แล้ว ระบบจะแสดงรายชื่อด้านล่าง'
); ?>
</p>

<form method="post" action="?">
    <?php echo csrf_token(); ?>
    <input type="hidden" name="do" value="install"/>
<table class="list" width="100%"><tbody>
<?php

$installed = $ost->plugins->allInstalled();
foreach ($ost->plugins->allInfos() as $info) {
    // Ignore installed plugins
    if (isset($installed[$info['install_path']]))
        continue;
    ?>
        <tr><td><button type="submit" name="install_path"
            value="<?php echo $info['install_path'];
            ?>"><?php echo __('ติดตั้ง'); ?></button></td>
        <td>
    <div><strong><?php echo $info['name']; ?></strong><br/>
        <div><?php echo $info['description']; ?></div>
        <div class="faded"><em><?php echo __('เวอร์ชั่น'); ?>: <?php echo $info['version']; ?></em></div>
        <div class="faded"><em><?php echo __('ผู้สร้าง'); ?>: <?php echo $info['author']; ?></em></div>
    </div>
    </td></tr>
    <?php
}
?>
</tbody></table>
</form>
