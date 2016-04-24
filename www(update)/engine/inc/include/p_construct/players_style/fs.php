<?php

global $CvStruct;

$style_config = array(
    "width" => VideoTubes::getInstance()->formatSize($vk_config['player_width']), // Ширина блока плеера
    "height" => VideoTubes::getInstance()->formatSize($vk_config['player_height']), // Высота блока плеера
    "width_css" => VideoTubes::getInstance()->formatSize($vk_config['player_width'], true),
    "height_css" => VideoTubes::getInstance()->formatSize($vk_config['player_height'], true),
);

if ($CvInfo["first_code"]!==false) {
    // Дополнительные классы для надстроек
    if (!isset($additional_classes))
        $additional_classes = "";
    else
        $additional_classes = htmlspecialchars($additional_classes, ENT_COMPAT, $config['charset']);
    
    //print_r($CvStruct); exit;
    if ($config["charset"]!="utf-8") {
        $CvStruct = array_iconv("windows-1251", "utf-8", $CvStruct);
        $CvInfo_ = array_iconv("windows-1251", "utf-8", $CvInfo);
    } else {
        $CvInfo_ = $CvInfo;
    }
    $data = json_encode($CvStruct);
    $data2 = json_encode($CvInfo_);
    if ($data===false) $data = "{}";
    if ($data2===false) $data2 = "{}";
    unset($CvInfo_);
    
?>
<!-- http://manos.malihu.gr/jquery-custom-content-scroller/ -->
<link href="/engine/inc/include/p_construct/players_style/fs/jquery.mCustomScrollbar.css" rel="stylesheet" type="text/css" />
<script src="/engine/inc/include/p_construct/players_style/fs/jquery.mCustomScrollbar.min.js"></script>

<script src="/engine/inc/include/p_construct/players_style/js_common/CRalodePlayer.js" type="text/javascript"></script>
<script src="/engine/inc/include/p_construct/players_style/fs/fs.js" type="text/javascript"></script>

<script type="text/javascript">
    $(document).ready(function(){
        RalodePlayer.init(<?php echo $data .','.$data2; ?>);
    });
</script>
<style>
    div.vc-player { width: <?php echo $style_config["width_css"]; ?>; }
    .vc-player .RalodePlayer { margin: 0 auto 0; background-color: #151515; padding:0;}
    .vc-player .RalodePlayer .playerCode {min-height: <?php echo $style_config["height_css"]; ?>; height: <?php echo $style_config["height_css"]; ?>;}
    .vc-player .RalodePlayer #rl-panel-bottom {padding: 3px 0 3px;}
    .vc-player .RalodePlayer .rl-lBtn { padding: 0 18px 0 5px; background: url('/engine/inc/include/p_construct/players_style/fs/down_white.png') no-repeat right center; color: white; font-weight: bold; font-size: 14px; line-height: 16px; max-width: 220px;}
    .vc-player .RalodePlayer .rl-lBtn:hover { color:#468bdf; cursor: pointer; }
    .vc-player .RalodePlayer .rl-lBtn.rl-active { background-image: url('/engine/inc/include/p_construct/players_style/fs/up_white.png'); }
    .vc-player .RalodePlayer .rl-seazon {margin-right: 12px;}
    .vc-player .RalodePlayer .rl-list {display: none; width:100%; height: 100px; margin-top:5px;}
    .vc-player .RalodePlayer .rl-list ul {color: white; list-style: none; padding: 0; margin:0; font-weight: normal; font-size: 13px; float: left;}
    .vc-player .RalodePlayer .rl-list ul li { margin: 0 8px 0; text-decoration: underline; cursor:pointer; }
    .vc-player .RalodePlayer .rl-list ul li.rl-active { color:#468bdf; }
    .vc-player .RalodePlayer .rl-list ul li:hover { color:#468bdf; }
    .vc-player .RalodePlayer .vc-complait-span {float:right; margin-right:8px;}
    .vc-player .RalodePlayer .vc-complait-span a {color: #cccccc;}
    .vc-player .RalodePlayer .vc-complait-span a:hover {color: #468bdf;}
    
    /* Rounded */
    .vc-player .RalodePlayer.rl-rounded {padding:4px; border-radius: 4px;}
    
    /* Light */
    .vc-player .RalodePlayer.rl-light {background-color: #f0f3f6;}
    .vc-player .RalodePlayer.rl-light .rl-lBtn {color: #25364a; background-image: url('/engine/inc/include/p_construct/players_style/fs/down.png'); }
    .vc-player .RalodePlayer.rl-light .rl-list ul {color: #25364a;}
    .vc-player .RalodePlayer.rl-light .vc-complait-span a {color: #25364a;}
</style>

<div id="VideoConstructor_v3_x_Player" class="vc-player">
    <div class="RalodePlayer <?php echo $additional_classes; ?>">
        <div class="playerCode">
            <?php echo $CvInfo["first_code"]; ?>
        </div>
        <div id="rl-panel-bottom" class="rl-buttons">
            <?php if ($CvInfo["zcount"]>1) { ?><span class="rl-lBtn rl-seazon"><?php echo $CvInfo["first_z_name"]; ?></span><?php } ?>
            <?php if ($CvInfo["scount"]>1) { ?><span class="rl-lBtn rl-serie"><?php echo $CvInfo["first_name"]; ?></span><?php } ?>
            <span>&nbsp;</span>
            <span class="vc-complait-span"><a href="#" class="CvComplaintShowModal">Пожаловаться</a></span>
        </div>
        <div class="rl-list">
            <ul><li class="rl-active"></li><li></li></ul>
        </div>
    </div>
    <div id="vc-complait-dialog" title="Выберите причину или введите ее вручную" style="display:none;">
        <div><label><input type="radio" name="cv_complaint" value="Видео не работает" checked> Видео не работает</label></div>
        <div><label><input type="radio" name="cv_complaint" value="Видео не соответствует названию"> Видео не соответствует названию</label></div>
        <div><label><input type="radio" name="cv_complaint" value="" > Другая:</label></div>
        <div><textarea id="cv_complaint_text" disabled style="width:100% !important;"></textarea></div>
    </div>
    <div style="clear:both;"></div>
</div>
<?php } ?>