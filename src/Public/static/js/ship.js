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

function triggerBeamCrewFrom(target, targetIsColony) {
	isUnload = true;
	colonyParam = targetIsColony ? '&isColony=1' : '';
	directionParam = isUnload ? '&isUnload=1' : '';
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_TROOP_TRANSFER=1&target=' + target + colonyParam + directionParam);
}

function triggerBeamCrewTo(target, targetIsColony) {
	isUnload = false;
	colonyParam = targetIsColony ? '&isColony=1' : '';
	directionParam = isUnload ? '&isUnload=1' : '';
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_TROOP_TRANSFER=1&target=' + target + colonyParam + directionParam);
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
function showAstroEntryWindow(isSystem) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	isSystemParam = isSystem ? '&isSystem=1' : '&isSystem=0';
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_ASTRO_ENTRY=1' + isSystemParam);
}
function openStarMap(obj, shipId) {
	closeAjaxWindow();
	var pos = findObject(obj);
	openWindowPosition('elt', 1, 700, pos[0], pos[1]);
	ajax_update('elt', 'starmap.php?SHOW_STARMAP_POSITION=1&sid=' + shipId);
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

var reactorOutput = null;
var epsUsage = null;
var flightCost = null;
var currentEps = null;
var maxEps = null;
var currentWarpdrive = null;
var maxWarpdrive = null;

function setReactorSplitConstants(output, usage, cost, eps, meps, wd, mwd) {
	reactorOutput = output;
	epsUsage = usage;
	flightCost = cost;
	currentEps = eps;
	maxEps = meps;
	currentWarpdrive = wd;
	maxWarpdrive = mwd;
}

function updateEpsSplitValues(value) {

	// calculate absolute values
	const warpCoreSplit = parseInt(value);
	const warpDriveProduction = Math.round((1 - (warpCoreSplit / 100)) * (reactorOutput - epsUsage) / flightCost);
	const epsProduction = reactorOutput - (warpDriveProduction * flightCost);

	// set input labels
	document.getElementById('calculatedEPS').textContent = epsProduction > 0 ? '+' + epsProduction : String(epsProduction);
	document.getElementById('calculatedWarpDrive').textContent = warpDriveProduction > 0 ? '+' + warpDriveProduction : '0';

	// calculate effective values
	const warpdriveCap = Math.round((1 - (warpCoreSplit / 100)) * (reactorOutput - epsUsage) / flightCost);
	const epsCap = reactorOutput - (warpdriveCap * flightCost);

	const missingEps = maxEps - currentEps;
	const epsGrowthCap = epsCap - epsUsage
	const effEpsProduction = Math.min(missingEps, epsGrowthCap);

	const missingWarpdrive = maxWarpdrive - currentWarpdrive;
	const effWarpdriveProduction = Math.min(missingWarpdrive, warpdriveCap);

	// set effective labels
	document.getElementById('effectiveEps').textContent = effEpsProduction > 0 ? '+' + effEpsProduction : String(effEpsProduction);
	document.getElementById('effectiveWarpdrive').textContent = effWarpdriveProduction > 0 ? '+' + effWarpdriveProduction : String(effWarpdriveProduction);
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
