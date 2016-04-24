<?php

/**
 * 
 * Запуск скрипта в консоли или CRON:
 * > cd full_path_to_site/engine/inc/include/p_construct && php cron.php license_key [delay]
 * , где license_key - ваш лиц. ключ, указанный в настройках констуктора, delay (стандарт. 1 сек) - задержка
 * 
 * Например:
 * > cd full_path_to_site/engine/inc/include/p_construct && php cron.php demodemodemodemodemo
 * 
 * или (для задержки в 2 сек. между запросами):
 * > cd full_path_to_site/engine/inc/include/p_construct && php cron.php demodemodemodemodemo 2
 * 
 */

if (!defined('E_DEPRECATED')) {
    @error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
    @ini_set('error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE);
} else {
    @error_reporting(E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE);
    @ini_set('error_reporting', E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE);
}

@ini_set('display_errors', true);
@ini_set('html_errors', false);

define('DATALIFEENGINE', true);

$member_id = FALSE;
$is_logged = FALSE;

define('ROOT_DIR', str_replace(DIRECTORY_SEPARATOR.'engine'.DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'p_construct', '', dirname(__FILE__)));
define('ENGINE_DIR', ROOT_DIR . '/engine');

require_once ROOT_DIR . '/engine/init.php';


include_once (ENGINE_DIR . '/inc/include/p_construct/config.php');
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
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VcCache.php');

if (!isset($argv[1]))
    exit('Secret not set in args!');
if ($vk_config['user_secret']!=$argv[1])
    exit('Secret is invalid!');
$_delay = isset($argv[2]) ? intval($argv[2]) : 1;

// Очистка ошибок
$db->query ("UPDATE `".PREFIX."_vidvk_s` SET err='0'");


$rows = $db->super_query( "SELECT ".PREFIX."_vidvk_z.id,".PREFIX."_post.id AS post_id FROM ".PREFIX."_vidvk_z JOIN ".PREFIX."_post ON ".PREFIX."_vidvk_z.post_id=".PREFIX."_post.id", true);
$zs = array();
$posts = array();
if ($rows) {
    foreach ($rows as $rowx) {
        $posts[$rowx["id"]] = $rowx["post_id"];
        $zs[$rowx["id"]] = 1;
    }
    unset($rows);
}

$rows = $db->super_query( "SELECT parent, id, sname, scode, codetype FROM ".PREFIX."_vidvk_s WHERE scode<>''", true);
$videos_all = array();
foreach ($rows as $keyx => $rowx)
    if ($zs[$rowx["parent"]])
        $videos_all[] = $rowx;
unset($rows);
$begin_codetype_0 = count($videos_all); // кількість фільмів

//if ($config['charset']=='windows-1251') {
//    $videos_all = array_iconv ("windows-1251", "utf-8", $videos_all);
//}

$errorsX = 0;

if ($videos_all) {
    foreach ($videos_all as $row) {
        // Проверка строки сериала
        if (isset($row['scode'])) {
            $codetype = VideoTubes::getInstance()->getTube($row['scode']);
            $err = VideoTubes::getInstance()->checkTube($row['scode'], $codetype);
            if ($err===false) $err = 51;
            if ($err!==0) {
                $sql = "UPDATE `".PREFIX."_vidvk_s` SET err='".intval($err)."', codetype='".intval($codetype)."' WHERE id ='".$row['id']."'";
                //echo "{$sql}\n";
                $res = $db->query($sql);
                if (!$res) $errorsX++;
            }
        }
        sleep($_delay);
    }
}

if ($errorsX>0)
    exit('Check finished - ERRORS '.$errorsX);
else
    exit('Check finished - OK');