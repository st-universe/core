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
	elt = 'alvl';
	openPJsWin(elt, 1);
	new Ajax.Updater(elt, 'ship.php?id=' + shipid + '&SHOW_ALVL=1');
}

function showETransferWindow(target) {
	elt = 'etrans';
	openWindow(elt, 1);
	new Ajax.Updater(elt, 'ship.php?id=' + shipid + '&SHOW_ETRANSFER=1&target=' + target);
}

function showBToWindow(target) {
	elt = 'beam'
	openPJsWin(elt, 1);
	new Ajax.Updater(elt, 'ship.php?id=' + shipid + '&SHOW_BEAMTO=1&target=' + target);
}

function showBFromWindow(target) {
	elt = 'beam'
	openPJsWin(elt, 1);
	new Ajax.Updater(elt, 'ship.php?id=' + shipid + '&SHOW_BEAMFROM=1&target=' + target);
}

function triggerBeamTo(target) {
	new Ajax.Updater(elt, 'ship.php?id=' + shipid + '&SHOW_BEAMTO=1&target=' + target);
}

function triggerBeamFrom(target) {
	new Ajax.Updater(elt, 'ship.php?id=' + shipid + '&SHOW_BEAMFROM=1&target=' + target);
}

function showBToColonyWindow(target) {
	elt = 'beam';
	openPJsWin(elt, 1);
	new Ajax.Updater(elt, 'ship.php?id=' + shipid + '&SHOW_COLONY_BEAMTO=1&target=' + target);
}

function showBFromColonyWindow(target) {
	elt = 'beam';
	openPJsWin(elt, 1, null, 400);
	new Ajax.Updater(elt, 'ship.php?id=' + shipid + '&SHOW_COLONY_BEAMFROM=1&target=' + target);
}

function showBTroopTransferWindow(target, targetIsColony, isUnload) {
	elt = 'troop';
	openPJsWin(elt, 1);
	colonyParam = targetIsColony ? '&isColony=1' : '';
	directionParam = isUnload ? '&isUnload=1' : '';
	new Ajax.Updater(elt, 'ship.php?id=' + shipid + '&SHOW_TROOP_TRANSFER=1&target=' + target + colonyParam + directionParam);
}

function triggerBeamToColony(target) {
	new Ajax.Updater(elt, 'ship.php?id=' + shipid + '&SHOW_COLONY_BEAMTO=1&target=' + target);
}

function triggerBeamFromColony(target) {
	new Ajax.Updater(elt, 'ship.php?id=' + shipid + '&SHOW_COLONY_BEAMFROM=1&target=' + target);
}

function showSelfdestructWindow(target) {
	elt = 'selfdestruct';
	openWindow(elt, 1, 300);
	ajax_update(elt, 'ship.php?id=' + shipid + '&SHOW_SELFDESTRUCT_AJAX=1&target=' + target);
}
function showScanWindow(shipid, target) {
	elt = 'scan';
	openPJsWin(elt, 1);
	new Ajax.Updater(elt, 'ship.php?id=' + shipid + '&SHOW_SCAN=1&target=' + target);
}
function showSectorScanWindow() {
	elt = 'sectorscan';
	openPJsWin(elt, 1, null, 400);
	ajax_update(elt, 'ship.php?id=' + shipid + '&SHOW_SECTOR_SCAN=1');
}
function openStarMap(obj, cx, cy) {
	var pos = findObject(obj);
	elt = 'starmap';
	openWindowPosition(elt, 1, 300, pos[0], pos[1]);
	ajax_update(elt, 'starmap.php?SHOW_STARMAP_POSITION=1&x=' + cx + '&y=' + cy);
}
function showMapBy(cx, cy) {
	elt = 'starmap';
	ajax_update(elt, 'starmap.php?SHOW_STARMAP_POSITION=1&x=' + cx + '&y=' + cy);
}
function showShipDetails(id) {
	elt = 'shipdetails'
	openPJsWin(elt, 1);
	new Ajax.Updater(elt, 'ship.php?id=' + shipid + '&SHOW_SHIPDETAILS=1&id=' + id);
}
function editDockPrivileges() {
	elt = 'dockprivileges';
	openPJsWin(elt, 1);
	ajax_update(elt, 'ship.php?SHOW_DOCKPRIVILEGE_CONFIG=1&id=' + shipid);
}
function addDockPrivilege() {
	var value = $('docktype').value;
	ajax_update('dockprivilegelist', 'ship.php?B_ADD_DOCKPRIVILEGE=1&id=' + shipid + "&type=" + $('docktype').value + "&target=" + $('docktarget_' + value).value + "&mode=" + $('dockmode').value + "&sstr=" + $('dock_sstr').value);
}
function deleteDockPrivilege(id, sstr) {
	ajax_update('dockprivilegelist', 'ship.php?B_DELETE_DOCKPRIVILEGE=1&id=' + shipid + "&privilegeid=" + id + "&sstr=" + sstr);
}
function openTradeMenu(postid) {
	elt = 'trademenu';
	openPJsWin(elt, 1);
	ajax_update(elt, 'ship.php?SHOW_TRADEMENU=1&id=' + shipid + '&postid=' + postid);
}
function tradeMenuChoosePayment(postid) {
	ajax_update('trademenucontent', 'ship.php?SHOW_TRADEMENU_CHOOSE_PAYMENT=1&id=' + shipid + "&postid=" + postid);
}
function payTradeLicence(postid, method, id) {
	ajax_update('trademenu', 'ship.php?B_PAY_TRADELICENCE=1&id=' + shipid + "&method=" + method + "&target=" + id + "&postid=" + postid + "&sstr=" + $('sstrajax').value);
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
	elt = 'regioninfo';
	openPJsWin(elt, 1);
	ajax_update(elt, 'ship.php?SHOW_REGION_INFO=1&id=' + shipid + '&region=' + region);
}
function showColonization(colonyId) {
	elt = 'colonization';
	openPJsWin(elt, 1);
	ajax_update(elt, 'ship.php?SHOW_COLONIZATION=1&id=' + shipid + '&colid=' + colonyId);
}
function showColonyScan() {
	elt = 'colonyscan';
	openPJsWin(elt, 1, null, 400);
	ajax_update(elt, 'ship.php?SHOW_COLONY_SCAN=1&id=' + shipid);
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
function showFightLog() {
	openPJsWin('fightresult_content', 1);
	$('fightresult_content').innerHTML = $('fightlog').innerHTML;
}
function showRenameCrew(obj, crew_id) {
	obj.hide();
	$('rn_crew_' + crew_id + '_input').show();
}
function renameCrew(crew_id) {
	name = $('rn_crew_' + crew_id + '_value').value;
	if (name.length < 1) {
		$('rn_crew_' + crew_id).show();
		$('rn_crew_' + crew_id + '_input').hide();
		return;
	}
	ajax_update('rn_crew_' + crew_id, 'ship.php?B_RENAME_CREW=1&id=' + shipid + '&crewid=' + crew_id + '&' + Form.Element.serialize('rn_crew_' + crew_id + '_value'));
}
