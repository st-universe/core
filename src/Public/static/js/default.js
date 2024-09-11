mf = 0;
var buildmode = 0;

function onmousewheel(element, callback) {

        // @author    Andrea Giammarchi        [http://www.devpro.it/]
        // @license    MIT                 [http://www.opensource.org/licenses/mit-license.php]
        // @credits    Adomas Paltanavicius         [http://adomas.org/javascript-mouse-wheel/]

        function __onwheel(event) {
                var delta = 0;
                if (event.wheelDelta) {
                        delta = event.wheelDelta / 120;
                        if (window.opera)
                                delta = -delta;
                }
                else if (event.detail)
                        delta = -event.detail / 3;
                if (delta)
                        callback.call(element, delta);
                if (event.preventDefault)
                        event.preventDefault();
                event.returnValue = false;
                return false;
        };

        if (element.addEventListener && !window.opera)
                element.addEventListener("DOMMouseScroll", __onwheel, false);
        else
                element.onmousewheel = (function (base) {
                        return function (evt) {
                                if (!evt) evt = window.event;
                                if (base) base.call(element, evt);
                                return __onwheel(evt);
                        }
                })(element.onmousewheel);
};

var closeAjaxCallbacks = new Array();
var closeAjaxCallbacksMandatory = new Array();

function closePopup() {
        if (over) {
                cClick();
                over = null;
        }
}

function kpListener(e) {
        if (!e) e = window.event; // Drecks IE
        if (e.keyCode == 27) {
                closeAjaxWindow();
        }
}
window.onkeydown = kpListener;

var isClosingAjaxWindow = false;

function closeAjaxWindow() {
        if (isClosingAjaxWindow) {
                return;
        }
        isClosingAjaxWindow = true;

        for (index = 0; index < closeAjaxCallbacks.length; index++) {
                closeAjaxCallbacks[index]();
        }
        for (index = 0; index < closeAjaxCallbacksMandatory.length; index++) {
                closeAjaxCallbacksMandatory[index]();
        }

        clearAjaxCallbacks();
        closeAjaxCallbacksMandatory = new Array();

        closePopup();
        isClosingAjaxWindow = false;
}

function clearAjaxCallbacks() {
        closeAjaxCallbacks = new Array();
}

function ajaxrequest(url) {
        new Ajax.Request(url);
}

function openWindow(elt, exclusive, width) {
        if (width) {
                if (exclusive) {
                        return overlib('<div id=' + elt + '></div>', WIDTH, width, BGCOLOR, '#8897cf', FGCOLOR, '#000000', TEXTCOLOR, '#8897cf', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, STICKY, DRAGGABLE, ALTCUT, EXCLUSIVE);
                } else {
                        return overlib('<div id=' + elt + '></div>', WIDTH, width, BGCOLOR, '#8897cf', FGCOLOR, '#000000', TEXTCOLOR, '#8897cf', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, DRAGGABLE, ALTCUT, STICKY);
                }
        } else {
                if (exclusive) {
                        return overlib('<div id=' + elt + '></div>', BGCOLOR, '#8897cf', TEXTCOLOR, '#8897cf', FGCOLOR, '#000000', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, STICKY, DRAGGABLE, ALTCUT, EXCLUSIVE);
                } else {
                        return overlib('<div id=' + elt + '></div>', BGCOLOR, '#8897cf', TEXTCOLOR, '#8897cf', FGCOLOR, '#000000', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, DRAGGABLE, ALTCUT, STICKY);
                }
        }
}
function openWindowPosition(elt, exclusive, width, posx, posy) {
        if (width) {
                if (exclusive) {
                        return overlib('<div id=' + elt + '></div>', WIDTH, width, RELX, posx, RELY, posy, BGCOLOR, '#8897cf', FGCOLOR, '#000000', TEXTCOLOR, '#8897cf', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, STICKY, DRAGGABLE, ALTCUT, EXCLUSIVE);
                } else {
                        return overlib('<div id=' + elt + '></div>', WIDTH, width, RELX, posx, RELY, posy, BGCOLOR, '#8897cf', FGCOLOR, '#000000', TEXTCOLOR, '#8897cf', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, DRAGGABLE, ALTCUT, STICKY);
                }
        } else {
                if (exclusive) {
                        return overlib('<div id=' + elt + '></div>', BGCOLOR, '#8897cf', RELX, posx, RELY, posy, TEXTCOLOR, '#8897cf', FGCOLOR, '#000000', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, STICKY, DRAGGABLE, ALTCUT, EXCLUSIVE);
                } else {
                        return overlib('<div id=' + elt + '></div>', BGCOLOR, '#8897cf', RELX, posx, RELY, posy, TEXTCOLOR, '#8897cf', FGCOLOR, '#000000', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, DRAGGABLE, ALTCUT, STICKY);
                }
        }
}

function ignoreUser(obj, userid) {
        ajax_update(obj, '/pm.php?B_IGNORE_USER=1&SHOW_IGNORE=1&recid=' + userid);
}
function addUserContact(obj, userid) {
        var contact = 'selector_' + obj;
        if (!$(contact)) {
                mode = document.forms[0].elements[contact].value;
        } else {
                mode = $(contact).value;
        }
        ajax_update(obj, '/pm.php?B_ADD_CONTACT=1&recid=' + userid + '&clmode=' + mode + '&cldiv=' + obj);
}
var clmodeswitchdiv = '';
function showContactModeSwitcher(div, contactid) {
        clmodeswitchdiv = div;
        elt = 'clmswitcher';
        openWindow(elt, 0, 70);
        ajax_update('clmswitcher', '/pm.php?SHOW_CONTACT_MODESWITCH=1&cid=' + contactid);
}
function switchContactMode(contactid, mode) {
        ajax_update(clmodeswitchdiv, '/pm.php?B_CHANGE_CONTACTMODE=1&cid=' + contactid + '&clmode=' + mode + "&cldiv=" + clmodeswitchdiv);
        clmodeswitcdiv = '';
        cClick();
}
function addLoadEvent(func) {
        var oldonload = window.onload;
        if (typeof window.onload != 'function') {
                window.onload = func;
        }
        else {
                window.onload = function () {
                        oldonload();
                        func();
                }
        }
}
function startUp() {
        startServerTimer();
}
var servertime = 0;
function startServerTimer() {
        if (servertime == 0) {
                servertime = new Date($('servertime').innerHTML);
        }
        var hours = servertime.getHours();
        var minutes = servertime.getMinutes();
        var seconds = servertime.getSeconds();
        servertime.setSeconds(seconds + 1);
        if (hours <= 9) hours = "0" + hours;
        if (minutes <= 9) minutes = "0" + minutes;
        if (seconds <= 9) seconds = "0" + seconds;
        dispTime = hours + ":" + minutes + ":" + seconds;
        $('servertime').innerHTML = dispTime;
        $('servertime').show();
        setTimeout("startServerTimer()", 1000);

}
var selectedFieldType = 0;
var selectedSystemType = 0;
var fieldeventselector = 0;
var selectedRegion = 0;
var adminregionselector = 0;
var passableselector = 0;
var borderselector = 0;
var tmpfield = 0;
var fieldevent = 0;
var bordercolor = '';
function ajax_update(elt, url) {
        new Ajax.Updater(elt, url, { method: 'get', evalScripts: true });
}

function fieldEventSelector(type) {
        var cells = document.querySelectorAll('.starmap');
        fieldevent = type;
        if (type === 0) {
                console.log("Executing type 0");
                cells.forEach(function (cell) {
                        var divbody = cell.querySelector('.divbody');
                        console.log("divbody:", divbody);
                        if (divbody) {
                                divbody.style.backgroundColor = '';
                        }
                });
        }
        if (type === 1) {
                cells.forEach(function (cell) {
                        var regionValue = cell.getAttribute('data-region');

                        if (regionValue > 1) {
                                var divbody = cell.querySelector('.divbody');
                                divbody.style.backgroundColor = 'rgba(255, 0, 0, 0.5)';
                        }
                });
        }

        if (type === 2) {
                cells.forEach(function (cell) {
                        var regionValue = cell.getAttribute('data-admin-region');

                        if (regionValue > 1) {
                                var divbody = cell.querySelector('.divbody');
                                divbody.style.backgroundColor = 'rgba(0, 0, 255, 0.5)';
                        }
                });
        }

        if (type === 3) {
                cells.forEach(function (cell) {
                        var regionValue = cell.getAttribute('data-system-type-id');

                        if (regionValue > 1) {
                                var divbody = cell.querySelector('.divbody');
                                divbody.style.backgroundColor = 'rgba(255, 255, 0, 0.5)';
                        }
                });
        }

        if (type === 4) {
                cells.forEach(function (cell) {
                        var regionValue = cell.getAttribute('data-passable');

                        if (regionValue == 0) {
                                var divbody = cell.querySelector('.divbody');
                                divbody.style.backgroundColor = 'rgba(255, 255, 0, 0.5)';
                        }
                });
        }
}

function selectMapFieldType(type) {
        if (type === 0) {
                $('fieldtypeselector').innerHTML = 'Nichts gewählt';
        }
        else {
                $('fieldtypeselector').innerHTML = '<img src="' + gfx_path + '/map/' + type + '.png" />';
        }
        selectedFieldType = type;
}
function selectSystemType(type) {
        if (type === 0) {
                $('systemtypeselector').innerHTML = 'Nichts gewählt';
        }
        else {
                $('systemtypeselector').innerHTML = '<img src="' + gfx_path + '/map/systemtypes/' + type + '.png" />';
        }
        selectedSystemType = type;
}
function selectRegion(type, name) {
        if (type === 0) {
                $('regionselector').innerHTML = 'Nichts gewählt';
        }
        else {
                $('regionselector').innerHTML = name;
        }
        selectedRegion = type;
}
function selectAdminRegion(type, name) {
        if (type === 0) {
                $('adminregionselector').innerHTML = 'Nichts gewählt';
        }
        else {
                $('adminregionselector').innerHTML = name;
        }
        adminregionselector = type;
}
function selectBorder(type, name, color) {
        if (type === 0) {
                $('borderselector').innerHTML = 'Nichts gewählt';
        }
        else {
                $('borderselector').innerHTML = name;
        }
        borderselector = type;
        bordercolor = color;
}
function selectPassable(type) {
        if (type === 0) {
                $('passable').innerHTML = 'Nichts gewählt';
        }
        if (type === 1) {
                $('passable').innerHTML = 'True';
        }
        if (type === 2) {
                $('passable').innerHTML = 'False';
        }
        passableselector = type;
}
function toggleMapfieldType(obj) {
        if (selectedFieldType == 0) {
                return;
        }
        if (tmpfield == 0) {
                tmpfield = obj.parentNode.style.backgroundImage;
                obj.parentNode.style.backgroundImage = "url(" + gfx_path + "/map/" + selectedFieldType + ".png)";
                return;
        }
        obj.parentNode.style.backgroundImage = tmpfield;
        tmpfield = 0;
        return;
}
function updateField(obj, fieldid) {
        if (selectedFieldType == 0 && selectedSystemType == 0 && selectedRegion == 0 && adminregionselector == 0 && passableselector == 0 && borderselector == 0) {
                alert("Es wurde weder ein Systemtyp, Region, AdminRegion, Border, Passiebarkeit noch ein Feldtyp ausgewählt");
                return;
        }
        if (selectedFieldType != 0) {
                ajax_update(false, '/admin/?B_EDIT_FIELD=1&field=' + fieldid + '&type=' + selectedFieldType);
                obj.parentNode.style.backgroundImage = "url(" + gfx_path + "/map/" + selectedFieldType + ".png)";
                tmpfield = obj.parentNode.style.backgroundImage;
        }
        if (selectedSystemType != 0) {
                ajax_update(false, '/admin/?B_EDIT_SYSTEMTYPE_FIELD=1&field=' + fieldid + '&type=' + selectedSystemType);
                if (fieldevent == 3) {
                        obj.parentNode.style.backgroundColor = 'rgba(255, 255, 0, 0.5)';
                }
        }
        if (selectedRegion != 0) {
                ajax_update(false, '/admin/?B_EDIT_REGION=1&field=' + fieldid + '&region=' + selectedRegion);
                if (fieldevent == 1) {
                        obj.parentNode.style.backgroundColor = 'rgba(255, 0, 0, 0.5)';
                }
        }
        if (adminregionselector != 0) {
                ajax_update(false, '/admin/?B_EDIT_ADMIN_REGION=1&field=' + fieldid + '&adminregion=' + adminregionselector);
                if (fieldevent == 2) {
                        obj.parentNode.style.backgroundColor = 'rgba(0, 0, 255, 0.5)';
                }
        }
        if (passableselector != 0) {
                ajax_update(false, '/admin/?B_EDIT_PASSABLE=1&field=' + fieldid + '&passable=' + passableselector);
                if (fieldevent == 4) {
                        obj.parentNode.style.backgroundColor = 'rgba(255, 255, 0, 0.5)';
                }
        }
        if (borderselector != 0) {
                ajax_update(false, '/admin/?B_EDIT_BORDER=1&field=' + fieldid + '&border=' + borderselector);
                obj.parentNode.style.border = '1px solid'.bordercolor;
        }
}
function setNewFieldType(obj, fieldid) {
        if (selectedFieldType == 0 && selectedSystemType == 0) {
                alert("Es wurde weder ein Systemtyp noch ein Feldtyp ausgewählt");
                return;
        }
        ajax_update(false, '/admin/?B_EDIT_FIELD=1&field=' + fieldid + '&type=' + selectedFieldType);
        obj.parentNode.style.backgroundImage = "url(" + gfx_path + "/map/" + selectedFieldType + ".png)";
        tmpfield = obj.parentNode.style.backgroundImage;
}
function setNewSystemType(fieldid) {
        if (selectedFieldType == 0 && selectedSystemType == 0) {
                alert("Es wurde weder ein Systemtyp noch ein Feldtyp ausgewählt");
                return;
        }
        ajax_update(false, '/admin/?B_EDIT_SYSTEMTYPE_FIELD=1&field=' + fieldid + '&type=' + selectedSystemType);
}

function openSystemFieldSelector(x, y, systemId) {
        elt = 'fieldselector';
        openWindow(elt, 0);
        ajax_update(elt, `/admin/?SHOW_SYSTEM_EDITFIELD=1&systemid=${systemId}&x=${x}&y=${y}`);
}
function selectNewSystemMapField(fieldid, cx, cy, typeid, type) {
        ajax_update(false, '/admin/?B_EDIT_SYSTEM_FIELD=1&field=' + fieldid + '&type=' + typeid);
        field = $(cx + '_' + cy);
        field.style.backgroundImage = "url(" + gfx_path + "/map/" + type + ".png)";
        closeAjaxWindow();
}
function findObject(obj) {
        var curleft = curtop = 0;
        if (obj.offsetParent) {
                do {
                        curleft += obj.offsetLeft;
                        curtop += obj.offsetTop;
                } while (obj = obj.offsetParent);
                return [curleft, curtop];
        }
}
function openTopicSettings(obj, tid, bid) {
        var pos = findObject(obj);
        elt = 'topicaction';
        openWindowPosition(elt, 1, 200, pos[0] - 200, pos[1]);
        ajax_update(elt, "alliance.php?SHOW_TOPIC_SETTINGS=1&tid=" + tid + "&bid=" + bid);
}
function openBoardSettings(obj, bid) {
        var pos = findObject(obj);
        elt = 'boardaction';
        openWindowPosition(elt, 1, 200, pos[0] - 200, pos[1]);
        ajax_update(elt, "alliance.php?SHOW_BOARD_SETTINGS=1&bid=" + bid);
}
function openPmWindow(fromId, toId, fromType, toType) {
        elt = 'pmwindow';
        openWindowPosition(elt, 1, 600, 90, 60);
        ajax_update(elt, '/pm.php?SHOW_WRITE_QUICKPM=1&fromid=' + fromId + '&toid=' + toId + '&fromtype=' + fromType + '&totype=' + toType);
}
function sendQuickPM(userId) {
        var elem = $('quickpm').serialize() + '&sstr=' + $('pm_sstr').value;
        ajaxPost('/pm.php', 'B_WRITE_PM=1&recipient=' + userId + "&" + elem);
        $('quickpm_compose').hide();
        $('quickpm_done').show();
}
function ajaxPostUpdate(destelement, url, elements) {
        new Ajax.Updater(destelement, url,
                {
                        method: 'post',
                        parameters: elements
                });
}
function ajaxPost(url, elements) {
        new Ajax.Request(url,
                {
                        method: 'post',
                        parameters: elements
                });
}
function showResearchDetails(researchId) {
        elt = 'researchwin';
        openWindow(elt);
        ajax_update(elt, '/research.php?SHOW_RESEARCH=1&id=' + researchId);
}
function openNotes() {
        str = "notes.php";
        Win = window.open(str, 'WinNotes', 'width=850,height=700,resizeable=no,location=no,scrollbars=yes,status=no');
        Win.opener = self;
}
function goToUrl(url) {
        window.location.href = url;
}
function openNewTab(url) {
        window.open(url, '_blank');
}
function toggleTableRowVisible(id) {
        if ($(id).style.display == 'block' || $(id).style.display == 'table-row') {
                $(id).style.display = 'none';
                return;
        }
        $(id).style.display = 'table-row';
}
function openPJsWin(elt, exclusive, width, offsety) {
        if (width) {
                var OLWIDTH = ' WIDTH, ' + width;
        } else {
                var OLWIDTH = '';
        }
        if (exclusive) {
                if (offsety) {
                        return overlib('<div id=' + elt + '></div>', OLWIDTH, BGCOLOR, '#8897cf', TEXTCOLOR, '#8897cf', FGCOLOR, '#000000', CELLPAD, 0, 0, 0, 0, OFFSETY, offsety, HAUTO, VAUTO, STICKY, DRAGGABLE, ALTCUT, EXCLUSIVE);
                } else {
                        return overlib('<div id=' + elt + '></div>', OLWIDTH, BGCOLOR, '#8897cf', TEXTCOLOR, '#8897cf', FGCOLOR, '#000000', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, STICKY, DRAGGABLE, ALTCUT, EXCLUSIVE);
                }
        }
        else {
                return overlib('<div id=' + elt + '></div>', OLWIDTH, BGCOLOR, '#8897cf', TEXTCOLOR, '#8897cf', FGCOLOR, '#000000', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, DRAGGABLE, ALTCUT, STICKY);
        }
}
function cp(obj, file, ending = 'png') {
        document.images[obj].src = gfx_path + "/" + file + "." + ending;
}

function updateComponent(id, url, refreshInterval) {
        if (refreshInterval) {
                setTimeout(`ajax_update('${id}', '${url}')`, refreshInterval);
                setTimeout(`updateComponent('${id}', '${url}', ${refreshInterval})`, refreshInterval);
        } else {
                ajax_update(id, url);
        }
}

function toggleVisible(id) {
        if ($(id).style.display == 'block') {
                $(id).style.display = 'none';
                return;
        }
        $(id).style.display = 'block';
}
function showAchievement(text) {
        var elem = new Element("div");
        $(elem).addClassName('achievementbox box boxshadow');
        var header = new Element("div");
        $(header).addClassName("box_title");
        $(header).innerHTML = 'Neue Errungenschaft!';
        $(elem).appendChild(header);
        var body = new Element("div");
        $(body).addClassName("box_body");
        $(body).innerHTML = text;
        $(elem).appendChild(body);
        var close = new Element("div");
        $(close).addClassName("closebutton");
        $(close).innerHTML = 'X';
        $(close).observe("click", function () {
                Effect.Fade(this.up());
        });
        $(close).addClassName('action');
        $(elem).appendChild(close);
        document.body.appendChild(elem);
        elem.show();
}
function nodelistToString(list) {
        if (!RadioNodeList.prototype.isPrototypeOf(list)) {
                if (list.checked) {
                        return list.value
                } else {
                        return '';
                }
        }
        return Array.from(list)
                .filter(function (node) {
                        if (node.checked) {
                                return true;
                        }
                        return false;
                })
                .map(function (node) {
                        return node.value;
                })
                .join(',');
}
function snafu(hostId, hostType, action, mode, sstr) {
        commodityId = $('commodityselector').getValue();
        goToUrl('/colony.php?id=' + hostId + '&hosttype=' + hostType + '&' + action + '=1&mode=' + mode + '&selection=' + commodityId + '&sstr=' + sstr);
}
function togglePanel(panelId) {
        var panel = document.getElementById(panelId);
        if (panel.style.display === "none") {
                panel.style.display = "block";
                loadImages(panel);
        } else {
                panel.style.display = "none";
        }
}
function loadImages(panel) {
        var images = panel.querySelectorAll("img[data-src]");
        images.forEach(function (image) {
                image.src = image.getAttribute("data-src");
                image.removeAttribute("data-src");
        });
}

function initTranslations() {
        let translatableSpans = document.querySelectorAll(".translatable-content");

        function replaceTranslateContent(spanElement) {
                let content = spanElement.innerHTML;
                let regex = /\[translate\]([\s\S]*?)\[translation\]([\s\S]*?)\[\/translate\]/g;

                let newContent = content.replace(regex, function (match, p1, p2) {
                        return `<span class="translatable" data-original="${p1}" data-translation="${p2}">${p1}</span>`;
                });

                spanElement.innerHTML = newContent;
        }

        translatableSpans.forEach(span => replaceTranslateContent(span));
        translatableSpans.forEach(span => {
                span.addEventListener("click", function (event) {
                        let clickedElement = event.target;

                        if (clickedElement.classList.contains("translatable")) {
                                let originalContent = clickedElement.getAttribute("data-original");
                                let translatedContent = clickedElement.getAttribute("data-translation");

                                if (originalContent && translatedContent) {
                                        if (clickedElement.innerHTML === translatedContent) {
                                                clickedElement.innerHTML = originalContent;
                                        } else {
                                                clickedElement.innerHTML = translatedContent;
                                        }
                                }
                        }
                });
        });
};

let allTranslated = false;

function toggleAll(boxBodyElement) {
        let translationSpan = boxBodyElement.querySelector('.translatable-content');

        if (translationSpan) {
                let translatableSections = translationSpan.querySelectorAll('.translatable');
                translatableSections.forEach(span => {
                        let originalContent = span.getAttribute('data-original');
                        let translatedContent = span.getAttribute('data-translation');

                        if (originalContent && translatedContent) {
                                if (span.innerHTML === translatedContent) {
                                        span.innerHTML = originalContent;
                                } else {
                                        span.innerHTML = translatedContent;
                                }
                        }
                });
        }
}
function toggleTranslation(targetId) {
        let translationSpan = document.getElementById(targetId);
        if (translationSpan) {
                let translatableSections = translationSpan.querySelectorAll('.translatable');
                translatableSections.forEach(span => {
                        let originalContent = span.getAttribute('data-original');
                        let translatedContent = span.getAttribute('data-translation');
                        if (originalContent && translatedContent) {
                                if (span.innerHTML === translatedContent) {
                                        span.innerHTML = originalContent;
                                } else {
                                        span.innerHTML = translatedContent;
                                }
                        }
                });
        }
}

if (typeof userDeals !== 'undefined' && userDeals) {
        setTimeout(function () {
                showPopup();
        }, 3000);
}

function showPopup() {
        var popup = document.getElementById('nagus');
        var popupContent = '<div class="header_box border_box action" style="text-align: center; margin: 5px 0 0 15px; padding: 5px; overflow: hidden; "> <a href="trade.php?SHOW_DEALS=1"><img id="romImage" src="/assets/buttons/romblinck.gif" style="width: 65px; clip: rect(0px, 0px, 50px, 0px);" alt="Zu den Deals gehen"></a> </div>';
        popup.innerHTML = popupContent;
        popup.style.display = 'block';


        var romImage = document.getElementById('romImage');

        romImage.addEventListener('mouseover', function () {
                romImage.title = 'Neue Deals: Zu den Deals gehen';
        });

        romImage.addEventListener('mouseout', function () {
                romImage.title = '';
        });

        setTimeout(function () {
                closeNagusPopup();
                setTimeout(function () {
                        showPopup();
                }, 2000);
        }, 7000);
}

function closeNagusPopup() {
        var popup = document.getElementById('nagus');
        popup.style.display = 'none';
}

function deleteColonyScan(id) {
        ajaxrequest('database.php?B_DELETE_COLONY_SCAN=1&id=' + id);
        document.getElementById(`colonyScan_${id}`).remove();
}

function switchView(view, title, url) {
        switchInnerContent('B_SWITCH_VIEW', title, `view=${view}`, '/game.php', url);
}

function actionToInnerContent(action, params, title, page) {
        switchInnerContent(action, title, params, page);
}

var isUpdateInProgress = false;
function switchInnerContent(identifier, title, params, page, stateUrl) {
        if (isUpdateInProgress) {
                return;
        }

        isUpdateInProgress = true;

        closeAjaxWindow();

        url = `?${identifier}=1`;
        if (page) {
                url = page + url;
        }

        if (params) {
                url += `&${params}`;
        }

        switchUrl = url + '&switch=1';

        new Ajax.Updater('innerContent', switchUrl, {
                onComplete: function (response) {
                        isUpdateInProgress = false;

                        if (400 == response.status) {
                                window.location.href = '/index.php'
                                return;
                        }
                        if (title) {
                                let doc = new DOMParser().parseFromString(title, 'text/html');
                                document.title = doc.body.textContent || "";
                        }
                        window.history.pushState(null, title, stateUrl ?? url);
                        if (page) {
                                window.scrollTo(0, 0);
                        }
                },
                method: 'get',
                evalScripts: true
        }
        );
}

function showTransfer(sourceId, targetId, transferTypeValue, isUnload, isColonyTarget, isReplace) {
        if (!isReplace) {
                closeAjaxWindow();
                openPJsWin('elt', 1);
        }

        isUnloadValue = isUnload ? 1 : 0;
        isColonyTargetValue = isColonyTarget ? 1 : 0;

        ajax_update('elt', `?SHOW_TRANSFER=1&id=${sourceId}&target=${targetId}&is_unload=${isUnloadValue}&is_colony=${isColonyTargetValue}&transfer_type=${transferTypeValue}`);
}

function maximizeCommodityAmounts() {
        document.querySelectorAll('.commodityAmount').forEach(function (elem) {
                elem.value = 'max';
        });
}

document.addEventListener("DOMContentLoaded", function () {
        var previewElement = document.getElementById("preview");
        if (previewElement) {
                initTranslations();
        }
});

/** ASYNCHRONOUS LOADING OF JAVASCRIPT FILES*/
const loadScript = (FILE_URL, async = true, type = "text/javascript") => {
        return new Promise((resolve, reject) => {
                try {
                        const scriptEle = document.createElement("script");
                        scriptEle.type = type;
                        scriptEle.async = async;
                        scriptEle.src = FILE_URL;

                        scriptEle.addEventListener("load", (ev) => {
                                resolve({ status: true });
                        });

                        scriptEle.addEventListener("error", (ev) => {
                                reject({
                                        status: false,
                                        message: `Failed to load the script ${FILE_URL}`
                                });
                        });

                        document.body.appendChild(scriptEle);
                } catch (error) {
                        reject(error);
                }
        });
};
function appendJsAsync(path) {

        loadScript(path)
                .then(data => {
                        console.log(`Script '${path}' loaded successfully`, data);
                })
                .catch(err => {
                        console.error(err);
                });
}
