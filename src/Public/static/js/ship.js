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

function showAvailableShips(fleetid) {
	closeAjaxWindow();
	openWindow('elt', 1, 300);
	ajax_update('elt', 'ship.php?SHOW_AVAILABLE_SHIPS=1&fleetid=' + fleetid);
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
function showScrapWindow() {
	closeAjaxWindow();
	openWindow('elt', 1, 300);
	ajax_update('elt', 'station.php?id=' + shipid + '&SHOW_SCRAP_AJAX=1');
}
function showWebEmitterWindow() {
	closeAjaxWindow();
	openWindow('elt', 1, 300);
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_WEBEMITTER_AJAX=1');
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
function openStarMap(obj, cx, cy, layerid) {
	closeAjaxWindow();
	var pos = findObject(obj);
	openWindowPosition('elt', 1, 700, pos[0], pos[1]);
	ajax_update('elt', 'starmap.php?SHOW_STARMAP_POSITION=1&x=' + cx + '&y=' + cy + '&sec=0&layerid=' + layerid);
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
function showShipDetails(id) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?SHOW_SHIPDETAILS=1&id=' + id);
}
function showShipCommunication(id) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?SHOW_SHIP_COMMUNICATION=1&id=' + id);
}
function openTradeMenu(postid) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?SHOW_TRADEMENU=1&id=' + shipid + '&postid=' + postid);
}
function tradeMenuChoosePayment(postid) {
	ajax_update('trademenucontent', 'ship.php?SHOW_TRADEMENU_CHOOSE_PAYMENT=1&id=' + shipid + "&postid=" + postid);
}
function payTradeLicense(postid, method, id) {
	ajax_update('trademenucontent', 'ship.php?B_PAY_TRADELICENSE=1&id=' + shipid + "&method=" + method + "&target=" + id + "&postid=" + postid + "&sstr=" + $('sstrajax').value);
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
function switchMenuToBroadcast() {
	$('menuemergency').removeClassName('selected');
	$('menulogbook').removeClassName('selected');
	$('menubroadcast').addClassName('selected');

	document.getElementById('broadcast').style.display = "";
	document.getElementById('logbook').style.display = "none";
	document.getElementById('emergency').style.display = "none";
}
function switchMenuToLogbook() {
	$('menubroadcast').removeClassName('selected');
	$('menuemergency').removeClassName('selected');
	$('menulogbook').addClassName('selected');

	document.getElementById('logbook').style.display = "";
	document.getElementById('broadcast').style.display = "none";
	document.getElementById('emergency').style.display = "none";
}
function switchMenuToEmergency() {
	$('menubroadcast').removeClassName('selected');
	$('menulogbook').removeClassName('selected');
	$('menuemergency').addClassName('selected');

	document.getElementById('emergency').style.display = "";
	document.getElementById('broadcast').style.display = "none";
	document.getElementById('logbook').style.display = "none";
}
function switchScanToDetails() {
	$('menuScanLogbook').removeClassName('selected');
	$('menuScanDetails').addClassName('selected');

	document.getElementById('scandetails').style.display = "";
	document.getElementById('scanlogbook').style.display = "none";
}
function switchScanToLogbook() {
	$('menuScanDetails').removeClassName('selected');
	$('menuScanLogbook').addClassName('selected');

	document.getElementById('scanlogbook').style.display = "";
	document.getElementById('scandetails').style.display = "none";
}
function postLogEntry(shipid) {
	log = Form.Element.serialize('log');
	ajax_update('kncomments', "comm.php?B_POST_COMMENT=1&posting=" + postingId + "&" + comment);
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
function toggleFleet(fleetid) {
	var x = document.getElementById('fleetbody_' + fleetid);

	if (x.style.display === "none") {
		x.style.display = "";
	} else {
		x.style.display = "none";
	}

	ajaxrequest('ship.php?B_TOGGLE_FLEET=1&fleet=' + fleetid);
}
function joinFleetInShiplist(fleetid) {

	chosenShipIdArray = [];

	Element.select($('availableShipsTable'), '.chosenShipsCheckbox').each(function (elem) {
		if (elem.checked) {
			chosenShipIdArray.push(elem.value);
		}
	});

	new Ajax.Updater('result', 'ship.php', {
		method: 'post',
		parameters: {
			'B_JOIN_FLEET': 1,
			'fleetid': fleetid,
			'chosen[]': chosenShipIdArray
		},
		evalScripts: true,
		onComplete: function (transport) {
			$('result').show();
		}
	});

	closeAjaxWindow();
}
function leaveFleetInShiplist(shipid, sessionstring) {
	new Ajax.Updater('result', 'ship.php', {
		method: 'get',
		parameters: 'B_LEAVE_FLEET=1&id=' + shipid + '&sstr=' + sessionstring,
		evalScripts: true,
		onComplete: function (transport) {
			$('result').show();
		}
	});
}
function refreshShiplistFleet(fleetid) {
	ajax_update('shiplist_fleet_form_' + fleetid, 'ship.php?SHOW_SHIPLIST_FLEET=1&fleetid=' + fleetid);
}
function refreshShiplistSingles() {
	ajax_update('shiplist_singles_table', 'ship.php?SHOW_SHIPLIST_SINGLES=1');
	$('shiplist_singles_table').show();
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
function maximizeCommodityAmounts() {
	var list = document.getElementsByClassName('commodityAmount');
	var n;
	for (n = 0; n < list.length; ++n) {
		list[n].value = 'max';
	}
}
function adjustCellHeight(image) {
	var cell = image.parentNode.parentNode;
	var height = image.offsetHeight;
	cell.style.height = height + 10 + 'px';
}
function adjustCellWidth(image) {
	var cell = image.parentNode.parentNode;
	var width = image.offsetWidth;
	var cellWidth = cell.offsetWidth;

	if (width > cellWidth) {
		cell.style.minWidth = width + 5 + 'px';
	}
	else { cell.style.minWidth = cellWidth + 'px'; }
}
function updateEPSSplitValue(value, reactoroutput, epsusage, flightcost, effektiveps, eps, maxeps, warpdrive, maxwarpdrive) {
	// Wandle reactoroutput, epsusage, flightcost und effektiveps in Integer um, falls sie keine gültigen Zahlen sind
	reactoroutput = parseInt(reactoroutput, 10);
	epsusage = parseInt(epsusage, 10);
	flightcost = parseInt(flightcost, 10);
	effektiveps = parseInt(effektiveps, 10);
	eps = parseInt(eps, 10);
	maxeps = parseInt(maxeps, 10);
	warpdrive = parseInt(warpdrive, 10);
	maxwarpdrive = parseInt(maxwarpdrive, 10);

	// Berechne die effektive EPS-Produktion und runde sie auf einen Integer
	const warpCoreSplit = parseInt(value);
	const EpsProduction = Math.round((reactoroutput - epsusage) * warpCoreSplit / 100);
	const EffektivEpsProduction = Math.round((reactoroutput - epsusage) * warpCoreSplit / 100);
	const WarpDriveProduction = Math.round((1 - (warpCoreSplit / 100)) * (reactoroutput - epsusage) / flightcost);
	document.getElementById('calculatedEPS').textContent = EpsProduction;
	document.getElementById('calculatedWarpDrive').textContent = WarpDriveProduction;


	// EPS ZUWACHS
	// Formatieren der effektiven EPS-Produktion basierend auf ihrem Wert
	let formattedEpsEffektiv;
	if (EpsProduction > 0) {
		if (EpsProduction <= (maxeps - eps)) {
			formattedEpsEffektiv = '+' + EpsProduction;
		}
		if (EpsProduction > (maxeps - eps)) {
			formattedEpsEffektiv = '+' + (maxeps - eps);
		}
	} else {
		formattedEpsEffektiv = String(EpsProduction);
	}

	// Überprüfen, ob der Wert die effektive EPS-Produktion nicht überschreitet
	document.getElementById('EffektivEPS').textContent = formattedEpsEffektiv;

	// WARP ZUWACHS
	if ((maxwarpdrive - warpdrive) > 0) {
		formatedWarpDifferenz = '+' + (maxwarpdrive - warpdrive);
	}
	else { formatedWarpDifferenz = String(maxwarpdrive - warpdrive); }

	if (WarpDriveProduction > 0) {
		formatedWarpDriveProduction = '+' + WarpDriveProduction;
	}
	else { formatedWarpDriveProduction = String(WarpDriveProduction); }

	if ((maxwarpdrive - warpdrive) < WarpDriveProduction) {
		document.getElementById('EffektivWarpDrive').textContent = formatedWarpDifferenz;
	}
	else { document.getElementById('EffektivWarpDrive').textContent = formatedWarpDriveProduction; }
}

var saveTimeout;

function saveWarpCoreSplit(value, shipId, successCallback) {
	clearTimeout(saveTimeout);

	saveTimeout = setTimeout(function () {
		new Ajax.Request('ship.php', {
			method: 'post',
			parameters: 'B_SPLIT_WARP_CORE_OUTPUT=1&id=' + shipId + '&value=' + value,
			evalScripts: true,
			onSuccess: function () {
				successCallback(value);
			},
		});
	}, 150);
}
