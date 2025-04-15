function showUserLock(userid) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', '/admin/?SHOW_USER_LOCK=1&id=' + userid);
}

function registerSystemEditorNavKeys(previousId, currentId, nextId) {
	document.addEventListener("keydown", (event) => {
		if (event.key === "ArrowLeft" && previousId > 0) {
			document.location.href = '?SHOW_SYSTEM=1&systemid=' + previousId;
		}
		if (event.key === "ArrowRight" && nextId > 0) {
			document.location.href = '?SHOW_SYSTEM=1&systemid=' + nextId;
		}
		if (event.key === "ArrowUp") {
			document.location.href = '?REGENERATE_SYSTEM=1&systemid=' + currentId;
		}
		if (event.key === "ArrowDown") {
			document.location.href = '?SHOW_SYSTEM=1&systemid=' + currentId;
		}
	});
}
