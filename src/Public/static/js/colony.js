var colonyid = null;
var hostid = null;
var hosttype = null;
var sstr = null;
var scrollOffset = 6;

function initializeJsVars(id, type, sessionString) {
	colonyid = id;
	hostid = id;
	hosttype = type;
	sstr = sessionString;
}

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
	ajax_update('buildmenu' + menu, createHostUri('B_SCROLL_BUILDMENU', '&menu=' + menu + '&offset=' + offset));
}

function switchColonyMenu(menu, func, fid) {
	switchMenu(menu, 'colonymenu', func, fid);
}

function switchColonySubmenu(menu, params) {
	document.querySelectorAll('.colmenubutton').forEach(function (elem) {
		Element.removeClassName(elem, 'selected');
	});
	switchMenu(menu, 'submenu', null, null, params);
	$('colmenubutton_' + menu).addClassName('selected');
}

function switchMenu(menu, id, func, fid, params) {
	$('result').hide();
	closeAjaxWindow();
	url = createHostUri('B_SWITCH_COLONYMENU', `&menu=${menu}`);
	if (func) {
		url += `&func=${func}`;
	}
	if (fid) {
		url += `&fid=${fid}`;
	}
	if (params) {
		url += `&${params}`;
	}

	ajax_update(id, url);

	if (menu == 1) {
		//initBuildmenuMouseEvent();
	}
}

var selectedbuilding = 0;

function openBuildingInfo(buildingId) {
	closeAjaxWindow();
	elt = 'buildinginfo';
	openPJsWin(elt);
	ajax_update(elt, createHostUri('SHOW_BUILDING', '&bid=' + buildingId));
	ajax_update('colsurface', createHostUri('SHOW_SURFACE', '&bid=' + buildingId));
	buildmode = 1;
	selectedbuilding = buildingId;
}

function closeBuildingInfo() {
	ajax_update('colsurface', createHostUri('SHOW_SURFACE'));
	buildmode = 0;
	selectedbuilding = 0;
}

var oldimg = 0;

function fieldMouseOver(obj, building, fieldtype) {
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

function fieldMouseClick(obj, fieldId, buildingId) {
	if (buildmode == 1) {
		if (obj.parentNode.className == 'cfb') {
			if (buildingId > 0) {
				if (confirm('Soll das Gebäude auf diesem Feld abgerissen werden?')) {
					buildOnField('B_BUILD', fieldId);
				}
			} else {
				buildOnField('B_BUILD', fieldId);
			}
		}
	} else {
		showField(fieldId);
	}
}

function showField(fieldId) {
	elt = 'fieldaction';
	openPJsWin(elt);
	ajax_update(elt, '/colony.php?fid=' + fieldId + '&SHOW_FIELD=1');
}
function buildOnField(action, fieldId, buildingId) {

	if (buildingId) {
		bid = buildingId;
	} else {
		bid = selectedbuilding;
	}

	performActionAndUpdateResult(action, `fid=${fieldId}&bid=${bid}`);
}

function terraformOnField(fieldId, terraformId) {
	performActionAndUpdateResult('B_TERRAFORM', `fid=${fieldId}&tfid=${terraformId}`);
}

function removeOnField(fieldId) {
	performActionAndUpdateResult('B_REMOVE_BUILDING', `fid=${fieldId}`);
}

function performActionAndUpdateResult(action, params) {

	new Ajax.Updater('result', '/colony.php', {
		method: 'post',
		parameters: `${action}=1&${params}`,
		evalScripts: true,
		onSuccess: function (transport) {
			var counter = document.getElementById("counter");
			if (counter) {
				counter.innerHTML = Math.max((counter.innerText - 1), 0);
			}

			$('result').show();
		}
	});
}

function refreshHost() {
	ajax_update('colsurface', createHostUri('SHOW_SURFACE', '&bid=' + selectedbuilding));
	ajax_update('colonyeps', createHostUri('SHOW_EPSBAR_AJAX'));
	ajax_update('colonyshields', createHostUri('SHOW_SHIELDBAR_AJAX'));
	ajax_update('colonystorage', createHostUri('SHOW_STORAGE_AJAX'));
}

function createHostUri(IDENTIFIER, extra) {
	uri = `?id=${hostid}&hosttype=${hosttype}&${IDENTIFIER}=1`;

	if (extra) {
		uri += extra;
	}

	return uri;
}

function getOrbitShipList(colonyId) {
	elt = 'shiplist';
	openPJsWin(elt);
	ajax_update(elt, 'colony.php?id=' + colonyId + '&SHOW_ORBIT_SHIPLIST=1');
}

function showBToSWindow() {
	var shipid = $('selshipid').value;
	elt = 'beam'
	openPJsWin(elt, 1);
	ajax_update(elt, 'colony.php?id=' + colonyid + '&SHOW_BEAMTO=1&target=' + shipid);
}

function showBFromSWindow() {
	var shipid = $('selshipid').value;
	elt = 'beam'
	openPJsWin(elt, 1);
	ajax_update(elt, 'colony.php?id=' + colonyid + '&SHOW_BEAMFROM=1&target=' + shipid);
}

function showColonySectorScanWindow(id) {
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

function triggerBeamToShip() {
	var shipid = $('selshipid').value;
	ajax_update(elt, 'colony.php?id=' + colonyid + '&SHOW_BEAMTO=1&target=' + shipid);
}

function triggerBeamFromShip() {
	var shipid = $('selshipid').value;
	ajax_update(elt, 'colony.php?id=' + colonyid + '&SHOW_BEAMFROM=1&target=' + shipid);
}

function toggleMaxEmpty(elem, max) {
	var input = elem.up('tr').down('.commodityAmount');
	var value = input.value;

	if (value) {
		input.value = '';
	} else {
		input.value = max;
	}
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
	onmousewheel($('buildmenu4'), function (delta) {
		scrollBuildmenuByMouse(4, delta);
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
function replaceTabImage(type, moduleId, commodityId, module_crew) {
	if (moduleId == 0) {
		$('tab_image_mod_' + type).src = 'assets/buttons/modul_' + type + '.png';
		$('module_type_' + type).innerHTML = '';
		updateCrewCount(type, 0);
	} else {
		Element.removeClassName($('module_tab_' + type), 'module_select_base_mandatory');
		$('tab_image_mod_' + type).src = 'assets/commodities/' + commodityId + '.png';
		$('module_type_' + type).innerHTML = $(moduleId + '_content').innerHTML;
		$('module_type_' + type).show();
		updateCrewCount(type, module_crew);
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
				updateCrewCount(elem.value, module_crew);
			}
			checkedCount++;
		} else {
			updateCrewCount(elem.value, 0);
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
var specialSlots;
function setFixValues(base_crew, max_crew, special_slots) {
	baseCrew = base_crew;
	maxCrew = max_crew;
	specialSlots = special_slots;
}
var crew_type = new Hash();
function updateCrewCount(type, module_crew) {
	crew_type.set(type, module_crew);
}
function checkCrewCount() {
	crewSum = baseCrew;
	crew_type.each(function (pair) {
		if (pair.value >= 0) {
			crewSum += pair.value;
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
	setTimeout('refreshHost()', 250);
}
function showGiveUpWindow(target) {
	elt = 'giveup';
	openWindow(elt, 1, 300);
	ajax_update(elt, 'colony.php?id=' + colonyid + '&SHOW_GIVEUP_AJAX=1&target=' + target);
}

function getCommodityLocations(commodityId) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'database.php?commodityId=' + commodityId + '&SHOW_COMMODITIES_LOCATIONS=1');
}

function calculateScanCost(cx, cy) {
	var difX = Math.abs(cx - colonyMapX);
	var difY = Math.abs(cy - colonyMapY);
	var diagonal = Math.ceil(Math.sqrt(difX * difX + difY * difY));

	var neededEnergy = 20 + (diagonal / 169) * 180;
	return Math.round(neededEnergy);
}
function updateTelescopeEnergy(cx, cy) {
	$('needed_energy').innerHTML = calculateScanCost(cx, cy);

	if (parseInt($('needed_energy').innerHTML) > parseInt($('current_energy').innerHTML)) {
		$('needed_energy').style.color = 'red';
	} else {
		$('needed_energy').style.color = '#dddddd';
	}
}
function showTelescopeScan(cx, cy) {
	closeAjaxWindow();
	openPJsWin('elt', 1);

	if (calculateScanCost(cx, cy) <= parseInt($('current_energy').innerHTML)) {
		ajax_update('elt', 'colony.php?SHOW_TELESCOPE_SCAN=1&id=' + colonyid + '&cx=' + cx + '&cy=' + cy);
	}

	//refresh current colony eps
	ajax_update('current_energy', 'colony.php?REFRESH_COLONY_EPS=1&id=' + colonyid);
}
