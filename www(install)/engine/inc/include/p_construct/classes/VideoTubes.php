<?php

/**
 * Класс видео-тюбов
 * 
 * @uses $config        Array
 * @uses array_iconv    Function
 */
class VideoTubes {
    
    private static $_instance = null;
    
    // Автономная работа, без запросов к внешним сервисам (prepare и т.д.)
    public $autonomous = false;
    
    // Используется ли плеер uppod (нужно ли подключать)
    public $uppodUsed = false; 
    
    public $types = array (
        1 => array (
            "name" => "Вконтакте",
            "name_html" => "<font color='#004080'><b>В</b>контакте</font>",
            "alt_name" => "vkv",
            "url" => "http://vk.com",
            "player" => '<iframe src="{url}" width="100%" height="100%" frameborder="0" class="item_vkv_video" allowfullscreen></iframe>'
        ),
        2 => array (
            "name" => "YouTube",
            "name_html" => "You<font color='#C60F13'>Tube</font>",
            "alt_name" => "ytb",
            "url" => "http://www.youtube.com/",
            "player" => '<iframe src="{url}" width="100%" height="100%" frameborder="0" scrolling="no" class="item_ytb_video" allowfullscreen></iframe>'
        ),
        3 => array (
            "name" => "RuTube",
            "name_html" => "<font color='white' style='background-color:#0E8F27;'>Ru</font>Tube",
            "alt_name" => "rtb",
            "url" => "http://rutube.ru/",
            "player" => '<iframe src="{url}" width="100%" height="100%" frameborder="0" scrolling="no" webkitAllowFullScreen mozallowfullscreen allowfullscreen></iframe>'
        ),
        4 => array (
            "name" => "MailRu", // В плеера есть автостарт
            "name_html" => "Mail.<font color='#E98101'>ru</font>",
            "alt_name" => "mlr",
            "url" => "http://video.mail.ru/",
            //"player" => '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="100%" height="100%" id="movie_name" align="middle"><param name="movie" value="http://img.mail.ru/r/video2/uvpv3.swf?3"/><param name="flashvars" value="movieSrc={url}&autoplay=0" /><param name="allowFullScreen" value="true" /><param name="AllowScriptAccess" value="always" /><!--[if !IE]>--><object type="application/x-shockwave-flash" data="http://img.mail.ru/r/video2/uvpv3.swf?3" width="100%" height="100%"><param name="movie" value="http://img.mail.ru/r/video2/uvpv3.swf?3"/><param name="flashvars" value="movieSrc={url}&autoplay=0" /><param name="allowFullScreen" value="true" /><param name="AllowScriptAccess" value="always" /><!--<![endif]--><a href="http://www.adobe.com/go/getflash"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player"/></a><!--[if !IE]>--></object><!--<![endif]--></object>'
            "player" => '<iframe src="//videoapi.my.mail.ru/videos/embed/{url}.html" width="100%" height="100%" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>',
            "player2" => '<iframe src="{url}" width="100%" height="100%" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>',
        ),
        5 => array (
            "name" => "Uppod",
            "name_html" => "<font color='red'>`Uppod</font>",
            "alt_name" => "upd",
            "descr" => "Видео в своем плеере (без списка серий)",
            "url" => "http://uppod.ru/",
            "player" => '<object id="videoplayer5330" type="application/x-shockwave-flash" data="{uppod_swf}" width="100%" height="100%">
    <param name="bgcolor" value="#ffffff" />
    <param name="allowFullScreen" value="true" />
    <param name="allowScriptAccess" value="always" />
    <param name="movie" value="{uppod_swf}" />
    <param name="flashvars" value="comment={uppod_comment}[uppod_style]&amp;st={uppod_style}[/uppod_style]&amp;file={url}[poster]&amp;poster={poster}[/poster][playlist]&amp;pl={playlist}[/playlist]" />
 </object>',
            "player_html5" => '<div class="player_html5" id="reationalVkUppod" style="width:100%;height:100%;"></div><script type="text/javascript">new Uppod({m:"video",uid:"reationalVkUppod",[playlist]pl:{playlist},[/playlist][poster]poster:"{poster}",[/poster]comment:"{uppod_comment}",[uppod_style]st:"uppodvideo",[/uppod_style]file:"{url}"});</script>',
        ),
        6 => array (
            "name" => "MoeVideo",
            "name_html" => "<font color='#2874E6'>Moё<strong>video</strong></font>.net",
            "alt_name" => "mvd",
            "descr" => "Сайт пренадлежит letitbit.net и позволяет проигровать загруженное видео.",
            "url" => "http://moevideo.net",
            /*"player" => '<embed src="http://moevideo.net/swf/letplayer.swf" quality="high" bgcolor="#000000" width="100%" height="100%" name="letplayer" align="middle" play="true" loop="false" quality="high" allowscriptaccess="always" type="application/x-shockwave-flash" flashvars="file={url}" allowfullscreen="true" pluginspage="http://www.adobe.com/go/getflashplayer"></embed>',*/
            'player' => '<iframe width="100%" height="100%" src="//moevideo.net/framevideo/{url}?width=100%&height=100%"  frameborder="0" allowfullscreen ></iframe>',
        ),
        7 => array (
            "name" => "Myvi.ru",
            "name_html" => "<font style='color:white;background-color:#366286;'>Myvi.ru</font>",
            "alt_name" => "myv",
            "url" => "http://www.myvi.ru/",
            "player" => '<object style="width: 100%; height: 100%;">
                <param name="allowFullScreen" value="true"/>
                <param name="allowScriptAccess" value="always" />
                <param name="movie" value="{url}" />
                <param name="flashVars" value="kgzp=replace" />
                <embed src="{url}" type="application/x-shockwave-flash" allowfullscreen="true" allowScriptAccess="always" width="100%" height="100%" flashVars="kgzp=replace">
                </object>',
            "player_iframe" => '<iframe width="100%" height="100%" src="{url}" frameborder="0" scrolling="no" allowfullscreen></iframe>'
        ), 
        8 => array (
            "name" => "Kiwi.kz",
            "name_html" => "<font style='color:#0e89a7;'>ki<strong style='color:#22a52b;'>v</strong>vi</font>",
            "alt_name" => "kiv",
            "url" => "http://kiwi.kz/",
            "player" => '<iframe width="100%" height="100%" src="{url}" frameborder="0" scrolling="no" allowfullscreen></iframe>'
        ),
        9 => array (
            "name" => "VideoYandex",
            "name_html" => "<font style='color:white;background-color:#333333'><font style='color:red'>Я</font>ндекс</font>",
            "alt_name" => "yan",
            "url" => "http://yandex.ru/video/",
            "player" => '<iframe width="100%" height="100%" src="{url}" frameborder="0" scrolling="no" allowfullscreen></iframe>'
        ),
        10 => array (
            "name" => "Megogo.net",
            "name_html" => '<span style="color:#999"><span style="background-color:#222;color:white;border-left:3px solid transparent;border-right:3px solid transparent;">M</span>egogo<span style="color:#555">.net</span></span>',
            "alt_name" => "meg",
            "url" => "http://megogo.net/",
            "player" => '<iframe width="100%" height="100%" src="{url}" frameborder="0" scrolling="no" allowfullscreen></iframe>'
        ),
        11 => array (
            "name" => "Ivi.ru",
            "name_html" => '<span style="color:#b3082f;font-weight:bold;">ivi</span>.ru',
            "alt_name" => "ivi",
            "url" => "http://ivi.ru/",
            "player" => '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="100%" height="100%"><param name="movie" value="{url}" /><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="true" /><param name="bgcolor" value="#000000" /><param name="wmode" value="opaque" /><embed src="{url}" quality="high" allowscriptaccess="always"allowfullscreen="true" wmode="opaque"  width="100%" height="100%" type="application/x-shockwave-flash"></embed></object>'
        ),
        12 => array (
            "name" => "Kset.kz",
            "name_html" => '<strong>K<span style="color:#a31212;">set</span>.</strong><span style="color:#535353;">kz</span>',
            "alt_name" => "kst",
            "url" => "http://kset.kz/",
            "player" => '<iframe width="100%" height="100%" src="{url}" frameborder="0" scrolling="no" allowfullscreen></iframe>'
        ),
        13 => array (
            "name" => "Video.sibnet.ru",
            "name_html" => '<span style="color:white;background-color:#777;padding:0 2px 0;">video.</span><span style="color:#265EB0;">sibnet</span><span style="color:white;background-color:#777;padding:0 2px 0;">.ru</span>',
            "alt_name" => "sib",
            "url" => "http://video.sibnet.ru/",
            "player" => '<iframe width="100%" height="100%" src="{url}" frameborder="0" scrolling="no" allowfullscreen></iframe>'
        ),
        14 => array (
            "name" => "Allserials.tv",
            "name_html" => '<span style="color:#172E6A;">all</span><span style="color:#DC0A0A;">serials</span><span style="color:#172E6A;">.</span><span style="color:white;background-color:#DC0A0A;padding:0 2px 0;">tv</span>',
            "alt_name" => "als",
            "url" => "http://allserials.tv/",
            "player" => '<iframe width="100%" height="100%" src="{url}" frameborder="0" scrolling="no" allowfullscreen></iframe>'
        ),
        15 => array (
            "name" => "Kinostok.tv",
            "name_html" => 'Kinostok.tv',
            "alt_name" => "kns",
            "url" => "http://kinostok.tv/",
            "player" => '<object width="100%" height="100%"><param name="movie" value="{url}"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="{url}" wmode="transparent" width="100%" height="100%" allowscriptaccess="always" allowfullscreen="true" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" /></embed></object>',
            "player2" => '<object type="application/x-shockwave-flash" data="//kinostok.tv/uppod_player/uppod.swf" width="100%" height="100%" id="player"><param name="allowFullScreen" value="true" /><param name="allowScriptAccess" value="always" /><param name="movie" value="//kinostok.tv/uppod_player/uppod.swf" /><param name="FlashVars" value="{url}" /></object>',
            "player_iframe" => '<iframe width="100%" height="100%" src="{url}" frameborder="0" scrolling="no" allowfullscreen></iframe>',
        ),
        16 => array (
            "name" => "Video.meta.ua",
            "name_html" => '<span style="color:#C5570E;">Video<span style="color:#1B4BA5;">&lt;meta&gt;</span>.ua</span>',
            "alt_name" => "met",
            "url" => "http://video.meta.ua/",
            "player" => '<iframe width="100%" height="100%" src="{url}" frameborder="0" scrolling="no" allowfullscreen></iframe>',
            "player_object" => '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="//download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="100%" height="100%" id="player_3505806" align="top"><param name="id" value="player_3505806"><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="true" /><param name="bgcolor" value="#A6A6A6" /><param name="wmode" value="opaque" /><param name="FlashVars" value="fileID={url}&fileID_url=http%3A%2F%2Fvideo.meta.ua%2Fplayers%2Fgetparam%2F%3Fv%3D&color=1" /><param name="movie" value="//video.meta.ua/players/video/3.2.22a/Player.swf" /><param name="quality" value="high" /><param name="border" value="0" /><embed border="0" src="//video.meta.ua/players/video/3.2.22a/Player.swf" wmode="opaque" quality="high" bgcolor="#A6A6A6" width="100%" height="100%" name="player" align="top" allowScriptAccess="always" allowFullScreen="true" type="application/x-shockwave-flash" pluginspage="//www.macromedia.com/go/getflashplayer" FlashVars="fileID={url}&fileID_url=http%3A%2F%2Fvideo.meta.ua%2Fplayers%2Fgetparam%2F%3Fv%3D&color=1" /></object>',
        ),
        17 => array (
            "name" => "Intv.ru",
            "name_html" => '<span style="color:#D83A29">In</span><span style="color:#467060">TV</span>.<span style="color:#0072B0">ru</span>',
            "alt_name" => "itv",
            "url" => "http://www.intv.ru/",
            "player" => '<object width="100%" height="100%"><param name="movie" value="{url}" /><param name="scale" value="noscale" /><param name="salign" value="lt" /><param name="allowFullScreen" value="true" /><param name="allowScriptAccess" value="always" /><embed src="{url}" type="application/x-shockwave-flash" scale="noscale" salign="lt" allowFullScreen="true" allowSriptAccess="always" width="100%" height="100%"></embed></object>'
        ),
        18 => array (
            "name" => "Openfile.ru",
            "name_html" => '<span style="color:#F80847">Open</span><span style="color:#A3A3A3">File</span>.ru',
            "alt_name" => "opf",
            "url" => "http://openfile.ru",
            "player" => '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="//download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="100%" height="100%" id="player" align="middle"><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="true" /><param name="FLASHVARS" value="videoID={url}" /><param name="movie" value="//whitecdn.org/player/f4af2384bdbe741ed23e7b0c230f4d17/player.swf" /><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF" /><embed src="//whitecdn.org/player/f4af2384bdbe741ed23e7b0c230f4d17/player.swf" quality="high" bgcolor="#FFFFFF" width="100%" height="100%" name="player" align="middle" allowScriptAccess="always" allowFullScreen="true" type="application/x-shockwave-flash" pluginspage="//www.adobe.com/go/getflashplayer" FLASHVARS="videoID={url}" /></object>'
        ),
        20 => array (
            "name" => "Veterok.tv",
            "name_html" => '<span style="color:#173777;">ВЕТЕРОК<sup style="color:#b9bcc2;">тв</sup></span>',
            "alt_name" => "vet",
            "url" => "http://veterok.tv/",
            "player" => '<iframe width="100%" height="100%" src="{url}" frameborder="0" scrolling="no" allowfullscreen></iframe>'
        ),
        21 => array (
            "name" => "Stepashka.com",
            "name_html" => '<span style="color:#797979;"><span style="color: #af2e11;">S</span>tepashka.com</span>',
            "alt_name" => "stp",
            "url" => "http://online.stepashka.com/",
            "player" => '<iframe width="100%" height="100%" src="{url}" frameborder="0" scrolling="no" allowfullscreen></iframe>',
            "player_obj" => '<object type="application/x-shockwave-flash" data="//online.stepashka.com/static/player.swf" width="100%" height="100%" id="player"><param name="allowFullScreen" value="true" /><param name="allowScriptAccess" value="always" /><param name="movie" value="//online.stepashka.com/static/player.swf" /><param name="FlashVars" value="{url}" /></object>',
        ),
        22 => array (
            "name" => "Clipiki.ru",
            "name_html" => '<span style="color:#707070;">Clipiki.ru</span>',
            "alt_name" => "clp",
            "url" => "http://clipiki.ru/",
            "player" => '<object type="application/x-shockwave-flash" data="//clipiki.ru/flash/uppod.swf" width="100%" height="100%" id="player"><param name="allowFullScreen" value="true" /><param name="allowScriptAccess" value="always" /><param name="movie" value="//clipiki.ru/flash/uppod.swf" /><param name="FlashVars" value="{url}" /></object>'
        ),
        23 => array (
            "name" => "video.az",
            "name_html" => '<span style="color:black;">Video<span style="color:#126d62;">.az</span></span>',
            "alt_name" => "vaz",
            "url" => "http://www.video.az/",
            "player" => '<iframe width="100%" height="100%" src="{url}" frameborder="0" scrolling="no" allowfullscreen></iframe>'
        ),
        24 => array (
            "name" => "Enter.az",
            "name_html" => '<span style="color:#252525;">ENTER<span style="color:#525252;border:1px solid silver;margin-left:1px;">AZ</span></span>',
            "alt_name" => "eaz",
            "url" => "http://enter.az",
            "player" => '<iframe width="100%" height="100%" src="{url}" frameborder="0" scrolling="no" allowfullscreen></iframe>'
        ),
        25 => array (
            "name" => "VideoKub",
            "name_html" => '<span style="color: #73355d;">Video</span><span style="color: #3a7e80;">Kub</span>',
            "alt_name" => "vkb",
            "url" => "http://www.videokub.com/",
            "player" => '<iframe width="100%" height="100%" src="{url}" frameborder="0" scrolling="no" allowfullscreen></iframe>'
        ),
        26 => array (
            "name" => "VZALE",
            "name_html" => '<span style="color:white;background-color:#4d7c9e;padding:0 2px;">VZALE</span>',
            "alt_name" => "vzl",
            "url" => "http://vzale.tv/",
            "player" => '<object type="application/x-shockwave-flash" data="//www.vzale.tv/player/player.swf" width="100%" height="100%"><param name="bgcolor" value="#ffffff" /><param name="allowFullScreen" value="true" /><param name="allowScriptAccess" value="always" /><param name="wmode" value="transparent" /><param name="movie" value="//www.vzale.tv/player/player.swf" /><param name="flashvars" value="{url}" /></object>'
        ),
        27 => array (
            "name" => "Truba.com",
            "name_html" => '<span style="color:#707070;">ТРУБА</span>',
            "alt_name" => "trb",
            "url" => "http://truba.com/",
            "player" => '<iframe width="100%" height="100%" src="{url}" frameborder="0" scrolling="no" allowfullscreen></iframe>'
        ),
        28 => array(
            "name" => "Cinem.tv",
            "name_html" => '<span style="color:gray;">Cinem.tv</span>',
            "alt_name" => "ctv",
            "url" => "http://cinem.tv/",
            "player" => '<iframe width="100%" height="100%" src="{url}" frameborder="0" scrolling="no" allowfullscreen></iframe>'
        ),
        29 => array(
            "name" => "Namba.net",
            "name_html" => 'Namba.net',
            "alt_name" => "nba",
            "url" => "http://namba.net/",
            "player" => '<object height="100%" width="100%" type="application/x-shockwave-flash" data="http://video.namba.net/swf/player/3.2.11/flowplayer-3.2.11.swf"><param value="true" name="allowfullscreen"><param value="opaque" name="wmode"><param value="always" name="allowscriptaccess"><param name="src" value="http://video.namba.net/swf/player/3.2.11/flowplayer-3.2.11.swf" /><param value="config=http://video.namba.net/{url}" name="flashvars"></object>',
            "player2" => '<object height="100%" width="100%" type="application/x-shockwave-flash" data="http://video.namba.kz/swf/player/3.2.11/flowplayer-3.2.11.swf"><param value="true" name="allowfullscreen"><param value="opaque" name="wmode"><param value="always" name="allowscriptaccess"><param name="src" value="http://video.namba.kz/swf/player/3.2.11/flowplayer-3.2.11.swf" /><param value="config=http://video.namba.kz/{url}" name="flashvars"></object>',
        ),
        30 => array (
            "name" => "Vimple.ru",
            "name_html" => '<span style="color:#00B4E1;">Vimple.ru</span>',
            "alt_name" => "vim",
            "url" => "http://vimple.ru/",
            "player" => '<iframe width="100%" height="100%" src="{url}" frameborder="0" scrolling="no" allowfullscreen></iframe>',
        ),
        110 => array (
            "name" => "Iframe",
            "name_html" => '<span style="color:#0c2552;">&lt;</span><span style="color: #87132f;">iframe</span><span style="color:#0c2552;">&gt;</span>',
            "alt_name" => 'ifr',
            "url" => "#",
            "player" => '<iframe width="100%" height="100%" src="{url}" frameborder="0" scrolling="no" allowfullscreen></iframe>',
        )
    );
    
    /** Список ошибок */
    var $errors = array();
    /** Массив типов видео (ID типа по ключах alt_names)  */
    var $alt_types = null;
    /** Нормальный текст кода (обработаный с помощью is_...() ) */
    var $code = null;
    var $quality = null;
    /** Максимальное качество (240, 360, 480, 720 ) */
    var $max_quality;
    /** Ошибка при формировании стандартного кода */
    var $code_def_err = false;
    /** Использование альтернативного шаблона плеера, для поддержки нескольких кодов плеера */
    var $player_tpl = null;
    
    var $check_errors = array(
        1 => "Удалено",
        2 => "Неизвестный tube",
        3 => "Начальная ошибка",
        4 => "Не поддерживается проверка для этого tube",
        5 => "Ошибка при проверке - возможно неполадки в PHP или JS",
        6 => "Тип кода не правильный",
        53 => "Нет ответа от сервера/таймаут",
        51 => "Ошибка проверки: checkTube()",
        52 => "Ошибка проверки на стороне PHP",
        11 => "Встраивание запрещено"
    );
    
    // Allow-лист хостингов
    var $allow_hosting = array (
        "vk.com", "youtube.com", "rutube.ru", "video.mail.ru",
        "myvi.ru","myvi.tv", "kiwi.kz", "video.yandex.ru",
        "megogo.net", "ivi.ru",
        "video.sibnet.ru", "kinostok.tv", "veterok.tv",
        "truba.com", "video.az", "vzale.tv", //clipiki.ru
        "cinem.tv", "now.ru", "video.meta.ua",
    );
    
    // тюбы, по которым разрешен поиск tubeId => site:"yyy"
    public $allow_search = array( // Дублируется на сервере
        "vkv" => "vk.com",
        "ytb" => "youtube.com",
        "rtb" => "rutube.ru",
        "mlr" => "video.mail.ru",
        "myv" => "myvi.ru",
        "kiv" => "kiwi.kz",
        "yan" => "video.yandex.ru",
        "meg" => "megogo.net",
        //"ivi" => "ivi.ru",
        "sib" => "video.sibnet.ru",
        "kns" => "kinostok.tv", 
        //"vet" => "veterok.tv", // почему не работает ???
        //"trb" => "truba.com", // почему не работает ???
        //"vaz" => "video.az",
        //"vzl" => "vzale.tv", // почему не работает ???
        //"ctv" => "cinem.tv", // почему не работает ???
        //"now.ru" => "now.ru",
        //"met" => "video.meta.ua", // почему не работает ???
        "kst" => "~/kset", // Отдельный поиск
    );
    
    private function __construct(){}
    private function __clone()    {}
    private function __wakeup()   {}
    public static function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new VideoTubes();
        }
        return self::$_instance;
    }
    
    /**
     * Получение кода вконтакте
     * @param   type        $str
     * @return  boolean     False или строка, напр.: oid=44171236&id=162708565&hash=95c81e591fb889c9&hd=1
     */
    function is_vkv($str) {
        if (substr($str,0,5)!=="ifr::") {
			$arr = array();
			if (preg_match ("/oid=[^\"'\?]+hash=[a-f0-9]+(&api_hash=[a-z\d]+)?&?s?h?d?=?[0-9]?/si", $str, $arr)) {
				// http://vk.com/video_ext.php?oid=44171236&id=162708565&hash=95c81e591fb889c9&hd=1
				// oid=-30590621&id=161466674&hash=a4b744867db0191c
				return $arr[0]; // oid=44613381&id=164773632&hash=4dd978a7c69b9b03&hd=1
			} else if (preg_match ("/vk\.com\/video([-\d]+)_([\d]+)/si", $str, $arr)) {
				// http://vk.com/video44171236_162708565
				return "prep(vkv-1)::".$arr[0]; //prep(vkv-1)::http://vk.com/video44171236_162708565
			}
		}
        return false;
    }
    
    function prep_vkv ($str, $func) {
        if ($this->autonomous) return $str;
        switch ($func) {
            case "1":
                // http://vk.com/video44171236_162708565
                global $CurlBrowser;
                $res = $CurlBrowser->request($str, 'get', null, null, array("CURLOPT_FOLLOWLOCATION"=>0));
                //var_dump($res);
                if (preg_match("/var src = '(.+?)';/",$res->content(),$arr)) {
                    return $arr[1]; // http://video.yandex.ru/iframe/tdp1204/gkbf9b9qoi.1212/?player-type=full
                }
                break;
        }
        return false;
    }
    
    function check_vkv ($code) {
        $code = $this->is_vkv($code);
        $this->max_quality = 0; // Макс. качество
        if (!($code===false)) {
            $url = 'http://vk.com/video_ext.php?' . $code;
            global $CurlBrowser;
            $res = $CurlBrowser->request($url, 'get', null, array(
                'Cookie: remixlang=0;' // rus
            ));
            $html = $res->content();
            if ($html=='' || $html==false) return 53;
            if (strpos($html, 'var video_max_hd =')!==false){ // id="playerObj"
                // Определение максимального качества
                if (preg_match ("/var video_max_hd = '([0-9]?)';/", $html, $arr)) {
                    switch (intval($arr[1])) {
                        case "3": $this->max_quality = 720; break;
                        case "2": $this->max_quality = 480; break;
                        case "1": $this->max_quality = 360; break;
                        case "0": $this->max_quality = 240; break;
                    }
                } else {
                    $this->error('Не удалось определить максимальное качество видео для vkv.', 'vkv', $html);
                }
                return 0;
            } else {
                if (stripos($html, 'Материал для взрослых')!==false)
                    return 11;
                else
                    return 1;
            }
        } else {
            return 6; // Неправильный тип кода
        }
    }
    
    /**
     * Получение кода ютуба
     * @param type $str Строка
     * @return boolean  False или строка (напр. "youtube.com/embed/Htp8UHPRhjk?rel=0") - ссылка на видео
     */
    function is_ytb ($str) {
        
        if (strpos($str,'youtube.com')!==false || strpos($str,'youtube-nocookie.com')!==false) {
            $arr = array();
//            // Тут надо подгружать
//            if (preg_match ("/youtube.com\/watch\?feature=player_embedded&v=[A-Za-z0-9-_]+/", $str, $arr)) {
//                // https://www.youtube.com/watch?feature=player_embedded&v=uSByAttcIuw
//                return $arr[0]; // youtube.com/watch?feature=player_embedded&v=uSByAttcIuw
//            }
            if (preg_match ("/youtube(-nocookie)?\.com\/embed\/[A-Za-z0-9-_]+/", $str, $arr)) { // нормальная строка
                // http://www.youtube.com/v/Htp8UHPRhjk?hl=ukUA&amp;version=3&amp;rel=0
                // www.youtube.com/embed/3Hz3kHRRISU?list=PLF40DD57434E48E66 - плер с плейлистом
                preg_match ("/[\?&](list=[A-Za-z0-9-_]+)/", $str, $arr2);
                return $arr[0].'?rel=0'.(isset($arr2[1]) ?  "&{$arr2['1']}": ""); 
            }
            if (preg_match ("/youtube\.com\/watch\?v=([A-Za-z0-9-_]+)/", $str, $arr)) {
                // http://www.youtube.com/watch?v=Htp8UHPRhjk
                return 'youtube.com/embed/'.$arr[1].'?rel=0'; // youtube.com/embed/Htp8UHPRhjk?rel=0
            }
            if (preg_match ("/youtube\.com\/v\/([A-Za-z0-9-_]+)/", $str, $arr)) {
                // http://www.youtube.com/v/Htp8UHPRhjk?hl=ukUA&amp;version=3&amp;rel=0
                return 'youtube.com/embed/'.$arr[1].'?rel=0'; 
            }
            $this->error('Нет подходящего шаблона для is_ytb().', 'ytb', $str);
        } 
        return false;
    }
    
    function check_ytb ($code) {
        $code = $this->is_ytb($code);
        $this->max_quality = 0; // Макс. качество
        if (!($code===false)) {
            // Получаем ID youtube
            if (preg_match ("/youtube\.com\/embed\/([A-Za-z0-9-_]+)/", $code, $id) && isset($id[1])) {
                $id = $id[1];
                $url = 'http://www.youtube.com/watch?v='.$id;
                global $CurlBrowser;
                $res = $CurlBrowser->request($url);
                $html = $res->content();
                //echo $html; exit;
                
                if (preg_match ("/\"fmt_list\": \"(.+?)\"/", $html, $arr)) {
                    //print_r ($arr); exit;
                    if (preg_match_all("/[0-9]+x([0-9]+)/", $arr[1], $arr2)) {
                        //print_r ($arr2); exit;
                        if (count($arr2[1])) {
                            $max_q = 0;
                            foreach ($arr2[1] as $key => $val) 
                                if ($val>$max_q) $max_q = $val;
                            $this->max_quality = $max_q;
                        } else 
                            $this->error('Не удалось определить максимальное качество видео для ytb. (3)', 'ytb', $html);
                    } else
                        $this->error('Не удалось определить максимальное качество видео для ytb. (2)', 'ytb', $html);
                } else 
                    $this->error('Не удалось определить максимальное качество видео для ytb.', 'ytb', $html);
                
                if (strpos($html,"'IS_UNAVAILABLE_PAGE': true,")>0){
                    return 1;
                } else {
                    if (strpos($html,'"allow_embed": 0')!==false) {
                        return 11;
                    } else {
                        return 0;
                    }
                }
            } else {
                return 5;
            }
        } else {
            return 6; // Неправильный тип кода
        }
    }
    
    /**
     * Получение кода рутуба
     * @param type $str
     * @return array|boolean
     */
    function is_rtb ($str) {
        
        $this->code_def_err = false;
        if (strpos($str,'rutube.ru')!==false) {
            $arr = array();
            if (preg_match ("/rutube\.ru\/play\/embed\/[A-Za-z0-9]+/", $str, $arr)) { // Новее код
                // http://rutube.ru/play/embed/6089719
                // http://rutube.ru/play/embed/c1839f23321d37b9b1bcea6b75a58c5e/
                return $arr[0]; // rutube.ru/play/embed/6089719
                                // rutube.ru/play/embed/c1839f23321d37b9b1bcea6b75a58c5e
            }
			if (preg_match ("/rutube\.ru\/video\/embed\/[A-Za-z0-9]+/", $str, $arr)) {
                // http://rutube.ru/video/embed/6089719
                // http://rutube.ru/video/embed/c1839f23321d37b9b1bcea6b75a58c5e/
                return $arr[0]; // rutube.ru/video/embed/6089719
                                // rutube.ru/video/embed/c1839f23321d37b9b1bcea6b75a58c5e
            } 
            if (preg_match ("/rutube\.ru\/video\/[A-Fa-z0-9]{32}\//", $str, $arr)) {
                // http://rutube.ru/video/32e6cd7565a5f3fdc6492f7e2e148432/ (потребує використання api)
                $url = 'http://' . $arr[0];
                $url = "http://rutube.ru/api/oembed/?url=$url&format=json"; // api url
                $html = file_get_contents($url);
                if ($html=='') {
                    $this->error('Нет ответа от сервера. Возможно, встройка видео запрещена', 'rtb', $str);
                    $this->code_def_err = true;
                    return $arr[0];
                }
				echo $html;

                $resp = @json_decode($html, true);
                if ($resp) {
                    //print_r($resp);
                    if ($resp['detail']=='Not found') {
                        $this->error('Видео не найдено', 'rtb', $str);
                    } else {
                        // Відео знайдене
                        if (isset($resp['html'])) {
                            if (preg_match ("/rutube\.ru\/play\/embed\/[0-9]+/", $resp['html'], $arr2)) {
                                return $arr2[0]; // rutube.ru/play/embed/6089719
                            } else {
                                $this->error('Не найдена ссылка в коде плеера по шаблону', 'rtb', $str);
                            }
                        } else {
                            $this->error('Код для вставки видео не найден', 'rtb', $str);
                        }
                    }
                    return $arr[0];
                } else {
                    $this->error('Данные JSON не распознаны!', 'rtb', $str);
                    return $arr[0];
                }

            } else {
                $this->error('Нет подходящего шаблона для is_rtb().', 'rtb', $str);
            }
        }
        return false;
    }
    
    function check_rtb ($code) {
        $code = $this->is_rtb($code);
        $this->max_quality = 0; // Макс. качество
        if ($this->code_def_err === false) {
            if (!($code===false)) {
                $url = 'http://'.$code;
                global $CurlBrowser;
                $res = $CurlBrowser->request($url);
                $html = $res->content();
                //echo $html; exit;
                if (strpos($html, '{&quot;video_url&quot;: &quot;')!==false) {
                    return 0;
                } else {
                    return 1;
                }
            }
        } else {
            return 11;
        }
        return false;
    }
    
    /**
     * Mail.ru
     * @param type $str
     * @return      Return: video.mail.ru/mail/sement68/775/4412 . Url: mail/sement68/775/4412
     */
    function is_mlr ($str) {
        
        $rx = preg_match("/^(mail|list|inbox)\/[A-Za-z0-9-_.]+\/[A-Za-z0-9-_.]+\/[0-9]+$/", $str);
        if ($rx || strpos($str,'mail.ru')!==false) {
            if ($rx) {
                // mail/stanislav.orlov/10300/16135
                // inbox/fic1955/16846/23979
                // list/lena-vitalik/29/27
                return $str;
            }
            if (preg_match ("/video\.mail\.ru\/([A-Za-z0-9-]+\/[^\/]+\/[^\/]+\/[^\/.]+)\.html/", $str, $arr)) { 
                // http://video.mail.ru/mail/stanislav.orlov/10300/16135.html
                // http://video.mail.ru/list/lena-vitalik/29/27.html
                // http://video.mail.ru/inbox/fic1955/16846/23979.html
                return $arr[1]; // mail/stanislav.orlov/10300/16135
            }
            if (preg_match ("/#video=\/(mail\/[^\/]+\/[^\/]+\/[^\/.]+)/", $str, $arr)) {
                // http://my.mail.ru/video/mail/upod/#video=/mail/retypod/_myvideo/2
                // http://my.mail.ru/video/mail/xxxxx/#video=/mail/retypod/_myvideo/2
                return $arr[1]; // mail/retypod/_myvideo/2
            }
            if (preg_match("/value=\"movieSrc=([^\"'&]+)/", $str, $arr)) {
                // <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="626" height="367" id="movie_name" align="middle"><param name="movie" value="http://img.mail.ru/r/video2/uvpv3.swf?3"/><param name="flashvars" value="movieSrc=mail/sement68/775/4412&autoplay=0" /><param name="allowFullScreen" value="true" /><param name="AllowScriptAccess" value="always" /><!--[if !IE]>--><object type="application/x-shockwave-flash" data="http://img.mail.ru/r/video2/uvpv3.swf?3" width="626" height="367"><param name="movie" value="http://img.mail.ru/r/video2/uvpv3.swf?3"/><param name="flashvars" value="movieSrc=mail/sement68/775/4412&autoplay=0" /><param name="allowFullScreen" value="true" /><param name="AllowScriptAccess" value="always" /><!--<![endif]--><a href="http://www.adobe.com/go/getflash"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player"/></a><!--[if !IE]>--></object><!--<![endif]--></object>
                // <lj-embed><object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="626" height="367" id="movie_name" align="middle"><param name="movie" value="http://img.mail.ru/r/video2/uvpv3.swf?3"/><param name="flashvars" value="movieSrc=mail/sement68/775/4412&autoplay=0" /><param name="allowFullScreen" value="true" /><param name="AllowScriptAccess" value="always" /><!--[if !IE]>--><object type="application/x-shockwave-flash" data="http://img.mail.ru/r/video2/uvpv3.swf?3" width="626" height="367"><param name="movie" value="http://img.mail.ru/r/video2/uvpv3.swf?3"/><param name="flashvars" value="movieSrc=mail/sement68/775/4412&autoplay=0" /><param name="allowFullScreen" value="true" /><param name="AllowScriptAccess" value="always" /><!--<![endif]--><a href="http://www.adobe.com/go/getflash"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player"/></a><!--[if !IE]>--></object><!--<![endif]--></object></lj-embed>
                return $arr[1]; // mail/stanislav.orlov/10300/16135
            }
            if (preg_match ("/api\.video\.mail\.ru\/videos\/embed\/([A-Za-z0-9-]+\/[^\/]+\/[^\/]+\/[^\/.]+)\.html/", $str, $arr)) { // изменение кода 26.08.2013
                // <iframe src="http://api.video.mail.ru/videos/embed/mail/retypod/_myvideo/2.html" width="626" height="367" frameborder="0"></iframe>
                return $arr[1]; // mail/stanislav.orlov/10300/16135
            }
            if (preg_match ("/https?:\/\/videoapi\.my\.mail\.ru\/videos\/embed\/[^<>\"'\s]+/", $str, $arr)) { // изменение кода 26.08.2013
                // <iframe src='https://videoapi.my.mail.ru/videos/embed/bk/iskenderova-lilia2010/_myvideo/324.html' width='626' height='367' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                $this->player_tpl = "player2";
                return 'mlr::'.$arr[0]; // mlr::https://videoapi.my.mail.ru/videos/embed/bk/iskenderova-lilia2010/_myvideo/324.html
            }
            $this->error('Нет подходящего шаблона для is_mlr().', 'mlr', $str);
        }
        return false;
    }
    
    function check_mlr ($code) {
        $code = $this->is_mlr($code);
        $this->max_quality = 0; // Макс. качество
        if (!($code===false)) {
            // Url ex: http://api.video.mail.ru/videos//mail/retypod/_myvideo/2.json
            
            // Каконизация ссылки
            if (substr($code, 0, 5) === 'mlr::') {
                if (preg_match("/\/videos\/embed\/(.+)\.html$/", $code, $arr)) {
                    $code = $arr[1];
                }
            }
            $url = "http://videoapi.my.mail.ru/videos/".$code.".json";

            global $CurlBrowser;
            $res = $CurlBrowser->request($url);
            //var_dump ($res); exit;
            if ($res->http_status=="403" && strpos($res->content(), "You have not got access to this private video")) {
                return 11; // Встраивание запрещено
            } if ($res->http_status=="404" && strpos($res->content(), "Can't find Video Instance")) {
                return 1; // Удалено
            } if ($res->http_status=="200" && strpos($res->content(), '"externalId"')) {
                return 0; // Нет ошибки
            } else
                return 52; // Ошибка проверки на стороне PHP
        } else
            return 6;
    }
    
    /**
     * Видео по прямой ссілке (используется плеер UPPOD)
     * @param type $str
     * @return      Return: upd::http://...
     */
    function is_upd($str) {
        
        $arr = array();
        if (substr($str, 0, 5)==='upd::') {
            return $str;
        }
        if(preg_match("/^\s*((http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/~\+#\[\]]*[\w\-\@?^=%&amp;\/~\+#\[\]])?)\s*\.(mp4|flv)$/", $str, $arr)) {
            return 'upd::'.$str; // upd::http://video.mysite.ru/mail/stanislav.orlov/10300/16135.flv
        }
        return false;
    }
    
    function check_upd ($code) {
        $code = $this->is_upd($code);
        $this->max_quality = 0; // Макс. качество
        if (!($code===false)) {
            $code = substr ($code, 5);
            if ($code{0}=='/') {
                global $config;
                $code = $config['http_home_url'].substr($code,1);
            }
            if (substr($code,0,4)=='http') {
                $fp = fopen($code, "r");
                if ($fp) {
                    $res = fread($fp, 2048);
                    fclose($fp);
                    if (strlen($res)>0) {
                        return 0;
                    } else {
                        return 1;
                    }
                } else {
                    return 1;
                }
            } else
                return 52;
        } else
            return 6;
    }
    
    /**
     * Проверка кода moevideo.net/letitbit
     * @param type $str
     * @return array|boolean
     */
    function is_mvd($str) {
        
        if ( substr($str,0,5)==="mvd::" || strpos($str,'letitbit.net')!==false || strpos($str,'moevideo.net')!==false) {
            
            if (preg_match("/^mvd::[A-Za-z0-9.]+$/", $str)) {
                //mvd::16122.13b99c3e9c21cad0d3203fc9b140
                return $str;
            }
            if (preg_match("/letitbit\.net\/download\/([A-Za-z0-9.]+)\//", $str, $arr)) {
                // http://u6286142.letitbit.net/download/52393.591e7d06ab4c9056ace666c2e078/brosok.kobry.2.700.letitbit-movie.com.avi.html
                return 'mvd::'.$arr[1]; //mvd::16122.13b99c3e9c21cad0d3203fc9b140
            }
            if (preg_match("/moevideo\.net\/video\/([A-Za-z0-9.]+)/", $str, $arr)) {
                // http://moevideo.net/video/16122.13b99c3e9c21cad0d3203fc9b140
                return 'mvd::'.$arr[1]; //mvd::16122.13b99c3e9c21cad0d3203fc9b140
            }
            if (preg_match("/moevideo\.net\/video\.php\?file=([A-Za-z0-9.]+)/", $str, $arr)) {
                // http://moevideo.net/video.php?file=2039.20940962d5857d34a9ddf49877ce&width=600&height=450
                return 'mvd::'.$arr[1]; //mvd::16122.13b99c3e9c21cad0d3203fc9b140
            }
            if (preg_match("/moevideo\.net\/framevideo\/([A-Za-z0-9.]+)\?/", $str, $arr)) { // изменение кода 26.08.2013
                // http://moevideo.net/framevideo/16456.143d0e146d7013de0aea6b031a81?width=640&height=360
                return 'mvd::'.$arr[1]; //mvd::16122.13b99c3e9c21cad0d3203fc9b140
            }  
            $this->error('Нет подходящего шаблона для is_mvd().', 'mvd', $str);
            
        }
        return false;
    }
    
    /**
     * Проверка кода myvi.ru
     * @param type $str
     * @return array|boolean
     */
    function is_myv($str) {
        
        if (strpos($str,'myvi.ru')!==false || strpos($str,'myvi.tv')!==false) {
            if (preg_match("/^myvi\.ru\/player\/flash\/[A-Za-z0-9_-]+$/", $str)) {
                //myvi.ru/player/flash/ocp2qZrHI-eZnHKQBK4cZV60hslH8LALnk0uBfKsB-Q6VchFnuU9mUcT3JS4P73TJ0
                return $str;
            }
            if (preg_match("/https?:\/\/(myvi\.ru\/player\/flash\/[A-Za-z0-9_-]+)/", $str, $arr)) {
                //http://myvi.ru/player/flash/ocp2qZrHI-eZnHKQBK4cZV60hslH8LALnk0uBfKsB-Q6VchFnuU9mUcT3JS4P73TJ0
                return $arr[1];
            }
            if (preg_match("/https?:\/\/(www\.)?myvi\.ru\/watch\/[A-Za-z0-9_-]+/", $str, $arr)) {
                //http://www.myvi.ru/watch/Ameli--2001-_eYhYLFsl5Ea8M8rgRynDWw2?ap=1
                return "prep(myv-1)::".$arr[0];
            }
            if (preg_match("/myvi\.ru\/player\/embed\/html\/[A-Za-z0-9_-]+/", $str, $arr)) {
                // myvi.ru/player/embed/html/ofSglAVK3SkD4vtcSe8yjDisDGO-HsVdCYJEZORRj8VFsUIS5TCLza1GozhR-XZ5j0
                $this->player_tpl = "player_iframe";
                return $arr[0];
            }
            if (preg_match("/myvi\.tv\/embed\/html\/[A-Za-z0-9_-]+/", $str, $arr)) {
                // //myvi.tv/embed/html/ohPIeLQ5JIFA_yhqQZBBNjx3lJOCTAFmlTbBVZ9EMNFXJOvjHFtFatzjTQBtlN8i40
                $this->player_tpl = "player_iframe";
                return $arr[0];
            }
            $this->error('Нет подходящего шаблона для is_myv().', 'myv', $str);
        }
        return false;
    }
    
    function prep_myv ($str, $func) {
        if ($this->autonomous) return $str;
        switch ($func) {
            case "1":
                // http://www.myvi.ru/watch/Ameli--2001-_eYhYLFsl5Ea8M8rgRynDWw2?ap=1
                global $CurlBrowser;
                $res = $CurlBrowser->request($str, 'get', null, null, array("CURLOPT_FOLLOWLOCATION"=>0));
                //var_dump($res);
                if (preg_match("/https?:\/\/(myvi\.ru\/player\/flash\/[A-Za-z0-9-_]+)/", $res->content(), $arr)) {
                    return $arr[1]; // http://myvi.ru/player/flash/ofSglAVK3SkD4vtcSe8yjDk9Q7kl9TLUBAVYYc_6FKgYthitDr9pK9s8ov5Qu2tYf0
                }
                if (preg_match("/myvi\.ru\/player\/embed\/html\/[A-Za-z0-9_-]+/", $res->content(), $arr)) {
                    return $arr[0]; // http://myvi.ru/player/flash/ofSglAVK3SkD4vtcSe8yjDk9Q7kl9TLUBAVYYc_6FKgYthitDr9pK9s8ov5Qu2tYf0
                }
                break;
        }
        return false;
    }
    
    function check_myv ($code) {
        $code = $this->is_myv($code);
        $this->max_quality = 0; // Макс. качество
        if ($code!==false) {
            $url = 'http://'.$code;
            global $CurlBrowser;
            $res = $CurlBrowser->request($url);
            //var_dump ($res); exit;
            if ($res->http_status=="200" && strpos($res->content(), "dataUrl:'/player/")) {
                return 0; // Нет ошибки
            } else {
                return 1; // Удалено
            }
        } else
            return 6;
    }
    
    /**
     * Проверка кода kiwi.kz
     * @param type $str
     * @return array|boolean
     */
    function is_kiv($str) {
        
        if (strpos($str,'kiwi.kz')!==false) {
            if (preg_match("/(v\.kiwi\.kz\/v2\/[A-Za-z0-9-_]+\/)/", $str, $arr)) {
                //http://v.kiwi.kz/v2/6e9cnfwbp3bo/
                return $arr[1]; //v.kiwi.kz/v2/6e9cnfwbp3bo/
            }
            if (preg_match("/kiwi\.kz\/watch\/([A-Za-z0-9-_]+\/)/", $str, $arr)) {
                //http://kiwi.kz/watch/2wd2wshf09wh/
                return "v.kiwi.kz/v2/".$arr[1]; //v.kiwi.kz/v2/6e9cnfwbp3bo/
            }
            $this->error('Нет подходящего шаблона для is_kiv().', 'kiv', $str);
        }
        return false;
    }
    
    function check_kiv ($code) {
        $code = $this->is_kiv($code);
        $this->max_quality = 0; // Макс. качество
        if ($code!==false) {
            $url = "http://".$code;
            global $CurlBrowser;
            $res = $CurlBrowser->request($url);
            //var_dump ($res); exit;
            if (strpos($res->content(), "Movie cannot be loaded")) {
                return 1; // Удалено
            } if ($res->http_status=="200") {
                return 0; // Нет ошибки
            } else
                return 52; // Ошибка проверки на стороне PHP
        } else
            return 6;
    }
    
    /**
     * Проверка кода Yandex.ru
     * @param type $str
     * @return array|boolean
     */
    function is_yan($str) {
        
        if (strpos($str,'video.yandex.ru')!==false) {
            if (preg_match("/^video\.yandex\.ru\/iframe\/[A-Za-z0-9-_.]+\/[A-Za-z0-9-_.]+\/(\?[^<>\"']+)?$/", $str)) {
                //video.yandex.ru/iframe/alfer-yuliya/3ut5w9yk8y.2528/
				//video.yandex.ru/iframe/super-ar123/lnp1ndgnyl.4127/?player-type=full&hidden=logo,about
                return $str; // video.yandex.ru/iframe/alfer-yuliya/3ut5w9yk8y.2528/
            }
            if (preg_match("/https?:\/\/(video\.yandex\.ru\/iframe\/[A-Za-z0-9-_.]+\/[A-Za-z0-9-_.]+\/)(\?[^<>\"']+)?/", $str, $arr)) {
                //http://video.yandex.ru/iframe/alfer-yuliya/3ut5w9yk8y.2528/
				// http://video.yandex.ru/iframe/super-ar123/lnp1ndgnyl.4127/?player-type=full&hidden=logo,about
				//print_r ($arr); exit;
                return $arr[1].(isset($arr[2])?$arr[2]:''); // video.yandex.ru/iframe/alfer-yuliya/3ut5w9yk8y.2528/
            }
            if (preg_match("/https?:\/\/(video\.yandex\.ru\/users\/[A-Za-z0-9-_.]+\/view\/[A-Za-z0-9-_.]+\/)/", $str, $arr)) {
                //http://video.yandex.ua/users/tdp1204/view/94/
                return "prep(yan-1)::http://".$arr[1]; // http://video.yandex.ua/users/tdp1204/view/94/
            }
            $this->error('Нет подходящего шаблона для is_yan().', 'yan', $str);
        }
        return false;
    }
    
    function prep_yan ($str, $func) {
        if ($this->autonomous) return $str;
        switch ($func) {
            case "1":
                // http://video.yandex.ua/users/tdp1204/view/94/
                global $CurlBrowser;
                $res = $CurlBrowser->request($str, 'get', null, null, array("CURLOPT_FOLLOWLOCATION"=>1));
                //var_dump($res);
                if (preg_match("/var src = '(.+?)';/",$res->content(),$arr)) {
                    return $arr[1]; // http://video.yandex.ru/iframe/tdp1204/gkbf9b9qoi.1212/?player-type=full
                }
                break;
        }
        return false;
    }

    /**
     * Проверка кода Megogo.net
     * @param type $str
     * @return array|boolean
     */
    function is_meg($str) {
        
        if (strpos($str,'megogo.net')!==false) {
            if (preg_match("/(megogo\.net\/e\/[0-9]+)/", $str, $arr)) {
                //http://megogo.net/e/345 http://megogo.net/e/26396
                return $arr[1]; // megogo.net/e/345
            }
            if (preg_match("/megogo\.net\/[A-za-z]+\/view\/([0-9]+)/", $str, $arr)) {
                //http://megogo.net/ua/view/100411-snigova-koroleva.html
                //http://megogo.net/ru/view/54181-obuchayu-igre-na-gitare.html
                return "megogo.net/e/".$arr[1]; // megogo.net/e/345
            }
            $this->error('Нет подходящего шаблона для is_meg().', 'meg', $str);
        }
        return false;
    }
    
    /**
     * Проверка кода Ivi.ru
     * @param type $str
     * @return array|boolean
     */
    function is_ivi($str) {
        
        if (strpos($str,'ivi.ru')!==false) {
            if (preg_match("/www\.ivi\.ru\/video\/player\?siteId=[a-z0-9]+&videoId=[0-9]+&_isB2C=[0-9]/", $str, $arr)) {
                //http://www.ivi.ru/video/player?siteId=s138&videoId=107857&_isB2C=1
                return $arr[0]; // www.ivi.ru/video/player?siteId=s138&videoId=107857&_isB2C=1
            }
            if (preg_match("/http:\/\/www\.ivi\.ru\/watch\/[A-Za-z0-9-_\.]+\/[0-9]+/", $str, $arr)) {
                //http://www.ivi.ru/watch/shtrafbat/66048
                return "prep(ivi-1)::".$arr[0]; // www.ivi.ru/video/player?siteId=s138&videoId=107857&_isB2C=1
            }
            if (preg_match("/www\.ivi\.ru\/external\/[A-Za-z\d]+\/\?videoId=\d+&subsiteId=\d+&marker=\d+/", $str, $arr)) {
                //http://www.ivi.ru/external/stub/?videoId=107301&subsiteId=138&marker=7
                return $arr[0]; // www.ivi.ru/external/stub/?videoId=107301&subsiteId=138&marker=7
            }
            $this->error('Нет подходящего шаблона для is_ivi().', 'ivi', $str);
        }
        return false;
    }
    
    function prep_ivi ($str, $func) {
        if ($this->autonomous) return $str;
        switch ($func) {
            case "1":
                // http://www.ivi.ru/watch/shtrafbat/66048
                global $CurlBrowser;
                $res = $CurlBrowser->request($str, 'get', null, null, array("CURLOPT_FOLLOWLOCATION"=>0));
                //var_dump($res);
                if (preg_match("/<input type=\"text\" value=\"(.+?)\"/", $res->content(), $arr)) {
                    $html = htmlspecialchars_decode($arr[1]);
                    if (preg_match("/www\.ivi\.ru\/video\/player\?siteId=[a-z0-9]+&videoId=[0-9]+&_isB2C=[0-9]/", $html, $arr)) {
                        return $arr[0]; // www.ivi.ru/video/player?siteId=s138&videoId=107857&_isB2C=1
                    }
                }
                break;
        }
        return false;
    }
    
    /**
     * Проверка кода kset.kz
     * @param type $str
     * @return array|boolean
     */
    function is_kst($str) {
        
        if (stripos($str,'kset.kz')!==false) {
            if (preg_match("/kset\.kz\/video_frame\.php\?id=[0-9]+/", $str, $arr)) {
                //http://kset.kz/video_frame.php?id=1728210 (код iframe)
                return $arr[0]; // kset.kz/video_frame.php?id=1728210 
            }
            if (preg_match("/kset\.kz\/v\.php\?id=([0-9]+)/", $str, $arr)) {
                //http://kset.kz/v.php?id=1728210 (код object)
                return "kset.kz/video_frame.php?id=".$arr[1]; // kset.kz/video_frame.php?id=1728210 
            }
            if (preg_match("/kset\.kz\/video\/view\/([0-9]+)/", $str, $arr)) {
                //http://kset.kz/video/view/1728210 (ссылка на видео)
                return "kset.kz/video_frame.php?id=".$arr[1]; // kset.kz/video_frame.php?id=1728210 
            }
            $this->error('Нет подходящего шаблона для is_kst().', 'kst', $str);
        }
        return false;
    }
    
    /**
     * Проверка кода video.sibnet.ru
     * @param type $str
     * @return array|boolean
     */
    function is_sib($str) {
        
        if (stripos($str,'video.sibnet.ru')!==false) {
            
            if (preg_match("/(video\.sibnet\.ru\/shell\.php\?videoid=[0-9]+&related_plst=\d+&playlist_position=[a-z]+&playlist_size=\d+)/", $str, $arr)) {
                // http://video.sibnet.ru/shell.php?videoid=1347683&related_plst=172502&playlist_position=right&playlist_size=215 (код iframe со списком)
                return $arr[0]; // video.sibnet.ru/shell.php?videoid=1272731
            }
            if (preg_match("/(video\.sibnet\.ru\/shell\.php\?videoid=[0-9]+)/", $str, $arr)) {
                //http://video.sibnet.ru/shell.php?videoid=1272731 (код iframe)
                return $arr[0]; // video.sibnet.ru/shell.php?videoid=1272731
            }
            if (preg_match("/video\.sibnet\.ru\/.+?\/video([0-9]+)\-/si", $str, $arr)) {
                //http://video.sibnet.ru/day/20130925/video1272731-Zabavnyiy_rozyigryish_devushek_na_BMW/
                return "video.sibnet.ru/shell.php?videoid=".$arr[1]; // video.sibnet.ru/shell.php?videoid=1272731
            }
            if (preg_match("/video\.sibnet\.ru\/shell\.swf\?videoid=([0-9]+)/", $str, $arr)) {
                //http://video.sibnet.ru/shell.swf?videoid=1272731 (код object)
                return "video.sibnet.ru/shell.php?videoid=".$arr[1]; // video.sibnet.ru/shell.php?videoid=1272731
            }
            if (preg_match("/video\.sibnet\.ru\/video([0-9]+)/", $str, $arr)) {
                //http://video.sibnet.ru/video504062-Pro_GMO__/
                return "video.sibnet.ru/shell.php?videoid=".$arr[1]; // video.sibnet.ru/shell.php?videoid=504062
            }
            $this->error('Нет подходящего шаблона для is_sib().', 'sib', $str);
        }
        return false;
    }
    
    function check_sib ($code) {
        $code = $this->is_sib($code);
        $this->max_quality = 0; // Макс. качество
        if ($code!==false) {
            $url = 'http://'.$code;
            global $CurlBrowser;
            $res = $CurlBrowser->request($url);
            //var_dump ($res); exit;
            if (strpos($res->content(), "Нет такого видео")) {
                return 1; // Удалено
            } if ($res->http_status=="200") {
                return 0; // Нет ошибки
            } else
                return 52; // Ошибка проверки на стороне PHP
        } else
            return 6;
    }
    
    /**
     * Проверка кода allserials.tv
     * @param type $str
     * @return array|boolean
     */
    function is_als($str) {
        if (stripos($str,'allserials.tv')!==false) {
            if (preg_match("/(allserials\.tv\/getfile\.php\?file=[^\"'<>]+)/", $str, $arr)) {
                //http://allserials.tv/getfile.php?file=Hello.Ladies.s1 (код iframe)
                return $arr[0]; // allserials.tv/getfile.php?file=Hello.Ladies.s1
            }
            $this->error('Нет подходящего шаблона для is_als().', 'als', $str);
        }
        return false;
    }
    
    /**
     * Проверка кода kinostok.tv
     * @param type $str
     * @return array|boolean
     */
    function is_kns($str) {
        if (substr($str,0,5)=="kns::" || substr($str,0,5)=="kn2::" || stripos($str,"kinostok.tv")!==false) {
            if (preg_match("/kinostok\.tv\/video\/[0-9]+\/[A-Za-z0-9-_\.]+/si", $str, $arr)) {
                // http://kinostok.tv/video/54679/pro-zhizn
                return "prep(kns-1)::http://".$arr[0]; // kinostok.tv/v/d3f6a80dc9b865b4d7763701b10f6806
            }
            if (preg_match("/kinostok\.tv\/embed\/[a-f0-9]+/si", $str, $arr)) {
                // <iframe width="640" height="480" src="http://kinostok.tv/embed/2f82d09c250a6178d1709b58eadadee3" frameborder="0" allowfullscreen></iframe>
                $this->player_tpl = "player_iframe";
                return $arr[0]; // http://kinostok.tv/embed/2f82d09c250a6178d1709b58eadadee3
            }
            if (preg_match("/kinostok\.tv\/[A-Za-z]+\/[a-f0-9]+/si", $str, $arr)) {
                // http://kinostok.tv/v/d3f6a80dc9b865b4d7763701b10f6806
                // http://kinostok.tv/v/b778501aa2d51ae3ccad
                return $arr[0]; // kinostok.tv/v/d3f6a80dc9b865b4d7763701b10f6806
            }
            if (preg_match("/^kn[2s]::(.+)/si", $str, $arr)) {
                $this->player_tpl = "player2";
                // kns::pl=c:ystXBYU1e3R4nV6fWT6rebt3e3mRDVmZ02k4kTm1e3x2wf5v7YFhDGQZwGFhkGwjyQnzD3FZeNEE&amp;st=c:ystXBYU1e3R4nV6fWT6rebt3e2mNBT6Z02cHD088Bu6fWs8Hk071kdvukdtzB2tgnToiWshX
                return "kns::".$arr[1]; // kns::pl=c:ystXBYU1e3R4nV6fWT6rebt3e3mRDVmZ02k4kTm1e3x2wf5v7YFhDGQZwGFhkGwjyQnzD3FZeNEE&amp;st=c:ystXBYU1e3R4nV6fWT6rebt3e2mNBT6Z02cHD088Bu6fWs8Hk071kdvukdtzB2tgnToiWshX
            }
            if (preg_match("/<param name=\"FlashVars\" value=\"(.+?)\" \/>/si", $str, $arr)) {
                $this->player_tpl = "player2";
                // <object type="application/x-shockwave-flash" data="http://kinostok.tv/uppod_player/uppod.swf" width="550" height="413" id="player"><param name="allowFullScreen" value="true" /><param name="allowScriptAccess" value="always" /><param name="movie" value="http://kinostok.tv/uppod_player/uppod.swf" /><param name="FlashVars" value="pl=c:ystXBYU1e3R4nV6fWT6rebt3e3mRDVmZ02k4kTm1e3x2wf5v7YFhDGQZwGFhkGwjyQnzD3FZeNEE&amp;st=c:ystXBYU1e3R4nV6fWT6rebt3e2mNBT6Z02cHD088Bu6fWs8Hk071kdvukdtzB2tgnToiWshX" /></object>
                return "kns::".$arr[1]; // kns::pl=c:ystXBYU1e3R4nV6fWT6rebt3e3mRDVmZ02k4kTm1e3x2wf5v7YFhDGQZwGFhkGwjyQnzD3FZeNEE&amp;st=c:ystXBYU1e3R4nV6fWT6rebt3e2mNBT6Z02cHD088Bu6fWs8Hk071kdvukdtzB2tgnToiWshX
            }
            //$this->error('Нет подходящего шаблона для is_kns().', 'kns', $str);
        }
        return false;
    }
    
    function prep_kns ($str, $func) {
        if ($this->autonomous) return $str;
        switch ($func) {
            case "1":
                // http://www.ivi.ru/watch/shtrafbat/66048
                global $CurlBrowser;
                $res = $CurlBrowser->request($str, 'get', null, null, array("CURLOPT_FOLLOWLOCATION"=>0));
                //var_dump($res);
                if (preg_match("/value=\"(&lt;object.+?)\"/", $res->content(), $arr)) {
                    $html = htmlspecialchars_decode($arr[1]);
                    if (preg_match("/kinostok\.tv\/[A-Za-z]+\/[a-f0-9]+/si", $html, $arr)) {
                        return $arr[0]; // kinostok.tv/v/1673236d833505566c36
                    }
                }
                break;
        }
        return false;
    }
    
    /**
     * Проверка кода video.meta.ua
     * @param type $str
     * @return array|boolean
     */
    function is_met($str) {
        if (substr($str,0,5)=='met::' || stripos($str,'video.meta.ua')!==false) {
            if (preg_match("/^met::[A-Za-z0-9%_-]+$/", $str)) {
                $this->player_tpl = "player_object";
                return $str;
            }
            if (preg_match("/video\.meta\.ua\/([0-9]+)\.video/si", $str, $arr)) {
                //http://video.meta.ua/3559263.video
                return "http://video.meta.ua/iframe/".$arr[1]."/"; //http://video.meta.ua/3559263.video
            }
            if (preg_match("/video\.meta\.ua\/iframe\/[0-9]+/si", $str, $arr)) {
                //http://video.meta.ua/iframe/6310943/
                return "http://".$arr[0]."/"; // http://video.meta.ua/iframe/6310943/
            }
            if (preg_match("/fileID=([A-Za-z0-9%_-]+)/si", $str, $arr)) {
                $this->player_tpl = "player_object";
                // Старый код object
                //http://video.meta.ua/players/video/3.2.20a/Player.swf?fileID=EIdJW8RBGpyyDthSHyrX&fileID_url=http%3A%2F%2Fvideo.meta.ua%2Fplayers%2Fgetparam%2F%3Fv%3D
                return "met::".$arr["1"];
            }
            $this->error('Нет подходящего шаблона для is_met().', 'met', $str);
        }
        return false;
    }
    
    function check_met ($code) {
        $code = $this->is_met($code);
        $this->max_quality = 0; // Макс. качество
        if ($code!==false) {
            if ($this->player_tpl === "player_object") {
                return 0; // Старый код нельзя проверить
            }
            $url = $code;
            global $CurlBrowser;
            $res = $CurlBrowser->request($url);
            //var_dump ($res); exit;
            if (strpos($res->content(), "Файл не найден или был удален.")) {
                return 1; // Удалено
            } if ($res->http_status=="200") {
                return 0; // Нет ошибки
            } else
                return 52; // Ошибка проверки на стороне PHP
        } else
            return 6;
    }
    
    /**
     * Проверка кода Intv.ru
     * @param type $str
     * @return array|boolean
     */
    function is_itv($str) {
        
        if (stripos($str,'intv.ru')!==false) {
            if (preg_match("/flash\.intv\.ru\/uplay\/[A-Za-z0-9]+/si", $str, $arr)) {
                // http://flash.intv.ru/uplay/jT1ge2aeAn (код плеера)
                return $arr[0]; // flash.intv.ru/uplay/jT1ge2aeAn
            }
            if (preg_match("/intv\.ru\/v\/([A-Za-z0-9]+)/si", $str, $arr)) {
                // http://www.intv.ru/v/tQepH51Fx3&playNow=1 (прямая ссылка)
                return "flash.intv.ru/uplay/".$arr[1]; // flash.intv.ru/uplay/jT1ge2aeAn
            }
            if (preg_match("/intv\.ru\/uplay\/[A-Za-z0-9\.]+\?agent=[0-9]+/si", $str, $arr)) {
                // http://intv.ru/uplay/roapLGtMF?agent=1999612 (код плеера)
                return $arr[0]; // intv.ru/uplay/roapLGtMF?agent=1999612
            }
            
            $this->error('Нет подходящего шаблона для is_itv().', 'itv', $str);
        }
        return false;
    }
    /**
     * Проверка кода openFile.ru
     * @param type $str
     * @return array|boolean
     */
    function is_opf($str) {
        
        if (substr($str, 0, 5)=="opf::" || stripos($str,'openfile.ru')!==false || stripos($str,'whitecdn.org/player/f4af2384bdbe741ed23e7b0c230f4d17')!==false) {
            if (preg_match("/^opf::[0-9]+/si", $str, $arr)) {
                // opf::204569
                return $arr[0]; // opf::204569
            }
            if (preg_match("/openfile\.ru\/video\/([0-9]+)\//si", $str, $arr)) {
                // http://openfile.ru/video/204569/ (ссылка)
                return "opf::".$arr[1]; // opf::204569
            }
            if (preg_match("/\"videoID=([0-9]+)\"/si", $str, $arr)) {
                // <param name="FLASHVARS" value="videoID=204569" /> (код плеера - старый или новый)
                return "opf::".$arr[1]; // opf::204569
            }
            
            $this->error('Нет подходящего шаблона для is_opf().', 'opf', $str);
        }
        return false;
    }
    
    /**
     * Проверка кода veterok.tv
     * @param type $str
     * @return array|boolean
     */
    function is_vet($str) {
        
        if (stripos($str,'veterok.tv')!==false) {
            if (preg_match("/veterok\.tv\/[A-Za-z0-9]+\/([0-9]+)/", $str, $arr)) {
                // http://veterok.tv/video/45153/Naedine-so-vsemi-21112013
                // http://veterok.tv/v/45153
                return "veterok.tv/v/".$arr[1]; // veterok.tv/v/45153
            }
            $this->error('Нет подходящего шаблона для is_vet().', 'vet', $str);
        }
        return false;
    }
    
    /**
     * Проверка кода Stepashka.com
     * @param type $str
     * @return array|boolean
     */
    function is_stp($str) {
        //exit($str);
        if (stripos($str,'pirateplayer.com')!==false || stripos($str,'stepashka.com')!==false || substr($str,0,5)=='stp::') {
            
            if (preg_match("/pirateplayer\.com\/embed\/[A-Za-z0-9-_\.]+/", $str, $arr)) {
                // pirateplayer.com/embed/VJyRre9NEnL (код для вставки)
                return $arr[0]; // pirateplayer.com/embed/VJyRre9NEnL
            }
            if (preg_match("/^stp::[^\n<>\"']+$/", $str, $arr)) {
                // clp::st=/player/1/mJo5bv6f12f4XXrTmwWF4rnUwgy10Jynr/86dbaef3cf1a7bc536c88064eb5d0cf0/
                $this->player_tpl = "player_obj";
                return $arr[0]; // pirateplayer.com/embed/VJyRre9NEnL
            }
            if (preg_match("/<param[^<>]*name=\"FlashVars\"[^<>]*value=\"([^\"]+)\"/si", $str, $arr)) {
                // <param name="FlashVars" value="st=/player/1/mJo5bv6f12f4XXrTmwWF4rnUwgy10Jynr/86dbaef3cf1a7bc536c88064eb5d0cf0/" />
                $this->player_tpl = "player_obj";
                return "stp::".$arr[1]; // clp::st=/player/1/mJo5bv6f12f4XXrTmwWF4rnUwgy10Jynr/86dbaef3cf1a7bc536c88064eb5d0cf0/
            }
            $this->error('Нет подходящего шаблона для is_vet().', 'vet', $str);
        }
        return false;
    }
    
    /**
     * Проверка кода Truba.com
     * @param type $str
     * @return array|boolean
     */
    function is_trb($str) {
        if (stripos($str,'truba.com')!==false) {
            if (preg_match("/truba\.com\/tools\/config_video\.php\?id=([0-9]+)/", $str, $arr)) {
                return "truba.com/tools/config_video.php?id=".$arr[1]; // truba.com/tools/config_video.php?id=392830
            }
            if (preg_match("/truba\.com\/video\/([0-9]+)\//", $str, $arr)) {
                return "truba.com/tools/config_video.php?id=".$arr[1]; // truba.com/tools/config_video.php?id=392830
            }
            $this->error('Нет подходящего шаблона для is_vet().', 'vet', $str);
        }
        return false;
    }
    
    /**
     * Проверка кода тега iframe
     * @param type $str
     * @return array|boolean
     */
    function is_ifr($str) {
        if (substr($str,0,5)=="ifr::" || stripos($str,'<iframe')!==false) {
            if (preg_match("/ifr::([+a-z0-9:_\.\/%=\?\&$\(\)-]+)/si", $str, $arr)) {
                return "ifr::".$arr[1]; // ifr::ссылка-источник iframe
            }
            if (preg_match("/<iframe.*? src=['\"]?([+a-z0-9:_\.\/%=\?\&-]+)['\"]?.*?>.*?<\/iframe>/si", $str, $arr)) {
                return "ifr::".$arr[1]; // ifr::ссылка-источник iframe
            }
            $this->error('Нет подходящего шаблона для is_ifr().', 'ifr', $str);
        }
        return false;
    }
    
    /**
     * Проверка кода тега clipiki.ru
     * @param type $str
     * @return array|boolean
     */
    function is_clp($str) {
        if (substr($str,0,5)=="clp::" || stripos($str,'clipiki.ru')!==false) {
            if (preg_match("/^clp::([^\"]+)$/si", $str)) {
                return $str; // clp::зашифрованные данные flashvars
            }
            if (preg_match("/<param[^<>]*name=\"FlashVars\"[^<>]*value=\"([^\"]+)\"/si", $str, $arr)) {
                return "clp::".$arr[1]; // clp::зашифрованные данные flashvars
            }
            /*if (preg_match("/clipiki\.ru\/video\/[0-9]+\/[A-Za-z0-9_.-]+/si", $str, $arr)) {
                // http://clipiki.ru/video/235269/Krepkiy-oreshek-Horoshiy-den-chtobyi-umeret--A-Good-Day-to-Die-Hard-2013
                return "prep(clp-1)::http://www.".$arr[0];
            }*/
            $this->error('Нет подходящего шаблона для is_clp().', 'clp', $str);
        }
        return false;
    }
    
    /*function prep_clp ($str, $func) {
       if ($this->autonomous) return $str; 
       switch ($func) {
            case "1":
                // http://clipiki.ru/video/235269/Krepkiy-oreshek-Horoshiy-den-chtobyi-umeret--A-Good-Day-to-Die-Hard-2013
                global $CurlBrowser;
                $res = $CurlBrowser->request($str, 'get', null, null, array("CURLOPT_FOLLOWLOCATION"=>0));
                //var_dump($res);
                if (preg_match("/<param[^<>]*name=\"FlashVars\"[^<>]*value=\"([^\"]+)\"/si", $res->content(), $arr)) {
                    $html = htmlspecialchars_decode($arr[1]);
                    $html = iconv ("utf-8", "windows-1251", $html);
                    return "vzl::".$html;
                }
                break;
        }
        return false;
    }*/
    
    /**
     * Проверка кода тега video.az
     * @param type $str
     * @return array|boolean
     */
    function is_vaz($str) {
        if (stripos($str,'video.az')!==false) {
            if (preg_match("/video\.az\/[a-z]{2,3}\/embed\/video\/([0-9]+)/si", $str, $arr)) {
                // http://www.video.az/ru/embed/video/92788 (код iframe)
                return "http://www.video.az/ru/embed/video/".$arr[1]; // http://www.video.az/ru/embed/video/92788
            }
            if (preg_match("/video\.az\/[a-z]{2,3}\/video\/([0-9]+)\//si", $str, $arr)) {
                // http://www.video.az/ru/video/92788/stiralynaya-mashina-tantsuet-harlem-sheyk (ссылка)
                return "http://www.video.az/ru/embed/video/".$arr[1]; // http://www.video.az/ru/embed/video/92788
            }
            $this->error('Нет подходящего шаблона для is_vaz().', 'vaz', $str);
        }
        return false;
    }
    
    /**
     * Проверка кода тега enter.az
     * @param type $str
     * @return array|boolean
     */
    function is_eaz($str) {
        if (stripos($str,'enter.az')!==false) {
            if (preg_match("/enter\.az\/embed\/([0-9]+)/si", $str, $arr)) {
                // http://enter.az/embed/38234 (код iframe)
                return "http://enter.az/embed/".$arr[1]; // http://enter.az/embed/38234
            }
            if (preg_match("/enter\.az\/[a-z0-9_\/.-]+?_([0-9]+)\.html/si", $str, $arr)) {
                // http://enter.az/Elizium-Ray-ne-na-Zemle-Elysium-2013-BDRipDUB-Licenziya_38234.html (ссылка)
                return "http://enter.az/embed/".$arr[1]; // http://enter.az/embed/38234
            }
            $this->error('Нет подходящего шаблона для is_eaz().', 'eaz', $str);
        }
        return false;
    }
    
    /**
     * Проверка кода тега ww.videokub.com
     * @param type $str
     * @return array|boolean
     */
    function is_vkb($str) {
        if (stripos($str,'videokub.com')!==false) {
            if (preg_match("/videokub\.com\/embed\/([0-9]+)/si", $str, $arr)) {
                // http://www.videokub.com/embed/23413 (код iframe)
                return "http://www.videokub.com/embed/".$arr[1]; // http://www.videokub.com/embed/23413
            }
            if (preg_match("/videokub\.com\/videos\/([0-9]+)/si", $str, $arr)) {
                // http://www.videokub.com/videos/23413/futbol-news-22-12-2013/ (ссылка)
                return "http://www.videokub.com/embed/".$arr[1]; // http://www.videokub.com/embed/23413
            }
            $this->error('Нет подходящего шаблона для is_vkb().', 'vkb', $str);
        }
        return false;
    }
    
    /**
     * Проверка кода тега vzale.tv
     * @param type $str
     * @return array|boolean
     */
    function is_vzl($str) {
        if (substr($str,0,5)=="vzl::" || stripos($str,'vzale.tv')!==false) {
            if (preg_match("/^vzl::([^\"]+)$/si", $str)) {
                return $str; // vzl::зашифрованные данные flashvars
            }
            if (preg_match("/<param[^<>]*name=\"FlashVars\"[^<>]*value=\"([^\"]+)\"/si", $str, $arr)) {
                return "vzl::".$arr[1]; // vzl::зашифрованные данные flashvars
            }
            if (preg_match("/vzale\.tv\/watch\/[A-Za-z0-9_.-]+/si", $str, $arr)) {
                // http://www.vzale.tv/watch/hobbit-nejdannoe-puteshestvie
                return "prep(vzl-1)::http://www.".$arr[0];
            }
            $this->error('Нет подходящего шаблона для is_vzl().', 'vzl', $str);
        }
        return false;
    }
    
    /**
     * Проверка кода тега Cinem.tv
     * @param type $str
     * @return array|boolean
     */
    function is_ctv($str) {
        if (stripos($str,'cinem.tv')!==false) {
            if (preg_match("/cinem\.tv\/em\/(\d+)/si", $str, $arr)) {
                // http://cinem.tv/em/13919
                return "cinem.tv/em/".$arr[1]; // cinem.tv/em/13919
            }
            if (preg_match("/https?:\/\/cinem\.tv\/(\d+)-/si", $str, $arr)) {
                // http://cinem.tv/13919-odnoklassniki-2-grown-ups-2-2013.html
                return "cinem.tv/em/".$arr[1]; // cinem.tv/em/13919
            }
            $this->error('Нет подходящего шаблона для is_ctv().', 'ctv', $str);
        }
        return false;
    }
    
    function prep_vzl ($str, $func) {
        if ($this->autonomous) return $str;
        switch ($func) {
            case "1":
                // http://www.vzale.tv/watch/hobbit-nejdannoe-puteshestvie
                global $CurlBrowser;
                $res = $CurlBrowser->request($str, 'get', null, null, array("CURLOPT_FOLLOWLOCATION"=>0));
                //var_dump($res);
                if (preg_match("/<param[^<>]*name=\"FlashVars\"[^<>]*value=\"([^\"]+)\"/si", $res->content(), $arr)) {
                    $html = htmlspecialchars_decode($arr[1]);
                    $html = iconv ("utf-8", "windows-1251", $html);
                    return "vzl::".$html;
                }
                break;
        }
        return false;
    }
    
    /**
     * Проверка кода тега Namba.net
     * @param type $str
     * @return array|boolean
     */
    function is_nba($str) {
        if (substr($str,0,5)=="nba::" || stripos($str,'namba.net')!==false || stripos($str,'namba.kz')!==false) {
            if (preg_match("/^nba::(flashvars-\d+\.\d+\.\d+\.php\?i=[0-9_]+)$/si", $str)) {
                // nba::flashvars-3.2.11.php?i=61903301_61903271__7739021_1
                return $str;
            }
            if (preg_match("/https?:\/\/video\.namba\.net\/(flashvars-\d+\.\d+\.\d+\.php\?i=[0-9_]+)/si", $str, $arr)) {
                // http://video.namba.net/flashvars-3.2.11.php?i=61903301_61903271__7739021_1" (object code)
                return "nba::".$arr[1]; // nba::flashvars-3.2.11.php?i=61903301_61903271__7739021_1
            }
            if (preg_match("/https?:\/\/video\.namba\.kz\/(flashvars-[^\"'<>]+)/si", $str, $arr)) {
                // http://video.namba.kz/flashvars-3.2.11.php?i=69454801_69454721__8805001_1
                $this->player_tpl = "player2";
                return "nba::".$arr[1]; // nba::flashvars-3.2.11.php?i=69454801_69454721__8805001_1
            }
            // <object height="385" width="640" type="application/x-shockwave-flash" data="http://video.namba.kz/swf/player/3.2.11/flowplayer-3.2.11.swf"><param value="true" name="allowfullscreen"><param value="opaque" name="wmode"><param value="always" name="allowscriptaccess"><param name="src" value="http://video.namba.kz/swf/player/3.2.11/flowplayer-3.2.11.swf" /><param value="config=http://video.namba.kz/flashvars-3.2.11.php?i=69454801_69454721__8805001_1" name="flashvars"></object>
            $this->error('Нет подходящего шаблона для is_nba().', 'nba', $str);
        }
        return false;
    }
  
    function check_nba ($code) {
        $code = $this->is_nba($code);
        $this->max_quality = 0; // Макс. качество
        if ($code!==false) {
            if (substr($code,0,5)=="nba::") $code = substr($code,5);
            $url = "http://video.namba.kz/".$code;
            global $CurlBrowser;
            $res = $CurlBrowser->request($url, 'get', null, null, array('CURLOPT_FOLLOWLOCATION'=>1));
            //var_dump ($res); exit;
            if (strpos($res->content(), '{"key":"')!==false) {
                return 0; // Нет ошибки
            } else if ($res->http_status=="200") {
                return 1;
           } else
                return 52; // Ошибка проверки на стороне PHP
        } else
            return 6;
    }
    
    /**
     * Проверка кода тега Vimple.ru
     * @param type $str
     * @return array|boolean
     */
    function is_vim($str) {
        if (stripos($str,'vimple.ru')!==false) {
            if (preg_match("/https?:\/\/player\.vimple\.ru\/iframe\/([A-fa-f0-9]+)/si", $str, $arr)) {
                // <iframe src="http://player.vimple.ru/iframe/7ba556f3df3b46de8501d34451cc298d" width="480" height="305" frameborder="0" style="z-index:2147483647;"></iframe>
                return $arr[1] ? $arr[0] : false; // http://player.vimple.ru/iframe/7ba556f3df3b46de8501d34451cc298d
            }
            if (preg_match("/https?:\/\/vimple\.ru\/([A-fa-f0-9]+)/si", $str, $arr)) {
                // http://vimple.ru/7ba556f3df3b46de8501d34451cc298d
                // Фикс старого плеера
                return $arr[1]!=='api' ? "http://player.vimple.ru/iframe/".$arr[1] : false; // http://player.vimple.ru/iframe/7ba556f3df3b46de8501d34451cc298d
            }
            $this->error('Нет подходящего шаблона для is_vim().', 'vim', $str);
        }
        return false;
    }
    
    
    
    /** Добавить ошибку во время работы методов класса */
    function error($error_text, $source = '', $string = '') {
        global $config;
        if ($config["charset"]=="utf-8") {
            $error_text = array_iconv("windows-1251", "utf-8", $error_text);
        }
        $this->errors[] = $error_text;
    }
    
    
    function prepCode ($tube, $func, $str) {
        $this->code = $str;
        $alt_names = $this->getAltNames();
        if (in_array($tube,$alt_names)) {
            $method = 'prep_'.$tube;
            if (is_callable(array($this, $method))){
                $res = $this->$method($str, $func);
                if (!($res===false)) {
                    $this->code = $res;
                    return $res;
                }
            }
        }
        return false;
    }
    
    /**
     * Получение codetype. Также заносит нормальный код в $this->code
     * @param type $code    Код плеера
     * @return int          Тип кода
     */
    function getTube ($code){
        $this->player_tpl = null;
        $this->code = $code;
        $alt_names = $this->getAltNames();
        foreach ($alt_names as $alt_name) {
            if ($alt_name!=="upd") {
                $method = 'is_'.$alt_name;
                if (is_callable(array($this, $method))){
                    $res = $this->$method($code);
                    if (!($res===false)) {
                        $this->code = $res;
                        $alt_name . $this->getCodeType($alt_name); ##!! wtf?
                        return $this->getCodeType($alt_name);
                    }
                }
            }
        }
        // Проверка UPPOD (в последнюю очередь)
        if (!$this->autonomous) {
            $res = $this->is_upd($code);
            if (!($res===false)) {
                $this->code = $res;
                return $this->getCodeType("upd");
            }
        }
        // Неизвестный тюб
        return 0;
    }
    
    /** Получение списка альтернативных имен тюбов */
    function getAltNames() {
        $arr = array();
        foreach ($this->types as $key => $val) {
            $arr[] = $val['alt_name'];
        }
        return $arr;
    }
    
    /**
     * Получение типа кода по альтернативному имени туба
     * @param type $alt_name
     */
    function getCodeType($alt_name){
        if ($this->alt_types===null){
            foreach ($this->types as $key => $val) {
                $this->alt_types[$val['alt_name']] = $key;
            }
        }
        // Получение типа кода
        if (isset($this->alt_types[$alt_name])) {
            return $this->alt_types[$alt_name];
        } else return 0;
    }
    
    /**
     * Получить название (или html-название) плеера по коду или codetype
     * @param type $code
     * @param type $codetype
     * @param type $is_html
     * @return type
     */
    function getCodeName ($code, $codetype, $is_html=false) {
        if ($code!==null) 
            $codetype = $this->getTube($code);
        if (isset($this->types[$codetype]))
            return $is_html ? $this->types[$codetype]["name_html"] : $this->types[$codetype]["name"];
        else
            return $is_html ? "<font color='red'>Unknown</font>" : "Unknown";
    }
    
    /**
     * Получить название (или html-название) ошибки проверки
     * @param type $err
     * @param type $is_html
     */
    function getCheckErrorName($err, $is_html=false) {
        if ($err>0) {
            if (isset($this->check_errors[$err])) {
                return $is_html ? "<font color='red'>".$this->check_errors[$err]."</font>" : $this->check_errors[$err];
            } else 
                return "-";
        } else
            return $is_html ? "<font color='green'>No errors</font>" : "No errors";
    }
    
    /**
     * Проверка кода на удаленные
     * @param type $code
     * @param type $code_type
     * @return int|boolean      Возвращает false в случае невозможности проверки или число - код результата проверки видео
     */
    function checkTube ($code, $code_type=null) {
        if ($code_type===null) {
            $code_type = $this->getTube($code);
        }
        if ($code_type>0 && isset($this->types[$code_type]['alt_name'])) {
            $method = 'check_'.$this->types[$code_type]['alt_name'];
            if (is_callable(array($this, $method))){
                return $this->$method($code);
            } else {
                return 4; // Проверка не поддерживается
                //$this->error('Method $this->'.$method.'() not found!', '', $code);
            }
        }
        return false;
    }
    
    /**
     * Статус доступности проверки (по codetype)
     * @param type $code_type
     * @return boolean
     */
    function isCheckable($code_type) {
        if ($code_type>0 && isset($this->types[$code_type]['alt_name'])) {
            $method = 'check_'.$this->types[$code_type]['alt_name'];
            if (is_callable(array($this, $method))){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Получение шаблона плеера по его названию ($player_tpl)
     * @param type $code_type
     * @param string $player_tpl
     * @return type
     */
    private function getPlayerTpl($code_type, $player_tpl) {
        if (!$player_tpl)
            $player_tpl = "player";
        return $this->types[$code_type][$player_tpl];
    }
    
    /**
     * Получение настроек скрипта для автономной работы вне дле
     */
    public function config() {
        if (defined('DATALIFEENGINE')) {
            global $vk_config;
            return $vk_config;
        } else {
            return array(
                'player_width' => '100%',
                'player_height' => '100%',
                'html_enabled' => false,
                'uppod_swf' => '',
                'uppod_style' => '',
            );
        }
    }
    
    /**
     * Получение кода плеера по типу кода и имени серии (или сборки!)
     * @param   type      $code
     * @param   type      $name
     * @param   type      $code_type
     * @return  string          Код плеера
     */
    public function getPlayer($code, $name, $code_type = null, $params=array()) {
        $vkConfig = $this->config();
        $width = isset($params['width']) ? $params['width'] : $vkConfig['player_width'];
        $height = isset($params['height']) ? $params['height'] : $vkConfig['player_height'];
        
        if ($code_type===null) {
            $code_type = $this->getTube($code);
            $code = $this->code;
        } else {
            if ($code_type>0 && isset($this->types[$code_type]['alt_name'])) {
                $method = 'check_'.$this->types[$code_type]['alt_name'];
                if (is_callable(array($this, $method))){
                    $res = $this->$method($code); // Канонизация кода
                    if ($res===false) {
                        return "Codetype {$code_type} not correct!";
                    }
                } else {
                    return "Can`t check codetype correction!";
                }
            } else
                return "Codetype {$code_type} not isset!";
        }
        if (substr($code,0,5)!=="prep(") {
            // Код не нуждается в подготовке
            if (isset($vkConfig['html_enabled']) && $vkConfig['html_enabled']) {
                $player = $code;
                $player = preg_replace ("/width=[\"|'][0-9%]+[\"|']/", 'width="'.$this->formatSize($width).'"', $player);
                $player = preg_replace ("/height=[\"|'][0-9%]+[\"|']/", 'height="'.$this->formatSize($height).'"', $player);
            } else
                $player = "";
            if (isset($this->types[$code_type]['player'])) {
                switch($code_type){
                    case 1: $url = 'http://vk.com/video_ext.php?'.$code; break;
                    case 2: $url = 'http://www.'.$code; break;
                    case 3: $url = 'http://'.$code; break;
                    case 4: $url = $code; if (substr($url,0,5)=="mlr::") $url = substr($url,5); else $url = $code; break;
                    case 5: $url = $code; if (substr($url,0,5)=="upd::") $url = substr($url,5); 
                        break;
                    case 6: $url = $code; if (substr($url,0,5)=="mvd::") $url = substr($url,5); break;
                    case 7: case 8: case 9: $url = 'http://'.$code; break;
                    case 10: $url = 'http://'.str_replace("io.ua::","",$code); break;
                    case 11: case 12: case 13: 
                    case 14: case 17: 
                    case 20: case 27: case 28: $url = 'http://'.$code; break;
                    case 15: $url = $code; if (substr($url,0,5)=="kns::") $url = substr($url,5); else $url = 'http://'.$code; break;
                    case 16: $url = $code; if (substr($url,0,5)=="met::") $url = substr($url,5); break;
                    case 18: $url = $code; if (substr($url,0,5)=="opf::") $url = substr($url,5); break;
                    case 21: $url = $code; if (substr($url,0,5)=="stp::") $url = substr($url,5); else $url = 'http://'.$code; break;
                    case 22: $url = $code; if (substr($url,0,5)=="clp::") $url = substr($url,5); break;
                    case 26: $url = $code; if (substr($url,0,5)=="vzl::") $url = substr($url,5); break;
                    case 29: $url = $code; if (substr($url,0,5)=="nba::") $url = substr($url,5); break;
                    case 110: $url = $code; if (substr($url,0,5)=="ifr::") $url = substr($url,5); break;
                    default: $url = $code;
                }
                
                if (substr($url, 0, 7)=='http://' && (($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off') || ( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO']==='https' ))) {
                    $url = preg_replace("/^http:\/\//", "https://", $url);
                }
                
                if ($code_type===5) {
                    $player = $this->getPlayerUppod($url, $name);
                } else {
                    $player = str_replace(
                        array('{url}', '{uppod_swf}', '{uppod_style}', '{uppod_comment}'),
                        array($url, $this->_getUppodPlayer(), $this->_getUppodStyle(), $name),
                        $this->getPlayerTpl($code_type, $this->player_tpl)
                    );
                }
            }
            return $player;
        } else {
            // Код нуждается в подготовке
            return $code;
        }
    }
    
    // Генерация плеера UPPOD
    public function getPlayerUppod ($url, $name, $params=array()) {
        // Поддержка качества видео
        if (is_array($url)) {
            $urlm = array();
            $qualities = isset($params['qualities']) ? $params['qualities'] : array('720','480','360','240');
            foreach ($qualities as $q) {
                if (isset($url[$q]))
                    $urlm[] = $url[$q];
                else
                    $urlm[] = '';
            }
            if (count($urlm)==0) {
                reset($url);
                $urlm[] = array_shift($url);
            }
            $url = implode(',',$urlm);
        }
        
        global $vk_config;
        $vkConfig = $this->config();
        $this->uppodUsed = true;
        
        $code = $this->getPlayerTpl(5, $vk_config['uppod_enable_html5']==1 ? 'player_html5' : 'player');
        $playlist = isset($params['playlist']) ? $params['playlist'] : '';
        $poster = isset($params['poster']) ? $params['poster'] : '';
        $uppod_style = $this->_getUppodStyle();
        if ($uppod_style==='-')
            $uppod_style = '';
        $player = str_replace(
            array(
                '{width}',
                '{width_css}', ##!! Этот тег заменен просто на 100%
                '{height}', 
                '{height_css}',
                '{url}', 
                '{uppod_swf}', 
                '{uppod_style}', 
                '{uppod_comment}',
                '{playlist}',
                '{poster}'
            ),
            array(
                '100%',
                '100%',
                '100%',
                '100%',
                $url, 
                $this->_getUppodPlayer(), 
                $uppod_style, 
                addslashes(strip_tags($name)),
                $playlist,
                $poster,
            ), $code
        );
        $player = preg_replace("'\\[playlist\\](.*?)\\[/playlist\\]'si", $playlist?'$1':'' ,$player);
        $player = preg_replace("'\\[poster\\](.*?)\\[/poster\\]'si", $poster?'$1':'' ,$player);
        $player = preg_replace("'\\[uppod_style\\](.*?)\\[/uppod_style\\]'si", ($uppod_style && $uppod_style!=='-')?'$1':'' ,$player);
        //exit($player);
        
        return $player;
    }
    
    private function _getUppodPlayer() {
        global $vk_config;
        $defaults = array(
            'swf_player' => '/engine/inc/include/p_construct/img/uppod/uppod.swf',
            'html5_player' => '/engine/inc/include/p_construct/img/uppod/html5_uppod.js',
        );
        if ($vk_config['uppod_enable_html5']==1)
            return $vk_config['uppod_html5'] ? $vk_config['uppod_html5'] : $defaults['html5_player'];
        else
            return $vk_config['uppod_swf'] ? $vk_config['uppod_swf'] : $defaults['swf_player'];
    }
    
    private function _getUppodStyle() {
        global $vk_config;
        $defaults = array(
            'swf_style' => '/engine/inc/include/p_construct/img/uppod/style.txt',
            'html5_style' => '/engine/inc/include/p_construct/img/uppod/html5_style.js',
        );
        if ($vk_config['uppod_enable_html5']==1)
            return $vk_config['uppod_html5_style'] ? $vk_config['uppod_html5_style'] : $defaults['html5_style'];
        else
            return $vk_config['uppod_style'] ? $vk_config['uppod_style'] : $defaults['swf_style'];
    }
    
    public function uppodInitialization() {
        global $vk_config;
        $html = '';
        if ($vk_config['uppod_enable_html5']==1) {
            $html .= '<script type="text/javascript" src="'.$this->_getUppodPlayer().'"></script>';
            $html .= '<script type="text/javascript" src="'.$this->_getUppodStyle().'"></script>';
        }
        return $html;
    }
    
    /**
     * Проверка допустимый ли хостинг (для поиска по yandex)
     * @param type $hosting
     * @return type
     */
    public function isAllowableHisting ($hosting) {
        if (substr($hosting,0,4)==="www.") $hosting = substr($hosting, 4);
        return in_array($hosting, $this->allow_hosting);
    }
    
    /**
     * Разрешен ли поиск по этому тюбу
     * @param type $tubeId
     * @return type
     */
    public function allowedSearch($tubeId) {
        return isset($this->allow_search[$tubeId]) ? $this->allow_search[$tubeId] : false;
    }
    
    public function types() {
        return $this->types;
    }
    
    /**
     * Форматирование размера в зависимости от формата (CSS|html param)
     * @param type $string
     * @param type $is_css
     * @return type
     */
    public function formatSize ($string, $is_css=false) {
        if ($is_css) {
            if (preg_match("/^\d+%$/", $string))
                return $string;
            if (preg_match("/^(\d+)px$/", $string))
                return $string;
            return intval($string).'px';
        } else {
            if (preg_match("/^\d+%$/", $string))
                return $string;
            if (preg_match("/^(\d+)px$/", $string, $arr))
                return intval($arr[1]);
            return intval($string);
        }
    }
}

