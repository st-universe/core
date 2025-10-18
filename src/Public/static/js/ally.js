function showMemberRumpInfo(obj, userid, rumpid) {
	var pos = findObject(obj);
	updatePopup('alliance.php?SHOW_MEMBER_RUMP_INFO=1&userid=' + userid + '&rumpid=' + rumpid,
		700, pos[0] - 400, pos[1]
	);
}

function showRelationText(relationid) {
	var viewportWidth = window.innerWidth || document.documentElement.clientWidth;
	var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
	var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
	var scrollTop = window.pageYOffset || document.documentElement.scrollTop;

	var posX = scrollLeft + (viewportWidth * 0.2);
	var posY = scrollTop + (viewportHeight * 0.2);

	updatePopup('alliance.php?SHOW_RELATION_TEXT=1&relationid=' + relationid, 650, posX, posY, false);
}

function editRelationText(relationid) {
	var viewportWidth = window.innerWidth || document.documentElement.clientWidth;
	var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
	var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
	var scrollTop = window.pageYOffset || document.documentElement.scrollTop;

	var posX = scrollLeft + (viewportWidth * 0.2);
	var posY = scrollTop + (viewportHeight * 0.2);

	updatePopup('alliance.php?EDIT_RELATION_TEXT=1&relationid=' + relationid, 650, posX, posY, false);
}
