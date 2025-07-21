function showMemberRumpInfo(obj, userid, rumpid) {
	var pos = findObject(obj);
	updatePopup('alliance.php?SHOW_MEMBER_RUMP_INFO=1&userid=' + userid + '&rumpid=' + rumpid,
		700, pos[0] - 400, pos[1]
	);
}
