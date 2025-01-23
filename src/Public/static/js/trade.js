function openTradepostInfo(postId) {
	var elt = 'tradepostinfo';
	openPJsWin(elt, 1);
	ajax_update(elt, 'trade.php?postid=' + postId + '&SHOW_TRADEPOST_INFO=1');
}
function showTradeOfferByCommodity(postId, commodityId) {
	var elt = 'tradecommodityinfo';
	openPJsWin(elt, 1);
	ajax_update(elt, 'trade.php?postid=' + postId + '&SHOW_OFFER_COMMODITY=1&commodityid=' + commodityId);
}
function showTradeOfferMenu(storid) {
	var elt = 'tradeoffermenu';
	openPJsWin(elt, 1);
	ajax_update(elt, 'trade.php?SHOW_OFFER_MENU=1&storid=' + storid);
}
function showOfferMenuNewOffer(storid) {
	ajax_update('tradeoffermenucontent', 'trade.php?SHOW_OFFER_MENU_NEW_OFFER=1&storid=' + storid);
	$('tradeoffermenunewoffer').addClassName('selected');
	$('tradeoffermenutransfer').removeClassName('selected');
}
function showOfferMenuTransfer(storid) {
	ajax_update('tradeoffermenucontent', 'trade.php?SHOW_OFFER_MENU_TRANSFER=1&storid=' + storid);
	$('tradeoffermenutransfer').addClassName('selected');
	$('tradeoffermenunewoffer').removeClassName('selected');
}
function showLicenseMenu(postId) {
	var elt = 'tradelicensemenu';
	openWindowPosition(elt, 1, 300, 90, 250, 400);
	ajax_update(elt, 'trade.php?SHOW_LICENSE_MENU=1&postid=' + postId);
}
function showLicenseInfo(postId) {
	var elt = 'tradelicenseinfo';
	openWindowPosition(elt, 1, 300, 90, 250);
	ajax_update(elt, 'trade.php?SHOW_LICENSE_INFO=1&postid=' + postId);
}
function takeTradeOffer(offerid) {
	var elt = 'tradeoffer';
	openPJsWin(elt, 1);
	ajax_update(elt, 'trade.php?SHOW_TAKE_OFFER=1&offerid=' + offerid);
}
function changeSearchCommodity(id) {
	document.getElementById('commoditySelect').value = id;
}
function changeSearchTradepost(id) {
	document.getElementById('tradepostSelect').value = id;
}
function showTradeLicenseList(obj, postId) {
	var elt = 'licenselist';
	openWindowPosition(elt, 1, 300, 300, 250);
	ajax_update(elt, 'trade.php?SHOW_LICENSE_LIST=1&postid=' + postId);
}
function openShoutbox(networkid) {
	var elt = 'shoutbox';
	openWindowPosition(elt, 1, 800, 90, 60);
	ajax_update(elt, 'trade.php?SHOW_SHOUTBOX=1&network=' + networkid);
	setTimeout('refreshShoutbox()', 5000);
	setTimeout('startKeyObserver()', 1000);
}
function openShiplist(tradepostid) {
	var elt = 'shiplist';
	openWindowPosition(elt, 1, 300, 300, 250);
	ajax_update(elt, 'trade.php?SHOW_SHIPLIST=1&postid=' + tradepostid);
}
function startKeyObserver() {
	if (!$('shoutboxentry')) {
		return;
	}
	$('shoutboxentry').observe('keypress', function (event) {
		if (Event.KEY_RETURN == event.keyCode) {
			addShoutboxEntry();
		}
	});
}
function addShoutboxEntry() {
	if (!$('shoutboxentry')) {
		return;
	}
	obj = $('shoutboxentry');
	if (obj.value.length <= 0) {
		return;
	}
	ajaxPostUpdate('shoutbox_list', 'trade.php', 'B_ADD_SHOUTBOX_ENTRY=1&network=' + $('network').value + '&' + Form.Element.serialize('shoutboxentry'));
	obj.value = '';
}
function refreshShoutbox() {
	if (over == null) {
		return;
	}
	ajax_update('shoutbox_list', 'trade.php?SHOW_SHOUTBOX_LIST=1&network=' + $('network').value);
	setTimeout('refreshShoutbox()', 5000);
}
function calculatePirateProtectionDates(currentWrath, currentTimeout) {
	const prestigeInput = document.getElementById('prestigeInput');
	const prestigeValue = parseInt(prestigeInput.value);

	if (isNaN(prestigeValue) || prestigeValue <= 0) {
		document.getElementById('pirateProtectionDates').innerHTML = '';
		return;
	}

	const startDate = calculateStartDate(currentWrath, currentTimeout, prestigeValue);
	const endDate = calculateEndDate(currentWrath, currentTimeout, prestigeValue);

	document.getElementById('pirateProtectionDates').innerHTML = `<br />Für diesen Preis kann ich großzügigerweise eine Vereinbarung mit den Kazon treffen! Der Nichtangriffspakt wird vermutlich zwischen<br />${startDate} Uhr und<br />${endDate} Uhr auslaufen. <br /> <input type="submit" name="B_PIRATE_PROTECTION" value="Akzeptieren" class="button" />`;
}

function calculateStartDate(currentWrath, currentTimeout, prestigeValue) {
	const wrathFactor = currentWrath / 1000;
	const timeoutInSeconds = Math.max(1, ((1 / wrathFactor) ** 2) * (prestigeValue * 10368) * 0.95); // 1 Prestige = 2.88 Stunden = 10368 Sekunden
	let timestamp = timeoutInSeconds;

	if (currentTimeout !== null && currentTimeout > Date.now() / 1000) {
		timestamp += currentTimeout;
	} else {
		timestamp += Date.now() / 1000;
	}

	const endDate = new Date(timestamp * 1000);
	let stuDate = new Date(endDate);
	stuDate.setFullYear(stuDate.getFullYear() + 370);

	return stuDate.toLocaleString('de-DE', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
}

function calculateEndDate(currentWrath, currentTimeout, prestigeValue) {
	const wrathFactor = currentWrath / 1000;
	const timeoutInSeconds = Math.max(1, ((1 / wrathFactor) ** 2) * (prestigeValue * 10368) * 1.05);
	let timestamp = timeoutInSeconds;

	if (currentTimeout !== null && currentTimeout > Date.now() / 1000) {
		timestamp += currentTimeout;
	} else {
		timestamp += Date.now() / 1000;
	}

	const endDate = new Date(timestamp * 1000);
	let stuDate = new Date(endDate);
	stuDate.setFullYear(stuDate.getFullYear() + 370);

	return stuDate.toLocaleString('de-DE', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
}
