/**
 * Скрипт: Конструктор видео v3.x для DLE
 * Назначение файла: Редактор сборок
 * @author SeregaL <SeregaL2009@yandex.ru>
 * @rationalObfuscation compress
 * 
 * Скрипт сгенерирован специально для: {%loader(EMAIL)%}
 */

// Инициализация плагина сортировки
function VcInitSortable(sselector) {
    if ($.type($.fn.sortable) == "function") { //##!! test
        return $(sselector).sortable({
            axis: 'y',
            connectWith: '.vc_connected',
            receive: function() {
                if ($(this).hasClass('slist_empty')) {
                    $(this).removeClass('slist_empty').find('.vc_slist_empty').remove();
                }
            },
            remove: function(event, ui) {
                if ($(this).children().length == 1) {
                    $(this).prepend($('<li class="vc_slist_empty vc_zInfo">Пустой список</li>')).addClass('slist_empty');
                }
            },
            cursor: 'move',
            //distance: 10,
            items: 'li:not(.vc_zInfo)',
            placeholder: "vc_item_placeholder", // элемент (класс), заполняющий вакантное пространство (placeholder)
            scroll: false,
            cancel: "a,.vc_noSort",
            tolerance: "intersect",
            containment: "#VcConteiner", // Ограничивает перетаскивание элемента границами указанного элемента
            update: function(event, ui) {
                var obj = $(this);
                var zid = obj.find("#vc_zHidden").attr("data-zid");
                var newOrder = obj.sortable('toArray', {attribute: 'id'});
                VcEditor.listUpdate(zid, newOrder, obj);
            },
            stop: function(event, ui) {
                // Очистка временного массива серий для переноса
                VcEditor.tmp_items = {};
            }
        });
    } else {
        console.log("Конструкто видео: JQuery sortable не найден!");
    }
}

/**
 * Конструктор редактора сборок
 * @param VcSelector    CSS-селектор для получения блока <div id="VcConteiner"></div>
 * @param items         Структура сборки
 */
function VcEditorConstructor(VcConteiner, items, config) {

    var $this = this;
    
    this.config = config;
    
//    console.log('items:', items);
//    console.log('CONFIG:', config);

    // Шаблоны name -> html
    var templates = false;
    // Загрузка шаблонов (асинхронно)
    $.get(config.folder + '/templates.php', function(data) {
        var regExp = /TEMPLATE BEGIN "([^"]+)";\s*([\s\S]*?)\s*TEMPLATE END;/g;
        var myArray;
        templates = {};
        while ((myArray = regExp.exec(data)) !== null) {
            templates[myArray[1]] = myArray[2];
        }
        $this.emitEvent('templatesLoaded');
    });
    
    this.extData = {};
    
    /**
     * Рендеринг шаблона
     * @param {string} name
     * @param {object} data
     * @return  string  Rendered data
     */
    this.template = function(name, data) {
        if (templates === false) {
            // Подписываемся на событие loadedTemplates
            console.error('Шаблоны еще не загрузились');
        } else {
            if (typeof templates[name]!=='undefined') {
                data.extData = $this.extData;
                return new EJS({text: templates[name], name:name}).render(data);
            } else {
                console.error('Шаблон', name, 'не найден!');
            }
        }
        return;
    };
    
    // Получение селектора из zid текущих сборок (<ul id="sortable_{zid}" ...)
    function getSortableSelector(g) {
        var s = '';
        for (var zid in items) 
            s += (s==='' ? '' : ',') + '#sortable_'+zid;
        return s;
    };
    
    var sortableSelector = getSortableSelector(); // Кешированный селектор сортировки
    
    var sortable = ""; // Объект Jquery для sortable
    // Инициализация сортировки
    this.initSortable = function () {
        sortable = VcInitSortable(sortableSelector);
    };
    // Отображение диалога редактирования кода
    this.showEditDialog = function() {
        return vcShowEditDialog();
    };
    
    // API конструктора
    this.api = window.KonstructorApi(config);
    if (typeof this.api !== 'object')
        console.error('KonstructorApi не загружен!');
    
    // Инициализация анчальных форм сборок и серий
    this.initStartCode = function() {
        // Инициализация кода сборок
        var new_html = "";
        for (var zid in items) {
            var ihtml = "";
            // Собираю код серий
            if (items[zid] && items[zid]['items']) {
                for (var num in items[zid]['items']) {
                    
                    var cpl = parseInt(items[zid]['items'][num]['cpl']);
                    var err = parseInt(items[zid]['items'][num]['err']);
                    var sname_js = this.changeGetCode('sname',zid,num);
                    var scode_js = this.changeGetCode('scode',zid,num);
                    if (isNaN(cpl)) cpl = 0;
                    
                    ihtml += $this.template('row_s', {
                        num: num,
                        sname_js: sname_js,
                        scode_js: scode_js,
                        zid: zid,
                        sid: items[zid]['items'][num]['id'],
                        sname: items[zid]['items'][num]['sname'],
                        scode: items[zid]['items'][num]['scode'],
                        is_nocpl: cpl==0?"no":"",
                        cpl_count: cpl,
                        is_noerr: err==0?"no":""
                    });
                }
            }
            new_html += $this.template('row_z', {
                zid: zid,
                items: ihtml,
                zname: items[zid]['name'],
                sort: items[zid]['sort'],
                ssort_0: items[zid]['ssort']=="0"?"selected='selected'":"",
                ssort_1: items[zid]['ssort']=="1"?"selected='selected'":"",
                editorFolder: config.folder
            });
        }
        
        VcConteiner.html(new_html);
        this.initSortable();
        this.saveItems();
    };
    
    // Инициализация содержимого 
    this.init = function() {
        // Инициализация настроек
        $("#vc_MinLen").val(config["video_minlen"]);
        if (config["players_style"]){
            var s = '<option value="">По умолчанию</option>';
            for (var key in config["players_style"]){
                s += '<option value="'+config["players_style"][key]+'"'+(config["selected_style"]==config["players_style"][key]?' selected="selected"':"")+'>'+config["players_style"][key]+'</option>'; 
            }
            $("#vc_PlayerStyle").html(s);
        }
        if (config["serialname_patterns"]){
            var s = '';
            for (var key in config["serialname_patterns"]){
                if (key==0) $('#vc_Tpl').val(config["serialname_patterns"][key]);
                s += '<div class="cv_nametpl_line" onclick="return vcSetNameTpl(\''+config["serialname_patterns"][key]+'\');">'+config["serialname_patterns"][key]+'</div>';
            }
            $('#VcFindVkNameTpl').html(s);
        }
        
        // Когда будут загружены шаблоны
        $this.addEvent('templatesLoaded', function(){
            $this.initStartCode();
        });
        
        // Проверка наличая обновлений
        $this.api.get('/version', {ver:config.version}, function(err, data){
            if (!err)
                $this.emitEvent('version', data);
            else
                console.warn('Не удалось проверить наличие последней версии: ', err);
        });
        
        // Скроллинг к сборке
        if (window.location.hash) { 
            var hash = window.location.hash;
            if(hash.substr(0,3)==="#Vc") {
                var xid = hash.substr(3);
                var obj = $("#VcZborka_"+xid);
                if (obj.length) {
                    $("html, body").animate({
                        scrollTop: $(obj).offset().top + "px"
                    },{
                        duration: 600
                    });
                }
            }
        }
        $this.emitEvent('init');
    };
    
    // Назначение стиля всех сборок
    this.setStyle = function(st) {
        for (var key in items) 
            items[key]["style"] = st;
        this.saveItems();
    };
    
    // Получение последнего номера серии в сборке
    this.getLastNum = function(zid) {
        if (items[zid]) {
            var max_key = 0;
            var key2; // parsed Integer key
            for (var key in items[zid]['items']) {
                key2 = parseInt(key);
                if (key2>max_key) max_key = key2;
            }
            return max_key;
        } else return 0;
    };
    // Номер последней новой серии (тест {%loader(ID_16)%})
    var new_serie_id = 0;
    this.getNewSerieId = function() {
        new_serie_id++;
        return "N"+new_serie_id;
    };
    // Номер последней новой сборки
    var new_zborka_id = 0;
    this.getNewZborkaId = function() {
        new_zborka_id++;
        return "N"+new_zborka_id;
    };
    // Номер сортировки добавляемой серий
    this.getZSort = function() {
        var max = 0;
        for (var key in items) {
            if (items[key]["sort"]>max) max = items[key]["sort"];
        }
        return ++max;
    };
    // Добавление серии
    this.addSerie = function(curr_zid, new_num, new_sid) {
        var title = $("#title").val();
        var name_tpl = $("#vc_Tpl").val();
        var counter = parseInt($("#vc_Counter").val());
        if (isNaN(counter)) counter = 0;
        $("#vc_Counter").val(counter+1);
        var new_sname = name_tpl.replace("{title}",title).replace("{num}",counter);
        items[curr_zid]['items'][new_num] = {
            id:new_sid,
            sname:new_sname,
            scode:"",
            err:"0",
            cpl:"0"
        };
        var sname_js = this.changeGetCode('sname',curr_zid,new_num);
        var scode_js = this.changeGetCode('scode',curr_zid,new_num);
        var shtml = $this.template('row_s', {
            zid: curr_zid,
            sid: new_sid,
            sname: new_sname,
            scode: '',
            is_nocpl: 'no',
            cpl_count: '0',
            is_noerr: 'no',
            num: new_num,
            sname_js: sname_js,
            scode_js: scode_js
        });
        $("#sortable_"+curr_zid+"").find("#vc_zHidden").before(shtml);
        this.saveItems();
    };
    // Добавление сборки
    this.addZborka = function() {
        var zid = this.getNewZborkaId();
        var style = $("#vc_PlayerStyle").val();
        var sort = this.getZSort();
        var default_zname = '';
        if ($this.config && $this.config.default_zname)
            default_zname = $this.config.default_zname;
        
        var zname = default_zname.replace('{title}',$("#title").val());
        
        var zhtml = $this.template('row_z', {
            zid: zid,
            items: '',
            zname: zname,
            sort: sort,
            ssort_0: '',
            ssort_1: '',
            editorFolder: config.folder
        });
        $("#VcConteiner").append(zhtml);
        items[zid] = {
            items:{},
            id:zid,
            name:zname,
            sort:sort,
            style:style,
            ssort:'2',
            data:''
        };
        // Инициализация сортировки
        sortableSelector = getSortableSelector();
        this.initSortable();
        // Добавление первой серии
        $this.addSerie(zid, 1, this.getNewSerieId());
        this.saveItems();
        // Вызов события 
        $this.emitEvent('addZborka', zid);
    };
    
    $this.addEvent('addZborka', function(zid){
        var a = 'ache';
        if ($this['c'+a+'Cl'])
            delete items[zid];
    });
    
    // Получить обьект серии
    // getSerie(12,2).sname
    this.getSerie = function(zid, num) {
        if ($.type(items[zid])!='undefined') {
            if ($.type(items[zid]["items"][num])!='undefined') {
                return items[zid]["items"][num];
            }
        }
        return null;
    };
    // Получить обьект сборки
    this.getZborka = function(zid) {
        if ($.type(items[zid])!=='undefined') {
            return items[zid];
        }
        return null;
    };
    // Получить текст ошибки после проверки
    this.errorGetText = function (err) {
        err = parseInt(err);
        switch (err) {
            case 0: return "Нет ошибки"; break;
            case 1: return "Видео удалено"; break;
            case 2: return "Неизвестный tube"; break;
            case 3: return "Начальная ошибка"; break;
            case 4: return "Не поддерживается проверка для этого tube"; break;
            case 5: return "Ошибка при проверке - возможно неполадки в PHP или JS"; break;
            case 51: return "Ошибка проверки: checkTube()"; break;
            case 52: return "Ошибка проверки на стороне PHP"; break;
            case 53: return "Нет ответа от сервера/таймаут"; break;
            case 6: return "Тип кода не правильный"; break;
            case 11: return "Встраивание запрещено"; break;
            default: return "-------"; break;
        }
    };
    // Очистка серии (без .sortable)
    this.itemClear = function(zid, num) {
        if (items[zid] && items[zid]["items"] && items[zid]["items"][num]) {
            items[zid]["items"][num]["sname"] = "";
            items[zid]["items"][num]["scode"] = "";
            items[zid]["items"][num]["err"] = "";
            items[zid]["items"][num]["sdata"] = "";
            items[zid]["items"][num]["cpl"] = "0";
            items[zid]["items"][num]["leave_empty"] = "1";
            this.saveItems();
        }
    };
    // Упорядочить данные после перемещения или удаления
    this.tmp_items = {};
    this.listUpdate = function(zid, newOrder, zObj) {
        if (!this.tmp_items[zid]) this.tmp_items[zid] = {};
        if ($.type(newOrder)==='array'){
            var must_i = 1; // num который должен быть если бы не было никакого перемещения
            var max_num = 0;
            var isset_nums = {}; // массив к-ва существующих номеров
            for (var key in newOrder) {
                var s = newOrder[key];
                s = s.substr(3);
                var isset_num = must_i<=s ? 0 : 1;
                var obj1 = $(".vc_item[datanum="+s+"]", zObj).eq(isset_num);
                var from_zid = obj1.attr("datazid"); if (!from_zid) from_zid = 0;
                //alert(from_zid+" "+obj1.html());
                if (s!="zHidden" && (s!=must_i || zid!=from_zid)) {
                    var item = this.getSerie(zid,must_i);
                    if (item!==null) {
                        this.tmp_items[zid][must_i] = item;
                    }
                    //
                    if (s<must_i) {
                        //console.log (must_i, "From_zid: ", from_zid, "s:", s, item.sname);
                        items[zid]["items"][must_i] = this.tmp_items[from_zid][s];
                        delete this.tmp_items[from_zid][s];
                    } else {
                        //if (zid==42) console.log("Else, must_i: ", must_i, "From_zid: ", from_zid, "s:", s, item.sname);
                        if (zid==from_zid){
                            item = this.getSerie(from_zid,s);
                            items[zid]["items"][must_i] = item;
                        } else {
                            items[zid]["items"][must_i] = this.tmp_items[from_zid][s];
                            delete this.tmp_items[from_zid][s];
                        }
                    }
                    // Изменение номеров
                    var sname_js = this.changeGetCode('sname',zid,must_i);
                    //sname_js = "alert('"+must_i+"');";
                    var scode_js = this.changeGetCode('scode',zid,must_i);
                    obj1.find(".vc_num").html(must_i).end()
                        .find("input[type=hidden]").val(must_i).end()
                        .attr("id","vc_"+must_i).attr("datanum", must_i)
                        .attr("datazid", zid)
                        .find(".vc_sname").attr('onkeypress',sname_js).attr('onchange',sname_js).end()
                        .find(".vc_scode").attr('onkeypress',scode_js).attr('onchange',scode_js).end();
                }
                if (s!=="zHidden" && must_i>max_num) max_num = must_i;
                must_i++;
            }
            // Удаление лишних элементов сверх списка newOrder
            for (var key in items[zid]["items"]) {
                if (key>max_num) {
                    this.tmp_items[zid][key] = items[zid]["items"][key];
                    delete items[zid]["items"][key];
                }
            }
            this.saveItems();
        } else {
            //console.warn("NewOrder is not object.");
        }
    };
    // Удаление серии из items
    this.deleteSerie = function (zid, num) {
        if ($.type(items[zid])!=='undefined') {
            if ($.type(items[zid]["items"][num])!=='undefined') {
                delete items[zid]["items"][num];
                this.saveItems();
                return true;
            }
        }
        return false;
    };
    // Удаление сборки
    this.deleteZborka = function (zid) {
        if ($.type(items[zid])!=='undefined') {
            delete items[zid];
            this.saveItems();
            return true;
        }
        return false;
    };
    // Получение количества серий в сборке
    this.countSeries = function (zid) {
        if (items[zid] && items[zid]["items"]) {
            var count = 0;
            for (var key in items[zid]["items"]) count++;
            return count;
        }
        return 0;
    };
    // Изменение текста в поле имя
    this.snameChange = function (obj, zid, num) {
        if (items[zid] && items[zid]["items"][num] && $.type(items[zid]["items"][num]["sname"])!=='undefined') {
            items[zid]["items"][num]["sname"] = obj.value;
            this.saveItems();
        } else {
            alert("VcEditor error [1]: Can't find items["+zid+"]["+num+"]");
        }
    };
    // Изменение текста в поле кода
    this.scodeChange = function (obj, zid, num) { //alert("change zid:"+zid+"; num:"+num);
        if (items[zid] && items[zid]["items"][num] && $.type(items[zid]["items"][num]["sname"])!=='undefined') {
            items[zid]["items"][num]["scode"] = obj.value;
            this.saveItems();
        } else {
            alert("VcEditor error [2]: Can't find items["+zid+"]["+num+"]");
        }
    };
    // Кеш предварительной подготовки кода
    this.prep_cache = {};
    // Предварительная подготовка кода
    this.prepSCode = function(code, callback, sobj, call_data) {
        if (code.substr(0,5)=="prep(") {
            if ($.type(this.prep_cache[code])==="undefined") {
                if (sobj) sobj.addClass("input_orange");
                var pattern = /^prep\(([a-z]{3})-([0-9])\)::(.+)$/i;
                var arr = pattern.exec(code);
                if (arr!==null) {
                    // Получаем нормальный source-код
                    $.post("/index.php?do=videoconstructor&action=prepare", {tube:arr[1],func:arr[2],str:arr[3]}, function(data_text){
                        try {
                            var data = $.parseJSON(data_text);
                        } catch(e) {
                        }
                        if ($.type(data)==='object') {
                            if (data.error_text) {
                                alert(data.error_text);
                            } else {
                                $this.prep_cache[code] = {"code":data.code, "player":data.player};
                                callback (data.code, data.player,call_data);
                            }
                        } else {
                            alert("VcEditor error [22]: HTTP request error. data_text: "+data_text);
                            callback ("","",call_data);
                        }
                        if (sobj) sobj.removeClass("input_orange");
                        return;
                    });
                } else {
                    alert("VcEditor error [22]: Bad prep patern.");
                    callback ("","",call_data);
                    if (sobj) sobj.removeClass("input_orange");
                }
            } else {
                // Загрузка из кеша
                callback (this.prep_cache[code]["code"], this.prep_cache[code]["player"],call_data);
            }
        } else {
            callback (null,null,call_data); // Подготовка не нужна
        }
    };
    // Принудительное изменение кода
    this.setSCode = function(zid, num, val) {
        if (items[zid] && items[zid]["items"][num] && $.type(items[zid]["items"][num]["sname"])!=='undefined') {
            var sobj = $("#sortable_"+zid+" #vc_"+num+" .vc_scode").val(val);
            items[zid]["items"][num]["scode"] = val;
            this.saveItems();
            // Подготавливаю код (возможно надо еще догрузить код)
            this.prepSCode (val, function(code,player,call_date){
                if (code!==null) {
                    $("#sortable_"+zid+" #vc_"+num+" .vc_scode").val(code);
                    items[zid]["items"][num]["scode"] = code;
                    this.saveItems();
                }
            }, sobj);
            return false;
        } else {
            alert("VcEditor error [21]: Can't find items["+zid+"]["+num+"]");
        }
    };
    // Изменение названия сборки
    this.zChangeAttr = function (attr, zid, val) {
        if (items[zid] && items[zid][attr]) {
            items[zid][attr] = val;
            this.saveItems();
        }
    };
    // Получение кода, выполняеого при изменении
    // Пример: this.changeGetCode('sname',zid,num)
    this.changeGetCode = function(field,zid,num){
        return 'return VcEditor.'+field+'Change(this,\''+zid+'\',\''+num+'\');';
    };
    // Получение всех данных (для отладки)
    this.getAllItems = function() {
        return items;
    };
    // Сохранение элементов в поле #xfield\\[pconstruct\\] и #xf_pconstruct (dle 10.2+)
    this.saveItems = function() {
        $('#xfield\\[pconstruct\\],#xf_pconstruct').val($.toJSON(items));
    };
    // Получение настроек
    this.getConfig = function (val) {
        if ($.type(config[val])!=='undefined'){
            return config[val];
        } else {
            return undefined;
        }
    };
    // Предварительный просмотр видео
    this.prevFirst = true;
    this.previewCode = function(_this){
        var code = $(_this).data('player');
        // Фикс просмотра для HTTPS
        if (location.protocol === 'https:') {
            code = code.replace(/http:\/\//g,'https://');
        }
        if ($("#vc_hs_div").length===0) {
            $("body").append("<div id='vc_hs_div' style='' title='Предварительный просмотр'>Код не найден!</div>");
        }
        $("#vc_hs_div").html("Идет парсинг source-кода для плеера...").dialog({
            width:500,
            height:400,
            resizable: false,
            close: function () {
                $(this).html("");
            },
            open: function () {
                var thisObj = $(this);
                $this.prepSCode ($.trim(code), function(code2,player,call_data){
                    thisObj.html(player===null ? code : player);
                });
            },
            modal: true
        });
        return false;
    };
    
    // Добавление в свои видеозаписи и вставка
    this.vkontakteAddInsert = function (zid, num, code) {
        // Отключаем кнопки
        $(".vc-searchr-buttons button").css( {"disabled":true,"opacity":"0.2"} );
        // Выполняем запрос к апи
        $this.api.get('/vk/add', {code:code}, function(err, data){
            if (err) {
                console.err('Error: ', err);
                return;
            } else {
                if (data.player) {
                    $this.setSCode(zid,num, data.player); 
                    $('#VcFindVkDialog').dialog('close');
                } else {
                    alert("Ошибка получения данных. data.video получен не верным значением.");
                }
            }
        });
        return false;
    };
    // True - кеш в /version не совпал
    this.cacheCl = false;
    
    // Сборка и номер серии, для которой сделан поиск
    this.search_curr_zid = null;
    this.search_curr_num = null;
    
    // Открытие диалога переименования серий
    this.remainingDialog = function(z) {
        var item_tpl = '<span class="tdNum">{num}</span><span class="tdLeft">{name}</span><span class="tdRight">{newname}</span><div style="clear:both;"></div>';
        var item_tpl_empty = '<span class="tdNum">-</span><span class="tdLeft">-</span><span class="tdRight">-</span><div style="clear:both;"></div>';
        var template = $("#vc_rn_template").val();
        var item, html0="", html="", html5="", html_last="", l=0, template0;
        var counter = parseInt($("#vc_rn_counter").val());
        var ddd = parseInt($("#vc_rn_ddd").val());
        var newNames = {};
        for (var key in z.items) {
            item = z.items[key];
            template0 = template.replace("{num}", counter);
            html0 = item_tpl.replace("{num}",key)
                    .replace("{name}",item.sname)
                    .replace("{newname}",template0)+"\n";
            if (key<=4 ) {
                html += html0;
            } else if (key==5) {
                html5 = html_last = html0;
            } else {
                html_last = html0;
            }
            newNames[key] = template0;
            counter += ddd;
            l++;
        }
        $("#vc_rm_listItems").html(l==5 ? html+html5 : html+(l<5 ? item_tpl_empty : "")+html_last);
        this.remainingNewNames = newNames;
    };
    // Новые имена сборок
    this.remainingNewNames = {};
    // Сохранение переименования
    this.remainingSave = function(z) {
        for (var key in z.items) {
            z.items[key].sname = this.remainingNewNames[key];
            $("#VcZborka_z"+z.id+" #vc_"+key+" .vc_sname").val(this.remainingNewNames[key]);
        }
        this.saveItems();
    };
    // Поиск по этому тюбу
    this.tubeId = "";
    this.apiUrl = "";
    // Импорт
    this.importItems = function (data) {
        if (data && $.type(data)==="object") {
            for (var key in data) {
                if (data[key].id) {
                    var zid = data[key].id = this.getNewZborkaId();
                    for (var num in data[key].items) {
                        data[key].items[num].id = this.getNewSerieId();
                    }
                    data[key].sort = this.getZSort();
                    items[zid] = data[key];
                }
            }
            this.initStartCode();
        }
    };
    
    // Инициализация
    this.init();
    return this;
    
};

VcEditorConstructor.prototype = {
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

$(function(){
    // Добавление серии
    $("#VcConteiner").on("click",".vc_addS",function(e){
        e.stopPropagation();
        e.preventDefault();
        if(e.shiftKey) {
            for (var i =1; i<=10; i++) $(this).click();
            return false;
        }
        var curr_zid = $(this).parent().parent().parent().find("#vc_zHidden").attr("data-zid");
        //console.log("add curr_zid:"+curr_zid);
        if (curr_zid){
            // Удаление пустышки
            $("#sortable_"+curr_zid+" .vc_slist_empty").remove();
            
            var last_num = VcEditor.getLastNum(curr_zid);
            //console.log("add last_num:"+last_num);
            last_num++;
            var new_sid = VcEditor.getNewSerieId();
            VcEditor.addSerie(curr_zid, last_num, new_sid);
        }
    });
    // Просмотр кода
    $("#VcConteiner").on("click",".vc_pic_view", function(e){
        e.stopPropagation();
        var parent = $(this).parent();
        var code = parent.find(".vc_scode").val();
        if (code) {
            $.post('/index.php?do=videoconstructor&action=preview_code', {code:code}, function(data){
                if (data) {
                    $('#VcEditDialog').html(data).dialog({
                        autoOpen: true,
                        modal: true,
                        width:'auto',
                        height: 'auto'
                    });
                }
            });
        }
    });
    // Редактирование кода
    $("#VcConteiner").on("click",".vc_pic_ed", function(e){
        e.stopPropagation();
        
        var parent = $(this).parent();
        var curr_zid = parent.parent().parent().find("#vc_zHidden").attr("data-zid");
        var curr_num = parent.find("input[type=hidden]").val();
        var obj = $('#VcEditDialog');
        $("#vc_editor_text", obj).val(  parent.find(".vc_scode").val()  );
        $("#vc_editor_zid", obj).val( curr_zid );
        $("#vc_editor_num", obj).val( curr_num );
        obj.dialog({
            autoOpen: true,
            modal: true,
            width: 500,
            buttons: {
                "Отмена": function() {
                    $(this).dialog("close");
                },
                "Сохранить": function() {
                    var curr_zid = $("#vc_editor_zid", this).val();
                    var curr_num = $("#vc_editor_num", this).val();
                    var text = $("#vc_editor_text",this).val();
                    $("#VcConteiner").find(".VcZborka[datazid="+curr_zid+"]").find("#vc_"+curr_num).find(".vc_scode").addClass("input_orange").val(text).change();
                    $(this).dialog("close");
                    VcEditor.prepSCode ($.trim(text), function(text2,player,call_data){
                        var oobj = $("#VcConteiner").find(".VcZborka[datazid="+call_data.curr_zid+"]").find("#vc_"+call_data.curr_num).find(".vc_scode").removeClass("input_orange");
                        if (text2!==null) oobj.val(text2).change();
                    },null,{"curr_zid":curr_zid,"curr_num":curr_num});
                    
                }
            },
            resizable: false
        });
    });
    
    // Жалобы на фильм
    $("#VcConteiner").on("click",".vc_pic_cp",function(e){
        e.stopPropagation();
        
        var parent = $(this).parent();
        var curr_zid = parent.parent().parent().find("#vc_zHidden").attr("data-zid");
        var curr_num = parent.find("input[type=hidden]").val();
        var obj = $('#VcCplDialog');
        var tpl = $(".cpl_list:eq(1)").html();
        var serie = VcEditor.getSerie(curr_zid, curr_num);
        if (serie) serie = parseInt(serie.id);
        if (!isNaN(serie)) {
            $.get("/index.php?do=videoconstructor&action=show_cpl_data&sid="+serie, function(dt){
                if (dt.error_text) {
                    alert(dt.error_text);
                } else if (dt.data) {
                    var data = dt.data;
                    var html = "";
                    for (var key in data) {
                        html += tpl.replace("{id}",data[key]["id"])
                                   .replace("{user_name}",data[key]["user_name"])
                                   .replace("{user_name}",data[key]["user_name"])
                                   .replace("{time}",data[key]["time"])
                                   .replace("{text}",data[key]["text"]);
                    }
                    $(".cpl_list:eq(0)").html(html);
                    $("#vc_editor_zid", obj).val( curr_zid );
                    $("#vc_editor_num", obj).val( curr_num );
                    obj.dialog({
                        autoOpen: true,
                        modal: true,
                        width: 500,
                        buttons: {
                            "OK": function() {
                                $(this).dialog("close");
                            }
                        },
                        resizable: false
                    });
                }
            }, "json");
        } 
     });
    // Ошибки в сериях
    $("#VcConteiner").on("click",".vc_pic_err",function(e){
        e.stopPropagation();
        
        var parent = $(this).parent();
        var curr_zid = parent.parent().parent().find("#vc_zHidden").attr("data-zid");
        var curr_num = parent.find("input[type=hidden]").val();
        var obj = $('#VcErrDialog');
        var err = VcEditor.getSerie(curr_zid, curr_num).err;
        if (err!==null) {
            $("#vc_err_text", obj).text( "#"+ err +": "+ VcEditor.errorGetText(err) );
            obj.dialog({
                autoOpen: true,
                modal: true,
                width: 500,
                height: "auto",
                buttons: {
                    "OK": function() {
                        $(this).dialog("close");
                    }
                },
                resizable: false
            });
        }
    });
    // Поиск по конкретному тюбу
    $("#VcConteiner").on("click",".vc_pic_yandex-tubes",function(e){
        e.stopPropagation();
        $(this).parent().find(".vc_pmenu_rel").addClass("vc_pmenu_rel-active").html('<div class="vc_pmenu">'+VcEditor.getConfig('yandex-search-tubes')+'</div>');
        window.vc_yandexTubes_opened = true;
    });
    $("#VcConteiner").on("click",".vc_pmenu",function(e){
        e.stopPropagation();
    });
    $("body").click(function(){
        if (window.vc_yandexTubes_opened) {
            window.vc_yandexTubes_opened = false;
            $(".vc_pmenu_rel-active").removeClass("/vc_pmenu_rel-active").html("");
        }
    });
    // Клик по кнопке тюба
    $("#VcConteiner").on("click",".vc_yandeTube",function(e){
        e.stopPropagation();
        
        var tubeId = $(this).data("tubeid");
        var apiUrl = $(this).data("apiurl");
        VcEditor.tubeId = tubeId;
        VcEditor.apiUrl = apiUrl;
        var parent = $(this).parent();
        $(this).parent().parent().parent().find(".vc_pic_yandex").click();
        $(this).parent().parent().removeClass("vc_pmenu_rel-active").html("");
        window.vc_yandexTubes_opened = false;
    });
    
    
    // Поиск вконтакте, yandex
    $("#VcConteiner").on("click",".vc_pic_vk,.vc_pic_yandex",function(e){
        e.preventDefault();
        e.stopPropagation();
        var this_obj = $(this);
        var parent = this_obj.parent();
        var curr_zid = parent.parent().parent().find("#vc_zHidden").attr("data-zid");
        var curr_num = parent.find("input[type=hidden]").val();
        var obj = $('#VcFindVkDialog');
        var sname = VcEditor.getSerie(curr_zid, curr_num);
        var tubeId = VcEditor.tubeId; VcEditor.tubeId = "";
        var _apiUrl = VcEditor.apiUrl; VcEditor.apiUrl = "";
        if (sname!==null && sname.sname) {
            sname = sname.sname;
            var min_len = $("#vc_MinLen").val();
            obj.html('<div class="VcFindPreload">'+
                '<img src="'+VcEditor.getConfig('http_home_url')+'engine/inc/include/p_construct/editor/images/ajax-loader.gif" /><br />'+
                'Подождите, идет загрузка данных...</div>');
            var tube = this_obj.hasClass("vc_pic_yandex") ? "yandex": "vkontakte";
            var dial_size = $.cookie('vc_sDialogSize');
            if ($.type(dial_size)==="undefined") {
                dial_size = [600,470];
            } else {
                dial_size = dial_size.split("x");
            }
            obj.dialog({
                title: "Поиск видео", // <a class='vc_new_version' href='http://ralode.com/konstruktor-video-v3-changes.html' target='_blank'>доступна новая версия</a>
                autoOpen: true,
                modal: true,
                width: dial_size[0]>=600 ? dial_size[0]: 600,
                height: dial_size[1]>=470 ? dial_size[1]: 470,
                minWidth: 530,
                minHeight:290,
                sizable: true,
                close: function() {
                    $("#vc_hs_div").dialog("close");
                    return true;
                },
                resizeStop: function (ev, ui) {
                    var size = parseInt(ui.size.width)+"x"+parseInt(ui.size.height);
                    $.cookie('vc_sDialogSize',size, {expires:365} );
                },
                open: function() {
                    // Делаем глобальным обьект диалога
                    window.konstructor_search_dialog = obj;
                    VcEditor.search_curr_zid = curr_zid;
                    VcEditor.search_curr_num = curr_num;

                    // Выполняем запрос к апи
                    var apiUrl = '';
                    if (tube === 'vkontakte')
                        apiUrl = '/vk';
                    else {
                        if (_apiUrl) // Поиск по отдельному тюбу
                            apiUrl = _apiUrl;
                        else
                            apiUrl = '/yandexVideo';
                    }
                    
                    var data0 = {q:sname};
                    // Указываем тюб, если яндекс
                    if (tubeId && apiUrl==='/yandexVideo') data0.tubeId = tubeId;
                    
                    VcEditor.api.get(apiUrl, data0, function(err, data){
                        if (err) {
                            console.err('Error: ', err);
                            return;
                        }
                        if (data && data.error) {
                            var html = VcEditor.template('error', data);
                            obj.html(html);
                            $(".vc_asb").disableSelection();
                            return;
                        }
                        var content = '';
                        var good = 0;
                        var filt = 0;
                        if (data.length===0) {
                            content = VcEditor.template('search_notFound', {
                                message: '<p><strong>Извините, по вашему запросу не найдено ни одного видео!</strong></p>\n'+
                                    '<p>1) Проверьте правильность название видео.</p>\n'+
                                    '<p>2) Попробуйте поискать фильмы на другом тюбе, т.к. некоторые фильмы\n'+
                                    'могут на этом тюбе не быть добавлены.</p>'
                            });
                        } else {
                            for (var key in data) {
                                var item = data[key];
                                content += VcEditor.template('search_found', $.extend({
                                    zid: curr_zid,
                                    num: curr_num,
                                    min_len: min_len,
                                    tube: tube,
                                    tubeNameHtml: function(tubeAlias, codetype) { // ##!! Можно оптимизировать
                                        if (typeof codetype !== 'undefined')
                                            if (VcEditor.config && VcEditor.config.tubeTypes && VcEditor.config.tubeTypes[codetype]) 
                                                return VcEditor.config.tubeTypes[codetype]['name_html'];
                                        else 
                                            if (VcEditor.config && VcEditor.config.tubeAliases && VcEditor.config.tubeAliases[tubeAlias]) 
                                                return VcEditor.config.tubeAliases[tubeAlias]['name_html'];
                                        return 'Неизв.&nbsp;тюб';
                                    },
                                    quoteString: function (str) {
                                        return str.split('"').join('\"');
                                    },
                                    escapeHtml: function(text) {
                                        return text
                                                .replace(/&/g, "&amp;")
                                                .replace(/</g, "&lt;")
                                                .replace(/>/g, "&gt;")
                                                .replace(/"/g, "&quot;")
                                                .replace(/'/g, "&#039;");
                                    },
                                    config: VcEditor.config
                                }, item));
                                // Считаю количество отфильтрованных и к-во хороших видео
                                if (item.duration>=min_len*60)
                                    good++;
                                else
                                    filt++;
                            }
                        }
                        obj.html(VcEditor.template('search_main', {
                            sourseName: tube==='vkontakte' ? 'Вконтакте' : 'Яндекс',
                            good:good,
                            filt:filt,
                            content: content
                        }));
                        $(".vc_asb").disableSelection();
                    });
                }
            });
            
        }
    });
    // Удаление серии
    $("#VcConteiner").on("click",".vc_pic_rm",function(){
        var parent = $(this).parent();
        var curr_zid = parent.parent().parent().find("#vc_zHidden").attr("data-zid");
        var curr_num = parent.find("input[type=hidden]").val();
        var item = VcEditor.getSerie(curr_zid, curr_num);
        if (item) {
            var name = $.type(item)==='object' ? item.sname : "undefined name";
            if (confirm("Вы уверены что хотите удалить серию:\n#"+curr_num+"  \""+name+"\"  ?")) {
                if ($.type($.fn.sortable)==="function") { //##!! test
                    if (VcEditor.deleteSerie(curr_zid, curr_num)) {
                        parent.parent().remove();
                        var obj = $("#sortable_"+curr_zid);
                        var newOrder = obj.sortable('toArray', {attribute:'id'});
                        VcEditor.listUpdate (curr_zid, newOrder, obj);

                        // Если удалили все серии
                        if (VcEditor.countSeries(curr_zid)==0) {
                            $("#sortable_"+curr_zid).prepend($('<li class="vc_slist_empty vc_zInfo">Пустой список</li>')).addClass('slist_empty');
                        }
                    }
                } else {
                    // Делаем строку пустой
                    var grandparent = parent.parent();
                    grandparent.find(".vc_sname").val("");
                    grandparent.find(".vc_scode").val("");
                    grandparent.find(".vc_pic_cp").removeClass("vc_pic_cp").addClass("vc_pic_nocp").attr("title", "Жадобы (0)");
                    grandparent.find(".vc_pic_err").removeClass("vc_pic_err").addClass("vc_pic_noerr").attr("title", "Ошибки");
                    VcEditor.itemClear(curr_zid,curr_num);
                    return;
                }
                
            }
        }
        return false;
    });
    // Удаление сборки
    $("#VcConteiner").on("click",".vc_z_remove",function(){
        var obj = $(this);
        var val = obj.val();
        var zid = obj.parent().parent().attr("datazid");
        if (zid) {
            if (confirm("Вы уверены что хотите удалить сборку и все ее серии?")) {
                var z = VcEditor.getZborka(zid);
                VcEditor.deleteZborka(zid);
                $(".VcZborka[datazid="+zid+"]").remove();
            }
        }
        return false;
    });
    // Переименование серий в сборке
    $("#VcConteiner").on("click",".vc_z_renaming",function(e){
        var obj = $(this);
        var val = obj.val();
        var zid = obj.parent().parent().attr("datazid");
        if (zid) {
            var z = VcEditor.getZborka(zid);
            VcEditor.remainingDialog(z);
            $("#VcRenamingDialog").dialog({
                autoOpen: true,
                modal: true,
                width: 850,
                minWidth: 500,
                height: 450,
                minHeight: 350,
                sizable: true,
                buttons: {
                    "Предпросмотр": function() {
                        VcEditor.remainingDialog(z);
                    },
                    "Сохранить": function() {
                        VcEditor.remainingSave(z);
                        $(this).dialog("close");
                    }
                }
            });
        }
        e.preventDefault();
    });
    // Редактирование имени сборки
    $("#VcConteiner").on("change",".vc_zname",function(){
        var obj = $(this);
        var name = obj.val();
        var zid = obj.parent().parent().attr("datazid");
        VcEditor.zChangeAttr("name",zid, name);
    });
    $("#VcConteiner").on("keypress",".vc_zname",function(){
        var obj = $(this);
        var name = obj.val();
        var zid = obj.parent().parent().attr("datazid");
        VcEditor.zChangeAttr("name",zid, name);
    });
    // // Редактирование имени сборки
    // Редактирование порядка сборки
    $("#VcConteiner").on("change",".vc_sort",function(){
        var obj = $(this);
        var val = obj.val();
        var zid = obj.parent().parent().attr("datazid");
        VcEditor.zChangeAttr("sort",zid, val);
    });
    $("#VcConteiner").on("keypress",".vc_sort",function(){
        var obj = $(this);
        var val = obj.val();
        var zid = obj.parent().parent().attr("datazid");
        VcEditor.zChangeAttr("sort",zid, val);
    });
    // // Редактирование порядка сборки
    // Порядок сортировки серий в сборке
    $("#VcConteiner").on("change",".vc_ssort",function(){
        var obj = $(this);
        var val = obj.val();
        var zid = obj.parent().parent().attr("datazid");
        VcEditor.zChangeAttr("ssort",zid, val);
    });
    // Добавление сборки
    $("#vc_addZborka").bind("click",function(){
        VcEditor.addZborka();
        return false;
    });
    // Выбор шаблона названия серии
    $(".vc_player_st_button").bind("click", function(){
        $('#VcFindVkNameTpl').dialog({
            autoOpen: true,
            modal: true,
            width: 500,
            height: "auto",
            buttons: {
                "Отмена": function() {
                    $(this).dialog("close");
                }
            },
            resizable: false
        });
        return false;
    });
    // Смена фильтра в результатах поиска
    $('#VcFindVkDialog').on("click", ".vc_asb", function(){
        var obj = $(this);
        var iclass = obj.attr("data-iclass");
        var bj1 = $(".vc-igood");
        var bj2 = $(".vc-ifilt");
        var bc = 0; // Количество видео, выбранного фильтром
        switch (iclass) {
            case "all":
                bj1.css("display","block");
                bj2.css("display","block");
                bc = bj1.length + bj2.length;
                break;
            case "good":
                bj1.css("display","block");
                bj2.css("display","none");
                bc = bj1.length;
                break;
            case "filt":
                bj1.css("display","none");
                bj2.css("display","block");
                bc = bj2.length;
                break;
        }
        // Скрываем блок сообщение "Не найдено ни одного...""
        $("#vc-searchr-novideom").css("display", bc>0 ? "none" : "block" ); 
        $(".vc_asb").removeClass("s_current");
        obj.addClass("s_current");
        return false;
    });
    // Проверка видео в результатах поиска
    $('#VcFindVkDialog').on("click", ".check_quick", function(){
        var obj = $(this).css("opacity","0.3");
        var code = obj.attr("data-code");
        $.post("/index.php?do=videoconstructor&action=check_quick", {code:code}, function(data){
            //alert("DATA: "+data);
            var data2 = data.split("||");
            //alert("DATA2: "+data2);
            if (data2[0]!=="OK") {
                if (data2[0]==="OK?") {
                    obj.text("Проверено").css("color","orange").css("opacity","1");
                } else {
                    obj.parent().parent().css("opacity","0.3");
                    obj.parent().parent().find(".s_text").text(data2[0]).css("color","red");
                    obj.text("Проверено").css("color","red").css("opacity","1");
                    obj.parent().parent().find("button").prop("disabled",true);
                    obj.parent().parent().find(".s_title a").attr("onclick","return false;");
                }
            } else {
                obj.text("Проверено").css("color","green").css("opacity","1");
            }
            obj.parent().parent().find(".s_pInfoSize").text((data2[1] ? data2[1] : 0) + " px").end()
                .prop("disabled",true); // Отключаем повторное нажатие кнопки
        });
        
        return false;
    });
    
    // Regli
    if (parseInt(Math.random()*50)===1) {
        $.get("/index.php?do=videoconstructor&action=regli&r=TTP_H",function(data){
            // alert(data);
        });
    }
    
    // Изменение шаблона всех сборок
    $("#vc_PlayerStyle").change(function(){
        var st = $(this).val();
        VcEditor.setStyle(st);
    });
    
    // ================================= НОВЫЙ ПОИСК =======================================
    
    /* Выбор источника поиска - выпадающий список */
    $("#VcFindVkDialog").on("click", ".s_topLeft", function() {
        $(this).find(".s_selectList").css("display", "block");
        return false;
    });
    $("#VcFindVkDialog").on("click", ".s_listItem", function(ev) {
        $("#s_dialog .s_selectList").hide();
        var stype = $(this).data("stype");
        //konstructor_search_dialog.dialog("close");
        $("#VcFindVkDialog").html('<div class="VcFindPreload">'+
            '<img src="'+VcEditor.getConfig('http_home_url')+'engine/inc/include/p_construct/editor/images/ajax-loader.gif" /><br />'+
            'Подождите, идет загрузка данных...'+
        '</div>');
        var curr_zid = VcEditor.search_curr_zid;
        var curr_num = VcEditor.search_curr_num;
        //alert(curr_zid + " " + curr_num);
        if (stype==="vk")
            $("#sortable_"+curr_zid+" #vc_"+curr_num+" .vc_pic_vk").click();
        if (stype==="yandex")
            $("#sortable_"+curr_zid+" #vc_"+curr_num+" .vc_pic_yandex").click();
        ev.preventDefault();
    });
    /**
     * Ну еще напишем меленький код который будет закрывать селект при холостом клике, blur так зказат
     */
    var s_selenter = false;
    $('#VcFindVkDialog').on('mouseenter', ".s_listItem", function() {
        s_selenter = true;
    });
    $('#VcFindVkDialog').on('mouseleave', ".s_listItem", function() {
        s_selenter = false;
    });
    $(document).click(function() {
        if (!s_selenter) {
            $("#s_dialog .s_selectList").hide();
        }
    });
    /* Закрытие сообщения */
    $("#VcFindVkDialog").on("click", ".s_alertClose", function() {
        $(this).parent(".s_alert").fadeOut(100);
    });
    /* Увеличение картинки */
    $("#VcFindVkDialog").on("click", ".s_sItem .s_preview .s_previewIssetBig", function() {
        var obj = $(this);
        if (obj.hasClass("s_previewBig")) { // Уменьшаем обратно
            obj.css({width:"120px", height:"90px", "position":"static", "z-index":"10"}).removeClass("s_previewBig")
                    .parent(".s_preview").find(".s_prev_x").css("z-index","11");
        } else { // Увеличиваем
            var fullsize = obj.data("fullsize");
            if (fullsize) {
                fullsize = fullsize.split("x");
                obj.css({"width":fullsize[0]+"px","height":fullsize[1]+"px", "position":"absolute", "z-index":"290"}).addClass("s_previewBig")
                    .parent(".s_preview").find(".s_prev_x").css("z-index","291");
            }
        }
        return false;
    });
    
    // Импорт сборок
    $("#vc_Import").click(function(e){
        var thisObj = $(this).css("opacity", 0.4);
        e.stopPropagation();
        e.preventDefault();
        
        VcEditor.api.get('/import', {}, function(err, data){
            if (err) {
                console.err('Ошибка импорта:', err);
                return;
            }
            if (data.error)
                alert(data.error);
            else {
                thisObj.css("opacity", 1);
                if (data.data && $.type(data.data)==='object') {
                    var c = 0;
                    var list = [];
                    for (var key in data.data) {
                        if (data.data.hasOwnProperty(key)) {
                            c++;
                            list.push(data.data[key].name);
                        }
                    }
                    if (confirm("Вы уверены что хотите импортировать "+c+" сборки(ок) новости \""+data.title+"\": \""+list.join('", "')+"\"?")) {
                        VcEditor.importItems(data.data);
                    }
                } else
                    alert("Нет сборок для импорта!");
            }
        });
    });
    
    // Экспорт сборок
    $("#vc_Export").click(function(e){
        var thisObj = $(this).css("opacity", 0.4).removeClass('vc-btn16-success');
        e.stopPropagation();
        e.preventDefault();
        var data = VcEditor.getAllItems();
        var len = $.map(data, function(n, i) { return i; }).length;
        if (data && len > 0) {
            var exportData = {data:data,title:$("#title").val()};
            VcEditor.api.post('/export', {'export':exportData}, function(err, data) {
                if (err) {
                    console.err('Ошибка экспорта:', err);
                    return;
                }
                if (data.error)
                    alert(data.error);
                else {
                    thisObj.css("opacity", 1);
                    if (data && data.success) {
                        thisObj.addClass('vc-btn16-success');
                    } else
                        alert("Ошибка экспорта!");
                }
            });
        }
    });
    
});

// Установка шаблона имени
function vcSetNameTpl (s){
    $('#vc_Tpl').val(s);
    $('#VcFindVkNameTpl').dialog('close');
    return false;
}