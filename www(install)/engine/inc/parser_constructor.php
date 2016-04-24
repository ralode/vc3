<?php
/**
 * Скрипт: Конструктор видео v3.x для DLE
 * Назначение файла: Админка скрипта
 * @author SeregaL <SeregaL2009@yandex.ru>
 * 
 * Скрипт сгенерирован специально для: {%loader(EMAIL)%}
 */

// Разрешено входить: Администраторы, Главные редакторы, Журналисты
if(!($member_id['user_group']<=3)) {
    msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

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
VideoConstructor::getInstance()->init();
include_once (ENGINE_DIR . '/inc/include/p_construct/constants.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/vkvideolib.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VideoTubes.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VcExtensionConfig.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VcExtension.php');
VcExtension::getInstance()->init();

// Редіректи на редагування жалоби
if (isset($_GET['sec']) && $_GET['sec']=='go_complaint'){
    $sid = isset($_GET['sid']) ? intval($_GET['sid']) : 0;
    $zid = isset($_GET['zid']) ? intval($_GET['zid']) : 0;
    if ($sid>0 || $zid>0) {
        if ($zid==0) {
            $row = $db->super_query( 'SELECT parent FROM ' . PREFIX . "_vidvk_s WHERE id = '$sid' LIMIT 1" );
            $zid = intval($row['parent']);
        }
        $row = $db->super_query( 'SELECT post_id FROM ' . PREFIX . "_vidvk_z WHERE id = '$zid'" );
        if ($row['post_id']>0) {
            header("Location: {$config['http_home_url']}{$config['admin_path']}?mod=editnews&action=editnews&id={$row['post_id']}#Vc". ($sid>0 ? "s{$sid}" : "z{$zid}" ) );
        } else {
            echo "<b style='color:red'>Error - new's ID is empty!</b>";
        }
    }
    exit();
}

// Масове видалення жалоб, результатов проверки
if (isset($_GET['sec']) && $_GET['sec']=='massdelete'){ 
    $idlist = $_POST['idlist'];
    $arrx = explode (',', $idlist);
    if ($arrx) {
        $del_ids = '';
        foreach ($arrx as $key => $val) {
            $val = intval ($val);
            if ($val>0) 
                if ($del_ids=='') $del_ids = "'{$val}'"; else $del_ids .= ",'{$val}'";
        }
        if ($_GET['what']=='errors') {
            // Удаление результатов проверки
            // ?mod=parser_constructor&sec=massdelete&what=errors
            $sql = 'UPDATE ' .PREFIX. "_vidvk_s SET err='0' WHERE id IN (".$del_ids.")";
        } else {
            // Удаление жалоб
            // ?mod=parser_constructor&sec=massdelete
            if (!vkv_check_access("ext")) {
                // Модераторы: помечаем как обработанные
                $sql = 'UPDATE ' .PREFIX. "_vidvk_c SET status='1' WHERE id IN (".$del_ids.")";
            } else {
                // Администрирование: удаляем
                $sql = 'DELETE FROM ' .PREFIX. "_vidvk_c WHERE id IN (".$del_ids.")";
            }
        }
        $db->query ($sql);
        exit("OK");
    } else {
        exit ("Error: idlist is empty!");
    }
}


# АДМІНКА
if (!vkv_check_access("base")) { exit("Module VideoConstructor - access denied!"); }

echoheader( '', '' );

// Раздел в админке
if (!isset($_GET['sub']) || $_GET['sub']=='') $_GET['sub']='info';

// Системні константи
$vk_sys_const = array();
// Субмодулі (розділи адмінки)
$vk_sys_const['sub'] = array (
    'info' => $vclang["h_info"],
    'check' => 'Проверка',
    'checked_films' => 'Результаты',
    'complaints' => $vclang["h_complaints"],
    'properties' => $vclang["h_properties"],
    'extensions' => $vclang["h_extensions"],
    'help' => $vclang["h_help"]
);

$otherPages = array(
    'server_check' => 'Проверка / активация',
);

?>
<link rel="stylesheet" type="text/css" href="<?php echo $config['http_home_url']; ?>engine/inc/include/p_construct/styles.css">
<?php if ($config['version_id']<=10.1) { ?>
<link rel="stylesheet" type="text/css" href="<?php echo $config['http_home_url']; ?>engine/inc/include/p_construct/styles_oldDle.css">
<?php } ?>
<script src="<?php echo $config['http_home_url']; ?>engine/classes/js/jquery.json.js"></script>
<script language="javascript" type="text/javascript">
var admin_path = '<?php echo $config['admin_path']; ?>'; // Путь к админке дле
</script>
<script language="javascript" type="text/javascript" src="<?php echo $config['http_home_url'];?>engine/inc/include/p_construct/admin.js"></script>

<div class="box boxRational">
    <div class="box-content">
        <div class="row box-section">
            <div class="action-nav-normal action-nav-line">
                <div class="row action-nav-row">
                    <?php
                    foreach ($vk_sys_const['sub'] as $key => $val) {
                    ?>
                    <div class="col-sm-1 action-nav-button <?php if ($_GET['sub'] == $key) echo 'btn-active'; ?>" style="min-width:90px;">
                        <a href="<?php echo "{$config['http_home_url']}{$config['admin_path']}?mod=parser_constructor&sub={$key}"; ?>" class="tip" title="<?php echo $vclang["h_{$key}"]; ?>">
                            <span><?php echo $val; ?></span>
                        </a>
                    </div>
                    <?php
                    }
                    ?>
                </div> 
            </div>

        </div>
    </div>
</div>

<div class="box">
<div class="box-header">
    <div class="title"><?php echo isset($otherPages["{$_GET['sub']}"]) ? $otherPages["{$_GET['sub']}"] : $vk_sys_const['sub']["{$_GET['sub']}"]; ?></div>
</div>
<div class="box-content">
<?php


// Информация    
if ($_GET['sub']=='info') {
    
    $start_timer = microtime(); $start_timer = explode(" ",$start_timer); $start_timer = $start_timer[1] + $start_timer[0];
    // Збір статистики
    $vkstat = array();
    // Текуча версія
    $vkstat['this_ver'] = VideoConstructor::getInstance()->version('ver') . '.'.
                          VideoConstructor::getInstance()->version('build');
    // Актуальна версія
    $vkstat['act_ver'] = VideoConstructor::getInstance()->actualVersion();
   
    // Статус кеша
    $vkstat['cache_status'] = "<span style='color:green;'>{$vclang['turned_on']}</span>";
    
    // Кількість зборок
    $res = $db->query( "SELECT COUNT(*) AS count FROM ".PREFIX."_vidvk_z");
    if ($res) {
        $row = $db->get_array($res);
        $vk_config['z_count'] = intval($row['count']);
    } else $vk_config['z_count'] = 0;
    // Кількість серій
    $rows = $db->super_query( "SELECT codetype, COUNT(*) AS count FROM ".PREFIX."_vidvk_s GROUP BY `codetype`", true);
    // Стандартный список плееров если даже нет ни одной серии
    $arr_t = VideoTubes::getInstance()->types;
    $vkstat['players_tr'] = array();
    foreach ($arr_t as $key_t => $val_t) {
        //print_r ($val_t);
        $vkstat['players_tr'][$key_t] = array ("key"=>$val_t["name_html"], "val"=>0, "url"=>$val_t["url"]);
    }
    //print_r ($vkstat['players_tr']); exit;
    $vkstat['codetype_0'] = 0;
    $vkstat['count_s'] = 0;
    if (count($rows)) {
        foreach ($rows as $key => $arr) {
            if (isset($vkstat['players_tr'][$arr['codetype']]["val"]))
                $vkstat['players_tr'][$arr['codetype']]["val"] = $arr['count'];
            $vkstat['count_s'] += $arr['count'];
        }
    }
    //print_r($vkstat['players_tr']); exit;
    // Кількість серій з помилками
    $res = $db->query( "SELECT COUNT(*) AS count FROM ".PREFIX."_vidvk_s WHERE err>0");
    if ($res) {
        $row = $db->get_array($res);
        $vk_config['err_s_count'] = intval($row['count']);
        if ($vk_config['err_s_count']==0) $vk_config['err_s_count'] = '<font color="green">0</font>'; else $vk_config['err_s_count'] = '<font color="red">'.$vk_config['err_s_count'].'</font>';
    } else $vk_config['err_s_count'] = 0;
    // Остання перевірка
    $last_check = intval(file_get_contents (ENGINE_DIR . '/inc/include/p_construct/ddata/lastcheck.txt'));
    if ($last_check==0) {
        $vk_config['last_check'] = "<font color='red'>{$vclang['was_not']}</font>";
    } else {
        $tmp = intval(time()-$last_check);
        $tmp = intval($tmp/86400);
        if ($tmp>30) {
            $vk_config['last_check'] = '<font color="red">'.$tmp.' '.$vclang['days_ago'].'</font>';
        } else {
            $vk_config['last_check'] = '<font color="green">'.$tmp.' '.$vclang['days_ago'].'</font>';
        }
    }
        
    $end_timer = microtime(); $end_timer = explode(" ",$end_timer); $end_timer = $end_timer[1] + $end_timer[0];
    $totaltime_timer = $end_timer - $start_timer;
    ?>

    <table width="800" id="newslist" border="0" class="table table-normal table-hover">
            <tr class="thead">
                <th style="padding-left:2px;padding-right:2px; color: #1B6BA0; font-weight: bold;" colspan="2">&nbsp;<?php echo $vclang["info_about_program"]; ?>&nbsp;</th>
                <th style="padding-left:2px;padding-right:2px; color: #1B6BA0; font-weight: bold; border-left: 2px solid silver;" colspan="2">&nbsp;<?php echo $vclang["clear_cache"]; ?>&nbsp;</th>
            </tr>
            <tr class="tfoot"><td colspan="4">
                    <div class="hr_line"></div>
                </td>
            <tr>
                <td style="padding:4px; text-align: right; color: #5C6F78; width: 150px;"><?php echo $vclang["current_version"]; ?>:</td>
                <td style="width: 300px;"><?php echo $vkstat['this_ver']; ?></td>
                <td style="padding:4px; text-align: right; color: #5C6F78; border-left: 2px solid silver; width: 150px;"><?php echo $vclang["z_cache"]; ?>:</td>
                <td style="width: 200px;"><?php echo $vkstat['cache_status']; ?></td>
            </tr>
            <tr>
                <td style="padding:4px; text-align: right; color: #5C6F78;"><?php echo $vclang["actual_version"]; ?>:</td>
                <td><?php echo $vkstat['act_ver']; ?></td>
                <td style="padding:4px; text-align: right; color: #5C6F78; border-left: 2px solid silver;">Тип кеша:</td>
                <td>DLE API</td>
            </tr>
            <tr>
                <td style="padding:4px; text-align: right; color: #5C6F78;">Дицензионный ключ:</td>
                <td><?php if (vkv_check_access("ext")) echo $vk_config['user_secret'];
    else echo "<span style=\"color:gray\">скрыто</span>"; ?></td>
                <td style="padding:4px; text-align: right; color: #5C6F78; border-left: 2px solid silver;">&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td style="padding:4px; text-align: right; color: #5C6F78;"><?php echo $vclang["search_server"]; ?>:</td>
                <td><?php echo 'https://'.$vk_config['vkv_server'].':8802'; ?> (<a href="<?php echo "{$config['http_home_url']}{$config['admin_path']}"; ?>?mod=parser_constructor&sub=server_check" style="color:#006699;"><?php echo $vclang["do_serv_check"]; ?></a>)</td>
                <td style="border-left: 2px solid silver;">&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        </table>

        <br /><br /><div class="hr_line"></div>

        <table width="800" id="newslist" border="0" class="table table-normal table-hover">
            <tr class="thead">
                <th style="padding-left:2px;padding-right:2px; color: #1B6BA0; font-weight: bold;" colspan="2">&nbsp;<?php echo $vclang["stat_by_video"]; ?>&nbsp;</th>
                <th style="padding-left:2px;padding-right:2px; color: #1B6BA0; font-weight: bold; border-left: 2px solid silver;" colspan="2">&nbsp;<?php echo $vclang["stat_by_players"]; ?>&nbsp;</th>
            </tr>
            <tr class="tfoot"><td colspan="4">
                    <div class="hr_line"></div>
                </td>
            <tr>
                <td style="padding:4px; text-align: right; color: #5C6F78; width: 150px;"><?php echo $vclang["all_z_c"]; ?>:</td>
                <td style="width: 300px;"><?php echo $vk_config['z_count']; ?></td>
                <td style="padding:4px; text-align: right; color: #5C6F78; border-left: 2px solid silver; width: 150px;"><a href="<?php echo VideoTubes::getInstance()->types[1]["url"]; ?>" target="_blank"><?php echo (isset($vkstat['players_tr'][1]) ? $vkstat['players_tr'][1]['key'] . ':' : ''); ?></a></td>
                <td style="width: 200px;"><?php echo (isset($vkstat['players_tr'][1]) ? $vkstat['players_tr'][1]['val'] : '&nbsp;'); ?></td>
            </tr>
            <tr>
                <td style="padding:4px; text-align: right; color: #5C6F78;"><?php echo $vclang["all_s_c"]; ?>:</td>
                <td><?php echo $vkstat['count_s']; ?></td>
                <td style="padding:4px; text-align: right; color: #5C6F78; border-left: 2px solid silver;"><a href="<?php echo VideoTubes::getInstance()->types[2]["url"]; ?>" target="_blank"><?php echo (isset($vkstat['players_tr'][2]) ? $vkstat['players_tr'][2]['key'] . ':' : '&nbsp;'); ?></a></td>
                <td><?php echo (isset($vkstat['players_tr'][2]) ? $vkstat['players_tr'][2]['val'] : '&nbsp;'); ?></td>
            </tr>
            <tr>
                <td style="padding:4px; text-align: right; color: #5C6F78;"><?php echo $vclang["with_errors"]; ?>:</td>
                <td><?php echo $vk_config['err_s_count']; ?></td>
                <td style="padding:4px; text-align: right; color: #5C6F78; border-left: 2px solid silver;"><a href="<?php echo VideoTubes::getInstance()->types[3]["url"]; ?>" target="_blank"><?php echo (isset($vkstat['players_tr'][3]) ? $vkstat['players_tr'][3]['key'] . ':' : '&nbsp;'); ?></a></td>
                <td><?php echo (isset($vkstat['players_tr'][3]) ? $vkstat['players_tr'][3]['val'] : '&nbsp;'); ?></td>
            </tr>
            <tr>
                <td style="padding:4px; text-align: right; color: #5C6F78;"><?php echo $vclang["uncnown_tube"]; ?>:</td>
                <td><?php echo $vkstat['codetype_0']; ?></td>
                <td style="padding:4px; text-align: right; color: #5C6F78; border-left: 2px solid silver;"><a href="<?php echo VideoTubes::getInstance()->types[4]["url"]; ?>" target="_blank"><?php echo (isset($vkstat['players_tr'][4]) ? $vkstat['players_tr'][4]['key'] . ':' : '&nbsp;'); ?></a></td>
                <td><?php echo (isset($vkstat['players_tr'][4]) ? $vkstat['players_tr'][4]['val'] : '&nbsp;'); ?></td>
            </tr>
            <tr>
                <td style="padding:4px; text-align: right; color: #5C6F78;"><?php echo $vclang["last_check"]; ?>:</td>
                <td><?php echo $vk_config['last_check']; ?></td>
                <td style="padding:4px; text-align: right; color: #5C6F78; border-left: 2px solid silver;"><a href="<?php echo VideoTubes::getInstance()->types[5]["url"]; ?>" target="_blank"><?php echo (isset($vkstat['players_tr'][5]) ? $vkstat['players_tr'][5]['key'] . ':' : '&nbsp;'); ?></a></td>
                <td><?php echo (isset($vkstat['players_tr'][5]) ? $vkstat['players_tr'][5]['val'] : '&nbsp;'); ?></td>
            </tr>
            <?php
            // Остальные плеера
            if (count($vkstat['players_tr']) > 5) {
                foreach ($vkstat['players_tr'] as $key => $arr) {
                    if ($key > 5 && !isset($arr["alias_id"]))
                        echo '<tr>
                    <td style="padding:4px; text-align: right; color: #5C6F78;">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="padding:4px; text-align: right; color: #5C6F78; border-left: 2px solid silver;"><a href="' . $arr['url'] . '" target="_blank">' . $arr['key'] . '</a></td>
                    <td>' . $arr['val'] . '</td>
                </tr>';
                };
            };
            ?>
        </table>
    <div class="hr_line"></div>
    <?php
    printf ("<div style='color:gray' align='right'>{$vclang['stat_get_time']}: %f s.</div>", $totaltime_timer); 
} else // // Информация   

// Проверка    
if ($_GET['sub']=='server_check') {
    md5($vk_config['user_secret']);
    ?>
<div class="box-content">
        <div class="row box-section">
            <p id="check_status"><span class="red">Скрипт JS еще не запустил проверку соединения с сервером... Если вы видите
            эту ошибку, и она не пропадает, значит у вас проблемма с JS.</span></p>
            <table class="table table-normal">
                <tr>
                    <td>Лицензионный ключ:</td>
                    <td id="check_lic" class="bold">-</td>
                </tr>
                <tr>
                    <td>Домен:</td>
                    <td id="check_domain" class="bold">-</td>
                </tr>
                <tr>
                    <td>Срок действия лицензии:</td>
                    <td id="check_licDays" class="bold">-</td>
                </tr>
                <tr>
                    <td>Авторизация Вконтакте:</td>
                    <td id="check_vkAuth" class="bold">-</td>
                </tr>
            </table>
        </div>
    </div>
    <script src="<?php echo $config['http_home_url']; ?>engine/inc/include/p_construct/editor/KonstructorApi.js"></script>
    <script type="text/javascript">
        $(function(){
            var kApi = new KonstructorApi(<?php echo json_encode(VideoConstructor::getInstance()->getConfig(true)); ?>);
            $('#check_status').html('<span class="gray">Идет запрос к серверу API...</span>');
            kApi.get('/check', {}, function(err, data){
                if (err) {
                    console.err('Error: ', err);
                    $('#check_status').html('<span class="red">'+err+'</span>');
                    $('#check_lic').html('Валидный').addClass('green');
                } else {
                    if (data.errorAccessDenied) {
                        $('#check_lic').html('Не найден').addClass('red');
                    } else {
                        $('#check_lic').html('Валидный').addClass('green');
                        if (data.domain) 
                            $('#check_domain').html('OK').addClass('green');
                        else
                            $('#check_domain').html('Не разрешен').addClass('red');
                        if (data.date>0) 
                            $('#check_licDays').html(parseInt(data.date)+' дней').addClass('green');
                        else
                            $('#check_licDays').html(parseInt(data.date)+' дней').addClass('red');
                        if (data.vk=='public') 
                            $('#check_vkAuth').html('Общий').addClass('green');
                        else if (data)
                            $('#check_vkAuth').html('OK').addClass('green');
                        else
                            $('#check_vkAuth').html('Не авторизован').addClass('red');
                        $('#check_status').html('Проверка закончена.');
                        //$('#check_status').html('<pre class="green">'+JSON.stringify(data)+'</pre>');
                    }
                }
            });
        });
    </script>
<?php
    exit;
} else // Проверка

// Проверка фильмов    
if ($_GET['sub']=='check') {
if (!vkv_check_access("ext")) { exit("Module VideoConstructor - access denied!"); }

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
        $videos_all[] = array_merge (array( 'post_id' => $posts[$rowx['parent']] ), $rowx);
unset($rows);

$begin_codetype_0 = count($videos_all); // кількість фільмів

if ($config['charset']=='windows-1251') {
    $videos_all = array_iconv ("windows-1251", "utf-8", $videos_all);
}
$videos_all = json_encode($videos_all);
if ($videos_all===false) $videos_all = '[]';

?>

<table width="100%" cellpadding="5" cellspacing="5">
<tr><td width="500" style="border: 1px solid #7C7C7C; vertical-align: top;">
    
    <div style="padding: 20px; text-align: center;" id="vc_video_checker">
        <input type="hidden" name="videos_all_c" value="<?php echo $videos_all_c; ?>" />
        <input type="hidden" name="begin_codetype_0" value="<?php echo $begin_codetype_0; ?>" />
        <?php echo $vclang['Delay']; ?>: <input type="text" value="100" size="8" maxlength="8" id="vChecker_delay" class="edit" />&nbsp;&nbsp;&nbsp;
        <?php echo $vclang['Jump_videos']; ?>: <input type="text" value="0" size="8" maxlength="8" id="vChecker_offset" class="edit" /><br />
        <input type="checkbox" name="reset_db" value="on" checked="checked" id="vChecker_resetdb" /> <?php echo $vclang['clear_last_results']; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br />
        <input type="button" value="<?php echo $vclang['Start']; ?>" class="edit" id="vChecker_start" />
    </div>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="5">
        <tr>
            <td style="padding:4px; text-align: right;"><?php echo $vclang['without_errors']; ?>:</td>
            <td style="padding:4px; color:green; font-weight:bold; width:40px;" id="chk_error_0">-</td>
            <td style="padding:4px; text-align: right;"><?php echo $vclang['Deleted_s']; ?>:</td>
            <td style="padding:4px; color:red; font-weight:bold; width:40px;" id="chk_error_1">-</td>
            <td style="padding:4px; text-align: right;"><?php echo $vclang['Unknovn_tubes']; ?>:</td>
            <td style="padding:4px; color:red; font-weight:bold; width:40px;" id="chk_error_2">-</td>
        </tr>
        <tr>
            <td style="padding:4px; text-align: right;"><?php echo $vclang['Start_errors']; ?>:</td>
            <td style="padding:4px; color:red; font-weight:bold; width:40px;" id="chk_error_3">-</td>
            <td style="padding:4px; text-align: right;" title="<?php echo $vclang['check_tube_cant_long']; ?>"><?php echo $vclang['check_tube_cant_short']; ?>:</td>
            <td style="padding:4px; color:red; font-weight:bold; width:40px;" id="chk_error_4">-</td>
            <td style="padding:4px; text-align: right;" title="<?php echo $vclang['php_js_err_long']; ?>"><?php echo $vclang['php_js_err_short']; ?>:</td>
            <td style="padding:4px; color:red; font-weight:bold; width:40px;" id="chk_error_5">-</td>
        </tr>
    </table>
    <div align="center" style="width: 100%;">
        <div style="width:500px;height:15px;background-image:url('/engine/inc/include/p_construct/img/progress-3-gray.gif');margin:0;padding:0;text-align:left;">
            <img id="progress-3" src="/engine/inc/include/p_construct/img/progress-3-green.gif" height="15" width="0" />
        </div>
    </div>
    <div align="right" style="font-weight: bold; color: #0A2547;"><span id="chk_position">-</span>/<span id="chk_count">-</span>&nbsp;&nbsp;<span id="chk_percent">-</span>%&nbsp;&nbsp;</div>
</td><td style="border: 1px solid #7C7C7C; padding:4px; padding-left: 10px; vertical-align: top;">
<div id="chk_log" style="width:100%; height: 350px; overflow: scroll;">
</div>
</td></tr></table>

<script language="javascript" type="text/javascript">
    
    var vChecker = vidosCheckerCreator('vChecker', <?php echo $videos_all; ?>);
    
    // Ініціалізація перевірки
    $(function(){
        var html = '<p><?php echo $vclang['Searched']; ?> <b>'+vChecker.count+'</b> <?php echo $vclang['films_for_check']; ?>';
        vChecker.displayMessage (html+'. <br /><?php echo $vclang['set_delay_and_start']; ?>.</p>');
        vChecker.refreshStatistics();
        
        /** Натиснення "Старт" */
        $('#vChecker_start').click(function(){
            $(this).attr("disabled", true);
            var delay_ms = parseInt ($('#vChecker_delay').val());
            if (isNaN(delay_ms)) delay_ms = 0;
            if (delay_ms<50) {delay_ms = 50;}
            $('#vChecker_delay').val(delay_ms);
            var voffset = parseInt ($('#vChecker_offset').val());
            if (isNaN(voffset)) voffset = 0;
            if (voffset<0) {voffset = 0;}
            $('#vChecker_offset').val(voffset);
            if (voffset>=0 && voffset<vChecker.count){
                vChecker.pos = voffset;
                if (vChecker.count>0) {
                    vChecker.checkNext();
                    vChecker.displayMessage('<b><?php echo $vclang['Start']; ?></b> '+(voffset>0?'(<?php echo $vclang['jump_video']; ?>: '+voffset+')':''));
                }
            } else {
                alert('<?php echo $vclang['offset_bad']; ?>');
                $(this).attr("disabled", false);
            }
            return false;
        });
        
        // Якщо нема відео для перевірки
        if (vChecker.count==0) {
            $('#vChecker_start').attr("disabled", true);
        }
    });
    
</script>
<?php
} // // Проверка фильмов 


// Результаты проверки    
if ($_GET['sub']=='checked_films') {
    
    if (isset($_GET['page'])) $page = intval ($_GET['page']); else $page = 0; // Сторінка
    if ($page < 1) $page = 1;
    
    $where1 = isset($_GET['show_all']) ? '(err>0)' : '(err>0 AND err<>4 AND err<>5)';
    
    $sql = "SELECT COUNT(*) AS `cnt` FROM `".PREFIX."_vidvk_s` JOIN `".PREFIX."_vidvk_z` ON ( {$where1} AND ".PREFIX."_vidvk_s.parent=".PREFIX."_vidvk_z.id AND ".PREFIX."_vidvk_s.scode<>'') JOIN `".PREFIX."_post` ON ".PREFIX."_vidvk_z.post_id=".PREFIX."_post.id";
    $count = $db->super_query($sql);
    $count = $count['cnt'];
    
    // Шукаю всі фільми з помилками
    $sql = "SELECT ".PREFIX."_vidvk_s.id AS sid, sname, scode, err, codetype, ".PREFIX."_vidvk_z.id AS zid, ".PREFIX."_vidvk_z.name AS zname, post_id, ".PREFIX."_post.title AS post_title FROM `".PREFIX."_vidvk_s` JOIN `".PREFIX."_vidvk_z` ON ( {$where1} AND ".PREFIX."_vidvk_s.parent=".PREFIX."_vidvk_z.id) JOIN `".PREFIX."_post` ON ".PREFIX."_vidvk_z.post_id=".PREFIX."_post.id";
    $res = $db->query($sql);
?>
<div align="right" class="errors-all-text">Ошибки, связанные с невозможностью проверки тюба <a <?php if(!isset($_GET["show_all"])) echo 'class="bt-active"'; ?> href="/<?php echo $config["admin_path"]; ?>?mod=parser_constructor&sub=checked_films">скрыты</a> <a <?php if(isset($_GET["show_all"])) echo 'class="bt-active"'; ?> href="/<?php echo $config["admin_path"]; ?>?mod=parser_constructor&sub=checked_films&show_all">отображены</a></div>
<table width="100%" id="vc_cpl_list" border="0">
    <tr class="thead">
        <th width="200">&nbsp;<?php echo $vclang['Post']; ?>&nbsp;</th>
        <th width="200" align="center"><?php echo $vclang['Package']; ?></th>
        <th width="180" align="center"><?php echo $vclang['Serie']; ?></th>
        <th width="130"><?php echo $vclang['Error']; ?></th>
        <th width="30"><?php echo $vclang['Player']; ?></th>
        <th width="50"><?php echo $vclang['Action']; ?></th>
        <th width="10" align="center"><input type="checkbox" title="<?php echo $vclang['Check_all']; ?>" id="checkbox_mass" /></th>
    </tr>
    <tr class="tfoot"><th colspan="7"><div class="hr_line"></div></td></th>
<?php
    while ( $row = $db->get_row( $res ) ) {
        //print_r ($row); 
        $sid = intval($row['sid']);
        $sname = htmlspecialchars($row['sname'], ENT_COMPAT, $config['charset']);
        $sname_short = htmlspecialchars(substr($row['sname'],0,70), ENT_COMPAT, $config['charset']);
        $scode = $row['scode'];
        $err = intval($row['err']);
        $err_text = VideoTubes::getInstance()->getCheckErrorName($err, true);
        $codetype = intval($row['codetype']);
        $codetype_text = VideoTubes::getInstance()->getCodeName(null, $row['codetype'], true);
        $zid = intval($row['zid']);
        $zname = htmlspecialchars($row['zname'], ENT_COMPAT, $config['charset']);
        $zname_short = htmlspecialchars(substr($row['zname'],0,70), ENT_COMPAT, $config['charset']);
        $post_id = intval($row['post_id']);
        $post_title_short = htmlspecialchars(substr($row['post_title'],0,70), ENT_COMPAT, $config['charset']);
        $row['post_title'] = htmlspecialchars($row['post_title'], ENT_COMPAT, $config['charset']);
        if ($err>0)
            $edit_text = '<input type="button" class="edit vc_do_delete" value="х" data-sid="'.$sid.'" title="Удалить" />';
        else 
            $edit_text = '-';
        $href= "{$config['http_home_url']}{$config['admin_path']}?mod=editnews&action=editnews&id={$row['post_id']}";
echo<<<DELIM
        <tr id="vc_error_{$sid}">
            <td><a href="{$href}" title="{$row['post_title']}">$post_title_short</a></td>
            <td><a href="{$href}#Vcz{$zid}" title="$zname">$zname_short</a></td>
            <td><a href="{$href}#Vcs{$sid}" title="$sname">$sname_short</a></td>
            <td>{$err_text}</td>
            <td>{$codetype_text}</td>
            <td>{$edit_text}</td>
            <td><input name="selected_complaints[]" value="{$sid}" type='checkbox' class="checkbox_mass" /></td>
        </tr>
        <tr><td background="engine/skins/images/mline.gif" height="1" colspan="7" class="no_border"></td></tr>
DELIM;
    }
?>
    <tr class="tfoot"><td colspan="7"><div class="hr_line"></div></td></tr>
    <tr class="tfoot"><td colspan="7">
        <div align="right" style="padding-right:40px;">
            <select size="1" style="width:160px;" class="edit action_mass_com" id="do_action">
        	<option value="">-- <?php echo $vclang['Action']; ?> --</option>
                <option value="delete">&quot;<?php echo $vclang['Without_errors']; ?>&quot;</option>
            </select>
            <input class="edit do_action_mass" type="button" value="<?php echo $vclang['Execute']; ?>" />
        </div>
    </td></tr>
</table>
<?php
}


// Жалобы    
if ($_GET['sub']=='complaints') {

if (isset($_GET['page'])) $page = intval($_GET['page']); else $page = 0; $page = $page * 50;


$sql = "SELECT COUNT(*) AS `cnt` FROM `".PREFIX."_vidvk_c` JOIN `".PREFIX."_vidvk_s` JOIN `".PREFIX."_vidvk_z` ON ".PREFIX."_vidvk_c.sid=".PREFIX."_vidvk_s.id AND ".PREFIX."_vidvk_c.zid=".PREFIX."_vidvk_z.id";
$vkfilm_res = $db->super_query ($sql);
$cmpl_c = intval($vkfilm_res["cnt"]); // к-ть жалоб всього
$pages_c = intval($cmpl_c/50)+1; // к-ть сторінок з жалобами

// Скрываем от модераторов сообщения со статусом 1
if (!vkv_check_access("ext")) {
    $where = " AND status='0'";
} else 
    $where = '';

$sql = "SELECT ".PREFIX."_vidvk_c.*,sname,".PREFIX."_vidvk_z.name AS `zname` FROM `".PREFIX."_vidvk_c` JOIN `".PREFIX."_vidvk_s` JOIN `".PREFIX."_vidvk_z` ON ".PREFIX."_vidvk_c.sid=".PREFIX."_vidvk_s.id AND ".PREFIX."_vidvk_c.zid=".PREFIX."_vidvk_z.id {$where} ORDER BY status, time DESC LIMIT {$page}, 50";
$vkfilm_res = $db->query ( $sql );
/*
Array
(
    [id] => 65
    [zid] => 77
    [sid] => 137
    [user_name] => admin
    [time] => 1376226026
    [text] => Видео не соответствует названию
    [status] => 1
    [sname] => Битва
    [zname] => Новая сборка
)
 */
?>
<table width="100%" id="newslist">
	<tr class="thead">
        <th style="padding-left:2px;padding-right:2px;">&nbsp;<?php echo $vclang['Package']; ?>&nbsp;</th>
        <th width="170" align="center" style="padding-left:2px;padding-right:2px;"><?php echo $vclang['Serie']; ?></th>
        <th width="170" align="center" style="padding-left:2px;padding-right:2px;"><?php echo $vclang['Name']; ?></th>
        <th width="280" style="padding-left:2px;padding-right:2px;"><?php echo $vclang['Complaint']; ?></th>
        <th width="100" style="padding-left:2px;padding-right:2px;"><?php echo $vclang['Status']; ?></th>
        <th width="10" align="center" style="padding-left:2px;padding-right:2px;"><input type="checkbox" value="1" title="<?php echo $vclang['Check_all']; ?>" id="checkbox_mass" /></th>
	</tr>
    <tr class="tfoot"><th colspan="6"><div class="hr_line"></div></td></th>
    <?php
    $tmp = array();
    while ( $vkfilm_row = $db->get_row( $vkfilm_res ) ) {
        //print_r ($vkfilm_row);
        $timex = date ("d.m.Y", $vkfilm_row['time']);
        $tmp['zname'] = htmlspecialchars(substr($vkfilm_row['zname'],0,100), ENT_COMPAT, $config['charset']);
        $vkfilm_row['zname'] = htmlspecialchars($vkfilm_row['zname'], ENT_COMPAT, $config['charset']);
        $tmp['sname'] = htmlspecialchars(substr($vkfilm_row['sname'],0,60), ENT_COMPAT, $config['charset']);
        $vkfilm_row['sname'] = htmlspecialchars($vkfilm_row['sname'], ENT_COMPAT, $config['charset']);
        $tmp['text'] = htmlspecialchars(substr($vkfilm_row['text'],0,70), ENT_COMPAT, $config['charset']);
        $vkfilm_row['text'] = htmlspecialchars($vkfilm_row['text'], ENT_COMPAT, $config['charset']);
        $status = $vkfilm_row['status']=='1' ? "<font color='green'>Обработана</font>" : "<font color='gray'>Не обработана</font>";
        
        $username = preg_match("/\d+\.\d+\.\d+\.\d+/", $vkfilm_row['user_name']) ? "<font color='gray'>{$vkfilm_row['user_name']}</font>" : "<a target=\"_blank\" href=\"{$config['http_home_url']}index.php?subaction=userinfo&user={$vkfilm_row['user_name']}\">{$vkfilm_row['user_name']}</a>";
echo<<<DELIM
        <tr>
            <td class="list" style="padding:2px;">$timex - <a href="{$config['http_home_url']}{$config['admin_path']}?mod=parser_constructor&sec=go_complaint&zid={$vkfilm_row['zid']}" title="$vkfilm_row[zname]">$tmp[zname]</a></td>
            <td class="list" style="padding:2px;"><a href="{$config['http_home_url']}{$config['admin_path']}?mod=parser_constructor&sec=go_complaint&sid={$vkfilm_row['sid']}" style="color:#862D2D" title="$vkfilm_row[sname]">$tmp[sname]</a></td>
            <td class="list" style="padding:2px;">{$username}</td>
            <td class="list" style="padding:2px;"><font style="color:#15682B" title="{$vkfilm_row['text']}">{$tmp['text']}</font></td>
            <td class="list" style="padding:2px;">{$status}</td>
            <td class="list" style="padding:2px;"><input value="{$vkfilm_row['id']}" type='checkbox' class='checkbox_mass' /></td>
        </tr>
        <tr><td background="engine/skins/images/mline.gif" height="1" colspan="5"></td></tr>
DELIM;
    }
    // Строчка страниц
    if ($pages_c>0) {
        echo '<tr class="tfoot"><td colspan="5" style="padding-top:5px;">';
        for ($i=0; $i<$pages_c; $i++) {
            if ($i==$page) {
                echo "- <a href='{$config['http_home_url']}{$config['admin_path']}?mod=parser_constructor&sub=complaints&page=$i'><b>".($i+1)."</b></a> -";
            } else {
                echo "- <a href='{$config['http_home_url']}{$config['admin_path']}?mod=parser_constructor&sub=complaints&page=$i'>".($i+1)."</a> -";
            }
        }
        echo '</td></tr>';
    }
    ?>
    <tr class="tfoot"><td colspan="5"><div class="hr_line"></div></td></tr>
    <tr class="tfoot"><td colspan="5">
        <div align="right" style="padding-right:40px;" id="rl_massform">
            <select size="1" style="width:160px;" class="edit action_mass_com">
                <option>-- <?php echo $vclang['Action']; ?> --</option>
                <option value="delete"><?php echo $vclang['Delete']; ?></option>
            </select>
            <input class="edit" type="button" value="<?php echo $vclang['Execute']; ?>" id="rl_action_mass" />
        </div>
    </td></tr>
</table>

<?php
}

// Настройки    
if ($_GET['sub']=='properties') {
if (!vkv_check_access("ext")) { exit("Module VideoConstructor - access denied!"); }

if (isset($_POST['vk_form_post']) && $_POST['vk_form_post']) {
    $res = vk_make_config ($vk_sys_const);
}

   
?>
<form action="<?php echo $config['http_home_url'].$config['admin_path'].'?mod=parser_constructor&sub=properties';?>" method="post" enctype="application/x-www-form-urlencoded">
    <table width="100%">
            <tr>
                <td style="padding:4px" class="option"><b>E-mail</b><br /><span class="small"><?php echo $vclang['E-mail_long']; ?>.</span></td>
                <td width="50%" align="left" ><input class="edit bk" type="text" style="text-align: center;" name="vk_config[user_email]" value="<?php echo $vk_config['user_email']; ?>" size="44" /></td>
            </tr>
            
            <tr>
                <td style="padding:4px" class="option"><b><?php echo $vclang['Lic_key']; ?></b><br /><span class="small"><?php echo $vclang['Lic_key_long']; ?>.</span></td>
                <td width="50%" align="left" ><input class="edit bk" type="text" style="text-align: center;" name="vk_config[user_secret]" value="<?php echo $vk_config['user_secret']; ?>" size="44" /></td>
            </tr>
            
            <tr>
                <td style="padding:4px" class="option"><b><?php echo $vclang['Server_search']; ?></b><br /><span class="small"><?php echo $vclang['Server_search_long']; ?> <b>s1.6prog.net</b></span></td>
                <td width="50%" align="left" ><input class="edit bk" type="text" style="text-align: center;" name="vk_config[vkv_server]" value="<?php echo $vk_config['vkv_server']; ?>" size="44" /></td>
            </tr>
            
            <tr>
                <td style="padding:4px" class="option"><b><?php echo $vclang['Debug_mode']; ?></b><br /><span class="small"><?php echo $vclang['Debug_mode_long']; ?>.</span></td>
                <td width="50%" align="left" ><select name="vk_config[is_debug]">
                        <?php echo get_option_by_array($vc_constants["onOff"], $vk_config['is_debug']); ?>
                    </select></td>
            </tr>
            
            <tr>
                <td style="padding:4px" class="option"><b><?php echo $vclang['Serialname_patterns']; ?></b><br /><span class="small"><?php echo $vclang['Serialname_patterns_long']; ?>.</span></td>
                <td width="50%" align="left" ><textarea cols="52" rows="5" class="edit bk" name="vk_config[serialname_patterns]" style="margin-top:5px;"><?php VcExtension::getInstance()->onInit(); echo stripslashes($vk_config['serialname_patterns']); ?></textarea></td>
            </tr>
            <!--
            <tr>
                <td style="padding:4px" class="option"><b><?php echo $vclang['Server_search']; ?><?php echo $vclang['Default_quality']; ?></b><br /><span class="small"><?php echo $vclang['Default_quality_long']; ?>.</span></td>
                <td width="50%" align="left" >
                    <select id="vk_config[quality_prefix]" name="vk_config[quality_prefix]" size="1" >
                        <?php echo get_option_by_array($vc_constants["qualityPrefix"], $vk_config['quality_prefix']); ?>
                    </select>&nbsp;<select id="vk_config[quality]" name="vk_config[quality]" size="1" <?php echo($vk_config['quality_prefix'] == 'none') ? 'disabled="disabled"' : ''; ?>>
                        <?php echo get_option_by_array($vc_constants["quality"], $vk_config['quality']); ?>
                    </select></td>
            </tr>
            -->
            <tr>
                <td style="padding:4px" class="option"><b><?php echo $vclang['Series_sort']; ?></b><br /><span class="small"><?php echo $vclang['Series_sort_long']; ?></span></td>
                <td width="50%" align="left" >
                    <select name="vk_config[serie_sort]">
                        <?php echo get_option_by_array($vc_constants["sortInCatConf"], $vk_config['serie_sort']); ?>
                    </select></td>
            </tr>
            
            <tr>
                <td style="padding:4px" class="option"><b><?php echo $vclang['Filter_minlen']; ?></b><br /><span class="small"><?php echo $vclang['Filter_minlen_long']; ?></span></td>
                <td width="50%" align="left" ><input class="edit bk" type="text" style="text-align: center;" name="vk_config[video_minlen]" value="<?php echo $vk_config['video_minlen']; ?>" size="22"></td>
            </tr>
            
            <tr>
                <td style="padding:4px" class="option"><b>Стандартное название сборки</b><br />
                    <span class="small">Можно использовать шаблон <code>{title}</code></span></td>
                <td width="50%" align="left" ><input class="edit bk" type="text" name="vk_config[default_zname]" value="<?php echo $vk_config['default_zname']; ?>" size="66"></td>
            </tr>
            
            <!--
            <tr>
                <td style="padding:4px" class="option"><b>Редактор на сайте</b><br /><span class="small">Включение/отключения возможности добавлять/редактировать видео на сайте.<br><font style="color:#e23f3f">Функция пока не доступна!<font></span></td>
                <td width="50%" align="left" >
                    <select name="vk_config[ext_editor]">
                        <?php echo get_option_by_array($vc_constants["onOff"], $vk_config['ext_editor']); ?>
                    </select></td>
            -->
            
            <tr>
                <td style="padding:4px" class="option"><b>"Мои видеозаписи"</b><br />
                    <span class="small">Разрешить добавлять видео в "Мои видеозаписи" из результатов поиска.</span></td>
                <td width="50%" align="left" >
                    <select name="vk_config[vk_addInMy]">
                        <?php echo get_option_by_array($vc_constants["onOff"], $vk_config['vk_addInMy']); ?>
                    </select>
                </td>
            </tr>
            
            <!-- Настройки плеера -->
            <tr><td style="padding:4px" collspan="2" style="border:1px solid red;">
                <h3 align="center"><?php echo $vclang['Player_config']; ?></h3></td></tr>
            
            <tr>
                <td style="padding:4px" class="option"><b><?php echo $vclang['Player_size']; ?></b><br /><span class="small"><?php echo $vclang['Player_size_long']; ?></span></td>
                <td width="50%" align="left" ><input class="edit bk" type="text" style="text-align: center;" name="vk_config[player_width]" value="<?php echo $vk_config['player_width'] ?>" size="4" />&nbsp;x&nbsp;<input class="edit bk" type="text" style="text-align: center;" name="vk_config[player_height]" value="<?php echo $vk_config['player_height']; ?>" size="4" /></td>
            </tr>
            
            <!-- Настройки UPPOD -->
            <tr><td style="padding:4px" collspan="2" style="border:1px solid red;">
                <h3 align="center">Настройка UPPOD</h3></td></tr>
            
            <tr>
                <td style="padding:4px" class="option"><b><?php echo $vclang['Player_style']; ?></b><br /><span class="small"><?php echo $vclang['Player_style_long']; ?></span></td>
                <td width="50%" align="left" >
                    <select name="vk_config[player_style]">
                        <?php echo get_player_styles_options($vk_config['player_style']); ?>
                    </select></td>
            </tr>
            
            <tr>
                <td style="padding:4px" class="option"><b>UPPOD HTML5</b><br />
                    <span class="small">Позволяет заменить flash-плеер uppod на HTML для видео на ваших серверах и модулей.</span></td>
                <td width="50%" align="left" >
                    <select name="vk_config[uppod_enable_html5]">
                        <?php echo get_option_by_array($vc_constants["onOff"], $vk_config['uppod_enable_html5']); ?>
                    </select>
                </td>
            </tr>
            
            <tr>
                <td style="padding:4px" class="option"><b>Pro version</b><br />
                    <span class="small">Включите эту настройку, если вы используете PRO версию.</span></td>
                <td width="50%" align="left" >
                    <select name="vk_config[uppod_enable_pro]">
                        <?php echo get_option_by_array($vc_constants["onOff"], $vk_config['uppod_enable_pro']); ?>
                    </select>
                </td>
            </tr>
            
            <tr>
                <td style="padding:4px" class="option"><b>Uppod swf</b><br /><span class="small"><?php echo $vclang['Player_uppod_swf']; ?></span></td>
                <td width="50%" align="left" ><input class="edit bk" type="text" style="text-align: center;" name="vk_config[uppod_swf]" value="<?php echo $vk_config['uppod_swf']; ?>" size="44" /></td>
            </tr>
             
            <tr>
                <td style="padding:4px" class="option"><b>Uppod style</b><br /><span class="small"><?php echo $vclang['Player_uppod_style']; ?> Поставьте - (минус), если не хотите использовать накикой стиль. Если пусто - будт использоваться стиль конструктора.</span></td>
                <td width="50%" align="left" ><input class="edit bk" type="text" style="text-align: center;" name="vk_config[uppod_style]" value="<?php echo $vk_config['uppod_style']; ?>" size="44" /></td>
            </tr>
            
            <tr>
                <td style="padding:4px" class="option"><b>Uppod HTML5 player</b><br /><span class="small">Ссылка на плеер uppod html5 (*.js).</span></td>
                <td width="50%" align="left" ><input class="edit bk" type="text" style="text-align: center;" name="vk_config[uppod_html5]" value="<?php echo $vk_config['uppod_html5']; ?>" size="44" /></td>
            </tr>
            <tr>
                <td style="padding:4px" class="option"><b>Uppod HTML5 style</b><br /><span class="small">Ссылка на стиль html5 (*.js). Поставьте - (минус), если не хотите использовать накикой стиль.</span></td>
                <td width="50%" align="left" ><input class="edit bk" type="text" style="text-align: center;" name="vk_config[uppod_html5_style]" value="<?php echo $vk_config['uppod_html5_style']; ?>" size="44" /></td>
            </tr>
            
            <!-- Настройки HTML-кода -->
            <tr><td style="padding:4px" collspan="2" style="border:1px solid red;">
                <h3 align="center">Настройка HTML-кода</h3></td></tr>
            
            <tr>
                <td style="padding:4px" class="option"><b>Вставка любого HTML</b><br /><span class="small">Позволяет вставить код любого HTML-плеера. <span style="color:#a12f2f;">Не рекомендуется включать без особой необходимости! Особенно если у вас есть журналисты/модераторы, которым вы не доверяете.</span></span></td>
                <td width="50%" align="left" >
                    <select name="vk_config[html_enabled]">
                        <?php echo get_option_by_array($vc_constants["onOff"], $vk_config['html_enabled']); ?>
                    </select></td>
            </tr>
            
            <!-- Расширения -->
            <tr><td style="padding:4px" collspan="2" style="border:1px solid red;">
                <h3 align="center">Расширения</h3></td></tr>
            
            <tr>
                <td style="padding:4px" class="option"><b>Включение расширений</b><br /><span class="small">Позволяет включить дополнительные расширения скрипта. Если вы не используете ни одного расширения, советую отключить эту настройку.</span></td>
                <td width="50%" align="left" >
                    <select name="vk_config[ext_enabled]">
                        <?php echo get_option_by_array($vc_constants["onOff"], $vk_config['ext_enabled']); ?>
                    </select></td>
            </tr>
            
            <!-- Настройки доступа -->
            <tr><td style="padding:4px" collspan="2" style="border:1px solid red;">
                <h3 align="center"><?php echo $vclang['Access_config']; ?></h3></td></tr>
            
            <tr>
                <td style="padding:4px" class="option"><b><?php echo $vclang['Access_base']; ?></b><br /><span class="small"><?php echo $vclang['Access_base_long']; ?></span></td>
                <td width="50%" align="left" ><input class="edit bk" type="text" name="vk_config[acces_base]" value="<?php echo $vk_config['acces_base']; ?>" size="44" /></td>
            </tr>
            
            <tr>
                <td style="padding:4px" class="option"><b><?php echo $vclang['Access_ext']; ?></b><br /><span class="small"><?php echo $vclang['Access_ext_long']; ?></span></td>
                <td width="50%" align="left" ><input class="edit bk" type="text" name="vk_config[acces_ext]" value="<?php echo $vk_config['acces_ext']; ?>" size="44" /></td>
            </tr>
            
            <!-- Отправка жалоб гостями -->
            <tr><td style="padding:4px" collspan="2" style="border:1px solid red;">
                <h3 align="center">Настройка жалоб</h3></td></tr>
            
            <tr>
                <td style="padding:4px" class="option"><b>Отправка гостями</b><br /><span class="small">Позволяет гостям отправлять жалобы на фильм.</span></td>
                <td width="50%" align="left" >
                    <select name="vk_config[complaint_guest]">
                        <?php echo get_option_by_array($vc_constants["onOff"], $vk_config['complaint_guest']); ?>
                    </select></td>
            </tr>
            
            
</table>
<input type="hidden" name="vk_form_post" value="1" />
<center><input type="submit" value="<?php echo $vclang['Save']; ?>" class="edit bk" style="margin: 3px;" /></center>
</form>
<?php
} // // Настройки


// Расширения    
if ($_GET['sub']=='extensions') {
if (!vkv_check_access("ext")) { exit("Module VideoConstructor - access denied!"); }
if (!$vk_config["ext_enabled"]) echo "<div style='padding:10px;width:500px;margin:10px auto 10px; border: 1px solid #8b1919; background-color: #e9d0d0; border-radius: 5px;'>Расширения глобально откоючены в настройках конструктора видео.</div>";
else {
if (!$_GET['action']){
?>
<style type="text/css">
    a.ext_link {color: #20619e;}
    a.ext_link:hover {text-decoration: underline; color: #20619e;}
</style>
<table width="100%">
<tr>
    <td style="padding:5px;"><B>ID</B></td>
    <td style="padding:5px;"><B>Название расширения</B></td>
    <td style="padding:5px;"><B>Описание</B></td>
    <td style="padding:5px;width:100px;"><B>Страница</B></td>
    <td style="padding:5px;width:100px;"><B>Статус</B></td>
</tr>
<tr>
    <td colspan=5><div class="hr_line"></div></td>
</tr>
<?php 
    foreach ($vk_config["extensions"] as $ext_id => $ext_arr) {
    ?>
        <tr>
          <td style="padding:5px;"><?php echo $ext_id; ?></td>
          <td style="padding:5px;"><a href="<?php echo $config["http_home_url"].$config["admin_path"]."?mod=parser_constructor&sub=extensions&action=config&id={$ext_id}"; ?>" class="ext_link"><?php echo $ext_arr["name"]; ?></a></td>
          <td style="padding:5px;"><?php echo $ext_arr["description"]; ?></td>
          <td style="padding:5px;"><?php if ($vk_config["extensions"][$ext_id]["link"]) echo "<a href='".$vk_config["extensions"][$ext_id]["link"]."' target='_blank'>перейти</a>"; else echo '-'; ?></td>
          <td style="padding:5px;"><?php if ($vk_config["extensions"][$ext_id]["config"]['enabled']) echo '<span style="color:green;">Включено</span>'; else echo '<span style="color:red;">Отключено</span>'; ?></td>
        </tr><tr><td background="engine/skins/images/mline.gif" height=1 colspan=5></td></tr>
    <?php
    }
?>
</table>
<?php
} else
    include ENGINE_DIR . "/inc/include/p_construct/ext/panel.php";
}} // // Расширения

// Справка
if ($_GET['sub']=='help') {
    echo ($vclang["help_text"]);
} // // Справка  
?>
</div><!-- //box-content -->
</div>

<?php
    echofooter();
?>