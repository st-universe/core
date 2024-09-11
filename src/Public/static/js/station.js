function showDockControl() {
	elt = 'dockcontrol';
	openPJsWin(elt, 1);
	ajax_update(elt, 'station.php?SHOW_DOCK_CONTROL=1&id=' + shipid);
}
function addDockPrivilege() {
	var value = $('docktype').value;
	ajax_update('dockprivilegelist', 'station.php?B_ADD_DOCKPRIVILEGE=1&id=' + shipid + "&type=" + $('docktype').value + "&target=" + $('docktarget_' + value).value + "&mode=" + $('dockmode').value + "&sstr=" + $('dock_sstr').value);
}
function deleteDockPrivilege(id, sstr) {
	ajax_update('dockprivilegelist', 'station.php?B_DELETE_DOCKPRIVILEGE=1&id=' + shipid + "&privilegeid=" + id + "&sstr=" + sstr);
}
function toggleDockPmAutoRead(sstr) {
	ajaxrequest(`station.php?B_DOCK_PM_AUTO_READ=1&id=${shipid}&sstr=${sstr}`);
}
function showStationCosts(obj, planid) {
	closeAjaxWindow();

	var pos = findObject(obj);
	openWindowPosition('elt', 1, 200, pos[0] + 360, pos[1] - 180);
	ajax_update('elt', 'station.php?SHOW_STATION_COSTS=1&id=' + shipid + '&pid=' + planid);
}
function showStationInformation(obj, planid) {
	closeAjaxWindow();

	var pos = findObject(obj);
	openWindowPosition('elt', 1, 200, pos[0] + 210, pos[1] - 180);
	ajax_update('elt', 'station.php?SHOW_STATION_INFO=1&pid=' + planid);
}
function getShipList() {
	closeAjaxWindow();
	openPJsWin('shiplist', 1);
	ajax_update('shiplist', 'station.php?id=' + shipid + '&SHOW_STATION_SHIPLIST=1');
}
