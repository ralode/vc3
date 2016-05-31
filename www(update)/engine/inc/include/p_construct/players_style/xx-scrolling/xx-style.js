/**
 *  Конструктор видео для DLE
 *  http://ralode.com
 *  @rationalObfuscation compress
 */
var RalodePlayer = Object.create(CRalodePlayer);
RalodePlayer.init = function(data, cvStruct) {
    this.__proto__.init(data, cvStruct);
    // События
    var $this = this;
    // Инициализация начальных списков
    $this.zborkilistInit();
    $this.serielistInit();
    // Жалобы
    $this.initComplaints();
    // Кнопки скроллинга
    $("#rl-buttons-top .ButtonLft").click(function(){ $this.move("top","left"); });
    $("#rl-buttons-top .ButtonRgh").click(function(){ $this.move("top","right"); });
    $("#rl-buttons-bottom .ButtonLft").click(function(){ $this.move("bottom","left"); });
    $("#rl-buttons-bottom .ButtonRgh").click(function(){ $this.move("bottom","right"); });
    
    // Disable selection
    $('.RalodePlayer').disableSelection();
};
RalodePlayer.zborkilistInit = function () {
    // Генерация списка
    var data = this.data;
    var len = this.length(data);
    if (len>1) {
        var html = "";
        for (var zid in data) {
            var val = data[zid];
            // <div class="RlItem serie-active">
            html += '<div class="RlItem '+(zid==this.curr_zid ? ' serie-active' : '')+'" onclick="RalodePlayer.selectZborka('+zid+');" id="zborka_'+zid+'">'+val.name+'</div>';
        }
        $("#rl-lenta-top").html(html);
        $("#rl-buttons-top").show();
        // Кнопки скроллинга
        var width = this._getItemsWidth('top');
        var visor_width = $("#rl-buttons-top .RlVisor").width();
        if (width > visor_width) {
            $("#rl-buttons-top .buttonLR").show();
        }
    }
};

RalodePlayer.serielistInit = function () {
    // Генерация списка серий
    var z = this.getCurrentZ();
    if (this.cvStruct && this.cvStruct.scount>1) {
        var html = "";
        for (var num in z.items) {
            var val = z.items[num];
            html += '<div class="RlItem '+(num==this.curr_num ? ' serie-active' : '')+'" onclick="RalodePlayer.selectSerie('+val.parent+',\''+num+'\');" id="serie_'+num+'">'+val.sname+'</div>';
        }
        $("#rl-lenta-bottom").html(html);
        $("#rl-buttons-bottom").show();
        // Кнопки скроллинга
        var width = this._getItemsWidth('bottom');
        var visor_width = $("#rl-buttons-bottom .RlVisor").width();
        if (width > visor_width) {
            $("#rl-buttons-bottom .buttonLR").show();
        } else
            $("#rl-buttons-bottom .buttonLR").hide();
    }
};

// Выбор сезона в списке
RalodePlayer.selectZborka = function (zid) {
    var cz = this.getZ(zid);
    if (cz) {
        var obj2 = this.firstElement(cz.items);
        if (obj2 && obj2.id!==this.curr_sid) {
            var obj = this.selectBySid(obj2.id);
            this.player(obj.scode);
            $("#rl-lenta-bottom").css("margin-left", 0);
            this.serielistInit();
        }
    }
}

RalodePlayer.selectSerie = function (zid, num) {
    var curr_sid = RalodePlayer.curr_sid;
    var obj = RalodePlayer.select(zid, num);
    if (obj && obj.id!==curr_sid) {
        RalodePlayer.player(obj.scode);
    }
}

RalodePlayer.addEvent('changeSerie', function(zid, sid, num){
    $('#rl-lenta-top .RlItem').removeClass('serie-active');
    $('#zborka_'+zid).addClass('serie-active');
    $('#rl-lenta-bottom .RlItem').removeClass('serie-active');
    $('#serie_'+num.replace('.','\\.')).addClass('serie-active');
});

(function($this){
    /**
     * Получение ширины ленты. pos = {"top","bottom"}
     */
    $this._getItemsWidth = function (pos) {
        if (!(pos=="top" || pos=="bottom")) return;
        var width = 0;
        var objs = $("#rl-lenta-"+pos+" .RlItem");
        var l = objs.length;
        if (l>0) {
            objs.each(function(){
                var pl = parseInt($(this).css('padding-left'));
                pl = isNaN(pl) ? 0 : pl;
                var pr = parseInt($(this).css('padding-right'));
                pr = isNaN(pr) ? 0 : pr;
                width+= $(this).outerWidth()+pl+pr;
            });
        }
        return width; // 808
    }

    function getMarginMin (pos) {
        if (!(pos=="top" || pos=="bottom")) return;
        var visor_width = $("#rl-buttons-"+pos+" .RlVisor").width();
        var min = visor_width - $this._getItemsWidth(pos);
        if (min>0) min = 0;
        return min;
    }

    function getMargin(pos) {
        var s = $("#rl-lenta-"+pos).css("margin-left");
        return parseInt(s);
    }

    function setMargin (pos, val) {
        var min = getMarginMin(pos);
        if (val<=0 && val>=min) 
            $("#rl-lenta-"+pos).css("margin-left",val+"px");
    }

    $this.move = function (pos, side) {
        var visor_width = $("#rl-buttons-"+pos+" .RlVisor").width();
        var margin_step = parseInt(visor_width * 0.7);
        var margin = getMargin(pos);
        
        if (side=="left") {
            var newMargin = margin+margin_step;
            if (newMargin>0) newMargin = 0;
        } else {
            var min = getMarginMin(pos);
            var newMargin = margin-margin_step;
            if (newMargin<min) newMargin = min;
        }
        setMargin(pos, newMargin);
    }
    
})(RalodePlayer);
