function showAvailableShips(fleetid) {
	closeAjaxWindow();
	openWindow('elt', 1, 300);
	ajax_update('elt', '?SHOW_AVAILABLE_SHIPS=1&fleetid=' + fleetid);
}

function tradeMenuChoosePayment(postid) {
	ajax_update('trademenucontent', 'ship.php?SHOW_TRADEMENU_CHOOSE_PAYMENT=1&id=' + spacecraftid + "&postid=" + postid);
}
function payTradeLicense(postid, method, id) {
	ajax_update('trademenucontent', 'ship.php?B_PAY_TRADELICENSE=1&id=' + spacecraftid + "&method=" + method + "&target=" + id + "&postid=" + postid + "&sstr=" + $('sstrajax').value);
}
function showColonization(colonyId) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?SHOW_COLONIZATION=1&id=' + spacecraftid + '&colid=' + colonyId);
}

function hideFleet(fleetid) {
	$('nbstab').select('.fleet' + fleetid).each(function (obj) {
		obj.hide();
	});
	$('hidefleet' + fleetid).hide();
	$('showfleet' + fleetid).show();
	$('fleetuser' + fleetid).show();
	ajaxrequest('ship.php?B_HIDE_FLEET=1&id=' + spacecraftid + '&fleet=' + fleetid);
}
function showFleet(fleetid) {
	$('nbstab').select('.fleet' + fleetid).each(function (obj) {
		obj.show();
	});
	$('hidefleet' + fleetid).show();
	$('showfleet' + fleetid).hide();
	$('fleetuser' + fleetid).hide();
	ajaxrequest('ship.php?B_SHOW_FLEET=1&id=' + spacecraftid + '&fleet=' + fleetid);
}
function toggleFleet(fleetid) {
	var x = document.getElementById('fleetbody_' + fleetid);

	if (x.style.display === "none") {
		x.style.display = "";
	} else {
		x.style.display = "none";
	}

	ajaxrequest('ship.php?B_TOGGLE_FLEET=1&fleet=' + fleetid);
}
function joinFleetInShiplist(fleetid) {

	chosenShipIdArray = [];

	Element.select($('availableShipsTable'), '.chosenShipsCheckbox').each(function (elem) {
		if (elem.checked) {
			chosenShipIdArray.push(elem.value);
		}
	});

	new Ajax.Updater('result', 'ship.php', {
		method: 'post',
		parameters: {
			'B_JOIN_FLEET': 1,
			'fleetid': fleetid,
			'chosen[]': chosenShipIdArray,
			'sstr': $('sstrajax').value
		},
		evalScripts: true,
		onSuccess: function (transport) {
			$('result').show();
		}
	});

	closeAjaxWindow();
}
function leaveFleetInShiplist(shipid) {
	new Ajax.Updater('result', 'ship.php', {
		method: 'get',
		parameters: 'B_LEAVE_FLEET=1&id=' + shipid,
		evalScripts: true,
		onSuccess: function (transport) {
			$('result').show();
		}
	});
}
function refreshShiplistFleet(fleetid) {
	ajax_update('shiplist_fleet_form_' + fleetid, 'ship.php?SHOW_SHIPLIST_FLEET=1&fleetid=' + fleetid);
}
function refreshShiplistSingles() {
	ajax_update('shiplist_singles_table', 'ship.php?SHOW_SHIPLIST_SINGLES=1');
	$('shiplist_singles_table').show();
}
