function moveToPosition(posx, posy) {
	if (!posx || !posy || !sstr || !shipid) {
		return;
	}
	window.location.href = 'ship.php?B_MOVE=1&id=' + shipid + '&posx=' + posx + '&posy=' + posy + '&sstr=' + sstr;
}

var lastPosition = '';

function focusNavApplet() {
	lastPosition = document.shipform.navapp.value;
	document.shipform.navapp.value = '';
}

function blurNavApplet() {
	if (document.shipform.navapp.value != lastPosition) {
		return;
	}
	document.shipform.navapp.value = lastPosition;
}

function showALvlWindow() {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_ALVL=1');
}

function showETransferWindow(target) {
	closeAjaxWindow();
	openWindow('elt', 1);
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_ETRANSFER=1&target=' + target);
}

function showBToWindow(target) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_BEAMTO=1&target=' + target);
}

function showBFromWindow(target) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_BEAMFROM=1&target=' + target);
}

function triggerBeamTo(target) {
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_BEAMTO=1&target=' + target);
}

function triggerBeamFrom(target) {
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_BEAMFROM=1&target=' + target);
}

function showBToColonyWindow(target) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_COLONY_BEAMTO=1&target=' + target);
}

function showBFromColonyWindow(target) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_COLONY_BEAMFROM=1&target=' + target);
}

function showBTroopTransferWindow(target, targetIsColony, isUnload) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	colonyParam = targetIsColony ? '&isColony=1' : '';
	directionParam = isUnload ? '&isUnload=1' : '';
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_TROOP_TRANSFER=1&target=' + target + colonyParam + directionParam);
}

function showBTorpTransferWindow(target, isUnload) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	directionParam = isUnload ? '&isUnload=1' : '';
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_TORP_TRANSFER=1&target=' + target + directionParam);
}

function triggerBeamToColony(target) {
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_COLONY_BEAMTO=1&target=' + target);
}

function triggerBeamFromColony(target) {
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_COLONY_BEAMFROM=1&target=' + target);
}

function showSelfdestructWindow(target) {
	closeAjaxWindow();
	openWindow('elt', 1, 300);
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_SELFDESTRUCT_AJAX=1&target=' + target);
}
function showScrapWindow(target) {
	closeAjaxWindow();
	openWindow('elt', 1, 300);
	ajax_update('elt', 'station.php?id=' + shipid + '&SHOW_SCRAP_AJAX=1&target=' + target);
}
function showScanWindow(shipid, target) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_SCAN=1&target=' + target);
}
function showSectorScanWindow(obj, x, y, sysid, loadSystemSensorScan) {
	closeAjaxWindow();
	var pos = findObject(obj);
	openWindowPosition('elt', 1, 800, pos[0] - 250, pos[1] - 250);
	if (x && y) {
		ajax_update('elt', 'station.php?id=' + shipid + '&SHOW_SENSOR_SCAN=1&cx=' + x + '&cy=' + y + '&sysid=' + sysid);
		if (loadSystemSensorScan) {
			ajax_update('systemsensorscan', 'station.php?id=' + shipid + '&SHOW_SYSTEM_SENSOR_SCAN=1&cx=' + x + '&cy=' + y);
		}
	} else {
		ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_SECTOR_SCAN=1');
	}
}
function showAstroEntryWindow() {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_ASTRO_ENTRY=1');
}
function openStarMap(obj, cx, cy) {
	closeAjaxWindow();
	var pos = findObject(obj);
	openWindowPosition('elt', 1, 700, pos[0], pos[1]);
	ajax_update('elt', 'starmap.php?SHOW_STARMAP_POSITION=1&x=' + cx + '&y=' + cy);
}
function openStorageInit(obj, id) {
	closeAjaxWindow();
	var timer = setTimeout('openStorage(' + id + ')', 1000); //wait 1 second
	obj.onmouseout = function () { clearTimeout(timer); } //remove timer
}
function openStorage(id) {
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?SHOW_SHIPSTORAGE=1&id=' + id);
}
function closeStorage() {
	closeAjaxWindow();
}
function showMapBy(cx, cy) {
	ajax_update('elt', 'starmap.php?SHOW_STARMAP_POSITION=1&x=' + cx + '&y=' + cy);
}
function showShipDetails(id) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?SHOW_SHIPDETAILS=1&id=' + id);
}
function openTradeMenu(postid) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?SHOW_TRADEMENU=1&id=' + shipid + '&postid=' + postid);
}
function tradeMenuChoosePayment(postid) {
	ajax_update('trademenucontent', 'ship.php?SHOW_TRADEMENU_CHOOSE_PAYMENT=1&id=' + shipid + "&postid=" + postid);
}
function payTradeLicence(postid, method, id) {
	ajax_update('trademenucontent', 'ship.php?B_PAY_TRADELICENCE=1&id=' + shipid + "&method=" + method + "&target=" + id + "&postid=" + postid + "&sstr=" + $('sstrajax').value);
}
function switchTransferFromAccount(postid) {
	ajax_update('trademenutransfer', 'ship.php?SHOW_TRADEMENU_TRANSFER=1&id=' + shipid + "&mode=from&postid=" + postid);
	$('transfertoaccount').removeClassName('selected');
	$('transferfromaccount').addClassName('selected');
}
function switchTransferToAccount(postid) {
	ajax_update('trademenutransfer', 'ship.php?SHOW_TRADEMENU_TRANSFER=1&id=' + shipid + "&mode=to&postid=" + postid);
	$('transferfromaccount').removeClassName('selected');
	$('transfertoaccount').addClassName('selected');
}
function showRegionInfo(region) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?SHOW_REGION_INFO=1&id=' + shipid + '&region=' + region);
}
function showColonization(colonyId) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?SHOW_COLONIZATION=1&id=' + shipid + '&colid=' + colonyId);
}
function showColonyScan() {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?SHOW_COLONY_SCAN=1&id=' + shipid);
}
function showRepairOptions(shipid) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_REPAIR_OPTIONS=1');
}
function toggleSpecialModuleDisplay(type, module_id, module_crew) {
}
currentTab = false;
function showModuleSelectTab(tabId) {
	if (currentTab) {
		currentTab.hide();

		Element.select(currentTab, '.specialModuleRadio').each(function (elem) {
			elem.checked = false;
		});
	}
	$('selector_' + tabId).show();
	currentTab = $('selector_' + tabId);
}
function hideFleet(fleetid) {
	$('nbstab').select('.fleet' + fleetid).each(function (obj) {
		obj.hide();
	});
	$('hidefleet' + fleetid).hide();
	$('showfleet' + fleetid).show();
	$('fleetuser' + fleetid).show();
	ajaxrequest('ship.php?B_HIDE_FLEET=1&id=' + shipid + '&fleet=' + fleetid);
}
function showFleet(fleetid) {
	$('nbstab').select('.fleet' + fleetid).each(function (obj) {
		obj.show();
	});
	$('hidefleet' + fleetid).show();
	$('showfleet' + fleetid).hide();
	$('fleetuser' + fleetid).hide();
	ajaxrequest('ship.php?B_SHOW_FLEET=1&id=' + shipid + '&fleet=' + fleetid);
}
function refreshShiplistFleet(fleetid) {
	ajax_update('nbsfleetform_' + fleetid, 'ship.php?SHOW_SHIPLIST_FLEET=1&fleetid=' + fleetid);
}
function showFightLog() {
	openPJsWin('fightresult_content', 1);
	$('fightresult_content').innerHTML = $('fightlog').innerHTML;
}
function showRenameCrew(obj, crew_id) {
	obj.hide();
	$('rn_crew_' + crew_id + '_input').show();
}
function renameCrew(crew_id) {
	crewName = $('rn_crew_' + crew_id + '_value').value;
	if (crewName.length < 1) {
		$('rn_crew_' + crew_id).show();
		$('rn_crew_' + crew_id + '_input').hide();
		return;
	}
	ajax_update('rn_crew_' + crew_id, 'ship.php?B_RENAME_CREW=1&id=' + shipid + '&crewid=' + crew_id + '&' + Form.Element.serialize('rn_crew_' + crew_id + '_value'));
}
function maximizeGoodAmounts() {
	var list = document.getElementsByClassName('goodAmount');
	var n;
	for (n = 0; n < list.length; ++n) {
		list[n].value = 'max';
	}
}
