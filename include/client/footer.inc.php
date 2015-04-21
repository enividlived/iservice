        </div>
    </div>
    <div id="footer">
        <p>Copyright &copy; <?php echo date('Y'); ?> <?php echo (string) $ost->company ?: 'trc-con.com'; ?> - All rights reserved.</p>
        <a id="poweredBy" href="http://osTicket.com" target="_blank"><?php echo __('TRC&SKW iService - Modified by ICT Dept and Powered by osTicket!'); ?></a>
        <div id="footer">
        	<!-- Histats.com  START  (standard)-->
<script type="text/javascript">document.write(unescape("%3Cscript src=%27http://s10.histats.com/js15.js%27 type=%27text/javascript%27%3E%3C/script%3E"));</script>
<a href="http://www.histats.com" target="_blank" title="histats" ><script  type="text/javascript" >
try {Histats.start(1,2828937,4,1034,150,25,"00011111");
Histats.track_hits();} catch(err){};
</script></a>
<noscript><a href="http://www.histats.com" target="_blank"><img  src="http://sstatic1.histats.com/0.gif?2828937&101" alt="histats" border="0"></a></noscript>
			<!-- Histats.com  END  -->
    </div>
<div id="overlay"></div>
<div id="loading">
    <h4><?php echo __('โปรดรอสักครู่!');?></h4>
    <p><?php echo __('ระบบกำลังทำงาน...');?></p>
</div>
<?php
if (($lang = Internationalization::getCurrentLanguage()) && $lang != 'en_US') { ?>
    <script type="text/javascript" src="ajax.php/i18n/<?php
        echo $lang; ?>/js"></script>
<?php } ?>
</body>
</html>
