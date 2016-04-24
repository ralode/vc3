<?php
ob_start();
?>
<style type="text/css">
    .reclame-bg {
        position:absolute; 
        top: 0; right: 0; bottom: 0; left: 0;
        width:100%; 
        height:100%; 
        background-color: gray;
        opacity: 0.6;
        filter: alpha(Opacity=60);
    }
    .reclame-container {
        position:absolute; 
        top: 0; right: 0; bottom: 0; left: 0;
        width:100%; 
        height:100%;
        overflow: hidden;
    }
    .reclame-center {
        margin: 0 auto 0;
    }
    .reclame-rounded { border-radius: 4px; padding:8px; }
    .reclame-close-block { width:100%; text-align: center; }
    .reclame-close { margin-top: 5px; border: 2px solid #207f9a; background-color: #e4f0f4; color: #000000; padding:2px 6px 2px; border-radius: 8px; font-size:14px; line-height:1;}
    .reclame-close:hover { cursor: pointer; background-color: #254750; color:#f0f2f2; }
    
</style>
<div class="reclame-code-start" style="display:none;">
    <?php
        global $vk_config;
        echo $vk_config['extensions']['Reclame']['config']['code'];
    ?>
</div>
<script type="text/javascript">
    $(function(){
        $('.RalodePlayer').css('position', 'relative').append('<div class="reclame-bg reclame-base"></div><div class="reclame-container reclame-base"><div class="reclame-close-block"><button class="reclame-close">Закрыть</button></div></div>');
        $('.reclame-close').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            $('.reclame-base').hide().remove();
        });
        
        // Инициализация кода рекламного блока
        var reclameCodeStart = $('.reclame-code-start').remove();
        $('.reclame-container').prepend(reclameCodeStart.html());
        delete reclameCodeStart;
    });
</script>
<?php
return ob_get_clean();