function showMemberRumpInfo(obj, userid, rumpid) {
	closeAjaxWindow();

	var pos = findObject(obj);
	openWindowPosition('elt', 1, 700, pos[0] - 400, pos[1]);
	ajax_update('elt', 'alliance.php?SHOW_MEMBER_RUMP_INFO=1&userid=' + userid + '&rumpid=' + rumpid);
}
