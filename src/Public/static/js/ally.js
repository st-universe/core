got = 0;
function showMap() {
	if (got == 1) {
		document.getElementById(elt).innerHTML = '<img src="backend/ally/shipmap.php" />';
		return;
	}
	got = 1;
	elt = 'shipposition';
	return overlib('<div id="shipposition" onClick="cl_win();"><img src="backend/ally/shipmap.php" /></div>', BGCOLOR, '#8897cf', TEXTCOLOR, '#8897cf', CELLPAD, 0, 0, 0, 0, EXCLUSIVE, ABOVE, LEFT, STICKY, DRAGGABLE, ALTCUT, WIDTH, 485);
}
function showPosition(cx, cy) {
	if (got == 1) {
		document.getElementById(elt).innerHTML = '<img src="backend/ally/shipmap.php?cx=' + cx + '&cy=' + cy + '" />';
		return;
	}
	got = 1;
	elt = 'shipposition';
	return overlib('<div id="shipposition" onClick="cl_win();"><img src="backend/ally/shipmap.php?cx=' + cx + '&cy=' + cy + '" /></div>', BGCOLOR, '#8897cf', TEXTCOLOR, '#8897cf', CELLPAD, 0, 0, 0, 0, EXCLUSIVE, ABOVE, LEFT, STICKY, DRAGGABLE, ALTCUT, WIDTH, 485);
}
function cl_win() {
	got = 0;
	cClick();
}
function getMemberSystems(sys) {
	elt = 'memsys';
	openSPJsWin(elt);
	sendRequest('backend/ally/membersystems.php?id=' + sys);
}
function getAllyRelationships(id) {
	elt = 'allyrel';
	openPJsWin(elt);
	sendRequest('backend/ally/relations.php?id=' + id);
}
function showMemberRumpInfo(obj, userid, rumpid) {
	closeAjaxWindow();

	var pos = findObject(obj);
	openWindowPosition('elt', 1, 700, pos[0] - 400, pos[1]);
	ajax_update('elt', 'alliance.php?SHOW_MEMBER_RUMP_INFO=1&userid=' + userid + '&rumpid=' + rumpid);
}
