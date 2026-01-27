function showDockControl(element) {
	updatePopupAtElement(element, 'station.php?SHOW_DOCK_CONTROL=1&id=' + spacecraftid);
}
function addDockPrivilege() {
	var value = $('docktype').value;
	ajax_update('dockprivilegelist', 'station.php?B_ADD_DOCKPRIVILEGE=1&id=' + spacecraftid + "&type=" + $('docktype').value + "&target=" + $('docktarget_' + value).value + "&mode=" + $('dockmode').value + "&sstr=" + $('dock_sstr').value);
}
function deleteDockPrivilege(id, sstr) {
	ajax_update('dockprivilegelist', 'station.php?B_DELETE_DOCKPRIVILEGE=1&id=' + spacecraftid + "&privilegeid=" + id + "&sstr=" + sstr);
}
function toggleDockPmAutoRead(sstr) {
	ajaxrequest(`station.php?B_DOCK_PM_AUTO_READ=1&id=${spacecraftid}&sstr=${sstr}`);
}
function showStationCosts(obj, planid) {
	var pos = findObject(obj);
	updatePopup('station.php?SHOW_STATION_COSTS=1&id=' + spacecraftid + '&planid=' + planid,
		200, pos[0] + 360, pos[1] - 180, false
	);
}
function showStationInformation(obj, planid) {
	var pos = findObject(obj);
	updatePopup('station.php?SHOW_STATION_INFO=1&planid=' + planid,
		200, pos[0] + 210, pos[1] - 180, false
	);
} function showScrapWindow(element) {
	var pos = findObject(element);
	updatePopup('station.php?id=' + spacecraftid + '&SHOW_SCRAP_AJAX=1',
		300, pos[0] - 300, pos[1], false
	);
}

function getShipList(element) {
	updatePopupAtElement(element, 'station.php?id=' + spacecraftid + '&SHOW_STATION_SHIPLIST=1');
}

currentTab = false;
function showStationModuleSelector(id) {
	if (currentTab) {
		currentTab.hide();

		Element.select(currentTab, '.specialModuleRadio').each(function (elem) {
			elem.checked = false;
		});
	}
	$('selector_' + id).show();
	currentTab = $('selector_' + id);
}
