function openTradepostInfo(postId) {
	var elt = 'tradepostinfo';
	openPJsWin(elt, 1);
	ajax_update(elt, 'trade.php?postid=' + postId + '&SHOW_TRADEPOST_INFO=1');
}
function showTradeOfferByGood(postId, goodId) {
	var elt = 'tradegoodinfo';
	openPJsWin(elt, 1);
	ajax_update(elt, 'trade.php?postid=' + postId + '&SHOW_OFFER_GOOD=1&goodid=' + goodId);
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
function showLicenceMenu(postId) {
	var elt = 'tradelicencemenu';
	openWindowPosition(elt, 1, 300, 90, 250, 400);
	ajax_update(elt, 'trade.php?SHOW_LICENCE_MENU=1&postid=' + postId);
}
function showLicenceInfo(postId) {
	var elt = 'tradelicenceinfo';
	openWindowPosition(elt, 1, 300, 90, 250);
	ajax_update(elt, 'trade.php?SHOW_LICENCE_INFO=1&postid=' + postId);
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
function showTradeLicenceList(obj, postId) {
	var elt = 'licencelist';
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
	openWindowPosition(elt, 1, 800, 90, 60);
	ajax_update(elt, 'trade.php?SHOW_SHIPLIST=1&id=' + tradepostid);
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
