function onmousewheel(element, callback) {
	// @author    Andrea Giammarchi        [http://www.devpro.it/]
	// @license    MIT                 [http://www.opensource.org/licenses/mit-license.php]
	// @credits    Adomas Paltanavicius         [http://adomas.org/javascript-mouse-wheel/]

	function __onwheel(event) {
		var delta = 0;
		if (event.wheelDelta) {
			delta = event.wheelDelta / 120;
			if (window.opera) delta = -delta;
		} else if (event.detail) delta = -event.detail / 3;
		if (delta) callback.call(element, delta);
		if (event.preventDefault) event.preventDefault();
		event.returnValue = false;
		return false;
	}

	if (element.addEventListener && !window.opera)
		element.addEventListener("DOMMouseScroll", __onwheel, false);
	else
		element.onmousewheel = (function (base) {
			return function (evt) {
				if (!evt) evt = window.event;
				if (base) base.call(element, evt);
				return __onwheel(evt);
			};
		})(element.onmousewheel);
}

function kpListener(e) {
	if (!e) e = window.event; // Drecks IE
	if (e.keyCode == 27) {
		closeAjaxWindow();
	}
}
window.onkeydown = kpListener;

function ajaxrequest(url) {
	new Ajax.Request(url);
}

function ignoreUser(obj, userid) {
	ajax_update(obj, "/pm.php?B_IGNORE_USER=1&SHOW_IGNORE=1&recid=" + userid);
}
function addUserContact(obj, userid) {
	var contact = "selector_" + obj;
	if (!$(contact)) {
		mode = document.forms[0].elements[contact].value;
	} else {
		mode = $(contact).value;
	}
	ajax_update(
		obj,
		"/pm.php?B_ADD_CONTACT=1&recid=" +
		userid +
		"&clmode=" +
		mode +
		"&cldiv=" +
		obj
	);
}
var clmodeswitchdiv = "";
function showContactModeSwitcher(div, contactid) {
	clmodeswitchdiv = div;
	updatePopup(
		"/pm.php?SHOW_CONTACT_MODESWITCH=1&contactid=" + contactid,
		70
	);
}
function switchContactMode(contactid, mode) {
	ajax_update(
		clmodeswitchdiv,
		"/pm.php?B_CHANGE_CONTACTMODE=1&cid=" +
		contactid +
		"&clmode=" +
		mode +
		"&cldiv=" +
		clmodeswitchdiv
	);
	clmodeswitcdiv = "";
	hidePopup();
}

function startUp() {
	startServerTimer();
}
var servertime = 0;
function startServerTimer() {

	element = $("servertime");
	if (!element) {
		return;
	}

	if (servertime == 0) {
		servertime = new Date(element.innerHTML);
	}
	var hours = servertime.getHours();
	var minutes = servertime.getMinutes();
	var seconds = servertime.getSeconds();
	servertime.setSeconds(seconds + 1);
	if (hours <= 9) hours = "0" + hours;
	if (minutes <= 9) minutes = "0" + minutes;
	if (seconds <= 9) seconds = "0" + seconds;
	dispTime = hours + ":" + minutes + ":" + seconds;
	element.innerHTML = dispTime;
	element.show();
	setTimeout("startServerTimer()", 1000);
} function ajax_update(elt, url) {
	new Ajax.Updater(elt, url, {
		method: "get",
		evalScripts: true,
		onComplete: function () {
			if (typeof initTooltips === 'function') {
				initTooltips();
			}
		}
	});
}

function findObject(obj) {
	var curleft = (curtop = 0);
	if (obj.offsetParent) {
		do {
			curleft += obj.offsetLeft;
			curtop += obj.offsetTop;
		} while ((obj = obj.offsetParent));
		return [curleft, curtop];
	}
}
function openTopicSettings(obj, tid, bid) {
	var pos = findObject(obj);

	updatePopup(
		"alliance.php?SHOW_TOPIC_SETTINGS=1&topicid=" + tid + "&boardid=" + bid,
		200, pos[0] - 200, pos[1]
	);
}
function openBoardSettings(obj, bid) {
	var pos = findObject(obj);
	updatePopup("alliance.php?SHOW_BOARD_SETTINGS=1&boardid=" + bid,
		200, pos[0] - 200, pos[1]
	);
}
function openPmWindow(fromId, toId, fromType, toType) {
	updatePopup(
		"/pm.php?SHOW_WRITE_QUICKPM=1&fromid=" +
		fromId +
		"&toid=" +
		toId +
		"&fromtype=" +
		fromType +
		"&totype=" +
		toType,
		600, 90, 60
	);
}
function sendQuickPM(userId) {
	var elem = $("quickpm").serialize() + "&sstr=" + $("pm_sstr").value;
	ajaxPost("/pm.php", "B_WRITE_PM=1&recipient=" + userId + "&" + elem);
	$("quickpm_compose").hide();
	$("quickpm_done").show();
}
function ajaxPostUpdate(destelement, url, elements) {
	new Ajax.Updater(destelement, url, {
		method: "post",
		parameters: elements,
	});
}
function ajaxPost(url, elements) {
	new Ajax.Request(url, {
		method: "post",
		parameters: elements,
	});
}
function showResearchDetails(researchId) {
	updatePopup("/research.php?SHOW_RESEARCH=1&id=" + researchId);
}
function openNotes() {
	str = "notes.php";
	Win = window.open(
		str,
		"WinNotes",
		"width=850,height=700,resizeable=no,location=no,scrollbars=yes,status=no"
	);
	Win.opener = self;
}
function goToUrl(url) {
	window.location.href = url;
}
function openNewTab(url) {
	window.open(url, "_blank");
}
function toggleTableRowVisible(id) {
	if ($(id).style.display == "block" || $(id).style.display == "table-row") {
		$(id).style.display = "none";
		return;
	}
	$(id).style.display = "table-row";
}
function cp(obj, file, ending = "png") {
	document.images[obj].src = gfx_path + "/" + file + "." + ending;
}

function updateComponent(id, url, refreshInterval) {
	if (refreshInterval) {
		setTimeout(`ajax_update('${id}', '${url}')`, refreshInterval);
		setTimeout(
			`updateComponent('${id}', '${url}', ${refreshInterval})`,
			refreshInterval
		);
	} else {
		ajax_update(id, url);
	}
}

function toggleVisible(id) {
	if ($(id).style.display == "block") {
		$(id).style.display = "none";
		return;
	}
	$(id).style.display = "block";
}
function showAchievement(text) {
	var elem = new Element("div");
	$(elem).addClassName("achievementbox box boxshadow");
	var header = new Element("div");
	$(header).addClassName("box_title");
	$(header).innerHTML = "Neue Errungenschaft!";
	$(elem).appendChild(header);
	var body = new Element("div");
	$(body).addClassName("box_body");
	$(body).innerHTML = text;
	$(elem).appendChild(body);
	var close = new Element("div");
	$(close).addClassName("closebutton");
	$(close).innerHTML = "X";
	$(close).observe("click", function () {
		this.up().classList.add('fade-out');
	});
	$(close).addClassName("action");
	$(elem).appendChild(close);
	document.body.appendChild(elem);
	elem.show();
}
function nodelistToString(list) {
	if (!RadioNodeList.prototype.isPrototypeOf(list)) {
		if (list.checked) {
			return list.value;
		} else {
			return "";
		}
	}
	return Array.from(list)
		.filter(function (node) {
			if (node.checked) {
				return true;
			}
			return false;
		})
		.map(function (node) {
			return node.value;
		})
		.join(",");
}
function snafu(hostId, hostType, action, mode, sstr) {
	commodityId = $("commodityselector").getValue();
	goToUrl(
		"/colony.php?id=" +
		hostId +
		"&hosttype=" +
		hostType +
		"&" +
		action +
		"=1&mode=" +
		mode +
		"&selection=" +
		commodityId +
		"&sstr=" +
		sstr
	);
}
function togglePanel(panelId) {
	var panel = document.getElementById(panelId);
	if (panel.style.display === "none") {
		panel.style.display = "block";
		loadImages(panel);
	} else {
		panel.style.display = "none";
	}
}
function loadImages(panel) {
	var images = panel.querySelectorAll("img[data-src]");
	images.forEach(function (image) {
		image.src = image.getAttribute("data-src");
		image.removeAttribute("data-src");
	});
}

function initTranslations() {
	let translatableSpans = document.querySelectorAll(".translatable-content");

	function replaceTranslateContent(spanElement) {
		let content = spanElement.innerHTML;
		let regex =
			/\[translate\]([\s\S]*?)\[translation\]([\s\S]*?)\[\/translate\]/g;

		let newContent = content.replace(regex, function (match, p1, p2) {
			return `<span class="translatable" data-original="${p1}" data-translation="${p2}">${p1}</span>`;
		});

		spanElement.innerHTML = newContent;
	}

	translatableSpans.forEach((span) => replaceTranslateContent(span));
	translatableSpans.forEach((span) => {
		span.addEventListener("click", function (event) {
			let clickedElement = event.target;

			if (clickedElement.classList.contains("translatable")) {
				let originalContent = clickedElement.getAttribute("data-original");
				let translatedContent = clickedElement.getAttribute("data-translation");

				if (originalContent && translatedContent) {
					if (clickedElement.innerHTML === translatedContent) {
						clickedElement.innerHTML = originalContent;
					} else {
						clickedElement.innerHTML = translatedContent;
					}
				}
			}
		});
	});
}

let allTranslated = false;

function toggleAll(boxBodyElement) {
	let translationSpan = boxBodyElement.querySelector(".translatable-content");

	if (translationSpan) {
		let translatableSections =
			translationSpan.querySelectorAll(".translatable");
		translatableSections.forEach((span) => {
			let originalContent = span.getAttribute("data-original");
			let translatedContent = span.getAttribute("data-translation");

			if (originalContent && translatedContent) {
				if (span.innerHTML === translatedContent) {
					span.innerHTML = originalContent;
				} else {
					span.innerHTML = translatedContent;
				}
			}
		});
	}
}
function toggleTranslation(targetId) {
	let translationSpan = document.getElementById(targetId);
	if (translationSpan) {
		let translatableSections =
			translationSpan.querySelectorAll(".translatable");
		translatableSections.forEach((span) => {
			let originalContent = span.getAttribute("data-original");
			let translatedContent = span.getAttribute("data-translation");
			if (originalContent && translatedContent) {
				if (span.innerHTML === translatedContent) {
					span.innerHTML = originalContent;
				} else {
					span.innerHTML = translatedContent;
				}
			}
		});
	}
}

function deleteColonyScan(id) {
	ajaxrequest("database.php?B_DELETE_COLONY_SCAN=1&id=" + id);
	document.getElementById(`colonyScan_${id}`).remove();
}

function switchView(view, title, url) {
	switchInnerContent("B_SWITCH_VIEW", title, `view=${view}`, "/game.php", url);
}

function actionToInnerContent(action, params, title, page) {
	switchInnerContent(action, title, params, page);
}

var isUpdateInProgress = false; function switchInnerContent(view, title, params, page, stateUrl) {
	if (isUpdateInProgress) {
		return;
	}
	isUpdateInProgress = true;

	if (isTutorial) {
		clearTutorial();
	}

	closeAjaxWindow();

	url = `?${view}=1`;
	if (page) {
		url = page + url;
	}

	if (params) {
		url += `&${params}`;
	}

	switchUrl = url + "&switch=1";

	new Ajax.Updater("innerContent", switchUrl, {
		onComplete: function (response) {
			isUpdateInProgress = false;

			if (400 == response.status) {
				window.location.href = "/index.php";
				return;
			}
			if (title) {
				let doc = new DOMParser().parseFromString(title, "text/html");
				document.title = doc.body.textContent || "";
			}
			window.history.pushState(null, title, stateUrl ?? url);
			if (page) {
				window.scrollTo(0, 0);
			}

			if (typeof initTooltips === 'function') {
				initTooltips();
			}

		},
		method: "get",
		evalScripts: true,
	});
}

function showTransfer(
	element,
	sourceId,
	sourceType,
	targetId,
	targetType,
	transferTypeValue,
	isUnload
) {
	isUnloadValue = isUnload ? 1 : 0;

	var posX = null;
	var posY = null;

	if (element) {
		var pos = findObject(element);
		posX = pos[0];
		posY = pos[1];
	}

	updatePopup(
		`?SHOW_TRANSFER=1&id=${sourceId}&source_type=${sourceType}&target=${targetId}&target_type=${targetType}&transfer_type=${transferTypeValue}&is_unload=${isUnloadValue}`,
		null, posX, posY, false, element !== null
	);
}

function maximizeCommodityAmounts() {
	document.querySelectorAll(".commodityAmount").forEach(function (elem) {
		elem.value = "max";
	});
}

document.addEventListener("DOMContentLoaded", function () {
	var previewElement = document.getElementById("preview");
	if (previewElement) {
		initTranslations();
	}
});

/** ASYNCHRONOUS LOADING OF JAVASCRIPT FILES*/
const loadScript = (FILE_URL, async = true, type = "text/javascript") => {
	return new Promise((resolve, reject) => {
		try {
			const scriptEle = document.createElement("script");
			scriptEle.type = type;
			scriptEle.async = async;
			scriptEle.src = FILE_URL;

			scriptEle.addEventListener("load", (ev) => {
				resolve({ status: true });
			});

			scriptEle.addEventListener("error", (ev) => {
				reject({
					status: false,
					message: `Failed to load the script ${FILE_URL}`,
				});
			});

			document.body.appendChild(scriptEle);
		} catch (error) {
			reject(error);
		}
	});
};

var loadedScripts = new Set();

function appendJsAsync(path, callback) {

	if (loadedScripts.has(path)) {
		if (callback) {
			callback();
		}
		return;
	}

	loadScript(path)
		.then((data) => {
			loadedScripts.add(path);
			if (callback) {
				callback();
			}
		})
		.catch((err) => {
			console.error(err);
		});
}

document.addEventListener('DOMContentLoaded', () => {
	initTooltips();
}); function initTooltips() {

	if (typeof tippy !== "function") {
		return;
	}

	const existingElements = document.querySelectorAll('[data-tippy-initialized="true"]');
	existingElements.forEach(element => {
		if (element._tippy) {
			element._tippy.destroy();
		}
		element.removeAttribute('data-tippy-initialized');
	});

	let lastInstance = null;

	const instances = tippy('[data-tippy-content]:not([data-tippy-initialized])', {
		allowHTML: true,
		interactive: true,
		arrow: true,
		followCursor: true,
		placement: 'bottom',
		theme: 'light-border',
		delay: [0, 0],
		duration: [0, 0],
		moveTransition: '',
		onShow(instance) {
			if (lastInstance && lastInstance !== instance) {
				lastInstance.hide();
			}
			lastInstance = instance;
		},
		popperOptions: {
			modifiers: [
				{
					name: 'offset',
					options: {
						offset: [0, 20],
					},
				},
			],
		}
	});

	instances.forEach(instance => {
		instance.reference.setAttribute('data-tippy-initialized', 'true');
		instance.reference.addEventListener('mouseleave', () => {
			instance.hide();
		});
	});
}
