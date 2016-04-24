<?php
/*
 * Назначение файла: Шаблон вывода плеера
 */

// $CvPlayerStyle - название текущего стиля
//print_r($CvStruct); exit; // - структура сборок
//print_r($CvInfo); exit; // - массив информации про структуру сборок
// Array ( [scount] => 2 [zcount] => 1 [first_code] => oid=-245070.. [first_zid] => 77 [first_sid] => 137 )

// Если нету ни одного кода
if ($CvInfo["first_code"]!==false) {

?>
<style>
    #VideoConstructor_v3_x_Player { width: <?php echo VideoTubes::getInstance()->formatSize($vk_config["player_width"], true); ?>; margin:8px auto 8px; }
    #VcCode {height: <?php echo VideoTubes::getInstance()->formatSize($vk_config["player_height"], true); ?>;}
    /* Выбор серии */
    #vc-player-select { display:block; margin:10px auto 5px; min-width:250px; }
    /* Блок жалоб */
    #vc-complait-box { float:right; }
    /* Ссылка "Пожаловаться на видео" */
    #CvComplaintShowModal {} 
    /* Форма отправки жалобы*/
    #vc-complait-dialog div:last-child {padding-top:8px;}
    #cv_complaint_text {width:330px; height:70px; opacity:0.25; resize:none; outline: none; overflow: auto;}
</style>
<div id="VideoConstructor_v3_x_Player" class="vc-player-default">
    <div id="VcCode">
        <?php echo $CvInfo["first_code"]; ?>
    </div>
    <?php 
    if ($CvInfo["scount"]>1) {
    ?>
    <div id="vc-player-selectbox">
        <select id="vc-player-select">
            <?php
            $vc_codes = array();
            $i = 0;
            foreach ($CvStruct as $zid => $arr) {
                $options = '';
                $z_count_series = 0;
                foreach ($arr['items'] as $film) {
                    if ($film['scode']<>'') {
                        $options .= '<option value="'.$i.'" data-zid="'.intval($film['parent']).'" data-sid="'.intval($film['id']).'">'.htmlspecialchars($film['sname'],ENT_QUOTES, $config['charset']).'</option>'."\n";
                        $vc_codes[$i] = $film['scode'];
                        $i++;
                        $z_count_series++;
                    }
                }
                if ($z_count_series>0) { // Если в сборке есть непустые серии
                    if ($CvInfo["zcount"]>1) { // Если несколько сборок
                    ?>
                        <optgroup label="<?php echo htmlspecialchars($arr['name'],ENT_QUOTES, $config['charset']);?>" data-zid="<?php echo intval($arr['id']);?>">
                            <?php echo $options; ?>
                        </optgroup>
                    <?php
                    } else {
                        echo $options;
                    }
                }
            }
            
            if ($config["charset"]!="utf-8" && is_array($vc_codes)) {
                foreach ($vc_codes as $key => $val) 
                    $vc_codes[$key] = iconv ("WINDOWS-1251", "UTF-8", $val);
            }
            $vc_codes = json_encode($vc_codes);
            if ($vc_codes===false) $vc_codes = "[]";
            ?>
        </select>
    </div>
    <?php
    } // endif ($CvInfo["scount"]>1)
    ?>
    <div id="vc-complait-box">
        <span class="vc-complait-span"><a href="#" class="CvComplaintShowModal">Пожаловаться на видео</a></span>
    </div>
    <div id="vc-complait-dialog" title="Выберите причину или введите ее вручную" style="display:none;">
        <div><label><input type="radio" name="cv_complaint" value="Видео не работает" checked> Видео не работает</label></div>
        <div><label><input type="radio" name="cv_complaint" value="Видео не соответствует названию"> Видео не соответствует названию</label></div>
        <div><label><input type="radio" name="cv_complaint" value="" > Другая:</label></div>
        <div><textarea id="cv_complaint_text" disabled></textarea></div>
    </div>
    <div style="clear:both;"></div>
</div>
<script type="text/javascript" language="javascript">
    /**
     * Конструктор видео для DLE v3.x
     * @autor SeregaL (www.ralode.com)
     */
    var vc_codes = <?php echo isset($vc_codes)&& $vc_codes ? $vc_codes : "[]"; ?>;
    $.data(document, "vcZid", <?php echo $CvInfo["first_zid"]; ?> ); // Первое видео
    $.data(document, "vcSid", <?php echo $CvInfo["first_sid"]; ?> );
    $(document).ready(function(){
        // Изменение серии
        $("#vc-player-select").change(function(){
            var obj = $(this);
            var i =obj.val();
            if ($.type(vc_codes[i])!="undefined") {
                var oobj = $("option[value="+i+"]",obj);
                $.data(document, "vcZid",oobj.attr("data-zid") ); // Текущая сборки и серия
                $.data(document, "vcSid",oobj.attr("data-sid") );
                $("#VcCode").html(vc_codes[i]);
            }
        });
        // Пожаловаться
        $(".CvComplaintShowModal").click(function(){
            // Подготовка переменных
            $("#vc-complait-dialog input[type=radio]:first").prop("checked",true);
            $("#cv_complaint_text").val("").css("opacity","0.25").prop("disabled",true);
            // Открытие диолога
            $("#vc-complait-dialog").dialog({
                closeText: "х",
				width: "auto",
                buttons: [ 
                    {
                        text: "Отправить", click: function() {
                            var zid = $.data(document,"vcZid");
                            var sid = $.data(document,"vcSid");
                            var text = "";
                            $("#vc-complait-dialog input[type=radio]").each(function(){
                                if ($(this).prop("checked")) text = $(this).val();
                            });
                            if (text=="") {
                                text = $("#cv_complaint_text").val();
                            }
                            if (text) {
                                var this_ = this;
                                $.post("/index.php?do=videoconstructor&action=add_cmpl",{zid:zid, sid:sid, text:text}, function(data_text){
                                    //alert(data_text);
                                    switch (data_text) {
                                        case "OK": $( this_ ).dialog( "close" ); break;
                                        case "AUTH": alert("Для отправки сообщения вам надо авторизоваться!"); break;
                                        case "ANTIFLOOD": alert("Вы отправляеете сообщения слишком часто! Повтирите через 30 секунд!"); break;
                                        default: alert("Ошибка при отправке сообщения. Пожалуйста, сообщите администратору!"); break;
                                    } 
                                });
                            } else {
                                alert("Ошибка: Заполните текст жалобы...");
                            }
                        }
                    },{
                        text: "Отмена", click: function() {
                            $( this ).dialog( "close" );
                        }
                    }
                ],
                resizable: false
            });
            return false;
        });
        // Смена чекбокса
        $("#vc-complait-dialog input[type=radio]").change(function(){
            var v = $(this).val();
            if (v=="") {
                $("#cv_complaint_text").css("opacity","1").prop("disabled",false);
            } else {
                $("#cv_complaint_text").css("opacity","0.25").prop("disabled",true);
            }
        });
    });
</script>
<?php
} // // Если нету ни одного кода
?>