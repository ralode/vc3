<?php
/*
 * Реализует подгрузку настроек расширения по требованию 
 * для экономии рессурсов
 */

class VcExtensionConfig
{
    protected  static $_instance;
    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}
    
    public static function getInstance() {
        if (!isset(self::$_instance) ) {
            self::$_instance = new VcExtensionConfig ();
        }
        return self::$_instance;
    }
    
    private function _parseXML ($id, $file) {
        $array = array();
        $path = ENGINE_DIR . "/inc/include/p_construct/ext/".$id."/".$file.".xml";
        if (is_file($path)) {
            $sxe = simplexml_load_file($path);
            foreach ($sxe as $key2 => $val) {
                if ($key2==='config') {
                    $array['config'] = array();
                    if (is_object($val)) {
                        foreach ($val as $key3 => $val3) {
                            $conf = array('default'=> (string) $val3,'attributes'=>array());
                            if (is_object($val3)) {
                                foreach ($val3->attributes() as $key4 => $val4) {
                                    $conf['attributes'][$key4] = (string) $val4;
                                }
                            }
                            $array['config'][$key3] = $conf;
                        }
                    }
                } else 
                    $array[$key2] = (string) $val;
            }
        }
        return $array;
    }
    
    public function encodeDeniedSymbols ($str) {return $str;
        return str_replace(array('&','<','>'), array('###amp;','###lt;','###gt;'), $str);
    }
    
    public function decodeDeniedSymbols ($str) {return $str;
        return str_replace(array('###amp;','###lt;','###gt;'), array('&','<','>'), $str);
    }
    
    /**
     * Получение настроек
     */
    public function get($id, $charset='windows-1251') {
        $array = $this->_parseXML($id, 'config');
        if ($charset==='windows-1251')
            $array = array_iconv('utf-8', 'windows-1251', $array);
        return $array;
    }
    
    public function getDescription($id, $charset='windows-1251') {
        $array = $this->_parseXML($id, 'description');
        if ($charset==='windows-1251')
            $array = array_iconv('utf-8', 'windows-1251', $array);
        return $array;
    }
}
