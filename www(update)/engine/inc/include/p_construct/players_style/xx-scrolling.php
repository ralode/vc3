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
<script src="/engine/inc/include/p_construct/players_style/js_common/CRalodePlayer.js" type="text/javascript"></script>
<script src="/engine/inc/include/p_construct/players_style/xx-scrolling/xx-style.js" type="text/javascript"></script>

<script type="text/javascript">
    $(document).ready(function(){
        RalodePlayer.init(<?php echo $data .','.$data2; ?>);
    });
</script>
<style>
    div.vc-player { width: <?php echo $style_config["width_css"]; ?>; margin:0; padding:0; overflow: hidden;}
    .vc-player .RalodePlayer {width:100%; height:100%; margin: 0 auto 0; background-color: #151515; padding:0; line-height:normal;}
    .vc-player .playerCode {min-height: <?php echo $style_config["height_css"]; ?>; height: <?php echo $style_config["height_css"]; ?>;}
</style>
<link type="text/css" rel="stylesheet" href="/engine/inc/include/p_construct/players_style/xx-scrolling/styles.css" >

<div id="VideoConstructor_v3_x_Player" class="vc-player">
    <div class="RalodePlayer <?php echo $xscrolling_additional_classes; ?>">
        <div id="rl-buttons-top" class="rl-buttons">
            <div class="buttonLR ButtonLft"></div>
            <div class="buttonLR ButtonRgh"></div>
            <div class="RlVisor">
                <div class="rl-lenta" id="rl-lenta-top">
                    <div class="RlItem serie-active">Пример сезона</div>
                </div>
            </div>
            <div style="clear:both;"></div>
        </div>
        <div class="playerCode">
            <?php echo $CvInfo["first_code"]; ?>
        </div>
        <div id="rl-buttons-bottom" class="rl-buttons">
            <div class="buttonLR ButtonLft"></div>
            <div class="buttonLR ButtonRgh"></div>
            <div class="RlVisor">
                <div class="rl-lenta" id="rl-lenta-bottom">
                    <div class="RlItem serie-active">Пример серии</div>
                </div>
            </div>
            <div style="clear:both;"></div>
        </div>
    </div>
    <div id="vc-complait-box">
        <span class="vc-complait-span"><a href="#" class="CvComplaintShowModal">Пожаловаться на видео</a></span>
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