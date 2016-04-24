<?php
/*
 * Назначение файла: Шаблон вывода плеера
 */

if (strpos($vk_config['player_width'], '%')!==false || strpos($vk_config['player_height'], '%')!==false) {
    echo '<div style="width:100%;height:350px;background-color:#bbb;">Шаблон x-scrolling (устаревший) не работает с размером плеера в процентах.'
        .'<br>Измените шаблон на другой или укажите размер плеера в настройках конструктора на фиксовыный.'
        .'</div>';
    return;
}

// $CvPlayerStyle - название текущего стиля
//print_r($CvStruct); exit; // - структура сборок
//print_r($CvInfo); exit; // - массив информации про структуру сборок
// Array ( [scount] => 2 [zcount] => 1 [first_code] => oid=-245070.. [first_zid] => 77 [first_sid] => 137 )

$xs_config = array (
    "width" => $vk_config['player_width'], // Ширина блока плеера
    "player_height" => $vk_config['player_height'], // Высота плееров
    "button_w" => 24, // Ширина кнопок "Вправо/влево"
    "button_h" => 24, // Высота кнопок "Вправо/влево"
);

if (strpos($vk_config['player_width'], '%')!==false || strpos($vk_config['player_height'], '%')!==false) {
    echo "Шаблон x-scrolling (утаревший) не работает с размером плеера в процентах.";
    return;
}

// Если нету ни одного кода
if ($CvInfo["first_code"]!==false) {
    
    // Дополнительные классы для надстроек
    if (!isset($xscrolling_additional_classes)) $xscrolling_additional_classes = "";
    else
        $xscrolling_additional_classes = htmlspecialchars($xscrolling_additional_classes, ENT_COMPAT, $config['charset']);
    
    // Разбиваю массив структуры сборок на 2 массива
    $data_z = array();
    $data_s = array();
	$list_z = array(); // Список zid => порядковый номер // mod mark
	$list_s = array(); // Список sid => порядковый номер // mod mark
    if (is_array($CvStruct)) {
        $zi = 0;
        foreach ($CvStruct as $z) {
            $data_z[$zi] = $z["name"];
			$list_z[$z["id"]] = $zi; // mod mark
			$list_s[$z["id"]] = array(); // mod mark
            if (is_array($z["items"])) {
                $si = 0;
                foreach ($z["items"] as $s) {
                    $data_s[$zi][$si] = array("name"=>$s["sname"],"code"=>$s["scode"], "zid"=>$z["id"], "sid"=>$s["id"]);
					$list_s[$z["id"]][$s["id"]] = $si; // mod mark
                    $si++;
                }
            }
            $zi++;
        }
    }
	$lists = json_encode(array("z"=>$list_z, "s"=>$list_s,));
    if ($config["charset"]!="utf-8") {
        $data_z = array_iconv("WINDOWS-1251", "UTF-8", $data_z);
        $data_s = array_iconv("WINDOWS-1251", "UTF-8", $data_s);
    }
    $data_z = json_encode($data_z);
    $data_s = json_encode($data_s);
    //print_r($data_s); exit;
    
    // Высота блока плеера
    $block_h = $xs_config["player_height"]+10;
    if ($CvInfo["zcount"]>1) $block_h += 29;
    if ($CvInfo["scount"]>1) $block_h += 29;
?>
<style>
    .RalodePlayer {width:<?php echo $xs_config["width"]; ?>px; height:<?php echo $block_h; ?>px; margin: 0 auto 0; background-color: #252525; padding:0 5px 0;}
    .playerCode {height: <?php echo VideoTubes::getInstance()->formatSize($vk_config["player_height"], true); ?>;}
    .rl-buttons {padding: 5px 0 5px; height:<?php echo $xs_config["button_h"]; ?>px;}
    .buttonLR {width:<?php echo $xs_config["button_w"]; ?>px; 
        height:<?php echo $xs_config["button_h"]; ?>px; cursor:pointer;}
    .RlVisor {width:<?php echo $xs_config["width"]-2*$xs_config["button_w"]-4; ?>px; height:26px; float:left; overflow: hidden; } /*  */
</style>
<link type="text/css" rel="stylesheet" href="/engine/inc/include/p_construct/players_style/x-scrolling/styles.css" >
<script src="/engine/inc/include/p_construct/players_style/x-scrolling-2/scripts.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function(){
        RalodePlayer.init(<?php echo $data_z.",".$data_s.",".$CvInfo["scount"].",".$lists ?>);
        $("#rl-buttons-top .ButtonLft").click(function(){ RalodePlayer.move("top","left"); });
        $("#rl-buttons-top .ButtonRgh").click(function(){ RalodePlayer.move("top","right"); });
        $("#rl-buttons-bottom .ButtonLft").click(function(){ RalodePlayer.move("bottom","left"); });
        $("#rl-buttons-bottom .ButtonRgh").click(function(){ RalodePlayer.move("bottom","right"); });
    });
</script>
<div id="VideoConstructor_v3_x_Player" class="vc-player-x-scrolling">
    <div class="RalodePlayer <?php echo $xscrolling_additional_classes; ?>">
        <div id="rl-buttons-top" class="rl-buttons">
            <div class="buttonLR ButtonLft"></div>
            <div class="RlVisor">
                <div class="rl-lenta" id="rl-lenta-top">
                    <div class="RlItem serie-active">Пример сезона</div>
                </div>
            </div>
            <div class="buttonLR ButtonRgh"></div>
        </div>
        <div class="playerCode">
            <?php echo $CvInfo["first_code"]; ?>
        </div>
        <div id="rl-buttons-bottom" class="rl-buttons">
            <div class="buttonLR ButtonLft"></div>
            <div class="RlVisor">
                <div class="rl-lenta" id="rl-lenta-bottom">
                    <div class="RlItem serie-active">Пример серии</div>
                </div>
            </div>
            <div class="buttonLR ButtonRgh"></div>
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
<?php



} // // Если нету ни одного кода

?>