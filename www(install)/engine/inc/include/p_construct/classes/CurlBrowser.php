<?php

class CurlBrowser
{
    
    // Настройка браузера
    private $config = array(
        'cookies_allow' => false, // Включен ли механизм сохранения куков после каждого запроса
        'CURLOPT_COOKIEJAR' => './cookies.txt', // Файл для сохранения куков
        'CURLOPT_COOKIEFILE' => './cookies.txt', // Файл для загрузки куков
        'CURLOPT_USERAGENT' => 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.110 Safari/537.36',
        'CURLOPT_MAXREDIRS' => 5, // Макс. к-во принимаемых редиректов
        'CURLOPT_PROXY' => '', // Прокси
        'CURLOPT_FOLLOWLOCATION' => 0, // Сделавать ли редиректам
        'CURLOPT_INTERFACE' => null, // Использование сетевого интерфейса
        'CURLOPT_TIMEOUT' => 10 // Макс. время операций в секундах
    );
    // BrowserId
    private $browserId = '';
    // Куки в памяти
    private $cookies = array();
    
    
    function __construct($config=array()){
        if (is_array($config) && count($config)) {
            foreach ($config as $key => $val) {
                if (isset($this->config[$key])){
                    $this->config[$key] = $val;
                }
            }
        }
    }
    
    /**
     * Выполнение запроса используя текущий браузер
     * 
     * Params:
     * [CURLOPT_SSL_VERIFYPEER]
     * [CURLOPT_SSL_VERIFYHOST]
     * [CURLOPT_CONNECTTIMEOUT] - Таймаут соединения (default: 10)
     * [CURLOPT_TIMEOUT] - Максимально позволенное количество секунд для выполнения cURL-функций (default: 30)
     * [CURLOPT_FOLLOWLOCATION] - Следование по редиректам: 0|1 (default: 0)
     * [CURLOPT_MAXREDIRS] - Макс. количество принимаемых редиректов (default: 5)
     * [CURLOPT_REFERER] - Referer (по умолчанию не передается)
     * []
     * @param str $url              Ссылка
     * @param str $method           Метод: get|post
     * @param array $data           Данные POST (ассоцативный массив) (игнорируются при get-запросе)
     * @param str|array $headers    Дополнительные заголовки (список)
     * @param array $params         Массив дополнительных параметров соединения
     */
    function request ($url, $method='get', $data=null, $headers=null, $params=array()) {
        // Ссылка
        $url = trim($url);
        $url_left = strtolower(substr($url, 0, 5));
        if (!($url_left=='https' || $url_left=='http:')){
            throw new Exception('Http and https urls allowed only.');
        }
       
        // Метод
        $method = strtolower($method);
        if (!$method) $method = 'get';
        if (!($method=='get' || $method=='post')) {
            throw new Exception('Get and post methods allowed only.');
        }
        // Данные POST ##!! Фикс (средний): сделать возможность отправки массивов 2 и более уровня
        if ($method=='post') {
            $_post = array();
            if (is_array($data)) {
                foreach ($data as $name => $value) {
                    $_post[] = urlencode($name) . '=' . urlencode($value);
                }
            }
        } else $_post = false;
        // Инициализация Curl
        $ch = curl_init($url);
        // Перенаправим вывод curl-a в переменную
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        // Если выбран метод post
        if ($_post!==false) {
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        // Если соединяемся по протоколу https
        if ($url_left == 'https') {
            // Проверка сертификата узла сети
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, isset($params['CURLOPT_SSL_VERIFYPEER']) ? $params['CURLOPT_SSL_VERIFYPEER'] : false);
            // 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, isset($params['CURLOPT_SSL_VERIFYHOST']) ? $params['CURLOPT_SSL_VERIFYHOST'] : 0);
        }
        // Данные post
        if (is_array($_post)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, join('&', $_post));
        }
        // Referer
        if (isset($params['CURLOPT_REFERER'])) {
            curl_setopt($ch, CURLOPT_REFERER, $params['CURLOPT_REFERER']);
        }
        // Скачиваем также заголовки
        curl_setopt($ch, CURLOPT_HEADER, 1);
        // Следование по редиректам
        $val = $this->getConfigValue('CURLOPT_FOLLOWLOCATION', 0, $params);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $val);
        if ($val) {
            $val = $this->getConfigValue('CURLOPT_MAXREDIRS', 5, $params);
            curl_setopt($ch, CURLOPT_MAXREDIRS, $val);
        }
        // Таймаут соединения
        $val = $this->getConfigValue('CURLOPT_CONNECTTIMEOUT', 30, $params);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $val);
        // Таймаут выполнения функций Curl
        $val = $this->getConfigValue('CURLOPT_TIMEOUT', 30, $params);
        curl_setopt($ch, CURLOPT_TIMEOUT, $val);
        // Прокси
        $val = $this->getConfigValue('CURLOPT_PROXY', null, $params);
        if ($val!==null && $val) {
            curl_setopt($ch, CURLOPT_PROXY, $val);
        }
        // User-agent
        $val = $this->getConfigValue('CURLOPT_USERAGENT', 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)', $params);
        curl_setopt($ch, CURLOPT_USERAGENT, $val);
        // Куки
        if ($this->config['cookies_allow']) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->config['CURLOPT_COOKIEFILE']); 
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->config['CURLOPT_COOKIEJAR']);
        }
        // Если заданы какие-то заголовки для браузера
        if (is_array($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        // Если задан сетевой интерфейс
        $val = $this->getConfigValue('CURLOPT_INTERFACE', null, $params);
        if ($val) {
            curl_setopt($ch, CURLOPT_INTERFACE, $val);
        }
        // Время выполнения
        $val = $this->getConfigValue('CURLOPT_TIMEOUT', null, $params);
        if ($val>0) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $val);
        }
        // Выполняем запрос
        $result = curl_exec($ch);
        if (curl_errno($ch) != 0 && empty($result)) {
            $result = false;
        }
        // Закрываем дескриптор
        curl_close($ch);
        return new CurlResponse($result);
    }
    
    /**
     * Получение настройки $key
     * 
     * Пример: $this->getConfigValue('CURLOPT_PROXY', null, $params);
     * @param type $key
     * @param type $default
     * @param type $array
     * $return type  Значение из $array, иначе значение из $this->config, иначе $default
     */
    function getConfigValue($key, $default, $array) {
        if (isset($array[$key])) return $array[$key];
        if (isset($this->config[$key])) return $this->config[$key];
        return $default;
    }
    
}
/*
$curl = new CurlBrowser();

$res = $curl->request(
        'http://localhost/test/curl-browser/headers.php?v=y',
        'get',
        '',
        array("Cookie: a=b; ")
);
echo $res->content();
*/
?>