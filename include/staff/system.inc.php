<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('ปฏิเสธการเข้าถึง');

$commit = GIT_VERSION != '$git' ? GIT_VERSION : (
    @shell_exec('git rev-parse HEAD | cut -b 1-8') ?: '?');

$extensions = array(
        'gd' => array(
            'name' => 'gdlib',
            'desc' => __('ใช้ในการเกี่ยวกับรูปภาพและนำข้อมูลออกเป็น PDF')
            ),
        'imap' => array(
            'name' => 'imap',
            'desc' => __('ใช้ในการดึงอีเมล')
            ),
        'xml' => array(
            'name' => 'xml',
            'desc' => __('API ของ XML')
            ),
        'dom' => array(
            'name' => 'xml-dom',
            'desc' => __('ใช้ในการดำเนินการเกี่ยวกับอีเมล HTML')
            ),
        'json' => array(
            'name' => 'json',
            'desc' => __('เพิ่มประสิทธิภาพในการสร้างและจัดการ JSON')
            ),
        'mbstring' => array(
            'name' => 'mbstring',
            'desc' => __('แนะนำสำหรับคอนเท็นท์ที่ไม่ใช่ภาษาในแถบยุโรป')
            ),
        'phar' => array(
            'name' => 'phar',
            'desc' => __('แนะนำสำหรับปลั๊กอินและแพ็คภาษา')
            ),
        'fileinfo' => array(
            'name' => 'fileinfo',
            'desc' => __('Used to detect file types for uploads')
            ),
        );

?>
<h2><?php echo __('เกี่ยวกับการติดตั้ง osTicket'); ?></h2>
<br/>
<table class="list" width="100%";>
<thead>
    <tr><th colspan="2"><?php echo __('ข้อมูลเซิร์ฟเวอร์'); ?></th></tr>
</thead>
<tbody>
    <tr><td><?php echo __('เวอร์ชั่น osTicket'); ?></td>
        <td><span class="ltr"><?php
        echo sprintf("%s (%s)", THIS_VERSION, trim($commit)); ?></span></td></tr>
    <tr><td><?php echo __('ซอฟต์แวร์ของเว็บเซิร์ฟเวอร์'); ?></td>
        <td><span class="ltr"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span></td></tr>
    <tr><td><?php echo __('เวอร์ชั่น MySQL'); ?></td>
        <td><span class="ltr"><?php echo db_version(); ?></span></td></tr>
    <tr><td><?php echo __('เวอร์ชั่น PHP'); ?></td>
        <td><span class="ltr"><?php echo phpversion(); ?></span></td></tr>
</tbody>
<thead>
    <tr><th colspan="2"><?php echo __('ส่วนเสริม PHP'); ?></th></tr>
</thead>
<tbody>
    <?php
    foreach($extensions as $ext => $info) { ?>
    <tr><td><?php echo $info['name']; ?></td>
        <td><?php
            echo sprintf('<i class="icon icon-%s"></i> %s',
                    extension_loaded($ext) ? 'check' : 'warning-sign',
                    $info['desc']);
            ?>
        </td>
    </tr>
    <?php
    } ?>
</tbody>
<thead>
    <tr><th colspan="2"><?php echo __('การตั้งค่า PHP'); ?></th></tr>
</thead>
<tbody>
    <tr>
        <td><span class="ltr"><code>cgi.fix_pathinfo</code></span></td>
        <td><i class="icon icon-<?php
                echo ini_get('cgi.fix_pathinfo') == 1 ? 'check' : 'warning-sign'; ?>"></i>
                <span class="faded"><?php echo __('"1" คือค่าแนะนำ ถ้า AJAX ไม่สามารถใช้งานได้'); ?></span>
        </td>
    </tr>
    <tr>
        <td><span class="ltr"><code>date.timezone</code></span></td>
        <td><i class="icon icon-<?php
                echo ini_get('date.timezone') ? 'check' : 'warning-sign'; ?>"></i>
                <span class="faded"><?php
                    echo ini_get('date.timezone')
                    ?: __('แนะนำให้ตั้งค่าเขตเวลาเริ่มต้น');
                    ?></span>
        </td>
    </tr>
</tbody>
<thead>
    <tr><th colspan="2"><?php echo __('รายละเอียดการใช้งานฐานข้อมูล'); ?></th></tr>
</thead>
<tbody>
    <tr><td><?php echo __('ชื่อฐานข้อมูล'); ?></td>
        <td><?php echo sprintf('<span class="ltr">%s (%s)</span>', DBNAME, DBHOST); ?> </td>
    </tr>
    <tr><td><?php echo __('รหัสเฉพาะ'); ?></td>
        <td><?php echo $cfg->getSchemaSignature(); ?> </td>
    </tr>
    <tr><td><?php echo __('ขนาดพื้นที่'); ?></td>
        <td><?php
        $sql = 'SELECT sum( data_length + index_length ) / 1048576 total_size
            FROM information_schema.TABLES WHERE table_schema = '
            .db_input(DBNAME);
        $space = db_result(db_query($sql));
        echo sprintf('%.2f MiB', $space); ?></td>
    <tr><td><?php echo __('ขนาดพื้นที่ไฟล์แนบ'); ?></td>
        <td><?php
        $sql = 'SELECT SUM(LENGTH(filedata)) / 1048576 FROM '.FILE_CHUNK_TABLE;
        $space = db_result(db_query($sql));
        echo sprintf('%.2f MiB', $space); ?></td>
</tbody>
</table>
<br/>
<h2><?php echo __('แพ็คภาษาที่ติดตั้ง'); ?></h2>
<div style="margin: 0 20px">
<?php
    foreach (Internationalization::availableLanguages() as $info) {
        $p = $info['path'];
        if ($info['phar']) $p = 'phar://' . $p;
        if (file_exists($p . '/MANIFEST.php')) {
            $manifest = (include $p . '/MANIFEST.php'); ?>
    <h3><strong><?php echo Internationalization::getLanguageDescription($info['code']); ?></strong>
        &mdash; <?php echo $manifest['Language']; ?>
<?php       if ($info['phar'])
                Plugin::showVerificationBadge($info['path']);
            ?>
        </h3>
        <div><?php echo __('เวอร์ชั่น'); ?>: <?php echo $manifest['Version']; ?>,
            <?php echo __('สร้างเมื่อ'); ?>: <?php echo $manifest['Build-Date']; ?>
        </div>
<?php }
    } ?>
</div>
