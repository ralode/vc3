/**
 *  Базовый класс для шаблона вывода
 *  http://ralode.com
 *  @rationalObfuscation compress
 */
var CRalodePlayer = {
    apiVer: 0.21, // Версия общего интерфейса
    data: null,
    curr_zid: null, // Текущий сезон (ID)
    curr_num: null, // Текущая серия (num - общий для всех сборок)
    curr_sid: null, // Текущая серия (ID)
    zidItems_count: {}, // Количество серий в сборке zid -> count
    sids: {}, // Соответствие sid => num
    // Инициализация (загрузка данных)
    cvStruct: {}, // Структура
    init: function(data, cvStruct) {
        this.cvStruct = cvStruct;
        this.emitEvent('beforeInit', data, cvStruct);
        this.curr_zid = cvStruct.first_zid;
        this.curr_sid = cvStruct.first_sid;
        this.curr_num = 1; // по дефолту
        for (var zid in data) {
            for (var num in data[zid]["items"]) {
                var sid = data[zid]["items"][num]["id"];
                if (this.curr_zid==zid && this.curr_sid==sid)
                    this.curr_num = num;
                this.sids[sid] = {zid:zid, num:num};
                // Count
                if (this.zidItems_count[zid] === undefined)
                    this.zidItems_count[zid] = 1;
                else
                    this.zidItems_count[zid]++;
            }
        }
        this.data = data;
        // Вызов событий
        this.emitEvent('init');
    },
    
    // Структура сборок в правильной сортировке (z1 => {} ... )
    dataSorted: function() {
        var $this = this;
        // Сортировка сборок
        var dataSorted = {};
        var keysSorted = Object.keys(this.data).sort(function(a,b){
            return parseInt($this.data[a].sort)>parseInt($this.data[b].sort);
        });
        for (var index in keysSorted) {
            dataSorted['z'+keysSorted[index]] = this.data[keysSorted[index]];
        }
        return dataSorted;
    },
    
    // Возвращает [{}, {}, {}...] сборок в порядке сортировки (синхронно)
    zList: function() {
        var $this = this;
        var list = [];
        // Сортировка сборок
        var dataSorted = {};
        var keysSorted = Object.keys(this.data).sort(function(a,b){
            return parseInt($this.data[a].sort)>parseInt($this.data[b].sort);
        });
        for (var index in keysSorted) {
            list.push(this.data[keysSorted[index]]);
        }
        return list;
    },
    
    // Возвращает список сборок в порядке сортировки (асинхронно)
    zEach: function(callback) {
        // Сортировка сборок
        var list = this.zList();
        for (var index in list) {
            if (typeof callback === 'function')
                callback(list[index]);
            else
                break;
        }
    },
    
    // Получение данных сборки
    getZ: function(zid) {
        if (this.data && this.data[zid])
            return this.data[zid];
        return false;
    },
    
    // Получение данных серии (по номеру)
    getS: function(zid, num) {
        if (this.data && this.data[zid] && this.data[zid]["items"] && this.data[zid]["items"][num]) 
            return this.data[zid]["items"][num];
        return false;
    },
    
    // Получение обьекта {zid:..., num:...} по sid
    getZidNumBySid: function (sid) {
        if (this.sids && this.sids[sid])
            return this.sids[sid];
        return false;
    },

    // Получение данных серии (по sid)
    getSBySid: function(sid) {
        var x = this.getZidNumBySid(sid);
        if (x && x.num)
            return this.getS(x.zid, x.num);
        return false;
    },
    
    // Получение данных текущей сборки
    getCurrentZ: function() {
        return this.getZ(this.curr_zid);
    },
    
    // Получение данных текущей серии
    getCurrentS: function() {
        return this.getS(this.curr_zid, this.curr_num);
    },
    
    // Получение zid, num наступной серии
    getNextZN: function() {
        var keys = Object.keys(this.data[this.curr_zid].items);
        var len = keys.length;
        var pos = keys.indexOf(this.curr_num.toString());
        if (pos<len-1) {
            var num = keys[pos+1];
            if (num) {
                return {zid:this.curr_zid, num:num};
            }
        } else {
            var currFound = false;
            var zid = false;
            var dataSorted = this.dataSorted()
            for (var z_ in dataSorted) {
                var z = dataSorted[z_].id;
                if (currFound===true) {
                    zid = z;
                    break;
                }
                if (z==this.curr_zid) {
                    currFound = true;
                }
            }
            if (zid!==false && this.zidItems_count[zid]>0) {
                return {zid:zid, num:Object.keys(this.data[zid].items)[0]};
            }
        }
        return false;
    },
    
    // Получение zid, num предыдущей серии
    getPrevZN: function() {
        var keys = Object.keys(this.data[this.curr_zid].items);
        var pos = keys.indexOf(this.curr_num.toString());
        if (pos>0) {
            var num = keys[pos-1];
            if (num) {
                return {zid:this.curr_zid, num:num};
            }
        } else {
            var zid = false;
            var dataSorted = this.dataSorted();
            for (var z_ in dataSorted) {
                var z = dataSorted[z_].id;
                if (z==this.curr_zid) {
                    break;
                } else {
                    zid = z;
                }
            }
            if (zid!==false && this.zidItems_count[zid]>0) {
                var keys2 = Object.keys(this.data[zid].items);
                return {zid:zid, num:keys2[keys2.length-1]};
            }
        }
        return false;
    },
    
    // Выбор серии
    select: function (zid, num) {
        var obj = this.getS(zid, num);
        if (obj) {
            this.curr_zid = zid;
            this.curr_num = num;
            this.curr_sid = obj.id;
            // Вызов событий
            this.emitEvent('changeSerie', this.curr_zid, this.curr_sid, this.curr_num);
            
            return obj;
        }
        return false;
    },
    
    // Выбор серии (по sid). Возвращает данные выбранной серии в случае удачи
    selectBySid: function (sid) {
        var x = this.getZidNumBySid(sid);
        if (x && x.num)
            return this.select(x.zid, x.num);
        return false;
    },
    
    _minHeightCodeInterval: null,
    
    // Использовать код. noEvent===true - не генерировать события
    player: function (code, noEvent) {
        var $this = this;
        var height = $(".playerCode").css('height');
        $(".playerCode").html(code);
        // Если уже есть слежение за кодом
        if (this._minHeightCodeInterval) 
            clearInterval($this._minHeightCodeInterval);
        // Запускаем слежение за кодом
        var c = 0; // лимит цыклов, иначе считаем код в 0 пикс. высотой
        this._minHeightCodeInterval = setInterval(function(){
            var height = $(".playerCode").css('height');
            if (height!=='0px' || c>=500) {
                $(".playerCode").css('min-height', height);
                clearInterval($this._minHeightCodeInterval);
            }
            c++;
        }, 10);
        
        if (!noEvent)
            this.emitEvent('player', code);
    },
    
    
    // Подсчет количества собственных элементов в объекте
    length: function (obj) {
        var c = 0;
        if ($.type(obj)=="object") {
            for (var key in obj)
                if (obj.hasOwnProperty(key)) c++;
        } else
            return false;
        return c;
    },
    // Получение первого элемента
    firstElement: function (obj) {
        for (var key in obj)
            return obj[key];
        return null;
    },
    
    // Инициализация жалоб
    initComplaints: function() {
        $(function(){
            // Пожаловаться
            $(".CvComplaintShowModal").click(function(){
                // Подготовка переменных
                $("#vc-complait-dialog input[type=radio]:first").prop("checked",true);
                $("#cv_complaint_text").val("").css("opacity","0.25").prop("disabled",true);
                // Открытие диолога
                $("#vc-complait-dialog").dialog({
                    closeText: "х",
                    width: "auto",
                    buttons: [ 
                        {
                            text: "Отправить", click: function() {
                                var text = "";
                                $("#vc-complait-dialog input[type=radio]").each(function(){
                                    if ($(this).prop("checked")) text = $(this).val();
                                });
                                if (text=="") {
                                    text = $("#cv_complaint_text").val();
                                }
                                if (text) {
                                    var this_ = this;
                                    $.post("/index.php?do=videoconstructor&action=add_cmpl",{zid:RalodePlayer.curr_zid, sid:RalodePlayer.curr_sid, text:text}, function(data_text){
                                        //alert(data_text);
                                        switch (data_text) {
                                            case "OK": $( this_ ).dialog( "close" ); break;
                                            case "AUTH": alert("Для отправки сообщения вам надо авторизоваться!"); break;
                                            case "ANTIFLOOD": alert("Вы отправляеете сообщения слишком часто! Повтирите через 30 секунд!"); break;
                                            default: alert("Ошибка при отправке сообщения. Пожалуйста, сообщите администратору!"); break;
                                        } 
                                    });
                                } else {
                                    alert("Ошибка: Заполните текст жалобы...");
                                }
                            }
                        },{
                            text: "Отмена", click: function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    ],
                    resizable: false
                });
                return false;
            });
            // Смена чекбокса
            $("#vc-complait-dialog input[type=radio]").change(function(){
                var v = $(this).val();
                if (v=="") {
                    $("#cv_complaint_text").css("opacity","1").prop("disabled",false);
                } else {
                    $("#cv_complaint_text").css("opacity","0.25").prop("disabled",true);
                }
            });
        });
    },
    // События name => [func1, func2...]
    _events: {},
    addEvent: function (name, func) {
        //console.info('addEvent ', name);
        if (name && typeof func === 'function') {
            if (typeof this._events[name]==='undefined') this._events[name] = [];
            this._events[name].push(func);
        } else
            console.error('CRalodePlayer - addEvent error: имя или функция заданы не верно');
    },
    /**
     * Вызов событий
     * @param {string} name Название события
     * @param {array} params    Список параметров
     * @returns {boolean}
     */
    emitEvent: function (name) {
        //console.info('emitEvent ', name);
        if (typeof this._events[name]==='object') {
            for (var key in this._events[name]) {
                this._events[name][key].apply(null, Array.prototype.slice.call(arguments, 1));
            }
        } else
            return false;
    }
};

