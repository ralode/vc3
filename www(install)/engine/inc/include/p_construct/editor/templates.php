<?php
header('Content-Type: text/html; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: X-Requested-With, content-type');
header('Access-Control-Max-Age: 86400');
?>
TEMPLATE BEGIN "search_main";
<div id="s_dialog">
    <div id="s_topPanel">
        <div class="s_topLeft"><%=sourseName%>
            <div class="s_selectList">
                <div class="s_listItem" data-stype="vk">Вконтакте</div>
                <div class="s_listItem" data-stype="yandex">Яндекс</div>
            </div>
        </div> 
        <div class="s_topRight">
            Видео: <a href="#" data-iclass="good" class="vc_asb s_current"><%=good%></a> 
            из <a href="#" data-iclass="all" class="vc_asb"><%= good + filt%></a>. 
            Под фильтром: <a href="#" data-iclass="filt" class="vc_asb"><%=filt%></a>
        </div>
        <div class="s_clear"></div>
    </div>
    <%=content%>
</div>
TEMPLATE END;

TEMPLATE BEGIN "search_notFound";
<div id="vc-searchr-novideom">
    <p>К сожалению, ни одного видео не найдено!</p>
    <p>1) Проверьте правильность название видео.</p>
    <p>2) Попробуйте поискать фильмы на другом тюбе, т.к. некоторые фильмы 
        могут на этом тюбе не быть добавлены.</p>
    <br /><a href="http://ralode.com/poisk-video-vkontakte.html" target="_blank">&laquo;Советы 
        по поиску и его улучшению&raquo;</a>
</div>
TEMPLATE END;

TEMPLATE BEGIN "search_found";
<div class="s_sItem <% if (duration>=min_len*60) { %>vc-igood<% } else { %>vc-ifilt<% } %>">
    <div class="s_preview">
        <img src="<%=preview%>" class="search_item_preview" />
        <!-- ImgVk mod -->
    </div>
    <div class="s_pInfo">
        <div class="s_pInfoTube" title="Видео тюб"><%= tubeNameHtml(tubeAlias, codetype) %></div>
        <div class="s_pInfoDuration" title="Длительность видео"><%=durationHuman%></div>
        <div class="s_pInfoSize" title="Размер видео">---</div>
    </div>
    <div class="s_mDiv">
        <div class="s_title">
            <a href="#" title="Вставить видео" onclick="VcEditor.setSCode('<%=zid%>','<%=num%>','<%= escapeHtml(code) %>'); $('#VcFindVkDialog').dialog('close'); return false;">
                <%=title%>
            </a>
        </div>
        <div class="s_text">
            <%=description%>
        </div>
    </div>
    <div class="s_iButtons">
        <button class="s_iB_view" title="Предварительный просмотр видео" onclick="return VcEditor.previewCode(this);" data-player="<%= escapeHtml(player ? player : code) %>">Смотреть</button>
        <button class="s_iB_chk check_quick" title="Проверка видео на существование и макс. качество" data-code="<%= escapeHtml(code) %>">Проверить</button>
        <% if (codetype==1 && config.vk_addInMy==1) { %>
            <button class="s_iB_addp" onclick="return VcEditor.vkontakteAddInsert('<%=zid%>','<%=num%>','<%= escapeHtml(code) %>');" title="Добавление видео в &lq;Мои видеозаписи&rq' и затем вставки">Добавить&nbsp;[+]</button>
        <% } %>
        <button class="s_iB_ins" onclick="VcEditor.setSCode('<%=zid%>','<%=num%>','<%= escapeHtml(code) %>'); $('#VcFindVkDialog').dialog('close'); return false;" title="Вставка видео на сайт">Вставить</button>
    </div>
</div>
TEMPLATE END;

<!-- Шаблон строки сборки -->
TEMPLATE BEGIN "row_z";
<div id="VcZborka_<%=zid%>" class="VcZborka" datazid="<%=zid%>">
    <div class="vc_zHead">
        <span class="rcbold">Сборка:</span><input class="vc_zname" type="text" value="<%=zname%>">
        <span class="rcbold">Порядок:</span><input class="vc_sort" type="text" value="<%=sort%>">
        <span class="rcbold">Серии&nbsp;в&nbsp;плейлисте:</span>
        <select class="vc_ssort">
            <option value="2">По умолчанию</option>
            <option value="0"<%=ssort_0%>>По возрастанию</option>
            <option value="1"<%=ssort_1%>>По убыванию</option>
        </select>
        <input type="image" src="<%=editorFolder%>/images/remove.png" class="vc_z_remove" title="Удалить сборку" />&nbsp;
        <input type="image" src="<%=editorFolder%>/images/renaming.png" class="vc_z_renaming" title="Переименование серий сборки" />
		<% if (extData && extData.row_zButtons3) { %><%= extData.row_zButtons3 %><% } %>
    </div>
    <ul id="sortable_<%=zid%>" class="vc_connected">
        <%=items%>
        <li id="vc_zHidden" data-zid="<%=zid%>"></li>
    </ul>
    <div class="vc_AddPanel">
        <span><a href="#" id="vc_addS" class="vc_addS" title="SHIFT+Click - добавить 10 серий">Добавить серию</a></span>
    </div>
</div>
TEMPLATE END;

TEMPLATE BEGIN "row_s";
<li class="vc_item" id="vc_<%=num%>" datanum="<%=num%>" datazid="<%=zid%>">
<!--<span id="VcZborka_s<%=sid%>"></span>-->
<span class="vc_num"><%=num%></span>
<div class="vc_noSort">
	<% if (extData && extData.row_sButtons1) { %><%= extData.row_sButtons1 %><% } %>
    <input class="vc_sname" type="text" value="<%=sname%>" onchange="<%=sname_js%>" onkeypress="<%=sname_js%>"/>
    <input class="vc_scode" type="text" value="<%=scode%>" onchange="<%=scode_js%>" onkeypress="<%=scode_js%>"/>
    <span class="vc_apic vc_pic_vk vc_space" title="Поиск вконтакте"></span>
    <span class="vc_apic vc_pic_yandex" title="Поиск Yandex.Video"></span>
    <span class="vc_pmenu_rel"></span><span class="vc_apic vc_pic_yandex-tubes" title="Поиск по тюбу"></span>
	<% if (extData && extData.row_sButtons2) { %><%= extData.row_sButtons2 %><% } %>
	<span class="vc_apic vc_pic_view" title="Просмотр кода"></span>
    <span class="vc_apic vc_pic_ed" title="Редактировать код"></span>
    <span class="vc_apic vc_pic_<%=is_nocpl%>cp" title="Жалобы (<%=cpl_count%>)"></span>
    <span class="vc_apic vc_pic_<%=is_noerr%>err" title="Ошибки"></span>
    <span class="vc_apic vc_pic_rm" title="Удалить"></span>
	<% if (extData && extData.row_sButtons3) { %><%= extData.row_sButtons3 %><% } %>
    <input type="hidden" value="<%=num%>">&nbsp;
</div></li>
TEMPLATE END;

TEMPLATE BEGIN "row_empty";
<li class="vc_slist_empty vc_zInfo">Пустой список</li>
TEMPLATE END;

TEMPLATE BEGIN "error";
<pre class='VcErrorAlert'><%= error %></pre>
<% if (stack) { %><pre><%= stack%></pre><% } %>
TEMPLATE END;