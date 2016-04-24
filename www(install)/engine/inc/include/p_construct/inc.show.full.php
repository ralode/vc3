<?php

/**
 * Скрипт: Конструктор видео v3.x для DLE
 * Назначение файла: Вставка плеера в полную новость
 * @author SeregaL <SeregaL2009@yandex.ru>
 * 
 * Скрипт сгенерирован специально для: {%loader(EMAIL)%}
 */

include_once (ENGINE_DIR . '/inc/include/p_construct/config.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/vkvideolib.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VcExtensionConfig.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VcExtension.php');
VcExtension::getInstance()->init();
// Расширение каталога - модификация настроек
if ($_GET["mod"] === "rational-catalog") VcExtension::getInstance()->inc("Catalog-modconf.php","Catalog");

include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VideoTubes.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VideoConstructor.php');


$vk_newsid = intval ($_GET['newsid']);
if ($vk_newsid>0) {
    
    // Пробую загрузить з кеша
    $CvStruct = VideoConstructor::getInstance()->cache($vk_newsid);
    // Если кеш не загружен
    if ($CvStruct===false) {
        $CvStruct = array(); // Деревовидний масив інформації по всіх категоріях і фільмах. Пригодний для збереження кешу
        // Загрузка списка сборок
        $vkfilm_res = $db->query( 'SELECT * FROM ' .PREFIX. "_vidvk_z WHERE post_id = '".$vk_newsid."' ORDER BY sort" );
        $vk_ids = ''; // Список ID добавлених зборок до фільма для SQL where
        while ( $vcrow = $db->get_row( $vkfilm_res ) ) {
            $CvStruct[$vcrow['id']] = array (
                'items' => array(), // фільми даної категорії
                'id' => $vcrow['id'],
                'name' => $vcrow['name'],
                'sort' => $vcrow['sort'],
                'style' => $vcrow['style'],
                'ssort' => $vcrow['ssort'],
                'data' => $vcrow['data']
            );
            if ($vk_ids=='') $vk_ids = "'$vcrow[id]'"; else $vk_ids .= ',\''.$vcrow['id'].'\'';
        }
        // Загрузка списка серий в сборки
        if ($vk_ids!=="") {
            $vkfilm_res = $db->query( 'SELECT * FROM ' .PREFIX. "_vidvk_s WHERE `parent` IN (".$vk_ids.") ORDER BY lssort" );
            $s_num = 1;
            while ( $vcrow = $db->get_row( $vkfilm_res ) ) {
                //print_r($vcrow);
                $CvStruct[$vcrow["parent"]]["items"][$s_num."."] = $vcrow;
                $s_num++;
            }
        }
        // Сохранение кеша
        VideoConstructor::getInstance()->cache($vk_newsid, $CvStruct);
        
    }
    //print_r($CvStruct); exit;
    
    if (count($CvStruct)>0) {
        // Предварительная обработка структуры
        $CvInfo = array( // Информация по сборкам
            "scount" => 0, // Количесво серий всего
            "zcount" => 0, // Количесво сборок всего
            "first_name" => "", // Первая серия
            "first_code" => "",
            "first_zid" => 0,
            "first_sid" => 0,
            "style" => false,
            "first_z_name" => "",
        );
        
        $isset_first_code = false;
        foreach ($CvStruct as $zid => $arr) {
            if (count($arr['items'])>0) {
                if ($CvInfo['first_z_name']=="") $CvInfo['first_z_name'] = $arr['name'];
                $count_items = 0;
                // Сортировка серий в сборке
                if ($arr['ssort']==2) $ssort = $vk_config['serie_sort']; else $ssort = $arr['ssort'];
                // Стиль плеера
                if ($arr['style'] && $CvInfo["style"]===false && preg_match("/^[A-Za-z0-9-_]+$/",$arr["style"]) && is_file(ENGINE_DIR . "/inc/include/p_construct/players_style/{$arr['style']}.php")) 
                    $CvInfo["style"] = $arr['style'];
                // Пробегаем серии
                foreach ($arr['items'] as $num => $film) {
                    if ($film["scode"]!="" && substr($film["scode"],0,5)!="prep(") {
                        $CvStruct[$zid]['items'][$num]['scode_begin'] = $film['scode'];
                        $CvStruct[$zid]['items'][$num]['scode'] = VideoTubes::getInstance()->getPlayer($film['scode'], $film['sname']);
                        if ($CvStruct[$zid]['items'][$num]['scode']) {
                            $count_items++;
                            if ($isset_first_code===false) {
                                // Берем первое видео
                                if ($ssort==0) { // Если сортировкасерий  по возрастанию
                                    if ($CvInfo["first_code"]=="") {
                                        $CvInfo["first_name"] = $CvStruct[$zid]['items'][$num]['sname'];
                                        $CvInfo["first_code"] = $CvStruct[$zid]['items'][$num]['scode'];
                                        $CvInfo["first_code_begin"] = $CvStruct[$zid]['items'][$num]['scode_begin'];
                                        $CvInfo["first_zid"] = $film['parent'];
                                        $CvInfo["first_sid"] = $film['id'];
                                    }
                                } else { // Если по убыванию
                                    $CvInfo["first_name"] = $CvStruct[$zid]['items'][$num]['sname'];
                                    $CvInfo["first_code"] = $CvStruct[$zid]['items'][$num]['scode'];
                                    $CvInfo["first_code_begin"] = $CvStruct[$zid]['items'][$num]['scode_begin'];
                                    $CvInfo["first_zid"] = $film['parent'];
                                    $CvInfo["first_sid"] = $film['id'];
                                }
                            }
                        }
                    } else 
                        unset($CvStruct[$zid]['items'][$num]);
                }
                
                if ($count_items>0) { 
                    $CvInfo["scount"] += $count_items;
                    $CvInfo["zcount"]++;
                } else
                    unset($CvStruct[$zid]);
                // Если нашли первый код в 1 сезоне, больше не ищем
                if ($CvInfo["first_code"]!="") {
                    $isset_first_code = true;
                }
                // Сортируем серии в сборке
                if ($ssort==1) {
                    $CvStruct[$zid]["items"] = array_reverse ($CvStruct[$zid]["items"], true);
                }
            }
        }
        // Расширение модификации размера плеера
        VcExtension::getInstance()->inc("PlayerResize.php","PlayerResize");
        
        //print_r($CvStruct); exit;
        //print_r($CvInfo); //exit;
        // Расширение каталога - модификация $CvStruct, $CvInfo
        if ($_GET["mod"] === "rational-catalog") VcExtension::getInstance()->inc("struct.php","Catalog");
        if (VcExtension::getInstance()->enabled("Catalog") && isset($rl_doReturnStruct)) return $CvStruct;
        
        // Передача данных шаблону вывода
        if ($CvInfo["style"])
            $CvPlayerStyle = $CvInfo["style"];
        else {
            $CvPlayerStyle = $vk_config['player_style'];
            $CvInfo["style"]="";
        }
        
        if (!preg_match("/^[A-Za-z0-9_-]+$/",$CvPlayerStyle)) exit("Hack attempt! Название стиля плеера '".htmlspecialchars($CvPlayerStyle, ENT_COMPAT, $config['charset'])."' содержит недопустимые символы!");
        
        // Продолжать работу по умолчанию
        $stopWork = false; 
        $CvBuffer = "";
        
        VcExtension::getInstance()->event('afterStruct');
        VcExtension::getInstance()->event('afterStruct2');
        if (!$stopWork) {
            if (isset($CvInfo) && $CvInfo["scount"]>0) {
                ob_start();
                include (ENGINE_DIR . "/inc/include/p_construct/players_style/{$CvPlayerStyle}.php");
                $CvBuffer = ob_get_clean();
            }
        }
        // Расширение каталога - возврат кода плеера
        if (VcExtension::getInstance()->enabled("Catalog") && $_GET["mod"] === "rational-catalog") return $CvBuffer;
        
        if ($CvBuffer!==''){
            // MOD TEST
            $file = ENGINE_DIR . '/inc/include/p_construct/mods/viewlater/afterStyle.php';
            if (file_exists($file))
                include_once ($file);
            
            VcExtension::getInstance()->event('beforeTemplateCompuillation');
            
            // Подключение плеера UPPOD
            if (VideoTubes::getInstance()->uppodUsed) {
                $CvBuffer = VideoTubes::getInstance()->uppodInitialization(). $CvBuffer;
            }
            
            $tpl->set_block( "'\\[video-constructor\\](.*?)\\[/video-constructor\\]'si", "$1" );
            $tpl->set( '{video-constructor}', $CvBuffer );
            $tpl->set_block( "'\\[is-video-constructor\\](.*?)\\[/is-video-constructor\\]'si", "$1" );
            $tpl->set_block( "'\\[no-video-constructor\\](.*?)\\[/no-video-constructor\\]'si", "" );
            
            VcExtension::getInstance()->event('afterTemplateCompuillation');
            
        } else {
            $tpl->set_block( "'\\[video-constructor\\](.*?)\\[/video-constructor\\]'si", "" );
            $tpl->set_block( "'\\[is-video-constructor\\](.*?)\\[/is-video-constructor\\]'si", "" );
            $tpl->set_block( "'\\[no-video-constructor\\](.*?)\\[/no-video-constructor\\]'si", "$1" );
        }
    } else {
        $tpl->set_block( "'\\[video-constructor\\](.*?)\\[/video-constructor\\]'si", "" );
        $tpl->set_block( "'\\[is-video-constructor\\](.*?)\\[/is-video-constructor\\]'si", "" );
        $tpl->set_block( "'\\[no-video-constructor\\](.*?)\\[/no-video-constructor\\]'si", "$1" );
    }
    
    // MOD TEST
    $file = ENGINE_DIR . '/inc/include/p_construct/mods/viewlater/always.php';
    if (file_exists($file))
        include_once ($file);
    
} else {
    $tpl->set_block( "'\\[video-constructor\\](.*?)\\[/video-constructor\\]'si", "" );
    $tpl->set_block( "'\\[is-video-constructor\\](.*?)\\[/is-video-constructor\\]'si", "" );
    $tpl->set_block( "'\\[no-video-constructor\\](.*?)\\[/no-video-constructor\\]'si", "$1" );
}

?>