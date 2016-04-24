<?php

function print_js_redirect($url, $miliseconds) {
    echo '<script language="javascript" type="text/javascript">setTimeout("window.location=\'' . $url . '\';", ' . $miliseconds . ');</script>';
}

function get_header_info($html) {
    $result = array();
    $pos = strpos($html, "\r\n\r\n");
    if ($pos and $pos < 1000) {
        $header_html = substr($html, 0, $pos);
        // Відповідь сервера
        if (preg_match("/HTTP[^ ]+ ([0-9]+)[^\r\n]+/", $header_html, $arr)) {
            $result['http_status'] = $arr[1];
        }
        // Кукіси
        preg_match_all("/Set-Cookie: ([^;]*)/", $header_html, $arr);
        $line = '';
        $c = count($arr);
        if ($c == 0) {
            return false;
        }
        foreach ($arr[1] as $key => $val) {
            $line .= $val;
            if ($key <> $c - 1) {
                $line .= '; ';
            }
        }
        $result['cookies_line'] = trim($line);
        // Кодування
        preg_match("/charset=([A-Za-z0-9-]+)/", $header_html, $arr);
        $result['charset'] = strtolower($arr[1]);
        // Lacation
        preg_match("/Location: ([^\n]*)/", $header_html, $arr);
        $result['location'] = trim($arr[1]);
        return $result;
    }
}

function get_option_style_name ($name) {
    $old = array(
        '/^default/',
        '/^modern/',
        '/^x-modern2/',
        '/^x-scrolling/',
        '/^fs-liquid/',
    );
    foreach ($old as $pattern)
        if (preg_match($pattern, $name)) {
            return '[устар.] '.$name;
        }
    return $name;
}

// Отримуємо <option><..> всіх шаблонів виводу плеєра з папки
##!! (Низкий) Функция устаревшая, использовать get_option_by_array и VideoConstructor class
function get_player_styles_options($selected, $with_default = false) {
    if ($with_default)
        $txt = "<option value='' selected='selected'>По умолчанию</option>\r\n"; else
        $txt = '';
    if ($dir = @opendir(ENGINE_DIR . '/inc/include/p_construct/players_style/')) {
        $styles = array();
        while (($file = readdir($dir)) !== false) {
            if (is_file(ENGINE_DIR . '/inc/include/p_construct/players_style/' . $file)) {
                $pos = strrpos($file, '.');
                if ($pos) {
                    $name = substr($file, 0, $pos);
                    $styles[] = $name;
                }
            }
        }
        foreach ($styles as $name) {
            $option_name = get_option_style_name($name);
            if ($selected == $name)
                $txt .= "<option value='{$name}' selected='selected' >{$option_name}</option>\r\n"; // if ($selected==$name && !$with_default)
            else
                $txt .= "<option value='{$name}'>{$option_name}</option>\r\n";
        }
        closedir($dir);
    }
    return ($txt);
}

// Отримуємо <option><..> для налаштувння
function get_option_by_array($option_array, $selected) {
    $txt = '';
    foreach ($option_array as $value => $text) {
        if ($value == $selected)
            $txt .= "<option value='$value' selected='selected' >$text</option>\r\n";
        else
            $txt .= "<option value='$value'>$text</option>\r\n";
    }
    return $txt;
}

// Отримуємо опції <option><..> для перемикача шаблонів назв серій
function get_serienames_option() {
    global $vk_config;
    $array = explode("\n", $vk_config['serialname_patterns']);
    $txt = '';
    $default = '';
    foreach ($array as $key => $value) {
        $txt .= "<option value='$value'>$value</option>\r\n";
        if ($default == '')
            $default = $value;
    }
    return array($default, $txt);
}

// Збереження налаштувань в адмінці
function vk_make_config() {
    global $vk_config;
    global $config;
    $vals = array(// Стандартні налаштування (одночасно обмежує всі можливі налаштування)
        'user_email' => "demo@ralode.com",
        'user_secret' => "demodemodemodemodemo",
        'player_width' => 500,
        'player_height' => 400,
        'player_style' => "default",
        'findmax_first' => (intval($vk_config['findmax_first'])>0) ? $vk_config['findmax_first'] : 60,
        'findmax' => (intval($vk_config['findmax'])>0) ? $vk_config['findmax'] : 20,
        'serialname_patterns' => "",
        'quality_prefix' => "none",
        'quality' => 240,
        'video_minlen' => 30,
        'cache_admin' => 0,
        'ext_editor' => 0,
        'is_debug' => 0,
        'vkv_server' => '', // сервер для пошуку
        'uppod_swf' => '',
        'uppod_style' => '',
        'acces_base' => '',
        'acces_ext' => '1',
        'extensions' => '0',
        'complaint_guest' => false,
        'default_zname' => 'Новая сборка',
        'vk_addInMy' => '1',
    );
    // Magic quotes
    if (get_magic_quotes_gpc()) {
        $_POST['vk_config'] = array_map("stripslashes", $_POST['vk_config']);
    }
    
    // Вношу нові зміни
    $_POST['vk_config']['user_email'] = strip_tags(htmlspecialchars($_POST['vk_config']['user_email'], ENT_QUOTES, $config["charset"]));
    if ($_POST['vk_config']['user_email']!="") {
        $vals['user_email'] = trim(strtolower($_POST['vk_config']['user_email']));
    }
    if (preg_match("/^[A-Za-z0-9]{20}$/", $_POST['vk_config']['user_secret'])) {
        $vals['user_secret'] = trim($_POST['vk_config']['user_secret']); 
    } else {
        if ($vals['user_secret']=='demo')
            $vals['user_secret'] = 'demodemodemodemodemo';
        else
            $vals['user_secret'] = '';
    }
    $vals['player_width'] = VideoTubes::getInstance()->formatSize($_POST['vk_config']['player_width']);
    $vals['player_height'] = VideoTubes::getInstance()->formatSize($_POST['vk_config']['player_height']);
    if (preg_match("/^[A-Za-z0-9-_]+$/",$_POST['vk_config']['player_style']))
        $vals['player_style'] = $_POST['vk_config']['player_style'];
    $vals['findmax_first'] = intval($_POST['vk_config']['findmax_first']);
    $vals['findmax'] = intval($_POST['vk_config']['findmax']);
    $tt = array();
    $arr = explode("\n", $_POST['vk_config']['serialname_patterns']);
    if (is_array($arr)) {
        foreach ($arr as $k => $v) {
            $v = trim($v);
            if (!$v == '') {
                $tt[] = $v;
            }
        }
    }
    $vals['serialname_patterns'] = addslashes(implode("\n", $tt));
    $vals['quality_prefix'] = addslashes($_POST['vk_config']['quality_prefix']);
    $vals['quality'] = intval($_POST['vk_config']['quality']);
    $vals['video_minlen'] = intval($_POST['vk_config']['video_minlen']);
    $vals['ext_editor'] = intval($_POST['vk_config']['ext_editor']);
    $vals['serie_sort'] = intval($_POST['vk_config']['serie_sort']);
    $vals['is_debug'] = intval($_POST['vk_config']['is_debug']);
    $vals['vkv_server'] = addslashes($_POST['vk_config']['vkv_server']);
    $vals['uppod_swf'] = strip_tags($_POST['vk_config']['uppod_swf']);
    $vals['uppod_style'] = strip_tags($_POST['vk_config']['uppod_style']);
    $vals['uppod_enable_html5'] = intval($_POST['vk_config']['uppod_enable_html5']);
    $vals['uppod_enable_pro'] = intval($_POST['vk_config']['uppod_enable_pro']);
    $vals['uppod_html5'] = strip_tags($_POST['vk_config']['uppod_html5']);
    $vals['uppod_html5_style'] = strip_tags($_POST['vk_config']['uppod_html5_style']);
    # Настройка любого кода
    $vals['html_enabled'] = intval($_POST['vk_config']['html_enabled']);
    # Настройки доступа
    $vals['acces_base'] = strip_tags($_POST['vk_config']['acces_base']);
    $vals['acces_ext'] = $_POST['vk_config']['acces_ext']==="" ? "1" : strip_tags($_POST['vk_config']['acces_ext']);
    # Extensions
    $vals['ext_enabled'] = intval($_POST['vk_config']['ext_enabled']);
    # Отправка жалоб гостями
    $vals['complaint_guest'] = intval($_POST['vk_config']['complaint_guest']);
    # Стандартные названия сборки
    $vals['default_zname'] = strip_tags($_POST['vk_config']['default_zname']);
    # Разрешить добавлять видео в мои видеозаписи?
    $vals['vk_addInMy'] = intval($_POST['vk_config']['vk_addInMy']);
    // Формую config-файл
    $code = "<?php\r\n" . '$vk_config = array();' . "\r\n";
    foreach ($vals as $k => $v) {
        $code .= '$vk_config[\'' . $k . '\'] = \'' . $v . '\';' . "\r\n";
    }
    
    $code .= '?>';
    // Зберігаю 
    $fp = fopen(ENGINE_DIR . '/inc/include/p_construct/config.php', 'w+');
    if ($fp) {
        fwrite($fp, $code);
        fclose($fp);
        $vk_config = $vals;
        return true;
    } else {
        echo "<b style='color:red'>Парсер-конструктор :: Ошибка открытия файла config.php для записи!</b>";
        return false;
    }
}

/**
 * Перекодирует текст в ассоцитивном массиве в другую кодировку,
 * используется перед выводом JSON
 * @param str $charset_from     Из кодировки
 * @param str $charset_to       В кодировку
 * @param array $array      Асоциативный массив
 * @uses iconv          для перекодирования строк
 * @return string
 */
function array_iconv($charset_from, $charset_to, $array) {
    if (is_array($array)) {
        foreach ($array as $key => $val) {
            $array[$key] = array_iconv($charset_from, $charset_to, $val);
        }
        return $array;
    } else if (is_string($array)) {
        if ($array == '') {
            return '';
        } else {
            return iconv($charset_from, $charset_to, $array);
        }
    } else
        return $array;
}

/**
 * Проверка доступа
 * @global type $vk_config
 * @global type $member_id
 * @param string    $type    "base" или "ext"
 * @return boolean  True-доступ разренен
 */
function vkv_check_access ($type = "base") {
    global $vk_config;
    global $member_id;
    if ($vk_config!==null) {
        $access = false;
        // Расширенный доступ
        if ($vk_config['acces_ext']!=="") {
            $acces_ext = explode(",",$vk_config['acces_ext']);
            $acces_ext = array_map("trim",$acces_ext);
        } else 
            $acces_ext = array();
        foreach ($acces_ext as $key => $val) {
            if ($val!="") {
                if (intval($val)==$val && $val>0) { // группа
                    if ($val == $member_id['user_group']) $access = true;
                } else { // пользователь
                    if ($val == $member_id['name']) $access = true;
                }
            }
        }
        if ($access===true) return $access;
        
        if ($type=="base") {
            // Базовый доступ
            if ($vk_config['acces_base']!=="") {
                $acces_base = explode(",",$vk_config['acces_base']);
                $acces_base = array_map("trim",$acces_base);
            } else 
                $acces_base = array();
            foreach ($acces_base as $key => $val) {
                if ($val!="") {
                    if (intval($val)==$val && $val>0) { // группа
                        if ($val == $member_id['user_group']) $access = true;
                    } else { // пользователь
                        if ($val == $member_id['name']) $access = true;
                    }
                }
            }
            return $access;
        } else 
            return false;
    } else 
        return false;
}

/**
 * Получить строку времени, типа 3:18:45 из к-ва секунд
 * @param type $duration
 */
function vc_get_duration ($duration) {
    $duration_min = intval($duration / 60);
    $duration_sec = $duration - $duration_min * 60;
    if ($duration_min > 60) {
        $duration_hour = intval($duration_min / 60);
        $duration_min = $duration_min - $duration_hour * 60;
        $duration = "$duration_hour:".($duration_min>10 ? $duration_min : "0".$duration_min).":".($duration_sec>10 ? $duration_sec : "0".$duration_sec);
    } else
        $duration = "$duration_min:".($duration_sec>10 ? $duration_sec : "0".$duration_sec);
    return $duration;
}

/**
 * Разбиение длинных слов
 * @param type $string
 * @return type
 */
function breakLonkWords ($string) {
    return preg_replace('/[^\s]{60}[^\s]+/', '', $string);
}

/**
 * Функция для сортировки массива сборок по ключу "sort"
 */
 function z_compare_sortf ($a, $b) {
	if ($a["sort"] == $b["sort"]) 
        return 0;
    else
		return ($a["sort"] < $b["sort"]) ? -1 : 1;
 }

 function toUtf ($string) {
    if (is_string($string))
        return iconv ('windows-1251', 'utf-8', $string);
    else
        return $string;
}

function printJSON($array) {
    global $config;
    if ($config['charset']==='windows-1251')
        $array = array_map('toUtf', $array);
    header('Content-type: application/json; charset=utf-8');
    if (defined('JSON_UNESCAPED_UNICODE'))
        exit(json_encode($array, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
    else
        exit(json_encode($array));
}