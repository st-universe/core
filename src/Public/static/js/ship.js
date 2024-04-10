var shipid = null;
var sstr = null;
function setShipIdAndSstr(id, sessionString) {
	shipid = id;
	sstr = sessionString;
}

function moveToPosition(posx, posy) {
	if (!posx || !posy || !sstr || !shipid) {
		return;
	}
	actionToInnerContent('B_MOVE', `id=${shipid}&posx=${posx}&posy=${posy}&sstr=${sstr}`);
}

function moveInDirection(action) {
	amount = document.shipform.navapp.value;
	actionToInnerContent(action, `id=${shipid}&navapp=${amount}&sstr=${sstr}`);
}

var lastPosition = '';

function focusNavApplet() {
	lastPosition = document.shipform.navapp.value;
	document.shipform.navapp.value = '';
}

function blurNavApplet() {
	if (document.shipform.navapp.value != '') {
		return;
	}
	document.shipform.navapp.value = lastPosition;
}

function showALvlWindow() {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_ALVL=1');
}

function showETransferWindow(target) {
	closeAjaxWindow();
	openWindow('elt', 1);
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_ETRANSFER=1&target=' + target);
}

function showAvailableShips(fleetid) {
	closeAjaxWindow();
	openWindow('elt', 1, 300);
	ajax_update('elt', 'ship.php?SHOW_AVAILABLE_SHIPS=1&fleetid=' + fleetid);
}

function showTransfer(targetId, transferTypeValue, isUnload, isColonyTarget, isReplace) {
	if (!isReplace) {
		closeAjaxWindow();
		openPJsWin('elt', 1);
	}

	isUnloadValue = isUnload ? 1 : 0;
	isColonyTargetValue = isColonyTarget ? 1 : 0;

	ajax_update('elt', `?SHOW_TRANSFER=1&id=${shipid}&target=${targetId}&is_unload=${isUnloadValue}&is_colony=${isColonyTargetValue}&transfer_type=${transferTypeValue}`);
}

function showSelfdestructWindow(target) {
	closeAjaxWindow();
	openWindow('elt', 1, 300);
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_SELFDESTRUCT_AJAX=1&target=' + target);
}
function showScrapWindow() {
	closeAjaxWindow();
	openWindow('elt', 1, 300);
	ajax_update('elt', 'station.php?id=' + shipid + '&SHOW_SCRAP_AJAX=1');
}
function showWebEmitterWindow() {
	closeAjaxWindow();
	openWindow('elt', 1, 300);
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_WEBEMITTER_AJAX=1');
}
function showScanWindow(shipid, target) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_SCAN=1&target=' + target);
}
function showSectorScanWindow(obj, x, y, sysid, loadSystemSensorScan) {
	closeAjaxWindow();
	var pos = findObject(obj);
	openWindowPosition('elt', 1, 800, pos[0] - 250, pos[1] - 250);
	if (x && y) {
		ajax_update('elt', 'station.php?id=' + shipid + '&SHOW_SENSOR_SCAN=1&cx=' + x + '&cy=' + y + '&sysid=' + sysid);
		if (loadSystemSensorScan) {
			ajax_update('systemsensorscan', 'station.php?id=' + shipid + '&SHOW_SYSTEM_SENSOR_SCAN=1&cx=' + x + '&cy=' + y);
		}
	} else {
		ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_SECTOR_SCAN=1');
	}
}
function showAstroEntryWindow(isSystem) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	isSystemParam = isSystem ? '&isSystem=1' : '&isSystem=0';
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_ASTRO_ENTRY=1' + isSystemParam);
}
function openStarMap(obj, shipId) {
	closeAjaxWindow();
	var pos = findObject(obj);
	openWindowPosition('elt', 1, 700, pos[0], pos[1]);
	ajax_update('elt', 'starmap.php?SHOW_STARMAP_POSITION=1&sid=' + shipId);
}

storageTimer = null;
function openStorageInit(obj, id) {
	closeAjaxWindow();
	storageTimer = setTimeout('openStorage(' + id + ')', 1000); //wait 1 second
	obj.onmouseout = function () { clearTimeout(storageTimer); } //remove timer
}
function openStorage(id) {
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?SHOW_SHIPSTORAGE=1&id=' + id);
}
function closeStorage() {
	closeAjaxWindow();
}
function showShiplistShip(id, title) {
	clearTimeout(storageTimer);
	switchInnerContent('SHOW_SHIP', title, `id=${id}`, 'ship.php');
}
function showShipDetails(id) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?SHOW_SHIPDETAILS=1&id=' + id);
}
function showShipCommunication(id) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?SHOW_SHIP_COMMUNICATION=1&id=' + id);
}
function openTradeMenu(postid) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?SHOW_TRADEMENU=1&id=' + shipid + '&postid=' + postid);
}
function tradeMenuChoosePayment(postid) {
	ajax_update('trademenucontent', 'ship.php?SHOW_TRADEMENU_CHOOSE_PAYMENT=1&id=' + shipid + "&postid=" + postid);
}
function payTradeLicense(postid, method, id) {
	ajax_update('trademenucontent', 'ship.php?B_PAY_TRADELICENSE=1&id=' + shipid + "&method=" + method + "&target=" + id + "&postid=" + postid + "&sstr=" + $('sstrajax').value);
}
function switchTransferFromAccount(postid) {
	ajax_update('trademenutransfer', 'ship.php?SHOW_TRADEMENU_TRANSFER=1&id=' + shipid + "&mode=from&postid=" + postid);
	$('transfertoaccount').removeClassName('selected');
	$('transferfromaccount').addClassName('selected');
}
function switchTransferToAccount(postid) {
	ajax_update('trademenutransfer', 'ship.php?SHOW_TRADEMENU_TRANSFER=1&id=' + shipid + "&mode=to&postid=" + postid);
	$('transferfromaccount').removeClassName('selected');
	$('transfertoaccount').addClassName('selected');
}
function switchMenuToBroadcast() {
	$('menuemergency').removeClassName('selected');
	$('menulogbook').removeClassName('selected');
	$('menubroadcast').addClassName('selected');

	document.getElementById('broadcast').style.display = "";
	document.getElementById('logbook').style.display = "none";
	document.getElementById('emergency').style.display = "none";
}
function switchMenuToLogbook() {
	$('menubroadcast').removeClassName('selected');
	$('menuemergency').removeClassName('selected');
	$('menulogbook').addClassName('selected');

	document.getElementById('logbook').style.display = "";
	document.getElementById('broadcast').style.display = "none";
	document.getElementById('emergency').style.display = "none";
}
function switchMenuToEmergency() {
	$('menubroadcast').removeClassName('selected');
	$('menulogbook').removeClassName('selected');
	$('menuemergency').addClassName('selected');

	document.getElementById('emergency').style.display = "";
	document.getElementById('broadcast').style.display = "none";
	document.getElementById('logbook').style.display = "none";
}
function switchScanToDetails() {
	$('menuScanLogbook').removeClassName('selected');
	$('menuScanDetails').addClassName('selected');

	document.getElementById('scandetails').style.display = "";
	document.getElementById('scanlogbook').style.display = "none";
}
function switchScanToLogbook() {
	$('menuScanDetails').removeClassName('selected');
	$('menuScanLogbook').addClassName('selected');

	document.getElementById('scanlogbook').style.display = "";
	document.getElementById('scandetails').style.display = "none";
}
function postLogEntry(shipid) {
	log = Form.Element.serialize('log');
	ajax_update('kncomments', "comm.php?B_POST_COMMENT=1&posting=" + postingId + "&" + comment);
}
function showRegionInfo(region) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?SHOW_REGION_INFO=1&id=' + shipid + '&region=' + region);
}
function showColonization(colonyId) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?SHOW_COLONIZATION=1&id=' + shipid + '&colid=' + colonyId);
}
function showColonyScan() {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?SHOW_COLONY_SCAN=1&id=' + shipid);
}
function showRepairOptions(shipid) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?id=' + shipid + '&SHOW_REPAIR_OPTIONS=1');
}

currentTab = false;
function showStationModuleSelector(id) {
	if (currentTab) {
		currentTab.hide();

		Element.select(currentTab, '.specialModuleRadio').each(function (elem) {
			elem.checked = false;
		});
	}
	$('selector_' + id).show();
	currentTab = $('selector_' + id);
}

function hideFleet(fleetid) {
	$('nbstab').select('.fleet' + fleetid).each(function (obj) {
		obj.hide();
	});
	$('hidefleet' + fleetid).hide();
	$('showfleet' + fleetid).show();
	$('fleetuser' + fleetid).show();
	ajaxrequest('ship.php?B_HIDE_FLEET=1&id=' + shipid + '&fleet=' + fleetid);
}
function showFleet(fleetid) {
	$('nbstab').select('.fleet' + fleetid).each(function (obj) {
		obj.show();
	});
	$('hidefleet' + fleetid).show();
	$('showfleet' + fleetid).hide();
	$('fleetuser' + fleetid).hide();
	ajaxrequest('ship.php?B_SHOW_FLEET=1&id=' + shipid + '&fleet=' + fleetid);
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
			'chosen[]': chosenShipIdArray
		},
		evalScripts: true,
		onSuccess: function (transport) {
			$('result').show();
		}
	});

	closeAjaxWindow();
}
function analysebuoy(id)
{
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'ship.php?id=' + id + '&SHOW_ANALYSE_BUOY=1');
}
function leaveFleetInShiplist(shipid, sessionstring) {
	new Ajax.Updater('result', 'ship.php', {
		method: 'get',
		parameters: 'B_LEAVE_FLEET=1&id=' + shipid + '&sstr=' + sessionstring,
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
function showFightLog() {
	openPJsWin('fightresult_content', 1);
	$('fightresult_content').innerHTML = $('fightlog').innerHTML;
}
function showRenameCrew(obj, crew_id) {
	obj.hide();
	$('rn_crew_' + crew_id + '_input').show();
}
function renameCrew(crew_id) {
	crewName = $('rn_crew_' + crew_id + '_value').value;
	if (crewName.length < 1) {
		$('rn_crew_' + crew_id).show();
		$('rn_crew_' + crew_id + '_input').hide();
		return;
	}
	ajax_update('rn_crew_' + crew_id, 'ship.php?B_RENAME_CREW=1&id=' + shipid + '&crewid=' + crew_id + '&' + Form.Element.serialize('rn_crew_' + crew_id + '_value'));
}
function adjustCellHeight(image) {
	var cell = image.parentNode.parentNode;
	var height = image.offsetHeight;
	cell.style.height = height + 10 + 'px';
}
function adjustCellWidth(image) {
	var cell = image.parentNode.parentNode;
	var width = image.offsetWidth;
	var cellWidth = cell.offsetWidth;

	if (width > cellWidth) {
		cell.style.minWidth = width + 5 + 'px';
	}
	else { cell.style.minWidth = cellWidth + 'px'; }
}

var reactorOutput = null;
var epsUsage = null;
var flightCost = null;
var missingEps = null;
var currentWarpdrive = null;
var maxWarpdrive = null;

function setReactorSplitConstants(output, usage, cost, meps, wd, mwd) {
	reactorOutput = output;
	epsUsage = usage;
	flightCost = cost;
	missingEps = meps;
	currentWarpdrive = wd;
	maxWarpdrive = mwd;
}

function updateReactorValues() {

	value = document.getElementById('warpdriveSplit').value;

	// calculate absolute values
	const warpdriveSplit = parseInt(value);
	const maxWarpdriveGain = Math.max(0, Math.floor((reactorOutput - epsUsage) / flightCost));
	const warpDriveProduction = Math.round((1 - (warpdriveSplit / 100)) * maxWarpdriveGain);
	const epsProduction = warpdriveSplit === 0 ? Math.min(reactorOutput, epsUsage) : reactorOutput - (warpDriveProduction * flightCost);

	// set input labels
	document.getElementById('calculatedEPS').textContent = epsProduction > 0 ? '+' + epsProduction : String(epsProduction);
	document.getElementById('calculatedWarpDrive').textContent = warpDriveProduction > 0 ? '+' + warpDriveProduction : '0';

	// calculate effective values
	let epsChange = epsProduction - epsUsage;
	let missingWarpdrive = maxWarpdrive - currentWarpdrive;
	let effEpsProduction = Math.min(missingEps, epsChange);
	let effWarpdriveProduction = Math.min(missingWarpdrive, warpDriveProduction);

	autoCarryOver = document.getElementById('autoCarryOver').checked;
	if (autoCarryOver) {
		excess = Math.max(0, reactorOutput - epsUsage - effEpsProduction - effWarpdriveProduction * flightCost);
		epsChange = epsProduction + excess - epsUsage;

		effEpsProduction = Math.min(missingEps, epsChange);
		effWarpdriveProduction = Math.min(missingWarpdrive, warpDriveProduction + Math.floor(excess / flightCost));
	}

	// set effective labels
	document.getElementById('effectiveEps').textContent = effEpsProduction > 0 ? '+' + effEpsProduction : String(effEpsProduction);
	document.getElementById('effectiveWarpdrive').textContent = effWarpdriveProduction > 0 ? '+' + effWarpdriveProduction : String(effWarpdriveProduction);
	document.getElementById('reactorUsage').textContent = epsUsage + effEpsProduction + (effWarpdriveProduction * flightCost);
}

var saveTimeout;

function saveWarpCoreSplit(shipId) {
	clearTimeout(saveTimeout);

	value = document.getElementById('warpdriveSplit').value;
	autoCarryOver = document.getElementById('autoCarryOver').checked ? 1 : 0;
	fleetSplit = document.getElementById('fleetSplit').checked ? 1 : 0;

	params = `B_SPLIT_REACTOR_OUTPUT=1&id=${shipId}&value=${value}&fleet=${fleetSplit}&autocarryover=${autoCarryOver}`;

	saveTimeout = setTimeout(function () {
		new Ajax.Updater('result', 'ship.php', {
			method: 'post',
			parameters: params,
			evalScripts: true,
			onSuccess: function () {
				$('result').show();
			},
		});
	}, 150);
}


let staticAmplitude = 0;
let staticWavelength = 0;
let dynamicAmplitude = 0;
let dynamicWavelength = 0;
let amplitudeStepSize = 1;
let wavelengthStepSize = 1;

function initialiseBuoyAnalysis(initialAmplitude, initialWavelength) {
    staticAmplitude = Math.round(initialAmplitude);
    staticWavelength = Math.round(initialWavelength);


	dynamicAmplitude = Math.round(staticAmplitude * (0.25 + Math.random()));

	dynamicWavelength = Math.round(staticWavelength * (0.25 + Math.random() ));


    amplitudeStepSize = calculateStepSize(dynamicAmplitude, staticAmplitude);
    wavelengthStepSize = calculateStepSize(dynamicWavelength, staticWavelength);

    drawSinusCurve();
    updateValueDisplays(); 
}

function calculateStepSize(currentValue, targetValue) {
    const maxSteps = 10; 
    const difference = Math.abs(currentValue - targetValue);
    return Math.ceil(difference / maxSteps); 
}

function drawSinusCurve() {
    const canvas = document.getElementById('buoyAnalysisCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const offsetX = 50;
    const offsetY = 50;
    const canvasWidth = canvas.width;
    const canvasHeight = canvas.height;
    const drawingWidth = 700;
    const drawingHeight = 300;

    ctx.clearRect(0, 0, canvasWidth, canvasHeight); 

    drawGrid(ctx, offsetX, offsetY, drawingWidth, drawingHeight);
    drawAxes(ctx, offsetX, offsetY, drawingWidth, drawingHeight);

    drawCurve(ctx, staticAmplitude, 5, '#ff0000', offsetX, offsetY, drawingWidth, drawingHeight); 
    drawCurve(ctx, dynamicAmplitude, dynamicWavelength / staticWavelength * 5, '#00ff00', offsetX, offsetY, drawingWidth, drawingHeight);
}

function drawGrid(ctx, offsetX, offsetY, width, height) {
    const gridColor = '#444444'; 
    const stepX = width / 20;
    const stepY = height / 10;

    ctx.beginPath();
    for (let x = 0; x <= width; x += stepX) {
        ctx.moveTo(offsetX + x, offsetY);
        ctx.lineTo(offsetX + x, offsetY + height);
    }
    for (let y = 0; y <= height; y += stepY) {
        ctx.moveTo(offsetX, offsetY + y);
        ctx.lineTo(offsetX + width, offsetY + y);
    }
    ctx.strokeStyle = gridColor;
    ctx.stroke();
}

function drawAxes(ctx, offsetX, offsetY, width, height) {
    ctx.beginPath();
    ctx.moveTo(offsetX, offsetY + height / 2);
    ctx.lineTo(offsetX + width, offsetY + height / 2); 
    ctx.moveTo(offsetX + width / 2, offsetY);
    ctx.lineTo(offsetX + width / 2, offsetY + height); 
    ctx.strokeStyle = '#ffffff';
    ctx.stroke();
}

function drawCurve(ctx, amplitude, wavelength, color, offsetX, offsetY, width, height) {
    ctx.beginPath();
    const scaledAmplitude = amplitude * (height / 2 * 0.9 / staticAmplitude);
    for (let x = 0; x <= width; x++) {
        const y = Math.sin((x / width) * wavelength * Math.PI * 2) * scaledAmplitude;
        ctx.lineTo(offsetX + x, offsetY + height / 2 - y);
    }
    ctx.strokeStyle = color;
    ctx.lineWidth = 2;
    ctx.stroke();
    ctx.lineWidth = 1; 
}

function updateValueDisplays() {
    document.getElementById("dynamicAmplitude").textContent = dynamicAmplitude;
    document.getElementById("dynamicWavelength").textContent = dynamicWavelength;
}

function increaseDynamicAmplitude() {
    let previousDifference = Math.abs(dynamicAmplitude - staticAmplitude);
    dynamicAmplitude += amplitudeStepSize;
    let newDifference = Math.abs(dynamicAmplitude - staticAmplitude);


    if (newDifference <= amplitudeStepSize && previousDifference > amplitudeStepSize) {
        dynamicAmplitude = staticAmplitude;
    }

    drawSinusCurve();
    updateValueDisplays();
    checkForMatch();
}

function decreaseDynamicAmplitude() {
    let previousDifference = Math.abs(dynamicAmplitude - staticAmplitude);
    dynamicAmplitude = Math.max(0, dynamicAmplitude - amplitudeStepSize);
    let newDifference = Math.abs(dynamicAmplitude - staticAmplitude);


    if (newDifference <= amplitudeStepSize && previousDifference > amplitudeStepSize) {
        dynamicAmplitude = staticAmplitude;
    }

    drawSinusCurve();
    updateValueDisplays();
    checkForMatch();
}

function increaseDynamicWavelength() {
    let previousDifference = Math.abs(dynamicWavelength - staticWavelength);
    dynamicWavelength += wavelengthStepSize;
    let newDifference = Math.abs(dynamicWavelength - staticWavelength);

 
    if (newDifference <= wavelengthStepSize && previousDifference > wavelengthStepSize) {
        dynamicWavelength = staticWavelength;
    }

    drawSinusCurve();
    updateValueDisplays();
    checkForMatch();
}

function decreaseDynamicWavelength() {
    let previousDifference = Math.abs(dynamicWavelength - staticWavelength);
    dynamicWavelength = Math.max(1, dynamicWavelength - wavelengthStepSize);
    let newDifference = Math.abs(dynamicWavelength - staticWavelength);

    if (newDifference <= wavelengthStepSize && previousDifference > wavelengthStepSize) {
        dynamicWavelength = staticWavelength;
    }

    drawSinusCurve();
    updateValueDisplays();
    checkForMatch();
}

function checkForMatch() {
    if (dynamicAmplitude === staticAmplitude && dynamicWavelength === staticWavelength) {
        document.getElementById('matchNotification').style.display = 'block'; 
    } else {
        document.getElementById('matchNotification').style.display = 'none'; 
    }
}

function updateSelectedShipId(shipId) {
    document.getElementById('selshipid').value = shipId;
}
