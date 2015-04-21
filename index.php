<?php
/*********************************************************************
    index.php

    Helpdesk landing page. Please customize it to fit your needs.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('client.inc.php');
$section = 'home';
require(CLIENTINC_DIR.'header.inc.php');
include_once("analyticstracking.php");
?>
<div id="landing_page">
		<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-54804437-7', 'auto');
  ga('send', 'pageview');
         </script>
    <?php
    if($cfg && ($page = $cfg->getLandingPage()))
        echo $page->getBodyWithImages();
    else
        echo  '<h1>'.__('ยินดีต้อนรับเข้าสู่ระบบ แจ้งขอใช้บริการออนไลน์ (iService)').'</h1>';
    ?>
    <div id="new_ticket" class="pull-left">
        <h3><?php echo __('สร้างคำขอใช้บริการ');?></h3>
        <br>
        <div><?php echo __('กรุณากรอกข้อมูลให้ครบถ้วนสมบูรณ์ เพื่อประโยชน์และความรวดเร็วในการให้บริการ');?></div>
    </div>

    <div id="check_status" class="pull-right">
        <h3><?php echo __('สถานะคำขอใช้บริการ');?></h3>
        <br>
        <div><?php echo __('ตรวจสอบสถานะคำขอใช้บริการ หากคุณยังไม่เคยสร้างคำขอใช้บริการ ระบบจะพาไปที่แบบฟอร์มสร้างคำขอใช้บริการใหม่ให้โดยอัตโนมัติ');?></div>
    </div>

    <div class="clear"></div>
    <div class="front-page-button pull-left">
        <p>
            <a href="open.php" class="green button"><?php echo __('สร้างคำขอใช้บริการ');?></a>
        </p>
    </div>
    <div class="front-page-button pull-right">
        <p>
            <a href="tickets.php" class="blue button"><?php echo __('สถานะคำขอใช้บริการ');?></a>
        </p>
    </div>
</div>
<div class="clear"></div>
<?php
if($cfg && $cfg->isKnowledgebaseEnabled()){
    //FIXME: provide ability to feature or select random FAQs ??
?>
<p><?php echo sprintf(
    __('โปรดอ่าน %s ก่อนสร้างคำขอใช้บริการ'),
    sprintf('<a href="kb/index.php">%s</a>',
        __('คำถามที่พบบ่อย (FAQs)')
    )); ?></p>
</div>
<?php
} ?>
<?php require(CLIENTINC_DIR.'footer.inc.php'); ?>
