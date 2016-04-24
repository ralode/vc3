<?php
/**
 * Скрипт презначен для вывода редактора сборок.
 * Не следует вручную инклюидить этот файл куда-то ни было - для этого есть метод:
 * VideoConstructor::getInstance()->runEditor($data);
 * 
 * @uses        $data           Массив данных сборок новости для редактирования
 * @uses        $vc_conf        Массив настроек для JS
 * @uses        $config         Dle configuration
 * @uses        $vk_config      Конфигурация скрипта
 */

$data = json_encode($data);
$vc_conf = json_encode($vc_conf);
?>
<div id="VcEditorAll" style="display:none;">
    <div class="VcHPanel">
        <!-- Выбор качества отключен. -->
        <!--<span class="rcbold">Качество:</span><span class="vc_Quality" id="vc_Quality_240">240</span><span class="vc_Quality" id="vc_Quality_360">360</span><span class="vc_Quality" id="vc_Quality_480">480</span><span class="vc_Quality" id="vc_Quality_720">720</span>-->
        <span class="rcbold">Минимальная&nbsp;длинна&nbsp;видео&nbsp;(минут):</span><input type="text" id="vc_MinLen" value="30" />
        <span class="rcbold">Шаблон&nbsp;вывода:</span><select id="vc_PlayerStyle">
            <option value="">По умолчанию</option>
            <option value="default">default</option>
            <option value="x-scrolling">x-scrolling</option>
        </select>
        <a id="VcNewVersion" href="http://ralode.com/konstruktor-video-v3-changes.html" target="_blank">Обновитесь ;)</a>
        <div></div>
        <span class="rcbold">Названия:</span><input type="text" id="vc_Tpl" value="{title}" />
        <input type="button" class="vc_player_st_button" title="Изменить шаблон названия" style="background: url(/engine/inc/include/p_construct/editor/images/arrow-circle-down.png) 1px 1px no-repeat #cae6ef;" />
        <span class="rcbold">Счетчик:</span><input type="text" id="vc_Counter" value="1" />
        <input type="button" title="Импорт сборок" id="vc_Import" class="vc-btn16">
        <input type="button" title="Экспорт сборок" id="vc_Export" class="vc-btn16">
    </div>
    <div id="VcConteiner"></div>
    <div class="vc_AddPanelFooter"><span><a href="#" id="vc_addZborka">Добавить сборку</a></span></div>
    <br />
    <!-- Сообщения об ошибах -->
    <div id="VcErrorsBlock"></div>
    <?php if ($vk_config['is_debug']) { ?> 
    Отладка: 
    <a href="#" onclick="console.log( $('#VcConteiner').html() ); return false;">VcConteiner HTML</a> - 
    <a href="#" onclick="console.log( VcEditor.getAllItems() ); return false;">Get all items</a>
    <?php } ?>
    <!-- Диалог просмотра кода -->
    <div id="VcPreviewDialog" title="Просмотр кода" style="display:none;"></div>
    <!-- Диалог редактирования кода -->
    <div id="VcEditDialog" title="Редактирование кода" style="display:none;">
        <div>
            <textarea id="vc_editor_text" rows="4"></textarea>
            <input type="hidden" id="vc_editor_zid" value="" />
            <input type="hidden" id="vc_editor_num" value="" />
        </div>
    </div>
    <!-- Диалог просмотра жалоб на фильм -->
    <div id="VcCplDialog" title="Список жалоб на серию" style="display:none;">
        <ul class="cpl_list">
            <li></li>
        </ul>
        <!-- Шаблон строки жалоб -->
        <ul class="cpl_list" style="display:none">
            <li style="margin-bottom:5px;"><strong>{text}</strong><br>Пользователь: <a href="/user/{user_name}/" style="color: #0c607e;">{user_name}</a>. Время: <span style="color: #0d6d23;">{time}</span></li>
        </ul>
    </div>
    <!-- Диалог просмотра ошибок в сериях -->
    <div id="VcErrDialog" title="Список ошибок" style="display:none;">
        <p><strong>Ошибка</strong>:</p>
        <p id="vc_err_text"></p>
    </div>
    <!-- Диалог поиска вконтакте -->
    <div id="VcFindVkDialog" style="display:none;">
        <!-- Строки ниже задаются с помощью JS -->
        <div class="VcFindPreload">
            <img src="<?php echo $config["http_home_url"]."engine/inc/include/p_construct/editor/images"; ?>/ajax-loader.gif" /><br />
            Подождите, идет загрузка данных...
        </div>
    </div>
    <!-- Диалог просмотра жалоб на фильм -->
    <div id="VcCplDialog" title="Список жалоб" style="display:none;">
        <ul class="cpl_list">
            <li></li>
        </ul>
        <!-- Шаблон строки жалоб -->
        <ul class="cpl_list" style="display:none">
            <li>{text} by {user_name} at {time}</li>
        </ul>
    </div>
    <!-- Диалог выбора шаблона названия -->
    <div id="VcFindVkNameTpl" title="Выбор шаблона" style="display:none;">
        Здесь HTML-код генерируется с помощью JS
    </div>
    <!-- Диалог переименования серий в сборке -->
    <div id="VcRenamingDialog" title="Переименование серий" style="display:none;">
        Новый шаблон: <input type="text" id="vc_rn_template" value="Новое имя {num}"><br>
        Счетчик (начальное значение): <input type="text" id="vc_rn_counter" value="1"> 
        Разница: <input type="text" id="vc_rn_ddd" value="1"><br><br>
        <div id="vc_rm_list">
            <span class="tdNum tdTHead">&nbsp;</span><span class="tdLeft tdTHead">Старое имя</span><span class="tdRight tdTHead">Новое имя</span>
            <div id="vc_rm_listItems">
                <span class="tdNum">1</span><span class="tdLeft">Новое</span><span class="tdRight">может быть</span>
                <span class="tdNum">2</span><span class="tdLeft">давно забытым</span><span class="tdRight">старым.</span>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
</div>

<script>
    (function(){
        $("head").append('<link href="<?php echo $config['http_home_url'] ?>engine/inc/include/p_construct/editor/style.css" rel="stylesheet" type="text/css"/>');
        var VcEditorAll = $("#VcEditorAll");
        $('#xfield\\[pconstruct\\],#xf_pconstruct').parent().html('<?php
if ($config["version_id"]>=10.2)
    echo '<input type="hidden" name="xfield[pconstruct]" id="xf_pconstruct" value="">';
else
    echo '<input type="hidden" name="xfield[pconstruct]" id="xfield[pconstruct]" value="" />';
?>'+VcEditorAll.html());
        VcEditorAll.remove();
    })();
</script>
<script src="<?php echo $config['http_home_url']; ?>engine/classes/js/jquery.json.js"></script>
<script src="<?php echo $config['http_home_url']; ?>engine/inc/include/p_construct/editor/sortable.js"></script>
<script src="<?php echo $config['http_home_url']; ?>engine/inc/include/p_construct/editor/KonstructorApi.js"></script>
<script src="<?php echo $config['http_home_url']; ?>engine/inc/include/p_construct/editor/ejs.js"></script>
<script src="<?php echo $config['http_home_url']; ?>engine/inc/include/p_construct/editor/sortable_editor.js"></script>
<script src="<?php echo $config['http_home_url']; ?>engine/inc/include/p_construct/editor/jquery.cookie.js"></script>
<script type="text/javascript" language="javascript">
    /*
    $.fn.on
     $().jquery; // проверка версии Jquery - ##!! добавить на главную для диагностики
    */
    var VcEditor;
    $(document).ready(function(){
        // Ошибка .sortable
        if ($.type($.fn.sortable)!=="function") 
            $("#VcErrorsBlock").text("(!) JQueryUI.sortable не доступен. Сортировка серий мышкой не доступна.");
        
        VcEditor = new VcEditorConstructor($("#VcConteiner"), <?php echo $data; ?>, <?php echo $vc_conf; ?>);
        
        $("p#first").after(VcEditor.init);
        VcInitSortable();
    });

</script>

<!-- Расширение, события afterEditor -->
<script type="text/javascript" language="javascript">
<?php VcExtension::getInstance()->event('afterEditor', 'js'); ?>
</script>