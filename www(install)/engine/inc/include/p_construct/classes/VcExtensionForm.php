<?php
/*
 * Генератор форм для настроек приложения
 */

class VcExtensionForm
{
    
    private $id; // Id расширения
    private $config;
    private $description;
    private $_preloaded = false;
    
    public function __construct($id) {
        global $vk_config;
        if (isset($vk_config["extensions"][$id])) {
            $this->id = $id;
        } else
            exit('Расширение '.  strip_tags($id).'не найдено!');
    }
    
    
    
    private function preloadFiles() {
        if (!$this->_preloaded) {
            global $config, $vk_config;
            $this->config = $vk_config["extensions"][$this->id];
            $this->description = VcExtensionConfig::getInstance()->getDescription($this->id, $config['charset']);
        }
    }
    
    // Подготовка хтмл аттрибута
    private function prepAttr($val) {
        return addslashes(strip_tags($val));
    }
    
    public function textareaField ($name, $attributes, $default) {
        $id = $this->prepAttr($this->id . '_' . $name);
        $html = '<label for="'.$id.'">'.$attributes['name'].'</label> <small>('.$attributes['description'].')</small><br>';
        $style = '';
        if (isset($attributes['width']))
            $style .= "width:{$attributes['width']};";
        $html .= '<textarea type="text" name="'.$this->id.'['.$this->prepAttr($name).']" rows="'.
                (isset($attributes['rows']) ? $this->prepAttr($attributes['rows']) : 4).'" style="'.
                $style.'">'.$this->htmlspecialchars($default).'</textarea>';
        return $html;
    }
    
    public function stringField ($name, $attributes, $default) {
        $id = $this->prepAttr($this->id . '_' . $name);
        $html = '<label for="'.$id.'">'.$attributes['name'].'</label> <small>('.$attributes['description'].')</small><br>';
        $html .= '<input type="text" name="'.$this->id.'['.$this->prepAttr($name).']" value="'.$this->prepAttr($default).'">';
        return $html;
    }
    
    public function enumField($name, $attributes, $default) {
        $id = $this->prepAttr($this->id . '_' . $name);
        $html = '<label for="'.$id.'">'.$attributes['name'].'</label> <small>('.$attributes['description'].')</small><br>';
        $html .= "<select size\"1\" name=\"".$this->id.'['.$this->prepAttr($name).']'.' id="'.$id.'">';
        // Парсинг опций
        $options = array();
        $arr = explode("||", $attributes['list']);
        if (is_array($arr)) {
            foreach ($arr as $val) {
                $pos = strpos($val, '|');
                if ($pos!==false) {
                    $options[substr($val,0,$pos)] = substr($val,$pos+1);
                }
            }
        }
        foreach ($options as $key => $val) {
            $html .= '<option value="'.$this->prepAttr($key).'"'.($key==$default ? ' selected':'').'>'.strip_tags($val).'</option>';
        }
        $html .= "</select>";
        return $html;
    }

    public function compuile () {
        $this->preloadFiles();
        if ($this->description && $this->description['config']) {
            $html = '';
            foreach ($this->description['config'] as $name => $arr) {
                $default_value = isset($this->config['config'][$name]) 
                        ? $this->config['config'][$name]
                        : $arr['default'];
                $attributes = $arr['attributes'];
                $type = $attributes['type'];
                if (method_exists($this, $type.'Field')) {
                    $func = $type.'Field';
                    $html .= $this->$func($name, $attributes, $default_value) . "<br><br>";
                }
            }
            return $html;
        }
        return false;
    }
    
    private function _parseData($data) {
        $save = $this->config['config'];
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if (isset($this->description['config'][$key])) {
                    $attributes = $this->description['config'][$key]['attributes'];
                    $default = $this->description['config'][$key]['default'];
                    if (isset($attributes['preg_match'])) {
                        $pattern = $attributes['preg_match'];
                        try {
                            if (preg_match($pattern, $val)) {
                                $save[$key] = $val;
                            }
                        } catch (Exception $ex) {
                            // Не меняем настройку
                        }
                    } else 
                        $save[$key] = $val;
                }
            }
        }
        return $save;
    }
    
    private function _dataToXML ($data) {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><config/>');
        if (is_array($data)) {
            foreach ($data as $key => $val)
                $xml->addChild($key, str_replace('&','&amp;',$val));
        }
        return $xml->asXML();
    }
    
    public function save() {
        if (isset($_POST[$this->id]) && is_array($_POST[$this->id])) {
            $this->preloadFiles();
            $save = $this->_parseData($_POST[$this->id]);
            global $config;
            if ($config['charset']==='windows-1251')
                $save = array_iconv ('windows-1251', 'utf-8', $save);
            if (count($save)>0) {
                $xml = $this->_dataToXML($save);
                if ($xml) {
                    $file = ENGINE_DIR . "/inc/include/p_construct/ext/".$this->id.'/config.xml';
                    $fp = fopen ($file, "w");
                    if ($fp) {
                        fwrite($fp, $xml);
                        fclose($fp);
                        VcExtension::getInstance()->clearExtCache();
                        return true;
                    } else
                        return 'Не удалось открыть файл '.$file." для записи.";
                } else
                    return 'Сгенерированный файл XML пуст. Не сохранено...';
            } else
                return 'Нет данных для сохранения...';
            exit;
            
        }
        return false; // Не сохранено
    }
    
    private function htmlspecialchars ($str) {
        global $config;
        return htmlspecialchars($str, null, $config['charset']);
    }
    
}
