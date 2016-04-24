<?php

class VcCache {
    private $prefix = null;
    
    public function __construct ($prefix='parserc_') {
        global $dle_api;
        $this->prefix = $prefix;
        if (!isset($dle_api))
            require_once (ROOT_DIR . '/engine/api/api.class.php');
    }
    
    public function get ($key, $expirense = 300) {
        global $dle_api;
        $result = $dle_api->load_from_cache($this->prefix.$key, $expirense);
        if ($result)
            $result = unserialize($result);
        //echo "Прочитан кеш: {$this->prefix}{$key}\n";
        return $result;
    }
    
    public function set ($key, $value) {
        global $dle_api;
        $dle_api->save_to_cache ($this->prefix.$key, $value);
        //echo "Создан кеш: {$this->prefix}{$key}\n";
        return true;
    }
    
    public function delete ($key) {
        if ($key) {
            global $dle_api;
            $dle_api->clean_cache ($this->prefix.$key);
            //echo "Удален кеш: {$this->prefix}{$key}\n";
        }
        return true;
    }
    
    // Elfktybt dctuj rtif lkt
    public function clear () {
        global $dle_api;
        $dle_api->clean_cache ();
        //echo "Удален кеш DLE...\n";
        return true;
    }
    
}