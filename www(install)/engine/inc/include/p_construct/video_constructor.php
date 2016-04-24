<?php
/**
 * Скрипт: Конструктор видео v3.x для DLE
 * Назначение файла: Модуль зоны сайта
 * @author SeregaL <SeregaL2009@yandex.ru>
 * 
 * Скрипт сгенерирован специально для: {%loader(EMAIL)%}
 */

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

include_once (ENGINE_DIR . '/inc/include/p_construct/vc.lng.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/CurlResponse.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/CurlBrowser.php');
// CurlBrowser
$CurlBrowser = new CurlBrowser(
    array(
        'cookies_allow' => true,
        'CURLOPT_COOKIEJAR' => ENGINE_DIR . '/inc/include/p_construct/ddata/cookies.txt',
        'CURLOPT_COOKIEFILE' => ENGINE_DIR . '/inc/include/p_construct/ddata/cookies.txt'
    )
);
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VideoConstructor.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/config.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/constants.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/vkvideolib.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VideoTubes.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VcExtensionConfig.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VcExtension.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VcCache.php');
VcExtension::getInstance()->init();


// MOD TEST 
// /index.php?do=videoconstructor&useMod=viewlater
if (isset($_GET['useMod'])) {
    switch ($_GET['useMod']) {
        case 'viewlater':
            $modLang = include_once (ENGINE_DIR . '/inc/include/p_construct/mods/viewlater/lang.php');
            include_once (ENGINE_DIR . '/inc/include/p_construct/mods/viewlater/main.php');
            break;
        default:
            $useMod = $_GET['useMod'];
            $file_mod = ENGINE_DIR . '/inc/include/p_construct/ext/'.$useMod.'/main.php';
            if (preg_match('/^[-a-z0-9_]+$/i', $useMod) && file_exists($file_mod)) {
                include($file_mod);
            } else {
                header("HTTP/1.0 404 Not Found");
                echo 'Мод конструктора видео не найден! Проверьте правильность пути / установки';
                exit;
            }
            break;
    }
} else {


    if (!isset($_GET['action'])) $_GET['action'] = '';

    switch ($_GET['action']) {
        
        // Просмотр кода
        case 'preview_code':
            header('Content-Type: text/html; charset=utf-8');
            if (!vkv_check_access("base")) { exit("Module VideoConstructor - access denied!"); }
            $code = $_POST['code'];
            if ($code) {
                $html = VideoTubes::getInstance()->getPlayer($code, 'Предварительный просмотр');
                if ($config["charset"]=="windows-1251") {
                    $html = iconv("windows-1251","utf-8",$html);
                }
                exit($html);
            }
            exit('Unknown code.');
            break;
        

        /* Предварительная обработка кода */
        case 'prepare':
            header('Content-Type: text/html; charset='.$config["charset"]);
            if (!vkv_check_access("base")) {
                $resp = array ("error_text"=>"Module VideoConstructor - access denied!");
                exit(json_encode($resp));
            }

            $tube = $_POST["tube"];
            $func = intval($_POST["func"]);
            $str = $_POST["str"];
            if (get_magic_quotes_gpc()) $str = stripslashes ($str);

            if (preg_match("/^[a-z]{3}$/",$tube) && $func>0 && $str) {
                $res = VideoTubes::getInstance()->prepCode($tube, $func, $str);
                if ($res)
                    $player = VideoTubes::getInstance()->getPlayer($res);
                else $player = "";
                if ($config["charset"]=="windows-1251") {
                    $res = iconv("windows-1251","utf-8",$res);
                    $player = iconv("windows-1251","utf-8",$player);
                }
                $resp = array ("error_text"=>"", "code"=>$res, "player"=>$player);
            } else {
                $resp = array ("error_text"=>"VideoConstructor: POST data is not valid!");
            }
            exit(json_encode($resp));
            break;

        /* Отправка жалобы */
        case 'add_cmpl': // /index.php?do=videoconstructor&action=add_cmpl
            $zid = intval($_POST['zid']);
            $sid = intval($_POST['sid']);
            // Magic quotes
            if (get_magic_quotes_gpc())
                $_POST['text'] = stripslashes($_POST['text']);
            if ($config['charset']!=="utf-8") {
                $_POST['text'] = iconv ("UTF-8", "WINDOWS-1251", $_POST['text']);
            }
            $text = htmlspecialchars ( substr ( $_POST['text'],0,255), ENT_QUOTES, $config['charset'] ) ;
            //print_r ($_POST); exit;
            $login = null;
            if ($member_id['user_id']>0 && strlen($member_id['name'])>0) 
                $login = $member_id['name'];
            else if (isset($vk_config['complaint_guest']) && $vk_config['complaint_guest'])
                $login = "";

            if ($login!==NULL && $zid>0 && $sid>0) {

                if ($login==="") $login = $_SERVER['REMOTE_ADDR'];

                // Перевіряю на антиспам
                $sql = "SELECT id FROM `" . PREFIX . "_vidvk_c` WHERE time > '" . (time()-30) . "' AND user_name='".$db->safesql($member_id['name'])."'";
                if (!$row = $db->super_query($sql)) {
                    $sql = "INSERT INTO `" . PREFIX . "_vidvk_c` 
                        (`zid`, `sid`, `user_name`, `time`, `text`) VALUES 
                        ('{$zid}','{$sid}','".$db->safesql($login)."','".time()."','".$db->safesql($text)."')";
                    $res = $db->query($sql);
                    if ($res) 
                        echo 'OK';
                    else echo 'ERR';
                } else {
                    echo 'ANTIFLOOD';    
                }
            } else {
                echo 'AUTH';
            }
            exit;
        break;

        /** Перевірка відео */
        case 'check':
            // Проверка доступа
            if (!vkv_check_access("ext")) {
                $resp = array ("error_text"=>"Module VideoConstructor - access denied!", "err"=>"", "codetype"=>"");
                exit(json_encode($resp));
            }
            // Очистка ошибок (если надо)
            if (isset($_POST['resetdb']) && $_POST['resetdb']==1) {
                $db->query ("UPDATE `".PREFIX."_vidvk_s` SET err='0'");
                //$db->query ("UPDATE `".PREFIX."_vidvk_s` SET err='2' WHERE codetype='0'"); // Неизвестный tube
            }
            // Получаем строку
            $row = $_POST['what'];
            if ($config['charset']=='windows-1251') {
                $row = array_iconv ("utf-8", "windows-1251", $row);
            }
            // Проверка строки сериала
            if (isset($row['scode'])) {
                $codetype = VideoTubes::getInstance()->getTube($row['scode']);
                $err = VideoTubes::getInstance()->checkTube($row['scode'], $codetype);
                if ($err===false) $err = 51;
                $resp = array (
                    "error_text" => "",
                    "err" => $err,
                    "codetype" => $codetype,
                    'codetype_name' => VideoTubes::getInstance()->types[$codetype]['name'],
                );
            } else {
                $resp = array ("error_text"=>"Field `scode` not isset!", "err"=>5, "codetype"=>0);
            }
            //header('Content-Type: application/json');
            header('Content-Type: text/html; charset='.$config["charset"]);
            exit(json_encode($resp));
        break;

        // Збереження результатів перевірки
        case 'check-save':
            // Проверка доступа
            if (!vkv_check_access("ext")) { exit("Module VideoConstructor - access denied!"); }
            //echo 'check=saev post data:';print_r($_POST); //exit;
            $no_errors = true;
            if (isset($_POST['results']) && count($_POST['results'])) {
                $types = array();
                foreach ($_POST['results'] as $key => $row) {
                    $err = intval($row['err']);
                    $codetype = intval($row['codetype']);
                    $types[$err][$codetype][] = intval($row['sid']);
                }
                //print_r ($types); 
                // Выполняю запросы
                if (count($types)) {
                    foreach ($types as $err => $arr) {
                        if (count($arr)) {
                            foreach ($arr as $codetype => $list) {
                                $sql = "UPDATE `".PREFIX."_vidvk_s` SET 
                                    err='".intval($err)."', 
                                    codetype='".intval($codetype)."' WHERE id IN ('".implode("','",$list)."')";
                                if (!$db->query($sql)) $no_errors = false;
                            }
                        }
                    }
                }
            }
            if ($no_errors) echo 'OK'; else echo 'ERRORS';
            // Дата паследнего сохранения
            file_put_contents (ENGINE_DIR . '/inc/include/p_construct/ddata/lastcheck.txt', time());
            exit;
        break;

        // Проверка видео на лету
        case 'check_quick': // /index.php?do=videoconstructor&action=check_quick
            // Проверка доступа
            if (!vkv_check_access("base")) { exit("Module VideoConstructor - access denied!"); }

            $code = htmlspecialchars_decode($_POST['code']); 
            if ($code) {
                $err = VideoTubes::getInstance()->checkTube($code);
                $max_q = VideoTubes::getInstance()->max_quality;
                if ($err===0) {
                    echo "OK";
                } else {
                    if ($err===2 || $err===4 || $err===51 || $err===53) {
                        echo "OK?";
                    } else
                        echo isset(VideoTubes::getInstance()->check_errors[$err]) ? VideoTubes::getInstance()->check_errors[$err] : "Неизвестная ошибка №2.";
                }
                echo "||{$max_q}";
            } else 
                echo "Код не указан!";
            header("Content-Type: text/html; charset=".$config["charset"]);
            exit;
            break;

        /** Жалобы на серию - повернуть информацию */
        case 'show_cpl_data': // /index.php?do=videoconstructor&action=show_cpl_data&sid=127
            // Проверка доступа
            if (vkv_check_access("base")) {
                $sid = $_GET["sid"];
                if ($sid) {
                    $sql = "SELECT id,user_name,time,text FROM `".PREFIX."_vidvk_c` WHERE sid='{$sid}'";
                    $rows = $db->super_query($sql, true);
                    if ($rows) {
                        foreach ($rows as &$row) {
                            $row["time"] = date("Y.m.d H:i",$row["time"]);
                        }
                    }
                    $resp = array ("response"=>"OK","error_text"=>"","data"=>$rows);
                } else 
                    $resp = array ("response"=>"OK","error_text"=>"Sid указан не верно","data"=>"");
            } else 
                $resp = array ("response"=>"OK","error_text"=>"Module VideoConstructor - access denied!","data"=>array());
            if ($config["charset"]!="utf-8") $resp = array_iconv("WINDOWS-1251", "UTF-8", $resp);
            header('Content-Type: application/json');
            exit(json_encode($resp));
        break;

        // Проверка версии
        case "check_version": // /index.php?do=videoconstructor&action=check_version
            echo "проверка версии отключена"; exit; // todo: заглушка
            if (vkv_check_access("base")) {
                $url = "http://ralode.com/konstruktor-video-v3.x/check_version";
                $res = $CurlBrowser->request( $url, 'get');
                $html = $res->content();
                if (strlen($html)>15) 
                    $html = "n/a (timeout)";
                else
                    $html = preg_replace("/[^0-9.]/","",$html);
                echo $html;
            } else {
                echo "Ошибка";
            }
            exit;
    }

    exit;
}
