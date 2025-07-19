function showUserLock(userid) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', '/admin/?SHOW_USER_LOCK=1&id=' + userid);
}
