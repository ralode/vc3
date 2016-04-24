/* 
 * Вторая версия АПИ для конструктора видео
 * @rationalObfuscation compress
 */

var KonstructorApi = function (config) {
    this.domain = config.vkv_server;
    this.port = 8802;
    
    // Получение ссылки на сервер апи
    this.apiUtl = function(path) {
        return 'https://'+this.domain+':'+this.port+'/api'+(path ? path : '');
    }
    this.get = function (path, data, callback) {
        var url = this.apiUtl(path);
        data.userQAuthHash = config.userQAuthHash;
        $.get(url, data, function(data){
            callback(null, data);
        }, 'json').fail(function(){
            callback('Ошибка соединения');
        });
    }
    this.post = function (path, data, callback) {
        var url = this.apiUtl(path)+'?userQAuthHash='+config.userQAuthHash;
        $.post(url, data, function(data){
            callback(null, data);
        }, 'json').fail(function(){
            callback('Ошибка соединения');
        });
    }
    return this;
};
