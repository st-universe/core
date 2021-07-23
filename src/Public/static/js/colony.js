function buildMenuScrollUp(menu, offset) {
	if (offset - scrollOffset < 0) {
		var newOffset = 0;
	} else {
		var newOffset = offset - scrollOffset;
	}
	buildMenuScroll(menu, newOffset);
}

function buildMenuScrollDown(menu, offset) {
	var newOffset = offset + scrollOffset;
	buildMenuScroll(menu, newOffset);
}

function buildMenuScroll(menu, offset) {
	ajax_update('buildmenu' + menu, 'colony.php?id=' + colonyid + '&B_SCROLL_BUILDMENU=1&menu=' + menu + '&offset=' + offset);
}

function switchColonyMenu(menu, func) {
	closeAjaxWindow();
	url = 'colony.php?id=' + colonyid + '&B_SWITCH_COLONYMENU=1&menu=' + menu;
	if (func) {
		url = url + '&func=' + func;
	}
	new Ajax.Updater('colonymenu', url, {
		onComplete: function (transport) {
			initBuildmenuMouseEvent();
		},
		method: 'get'
	}
	);
}

var selectedbuilding = 0;

function openBuildingInfo(buildingId) {
	closeAjaxWindow();
	elt = 'buildinginfo';
	openPJsWin(elt);
	ajax_update(elt, 'colony.php?id=' + colonyid + '&SHOW_BUILDING=1&bid=' + buildingId);
	ajax_update('colsurface', 'colony.php?id=' + colonyid + '&SHOW_COLONY_SURFACE=1&bid=' + buildingId);
	buildmode = 1;
	selectedbuilding = buildingId;
}

function closeBuildingInfo() {
	ajax_update('colsurface', 'colony.php?id=' + colonyid + '&SHOW_COLONY_SURFACE=1');
	buildmode = 0;
	selectedbuilding = 0;
}

var oldimg = 0;
var fieldonm = 0;

function showFieldInfo(field) {
	if (field == 0) {
		return;
	}
	if (field != fieldonm) {
		fieldonm = 0;
		return;
	}
	elt = 'fieldinfo';
	openPJsWin(elt);
	ajax_update(elt, 'colony.php?id=' + colonyid + '&SHOW_FIELD_INFO=1&fid=' + field);
}
function fieldMouseOver(obj, field, building, fieldtype) {
	document.body.style.cursor = 'pointer';
	if (buildmode == 1 && building == 0) {
		if (obj.parentNode.parentNode.parentNode.parentNode.className == 'cfb') {
			oldimg = obj.src;
			obj.parentNode.style.backgroundImage = 'url(' + oldimg + ')';
			obj.src = gfx_path + "/generated/buildings/" + selectedbuilding + "/0at.png";
		}
	}
	if (buildmode == 1 && $('building_preview_' + fieldtype)) {
		$('building_preview_default').style.display = 'none';
		$('building_preview_' + fieldtype).style.display = 'block';
	}
}

function fieldMouseOut(obj, fieldtype) {
	if (buildmode == 1 && oldimg != 0) {
		if (obj.parentNode.parentNode.parentNode.parentNode.className == 'cfb') {
			obj.src = oldimg;
			obj.parentNode.style.backgroundImage = '';
			oldimg = 0;
		}
	}
	if (buildmode == 1 && $('building_preview_' + fieldtype)) {
		$('building_preview_' + fieldtype).style.display = 'none';
		$('building_preview_default').style.display = 'block';
	}
	document.body.style.cursor = 'auto';
}

function fieldMouseClick(obj, field, buildingId) {
	if (buildmode == 1) {
		if (obj.parentNode.className == 'cfb') {
			if (buildingId > 0) {
				if (confirm('Soll das Gebäude auf diesem Feld abgerissen werden?')) {
					buildOnField(field);
				}
			} else {
				buildOnField(field);
			}
		}
	} else {
		fieldAction(field);
	}
}

function fieldAction(field) {
	fieldonm = 0;
	elt = 'fieldaction';
	openPJsWin(elt, 1);
	ajax_update(elt, 'colony.php?id=' + colonyid + '&SHOW_FIELD=1&fid=' + field);
}
function buildOnField(field) {
	new Ajax.Updater('result', 'colony.php', {
		method: 'post',
		parameters: 'id=' + colonyid + '&B_BUILD=1&fid=' + field + '&bid=' + selectedbuilding,
		evalScripts: true,
		onComplete: function (transport) {
			var counter = document.getElementById("counter");
			counter.innerHTML = Math.max((counter.innerText - 1), 0);
			$('result').show();
		}
	});
}

function refreshColony() {
	ajax_update('colsurface', 'colony.php?id=' + colonyid + '&SHOW_COLONY_SURFACE=1&bid=' + selectedbuilding);
	ajax_update('colonyeps', 'colony.php?id=' + colonyid + '&SHOW_EPSBAR_AJAX=1');
	//ajax_update('colonyshields', 'colony.php?id=' + colonyid + '&SHOW_SHIELDBAR_AJAX=1');
	ajax_update('colonystorage', 'colony.php?id=' + colonyid + '&SHOW_STORAGE_AJAX=1');
}

function getOrbitShipList() {
	elt = 'shiplist';
	openPJsWin(elt);
	ajax_update(elt, 'colony.php?id=' + colonyid + '&SHOW_ORBIT_SHIPLIST=1');
}
var selbg = '';
function shipSelectorHover(obj) {
	selbg = obj.style.backgroundColor;
	obj.style.backgroundColor = '#7c85a8';
	obj.style.cursor = 'pointer';
}
function shipSelectorHoverEnd(obj) {
	obj.style.backgroundColor = selbg;
	obj.style.cursor = 'auto';
	selbg = '';
}
function switchToShip(obj) {
	link = Element.select(obj, 'a');
	if (link.length == 0) {
		return;
	}
	goToUrl(link[0].href);
}
function shipSelectorChoose(obj) {
	shiplist = document.getElementById("shiplist");
	if (!shiplist) {
		switchToShip(obj);
		return;
	}
	shipSelectorHoverEnd(obj);
	sel = $('shipselector');
	sel.innerHTML = '';
	sel.appendChild(obj.parentNode);
	closeAjaxWindow();
	Element.remove($('shiplist'));
}

function showBToWindow() {
	var shipid = $('selshipid').value;
	elt = 'beam'
	openPJsWin(elt, 1);
	ajax_update(elt, 'colony.php?id=' + colonyid + '&SHOW_BEAMTO=1&target=' + shipid);
}

function showBFromWindow() {
	var shipid = $('selshipid').value;
	elt = 'beam'
	openPJsWin(elt, 1);
	ajax_update(elt, 'colony.php?id=' + colonyid + '&SHOW_BEAMFROM=1&target=' + shipid);
}

function showSectorScanWindow(id) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'colony.php?id=' + id + '&SHOW_SECTOR_SCAN=1');
}

function showPodLocationWindow() {
	elt = 'podlocations';
	openPJsWin(elt, 1);
	ajax_update(elt, 'colony.php?SHOW_PODS_LOCATIONS=1');
}

function openShuttleManagement(obj, ship, colony) {
	closeAjaxWindow();

	var pos = findObject(obj);
	openWindowPosition('elt', 1, 200, pos[0] - 200, pos[1]);
	ajax_update('elt', 'colony.php?SHOW_SHUTTLE_MANAGEMENT=1&ship=' + ship + '&colony=' + colony);
}

function decreaseShuttleAmount(cid) {
	old = parseInt($('shuttleAmount_' + cid).value);

	if (old > 0) {
		$('shuttleAmount_' + cid).value = old - 1;

		current = parseInt($('storedshuttles').innerHTML);
		$('storedshuttles').innerHTML = current - 1;
	}
}

function increaseShuttleAmount(cid, maxOf, maxTotal) {
	old = parseInt($('shuttleAmount_' + cid).value);
	current = parseInt($('storedshuttles').innerHTML);

	if (old < maxOf && current < maxTotal) {
		$('shuttleAmount_' + cid).value = old + 1;
		$('storedshuttles').innerHTML = current + 1;
	}
}

function triggerBeamTo() {
	var shipid = $('selshipid').value;
	ajax_update(elt, 'colony.php?id=' + colonyid + '&SHOW_BEAMTO=1&target=' + shipid);
}

function triggerBeamFrom() {
	var shipid = $('selshipid').value;
	ajax_update(elt, 'colony.php?id=' + colonyid + '&SHOW_BEAMFROM=1&target=' + shipid);
}

function initBuildmenuMouseEvent() {
	onmousewheel($('buildmenu1'), function (delta) {
		scrollBuildmenuByMouse(1, delta);
	});
	onmousewheel($('buildmenu2'), function (delta) {
		scrollBuildmenuByMouse(2, delta);
	});
	onmousewheel($('buildmenu3'), function (delta) {
		scrollBuildmenuByMouse(3, delta);
	});
}
function scrollBuildmenuByMouse(menu, delta) {
	offset = parseInt($('buildmenu' + menu + '_offset').value);
	if (delta < 0) {
		buildMenuScrollDown(menu, offset);
	}
	if (delta > 0) {
		buildMenuScrollUp(menu, offset);
	}
}
currentTab = false;
function showModuleSelectTab(obj, tabId) {
	$('module_select_tabs').select('td').each(function (tab) {
		Element.removeClassName(tab, 'module_select_base_selected');
	});
	Element.addClassName(obj, 'module_select_base_selected');
	if (!currentTab) {
		$('module_select_tab_0').hide();
	} else {
		currentTab.hide();
	}
	$('module_select_tab_' + tabId).show();
	currentTab = $('module_select_tab_' + tabId);
}
function replaceTabImage(type, moduleId, goodId, module_crew, module_lvl) {
	if (moduleId == 0) {
		$('tab_image_mod_' + type).src = 'assets/buttons/modul_' + type + '.gif';
		$('module_type_' + type).innerHTML = '';
		updateCrewCount(type, 0, 0);
	} else {
		Element.removeClassName($('module_tab_' + type), 'module_select_base_mandatory');
		$('tab_image_mod_' + type).src = 'assets/goods/' + goodId + '.gif';
		$('module_type_' + type).innerHTML = $(moduleId + '_content').innerHTML;
		$('module_type_' + type).show();
		updateCrewCount(type, module_crew, module_lvl);
	}
	enableShipBuildButton();
}
var disabledSlots = new Set();
function toggleSpecialModuleDisplay(type, module_id, module_crew) {
	let innerHTML = '';
	let checkedCount = 0;
	Element.select($('module_select_tab_' + type), '.specialModuleRadio').each(function (elem) {
		if (elem.checked) {
			innerHTML = innerHTML.concat($(elem.value + '_content').innerHTML);
			if (elem.value == module_id) {
				updateCrewCount(elem.value, module_crew, 0);
			}
			checkedCount++;
		} else {
			updateCrewCount(elem.value, 0, 0);
		}
	});
	$('module_tab_info_' + type).innerHTML = checkedCount + ' von max. ' + specialSlots;
	if (checkedCount == specialSlots) {
		Element.select($('module_select_tab_' + type), '.specialModuleRadio').each(function (elem) {
			if (!elem.checked && !elem.disabled) {
				elem.disabled = true;
				disabledSlots.add(elem);
			}
		});
	}
	else {
		disabledSlots.forEach(function (elem) {
			elem.disabled = false;
		});
	}
	$('module_type_' + type).innerHTML = innerHTML;
	$('module_type_' + type).show();

	enableShipBuildButton();
}
var maxCrew;
var baseCrew;
var rumpModuleLvl;
var specialSlots;
function setFixValues(base_crew, max_crew, rump_module_lvl, special_slots) {
	baseCrew = base_crew;
	maxCrew = max_crew;
	rumpModuleLvl = rump_module_lvl;
	specialSlots = special_slots;
}
var crew_type = new Hash();
function updateCrewCount(type, module_crew, module_lvl) {
	crew_type.set(type, { lvl: module_lvl, crew: module_crew });
}
function checkCrewCount() {
	crewSum = baseCrew;
	crew_type.each(function (pair) {
		if (pair.value.crew >= 0) {
			if (pair.value.lvl > rumpModuleLvl) {
				crewSum += pair.value.crew + 1;
			} else {
				crewSum += pair.value.crew;
			}
		}
	});
	$('crewdisplay').select('div').each(function (elem) {
		elem.hide();
	});
	if (crewSum > maxCrew) {
		Form.Element.disable('buildbutton');
		$('crewerr').show();
		return false;
	} else {
		$('crewSum').show();
		$('crewMax').show();
		$('crewSum').innerHTML = "Benötigte Crew: " + crewSum;
		return true;
	}
}
function enableShipBuildButton() {
	if (!checkCrewCount()) {
		return;
	}
	mandatory = false;
	$('module_select_tabs').select('td').each(function (tab) {
		if (Element.hasClassName(tab, 'module_select_base_mandatory')) {
			mandatory = true;
		}
	});
	if (mandatory) {
		return;
	}
	Form.Element.enable('buildbutton');
	new Effect.Highlight($('buildbutton'));
}
function cancelModuleQueueEntries(module_id) {
	ajaxPostUpdate(
		'module_' + module_id + '_action',
		'colony.php', 'B_CANCEL_MODULECREATION=1&id=' + colonyid + '&module=' + module_id + '&func=' + $('func').value + '&count=' + $('module_' + module_id + '_count').value
	);
	setTimeout('refreshColony()', 250);
}
function showGiveUpWindow(target) {
	elt = 'giveup';
	openWindow(elt, 1, 300);
	ajax_update(elt, 'colony.php?id=' + colonyid + '&SHOW_GIVEUP_AJAX=1&target=' + target);
}
