<?php
/*********************************************************************
    dashboard.php

    Staff's Dashboard - basic stats...etc.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('staff.inc.php');
$nav->setTabActive('dashboard');
$ost->addExtraHeader('<meta name="tip-namespace" content="dashboard.dashboard" />',
    "$('#content').data('tipNamespace', 'dashboard.dashboard');");
require(STAFFINC_DIR.'header.inc.php');
?>

<script type="text/javascript" src="js/raphael-min.js?c18eac4"></script>
<script type="text/javascript" src="js/g.raphael.js?c18eac4"></script>
<script type="text/javascript" src="js/g.line-min.js?c18eac4"></script>
<script type="text/javascript" src="js/g.dot-min.js?c18eac4"></script>
<script type="text/javascript" src="js/bootstrap-tab.js?c18eac4"></script>
<script type="text/javascript" src="js/dashboard.inc.js?c18eac4"></script>

<link rel="stylesheet" type="text/css" href="css/bootstrap.css?c18eac4"/>
<link rel="stylesheet" type="text/css" href="css/dashboard.css?c18eac4"/>

<h2><?php echo __('สถิติการดำเนินงาน');
?>&nbsp;<i class="help-tip icon-question-sign" href="#ticket_activity"></i></h2>
<p><?php echo __('เลือกช่วงเวลาที่ต้องการ เพื่อให้ระบบแสดงข้อมูลสถิติการดำเนินงาน');?></p>
<form class="well form-inline" id="timeframe-form">
    <label>
        <i class="help-tip icon-question-sign" href="#report_timeframe"></i>&nbsp;&nbsp;<?php
            echo __('ช่วงเวลา'); ?>:
        <input type="text" class="dp input-medium search-query"
            name="start" placeholder="<?php echo __('เดือนที่แล้ว');?>"/>
    </label>
    <label>
        <?php echo __('ถึง');?>:
        <select name="period">
            <option value="now" selected="selected"><?php echo __('วันนี้');?></option>
            <option value="+7 days"><?php echo __('หนึ่งสัปดาห์');?></option>
            <option value="+14 days"><?php echo __('สองสัปดาห์');?></option>
            <option value="+1 month"><?php echo __('หนึ่งเดือน');?></option>
            <option value="+3 months"><?php echo __('หกเดือน');?></option>
        </select>
    </label>
    <button class="btn" type="submit"><?php echo __('แสดงผล');?></button>
</form>

<!-- Create a graph and fetch some data to create pretty dashboard -->
<div style="position:relative">
    <div id="line-chart-here" style="height:300px"></div>
    <div style="position:absolute;right:0;top:0" id="line-chart-legend"></div>
</div>

<hr/>
<h2><?php echo __('รายละเอียด'); ?>&nbsp;<i class="help-tip icon-question-sign" href="#statistics"></i></h2>
<p><?php echo __('สถิติการดำเนินงานแยกเป็นแผนก คำขอใช้บริการ และเจ้าหน้าที่');?></p>
<ul class="nav nav-tabs" id="tabular-navigation"></ul>

<div id="table-here"></div>

<?php
include(STAFFINC_DIR.'footer.inc.php');
?>
