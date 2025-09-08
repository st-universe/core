const ID = "popupWindow";
const CONTENT_ID = "popupContent";

function showPopup(width = null, posX = null, posY = null, relative = true) {

	const popup = document.getElementById(ID);
	popup.style.display = 'block';

	if (width) {
		popup.style.width = width + 'px';
	} else {
		popup.style.width = null;
	}

	if (posX === null || posY === null) {
		return;
	}

	if (relative) {
		var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
		var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

		posX += scrollLeft;
		posY += scrollTop;
	}

	popup.style.left = posX + 'px';
	popup.style.top = posY + 'px';
}
function hidePopup() {
	$(ID).style.display = 'none';
}

function enablePopupDrag() {

	const popup = document.getElementById(ID);
	const dragHandle = document.getElementById('popup-drag');
	let offsetX = 0, offsetY = 0, isDragging = false;

	dragHandle.addEventListener('mousedown', (e) => {
		isDragging = true;
		const rect = popup.getBoundingClientRect();
		offsetX = e.clientX - rect.left;
		offsetY = e.clientY - rect.top;
		document.body.style.userSelect = 'none';
	});

	document.addEventListener('mousemove', (e) => {
		if (!isDragging) return;
		const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
		const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

		popup.style.left = (e.clientX - offsetX + scrollLeft) + 'px';
		popup.style.top = (e.clientY - offsetY + scrollTop) + 'px';
	});

	document.addEventListener('mouseup', () => {
		isDragging = false;
		document.body.style.userSelect = '';
	});

	popupDragEnabled = true;
};

function updatePopupAtElement(element, url, width) {

	var posX = null;
	var posY = null;

	if (element) {
		var pos = findObject(element);
		posX = pos[0];
		posY = pos[1];
	} else {
		posX = window.scrollX;
		posY = window.scrollY;
	}

	updatePopup(url, width, posX, posY, false, element !== null);
}

function updatePopup(url, width = null, posX = null, posY = null, relative = true, hide = true) {
	if (hide) {
		hidePopup();
	}

	if (url) {
		new Ajax.Updater(CONTENT_ID, url, {
			onComplete: function (response) {
				enablePopupDrag();
				showPopup(width, posX, posY, relative);
			},
			method: "get",
			evalScripts: true
		});
	} else {
		enablePopupDrag();
		showPopup(width, posX, posY, relative);
	}
}

var isAjaxMandatory = false;

function setAjaxMandatory(isMandatory) {
	isAjaxMandatory = isMandatory;
}

var closeAjaxCallbacks = new Array();
var closeAjaxCallbacksMandatory = new Array();
var isClosingAjaxWindow = false;

function closeAjaxWindow() {
	if (isClosingAjaxWindow || isAjaxMandatory) {
		return;
	}
	isClosingAjaxWindow = true;

	for (index = 0; index < closeAjaxCallbacks.length; index++) {
		closeAjaxCallbacks[index]();
	}
	for (index = 0; index < closeAjaxCallbacksMandatory.length; index++) {
		closeAjaxCallbacksMandatory[index]();
	}

	clearAjaxCallbacks();
	closeAjaxCallbacksMandatory = new Array();

	hidePopup();
	isClosingAjaxWindow = false;
}

function clearAjaxCallbacks() {
	closeAjaxCallbacks = new Array();
}
