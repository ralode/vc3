<?php
/**
 * Скрипт: Конструктор видео v3.x для DLE
 * Назначение файла: Полуавтоматическая установка
 * @author SeregaL <SeregaL2009@yandex.ru>
 * 
 * Скрипт сгенерирован специально для: Bolix10@yandex.ru
 */
error_reporting(E_ALL ^ E_NOTICE);
@ini_set('display_errors', true);
@ini_set('html_errors', false);
@ini_set('error_reporting', E_ALL ^ E_NOTICE);
define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname(__file__));
define('ENGINE_DIR', ROOT_DIR . '/engine');
include ENGINE_DIR . '/data/config.php';
require_once ENGINE_DIR . '/modules/functions.php';
require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/sitelogin.php';
if ($member_id['user_group'] != 1) {
    exit('Для установки модуля необходимо авторизоваться на сайте как администратор.');
}

##########################################################
$this_path = "install_vc.php";

$step = isset($_GET["step"]) ? intval($_GET["step"]) : 0; // Шаг
$data = array();
switch ($step) {
    case 0: // Первый экран
        $data["title"] = "Установка Конструктора видео v3.x";
        $data["text"] = "<p>Здравствуйте!<br>Вы собираетесь установить конструктор видео v3.x.<br>
Офф. страница скрипта: <a href='http://ralode.com/konstruktor-video-v3.x'>http://ralode.com/konstruktor-video-v3.x</a></p>
<p>По поводу багов, предложений и вопросов можно писать:</p>
<ul><li>E-mail: <span class='red'>SeregaL2009@yandex.ru</span></li>
<li>Skype: <span class='red'>serg5734</span></li>
<li>ICQ: <span class='red'>323-395</span></li></ul>
<p class='red bold'>Внимание! Перед началом установки сделайте бекап базы данных через PhpMyAdmin (не через 'Управление базой данных' в админке DLE!).</p>
<p>Для начала установки нажмите кнопку \"Дальше\".</p>";
        $data["next"] = "1";
        break;
    case 1: // Создание таблиц
        $data["title"] = "Шаг 1: Создание таблиц в базе";
        $data["text"] = "<p>Поиск таблиц в базе данных...</p>";
        $sql = "SHOW tables LIKE '" . PREFIX . "_vidvk_%'";
        $res = $db->super_query($sql);
        if (!$res) {
            $data["text"] .= "<p class='green'>Таблицы не найдены.</p>";
            $sqls = array();
            $char_set = $config["charset"] == "utf-8" ? "utf8" : "cp1251";
            $sqls[] = "CREATE TABLE IF NOT EXISTS `" . PREFIX . "_vidvk_c` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zid` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `user_name` varchar(200) NOT NULL,
  `time` bigint(20) NOT NULL,
  `text` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=" . $char_set;
            $sqls[] = "CREATE TABLE IF NOT EXISTS `" . PREFIX . "_vidvk_s` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) NOT NULL DEFAULT '0',
  `sname` varchar(250) NOT NULL,
  `scode` text NOT NULL,
  `lssort` int(11) NOT NULL,
  `err` tinyint(3) NOT NULL,
  `codetype` tinyint(3) NOT NULL,
  `max_size` int(4) NOT NULL,
  `sdata` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=" . $char_set;
            $sqls[] = "CREATE TABLE IF NOT EXISTS `" . PREFIX . "_vidvk_z` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `sort` tinyint(1) NOT NULL DEFAULT '0',
  `style` varchar(50) NOT NULL,
  `ssort` tinyint(1) NOT NULL DEFAULT '0',
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=" . $char_set;
            $ok = true;
            foreach ($sqls as $sql) {
                $res = $db->query($sql);
                if (!$res) {
                    $ok = false;
                    $data["text"] .= "<span class='red'>Ошибка: не удалось выполнить запрос</span>:<br><textarea>{$sql}</textarea>";
                }
            }
            if ($ok) {
                $data["text"] .= "<p class='green'>Структура БД успешно создана!</p>";
                $data["next"] = "2";
            }
        } else {
            $data["text"] = "<p class='green'>В базе уже есть таблицы от версии 3.х. Конвертация базы данных не требуется.</p>";
            $data["next"] = "4";
        }

        break;

    case 2: // Поиск предыдущей версии (2.х)
        $data["title"] = "Шаг 2: Проверка установленной 2й версии";
        $sql = "SHOW tables LIKE 'vk%'";
        $res = $db->query($sql, true);
        $isset = false;
        while ($row = $db->get_array($res)) {
            if ($row[0] == "vkfilms" || $row[0] == "vkitems")
                $isset = true;
        }
        if ($isset) {
            $data["text"] = "<p>В базе данных найдены таблицы от версии 2.х: vkfilms и vkitems.</p>";
            $data["text"] .= "<p>Для конвертации старой базы данных в новую нажмите \"Дальше\".</p>";
            $data["next"] = "3";
        } else {
            $data["text"] = "<p>В базе данных <strong>не найдены</strong> таблицы от версии 2.х.</p>";
            $data["text"] .= "<p>Значит вы устанавливаете Конструктор видео на Ваш сайт впервые.</p>";
            $data["text"] .= "<p class='green'>Конвертация базы данных не требуется.</p>";
            $data["next"] = "4";
        }
        break;
    case 3: // Конвертация базы данных в новую
        $data["title"] = "Шаг 3: Конвертация базы данных";

        // Поиск максимальных значений таблиц dle_vidvk_z и dle_vidvk_s
        $row = $db->super_query("SELECT MAX(id) AS `max` FROM `" . PREFIX . "_vidvk_z`");
        $vidvk_z_max = intval($row["max"]) + 100;
        $row = $db->super_query("SELECT MAX(id) AS `max` FROM `" . PREFIX . "_vidvk_s`");
        $vidvk_s_max = intval($row["max"]) + 100;

        // Поиск всех новостей
        $news = array(); // key = post.id, value = vkfilms.id
        $sql = "SELECT id, xfields FROM `" . PREFIX . "_post` WHERE xfields LIKE '%vk_filmpack_id|%'";
        $res = $db->query($sql);
        while ($row = $db->get_row($res)) {
            $array = explode("||", $row["xfields"]);
            $film_id = 0;
            if ($array) {
                foreach ($array as $val)
                    if (substr($val, 0, 15) == "vk_filmpack_id|") {
                        $film_id = intval(substr($val, 15));
                        break;
                    }
            }
            if ($film_id)
                $news[$row["id"]] = $film_id;
        }
        ksort($news);
        //echo "NEWS: "; print_r ($news); //exit;
        // Поиск всех фильмов
        $sql = "SELECT * FROM `vkfilms`";
        $rows = $db->super_query($sql, true);
        $vkfilms = array();
        if ($rows) {
            foreach ($rows as $row) {
                $vkfilms[$row["id"]] = array("items" => array(), "name" => $row["name"]);
            }
        }
        //echo "VKFILMS: "; print_r ($vkfilms);
        // Поиск всех серий
        $sql = "SELECT * FROM `vkitems`";
        $rows = $db->super_query($sql, true);
        if ($rows) {
            foreach ($rows as $row) {
                if (!isset($vkfilms[$row["parent"]]["items"][$row["sort"]])) {
                    $vkfilms[$row["parent"]]["items"][$row["sort"]] = $row;
                } else {
                    echo "Ошибка: серия {$row['item_name']} ({$row['parent']}#{$row['sort']}) уже существует!<br>\n";
                }
            }
        }
        //echo "VKFILMS: "; print_r ($vkfilms); 
        // Массив структуры сборок старой версии
        $struct = array(); // post.id => struct
        $count_films = 0;
        $count_items = 0;
        foreach ($news as $post_id => $zid) {
            if (isset($vkfilms[$zid])) {
                if (!isset($vkfilms[$zid]["used"])) {
                    $count_items += count($vkfilms[$zid]["items"]);
                    $count_films++;
                }
                $struct[$post_id] = $vkfilms[$zid];
                $vkfilms[$zid]["used"] = 1;
            } else {
                //echo "Предупреждение: фильм #{$zid} в новосити #{$post_id} не найден в таблице vkfilms!<br>\n";
            }
        }
        //echo "STRUCT: "; print_r ($struct);
        $data["text"] .= "Предварительно найдено <strong>{$count_films}</strong> фильмов и {$count_items} серий без учета дублей.<br>\n";

        $sql_text = ""; // Текст запроса для самостоятельного выполнения
        require_once (ENGINE_DIR . '/inc/include/p_construct/classes/VideoTubes.php');

        if (count($struct)) {
            $i_z = 0;
            $i_s = 0; // Счетчики
            $i_empty = 0;
            foreach ($struct as $post_id => $array) {
                if (count($array["items"]) > 0) {
                    $sql_text .= "INSERT INTO `" . PREFIX . "_vidvk_z` (id,post_id,name,sort,ssort) VALUES('{$vidvk_z_max}','{$post_id}','{$array['name']}','1','2');\n";
                    // Добавляю серии
                    foreach ($array["items"] as $lssort => $row) {
                        $sname = str_replace('&quot;', '"', $row["item_name"]);
                        $scode = htmlspecialchars_decode($row["item_code"]);
                        $codetype = VideoTubes::getInstance()->getTube($scode);
                        $scode = VideoTubes::getInstance()->code; // обработанный код
                        $sql_text .= "INSERT INTO `" . PREFIX . "_vidvk_s` (id,parent,sname,scode,lssort,codetype) 
							VALUES('{$vidvk_s_max}','{$vidvk_z_max}','" . $db->safesql($sname) . "','" . $db->safesql($scode) . "','{$lssort}','{$codetype}');\n";
                        $vidvk_s_max++;
                        $i_s++;
                    }
                    $i_z++;
                    $vidvk_z_max++;
                } else {
                    $i_empty++;
                }
            }
            if (isset($_GET["load"])) {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=dump.sql');
                exit($sql_text);
            }

            $sql_text = htmlspecialchars($sql_text, ENT_QUOTES, $config["charset"]);

            $data["text"] .= "<strong class='red'>Внимание!!!</strong> <span class='green'>Для конвертации БД ({$i_z} сборок, {$i_s} серий без {$i_empty} пустых сборок) вам надо 
                 вручную выполнить запрос</span>:<br>
					<a href='/install_vc.php?step=3&load'>Скачать</a> - более предпочтительнее чем вставка запроса из поля ниже.
                    <textarea>{$sql_text}</textarea>";
            $data["text"] .= "<p>Для выполнения запроса надо:</p>
                <ul><li>Зайти в PhpMyAdmin на вашем хостинге</li>
                <li>Выбрать вашу базу данных (" . DBNAME . ")</li>
                <li>Перейти на вкладку SQL</li>
                <li>В текстовое поле скопировать вышеуказанный запрос и нажать 
                'Выполнить' (или 'Go' на английском)</li></ul>";
            $data["text"] .= "<p>После выполнения запроса нажмите 'Дальше'.</p>";
        } else {
            $data["text"] .= "<p class='red'>Не найдено ни одного видео: 
                конвертация базы данных не требуется.</p>";
        }
        $data["next"] = "4";
        break;

    case 4: // Создание доп. поля  pconstruct
        $data["title"] = "Шаг 4: Создание доп. поля новостей";
        $text = file_get_contents(ENGINE_DIR . "/data/xfields.txt");
        if (strpos($text, "pconstruct") !== false) {
            $data["text"] .= "<p color='green'>Дополнительное поле новостей 
                'pconstruct' найдено. Нажмите 'Дальше'.</p>";
        } else {
            $href = $config["http_home_url"] . $config["admin_path"] . "?mod=xfields&xfieldsaction=configure";
            $data["text"] .= "<p>Зайдите в раздел '<a href='{$href}' target='_blank'>Список дополнительных полей</a>' админки.</p>";
            $data["text"] .= "<p>Добавьте поле:<br><span class='green'>Название поля: 
                <strong>pconstruct</strong><br>Описание поля: <strong>Конструктор видео</strong><br>
                Категория: <strong>Все</strong><br>Тип поля: <strong>Одна строка</strong><br>
                Значение по умолчанию: <strong>\"\" (оставить пустым)</strong><br>
                Использовать при желании (можно оставить поле пуcтым): <strong>Да (отметить)</strong></span></p>";
            $data["text"] .= "<p>Если у вас несколько доп. полей, вы можете их 
                сортировать, меняя таким образом место вывода формы конструктора.</p>";
        }
        $data["next"] = "5";
        break;

    case 5: // Создание раздела админке
        $data["title"] = "Шаг 4: Создание раздела админке";
        $row = $db->super_query("SELECT id FROM `" . PREFIX . "_admin_sections` WHERE name='parser_constructor'");
        if ($row) {
            $data["text"] .= "<p class='green'>Раздел уже был добавлен ранее. Добавление не требуется.</p>";
        } else {
            $sql = "INSERT INTO `" . PREFIX . "_admin_sections` (`name`, `title`, `descr`, `icon`, `allow_groups`) VALUES
('parser_constructor', 'Конструктор видео', 'Организация онлайн-видео на сайте, используя сторонние тюбы (vk.com, YouTube, RuTube, video.mail.ru и т.д.). ', 'vc3.png', '1,2,3');";
            $db->query($sql);
            $data["text"] .= "<p class='green'>Раздел создан.</p>";
            $href = $config["http_home_url"] . $config["admin_path"] . "?mod=parser_constructor";
        }
        $data["next"] = "6";
        break;

    case 6:
        $data["title"] = "Полуавтоматическая установка завершена";
        $data["text"] .= "<p class='red'>Удалите установочный файл <strong>/{$this_path}</strong>, 
            затем продолжите установку согласно инструкции по установке.</p>";

        // Поиск предыдущей версии (2.х)
        $sql = "SHOW tables LIKE 'vk%'";
        $res = $db->query($sql, true);
        $isset = false;
        while ($row = $db->get_array($res)) {
            if ($row[0] == "vkfilms" || $row[0] == "vkitems")
                $isset = true;
        }
        if ($isset) {
            $data["text"] .= "<p>Удостоверившись, что перенос видео прошёл без проблем, 
                Вы можете удалить таблицы <strong>vkfilms</strong> и <strong>vkitems</strong> 
                из базы данных, а также доп. поле <strong>vk_filmpack_id</strong>. 
                Можно и не удалять, мешать работе скрипта они не будут.</p>
                <p>Приятного пользования скриптом!</p>";
            $href = $config["http_home_url"] . $config["admin_path"] . "?mod=parser_constructor";
            $data["text"] .= "<button onclick=\"document.location.href='$href';\">В админку конструктора</button>";
        }

        break;

    default:
        $data["title"] = "Ошибка установщика";
        $data["text"] = "<p>Шаг не найден! Адрес не правильный!</p>";
        break;
}
header('Content-type: text/html; charset='.($config["charset"] == "utf-8" ? "utf-8" : "windows-1251") );
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Установка - Конструтор видео v3.x для DLE</title>
        <meta charset="<?php echo $config["charset"]; ?>">
    </head>
    <style>
        body { margin:0; color: #3a3a3a; }
        .conteiner {background-color: #eaf2f4; width:700px; height:450px; margin: 100px auto 0; border:6px solid #b4d0d7; border-bottom-color: #d1dcdf; border-right-color: #d1dcdf; border-radius:4px;}
        .head_block {font-size:18px; color: #052127; font-weight: bold; border-bottom: 2px dotted #146c82; padding:3px 10px 3px; }
        .content {height:360px; padding:10px; overflow-y: auto; }
        .bottom {margin: 10px 30px 10px;}
        .left{float:left;}
        .right{float:right;}
        a, a:visited {color: #05587a; }
        .red {color:#9f0335;}
        .green {color:#0c5b0e;}
        textarea {width:100%; height:60px;}
        .bold {font-weight:bold;}
    </style>
    <body>
        <div class="conteiner">
            <div class="head_block"><?php echo $data["title"]; ?></div>
            <div class="content"><?php echo $data["text"]; ?></div>
            <div class="bottom">
<?php if (isset($data["prev"])) echo "<button class='left' onclick=\"document.location='{$this_path}?step={$data['prev']}';\">Назад</button>"; ?>
<?php if (isset($data["next"])) echo "<button class='right' onclick=\"document.location='{$this_path}?step={$data['next']}';\">Дальше</button>"; ?>
            </div>
        </div>

    </body>
</html>