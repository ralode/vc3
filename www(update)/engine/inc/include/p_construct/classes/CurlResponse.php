<?php
/**
 * Ответ Curl
 */

class CurlResponse
{
    private $_headers = null; // Заголовки ответа
    private $_content = null; // Тело ответа
    private $_cookies; // Заданные сервером куки (массив)
    
    public $http_status; // Код ответа HTTP
    public $charset; // Кодировка
    
    /**
     * Конструктор
     * @param type $response    Текст ответа з заголовками
     */
    function __construct($response) {
        // Удаляем ответ HTTP/1.1 100 Continue? если есть
        if (substr($response,0,25)=="HTTP/1.1 100 Continue\r\n\r\n") $response = substr($response,25);
        
        $pos = strpos($response, "\r\n\r\n");
	if ($pos!==false) {
            // Заголовки
            $this->_headers = substr ($response, 0, $pos);
            // Тело
            $this->_content = substr ($response, $pos+4);
            unset($response);
            // Ответ сервера
            if (preg_match("/HTTP[^ ]+ ([0-9]+)[^\r\n]+/", $this->_headers, $arr)){
                $this->http_status = $arr[1];
            }
            // Куки
            preg_match_all ("/Set-Cookie:([^\r\n]+)/", $this->_headers, $arr);
            $this->_cookies = array();
            if (count($arr[1])){
                foreach ($arr[1] as $value) {
                    $array = explode(';',trim($value));
                    $cookie = array();
                    $first = true;
                    foreach($array as $val2) {
                        $val2 = trim($val2);
                        $pos = strpos($val2,'=');
                        if ($first) {
                            $cookie['key'] = urldecode(substr($val2,0,$pos));
                            $cookie['value'] = urldecode(substr($val2,$pos+1));
                        } else {
                            $cookie[substr($val2,0,$pos)] = substr($val2,$pos+1);
                        }
                        $first = false;
                    }
                    if (isset($cookie['key'])) {
                        if (isset($cookie['expires']))
                            $cookie['expires'] = strtotime($cookie['expires']);
                            $this->_cookies[] = $cookie;
                    }
                };
            }
            // Кодировка
            if (preg_match("/charset=([A-Za-z0-9-]+)/", $this->_headers, $arr)) {
                $this->charset = strtolower($arr[1]);
            }
	}
    }
    
    /**
     * Проверка правильный ли ответ
     * @return boolean
     */
    function check() {
        if ($this->_headers!==null || $this->_content!==null)
            return true;
        else 
            return false;
    }
    
    /**
     * Получить строку заголовков
     * @return type
     */
    function headers() {
        return $this->_headers;
    }
    
    /**
     * Получить массив заголовков
     * @return type
     */
    function headersArray() {
        $h = explode("\r\n", $this->_headers);
        $headers = array();
        foreach ($h as $k => $v) {
            if (strpos($v, ':')) {
                $k = substr($v, 0, strpos($v, ':'));
                $v = trim(substr($v, strpos($v, ':') + 1));
            }
            $headers[$k] = $v;
        }
        return $headers;
    }
    
    /**
     * Получить контент ответа
     * @return type
     */
    function content() {
        return $this->_content;
    }
    
    /**
     * Получить массив куков ответа
     * @return type
     */
    function cookies() {
        return $this->_cookies;
    }
    
    /**
     * Получить строку куков
     * @return string   Строка вида: key1=val1; key2=val2; ...
     */
    function cookiesLine() {
        $cookies_line = '';
        if (count($this->_cookies)){
            foreach ($this->_cookies as $cookie) {
                $cookies_line.= $cookie['key'].'='.$cookie['value'].'; ';
            }
        }
        return $cookies_line;
    }
}

?>