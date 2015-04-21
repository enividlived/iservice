jQuery(function($) {

    $("#overlay").css({
        opacity : 0.3,
        top     : 0,
        left    : 0,
        width   : $(window).width(),
        height  : $(window).height()
        });

    $("#upgrading").css({
        top  : ($(window).height() / 3),
        left : ($(window).width() / 2 - 160)
        });

    $('form#upgrade').submit(function(e) {
        var form = $(this);
        $('input[type=submit]', this).attr('disabled', 'disabled');
        $('#overlay, #upgrading').show();
        if($('input#mode', form).val() == 'manual') {
            return  true;
        } else {
            e.preventDefault();
            autoUpgrade('upgrade.php',form.serialize());
            return false;
        }
      });

    function autoUpgrade(url, data) {
        function _lp(count) {
            $.ajax({
                type: 'POST',
                url: 'ajax.php/upgrader',
                async: true,
                cache: false,
                data: data,
                dataType: 'text',
                success: function(res) {
                    $('#main #task').html(res);
                    $('#upgrading #action').html(res);
                    $('#upgrading #msg').html(__('กำลังยุ่งอยู่... smile #')+count);
                },
                statusCode: {
                    200: function() {
                        setTimeout(function() { _lp(count+1); }, 200);
                    },

                    201: function() {
                        $('#upgrading #msg').html(__("ทำความสะอาด!..."));
                        setTimeout(function() { location.href =url+'?c='+count+'&r='+Math.floor((Math.random()*100)+1); }, 3000);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#upgrading #action').html(__('เกิดปัญหา! กำลังยกเลิก...'));
                    switch(jqXHR.status) {
                        case 404:
                            $('#upgrading #msg').html(__("ต้องอัพเกรดด้วยตัวเอง (ajax ล้มเหลว)"));
                            setTimeout(function() { location.href =url+'?m=manual&c='+count+'&r='+Math.floor((Math.random()*100)+1); }, 2000);
                            break;
                        default:
                            $('#upgrading #msg').html(__("อะไรซักอย่างผิดพลาด"));
                            setTimeout(function() { location.href =url+'?c='+count+'&r='+Math.floor((Math.random()*100)+1); }, 2000);
                    }
                }
            });
        };
        _lp(1);
    }
});
