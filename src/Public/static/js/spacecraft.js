var spacecraftid = null;
var sstr = null;
function setSpacecraftIdAndSstr(id, sessionString) {
  spacecraftid = id;
  sstr = sessionString;
}

function moveToPosition(posx, posy) {
  if (!posx || !posy || !sstr || !spacecraftid) {
    return;
  }
  actionToInnerContent(
    "B_MOVE",
    `id=${spacecraftid}&posx=${posx}&posy=${posy}&sstr=${sstr}`
  );
}

function moveInDirection(action) {
  amount = document.shipform.navapp.value;
  actionToInnerContent(
    action,
    `id=${spacecraftid}&navapp=${amount}&sstr=${sstr}`
  );
}

var lastPosition = "";

function focusNavApplet() {
  lastPosition = document.shipform.navapp.value;
  document.shipform.navapp.value = "";
}

function blurNavApplet() {
  if (document.shipform.navapp.value != "") {
    return;
  }
  document.shipform.navapp.value = lastPosition;
}

function showALvlWindow() {
  closeAjaxWindow();
  openPJsWin("elt", 1);
  ajax_update("elt", "?id=" + spacecraftid + "&SHOW_ALVL=1");
}

function showETransferWindow(target) {
  closeAjaxWindow();
  openWindow("elt", 1);
  ajax_update(
    "elt",
    "?id=" + spacecraftid + "&SHOW_ETRANSFER=1&target=" + target
  );
}

function showSelfdestructWindow(target) {
  closeAjaxWindow();
  openWindow("elt", 1, 300);
  ajax_update(
    "elt",
    "?id=" + spacecraftid + "&SHOW_SELFDESTRUCT_AJAX=1&target=" + target
  );
}
function showScanWindow(spacecraftid, target) {
  closeAjaxWindow();
  openPJsWin("elt", 1);
  ajax_update("elt", "?id=" + spacecraftid + "&SHOW_SCAN=1&target=" + target);
}
function showSectorScanWindow(obj, x, y, sysid, loadSystemSensorScan) {
  closeAjaxWindow();
  var pos = findObject(obj);
  openWindowPosition("elt", 1, 800, pos[0] - 250, pos[1] - 250);
  if (x && y) {
    ajax_update(
      "elt",
      "station.php?id=" +
      spacecraftid +
      "&SHOW_SENSOR_SCAN=1&x=" +
      x +
      "&y=" +
      y +
      "&systemid=" +
      sysid
    );
    if (loadSystemSensorScan) {
      ajax_update(
        "systemsensorscan",
        "station.php?id=" +
        spacecraftid +
        "&SHOW_SYSTEM_SENSOR_SCAN=1&x=" +
        x +
        "&y=" +
        y
      );
    }
  } else {
    ajax_update("elt", "?id=" + spacecraftid + "&SHOW_SECTOR_SCAN=1");
  }
}
function openStarMap(obj, id) {
  closeAjaxWindow();
  var pos = findObject(obj);
  openWindowPosition("elt", 1, 700, pos[0], pos[1]);
  ajax_update("elt", "starmap.php?SHOW_STARMAP_POSITION=1&id=" + id);
}

storageTimer = null;
function openStorageInit(obj, id) {
  closeAjaxWindow();
  storageTimer = setTimeout("openStorage(" + id + ")", 1000); //wait 1 second
  closeAjaxCallbacks.push(() => {
    clearTimeout(storageTimer);
  });
  obj.onmouseout = function () {
    clearTimeout(storageTimer);
  }; //remove timer
}
function openStorage(id) {
  openPJsWin("elt", 1);
  ajax_update("elt", "?SHOW_SPACECRAFTSTORAGE=1&id=" + id);
}
function closeStorage() {
  closeAjaxWindow();
}
function showSpacecraftDetails(id) {
  closeAjaxWindow();
  openPJsWin("elt", 1);
  ajax_update("elt", "?SHOW_SPACECRAFTDETAILS=1&id=" + id);
}
function showCommunication(id) {
  closeAjaxWindow();
  openPJsWin("elt", 1);
  ajax_update("elt", "?SHOW_SPACECRAFT_COMMUNICATION=1&id=" + id);
}
function openTradeMenu(postid) {
  closeAjaxWindow();
  openPJsWin("elt", 1);
  ajax_update(
    "elt",
    "?SHOW_TRADEMENU=1&id=" + spacecraftid + "&postid=" + postid
  );
}
function openWasteMenu() {
  closeAjaxWindow();
  openPJsWin("elt", 1);
  ajax_update(
    "elt",
    "?SHOW_WASTEMENU=1&id=" + spacecraftid
  );
}
function switchTransferFromAccount(postid) {
  ajax_update(
    "trademenutransfer",
    "?SHOW_TRADEMENU_TRANSFER=1&id=" +
    spacecraftid +
    "&mode=from&postid=" +
    postid
  );
  $("transfertoaccount").removeClassName("selected");
  $("transferfromaccount").addClassName("selected");
}
function switchTransferToAccount(postid) {
  ajax_update(
    "trademenutransfer",
    "?SHOW_TRADEMENU_TRANSFER=1&id=" +
    spacecraftid +
    "&mode=to&postid=" +
    postid
  );
  $("transferfromaccount").removeClassName("selected");
  $("transfertoaccount").addClassName("selected");
}
function switchMenuToBroadcast() {
  $("menuemergency").removeClassName("selected");
  $("menulogbook").removeClassName("selected");
  $("menubroadcast").addClassName("selected");

  document.getElementById("broadcast").style.display = "";
  document.getElementById("logbook").style.display = "none";
  document.getElementById("emergency").style.display = "none";
}
function switchMenuToLogbook() {
  $("menubroadcast").removeClassName("selected");
  $("menuemergency").removeClassName("selected");
  $("menulogbook").addClassName("selected");

  document.getElementById("logbook").style.display = "";
  document.getElementById("broadcast").style.display = "none";
  document.getElementById("emergency").style.display = "none";
}
function switchMenuToEmergency() {
  $("menubroadcast").removeClassName("selected");
  $("menulogbook").removeClassName("selected");
  $("menuemergency").addClassName("selected");

  document.getElementById("emergency").style.display = "";
  document.getElementById("broadcast").style.display = "none";
  document.getElementById("logbook").style.display = "none";
}
function switchScanToDetails() {
  $("menuScanLogbook").removeClassName("selected");
  $("menuScanDetails").addClassName("selected");

  document.getElementById("scandetails").style.display = "";
  document.getElementById("scanlogbook").style.display = "none";
}
function switchScanToLogbook() {
  $("menuScanDetails").removeClassName("selected");
  $("menuScanLogbook").addClassName("selected");

  document.getElementById("scanlogbook").style.display = "";
  document.getElementById("scandetails").style.display = "none";
}
function showRegionInfo(region) {
  closeAjaxWindow();
  openPJsWin("elt", 1);
  ajax_update(
    "elt",
    "?SHOW_REGION_INFO=1&id=" + spacecraftid + "&regionid=" + region
  );
}
function showColonyScan() {
  closeAjaxWindow();
  openPJsWin("elt", 1);
  ajax_update("elt", "?SHOW_COLONY_SCAN=1&id=" + spacecraftid);
}
function showRepairOptions(spacecraftid) {
  closeAjaxWindow();
  openPJsWin("elt", 1);
  ajax_update("elt", "?id=" + spacecraftid + "&SHOW_REPAIR_OPTIONS=1");
}

function analysebuoy(buoyId) {
  closeAjaxWindow();
  openPJsWin("elt", 1);
  ajax_update("elt", `?SHOW_ANALYSE_BUOY=1&id=${spacecraftid}&buoyid=${buoyId}`);
}
function showFightLog() {
  openPJsWin("fightresult_content", 1);
  $("fightresult_content").innerHTML = $("fightlog").innerHTML;
}
function showRenameCrew(obj, crew_id) {
  obj.hide();
  $("rn_crew_" + crew_id + "_input").show();
}
function renameCrew(crew_id) {
  crewName = $("rn_crew_" + crew_id + "_value").value;
  if (crewName.length < 1) {
    $("rn_crew_" + crew_id).show();
    $("rn_crew_" + crew_id + "_input").hide();
    return;
  }
  ajax_update(
    "rn_crew_" + crew_id,
    `?B_RENAME_CREW=1&id=${spacecraftid}&crewid=${crew_id}&`
    + Form.Element.serialize("rn_crew_" + crew_id + "_value")
  );
}
function adjustCellHeight(image) {
  var cell = image.parentNode.parentNode;
  var height = image.offsetHeight;
  cell.style.height = height + 10 + "px";

  var graphics = cell.querySelectorAll(".indexedGraphics");
  graphics.forEach(function (graphic) {
    graphic.style.marginTop = -(height / 2) + "px";
  });

  var graphics = cell.querySelectorAll(".indexedGraphicsShips");
  graphics.forEach(function (graphic) {
    graphic.style.marginTop = -(height / 2) + "px";
  });



}
function adjustCellWidth(image) {
  var cell = image.parentNode.parentNode;
  var width = image.offsetWidth;
  var cellWidth = cell.offsetWidth;

  if (width > cellWidth) {
    cell.style.minWidth = width + 5 + "px";
  } else {
    cell.style.minWidth = cellWidth + "px";
  }
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
  value = document.getElementById("warpdriveSplit").value;

  // calculate absolute values
  const warpdriveSplit = parseInt(value);
  const maxWarpdriveGain = Math.max(
    0,
    Math.floor((reactorOutput - epsUsage) / flightCost)
  );
  const warpDriveProduction = Math.round(
    (1 - warpdriveSplit / 100) * maxWarpdriveGain
  );
  const epsProduction =
    warpdriveSplit === 0
      ? Math.min(reactorOutput, epsUsage)
      : reactorOutput - warpDriveProduction * flightCost;

  // set input labels
  document.getElementById("calculatedEPS").textContent =
    epsProduction > 0 ? "+" + epsProduction : String(epsProduction);
  document.getElementById("calculatedWarpDrive").textContent =
    warpDriveProduction > 0 ? "+" + warpDriveProduction : "0";

  // calculate effective values
  let epsChange = epsProduction - epsUsage;
  let missingWarpdrive = maxWarpdrive - currentWarpdrive;
  let effEpsProduction = Math.min(missingEps, epsChange);
  let effWarpdriveProduction = Math.min(missingWarpdrive, warpDriveProduction);

  autoCarryOver = document.getElementById("autoCarryOver").checked;
  if (autoCarryOver) {
    excess = Math.max(
      0,
      reactorOutput -
      epsUsage -
      effEpsProduction -
      effWarpdriveProduction * flightCost
    );
    epsChange = epsProduction + excess - epsUsage;

    effEpsProduction = Math.min(missingEps, epsChange);
    effWarpdriveProduction = Math.min(
      missingWarpdrive,
      warpDriveProduction + Math.floor(excess / flightCost)
    );
  }

  // set effective labels
  document.getElementById("effectiveEps").textContent =
    effEpsProduction > 0 ? "+" + effEpsProduction : String(effEpsProduction);
  document.getElementById("effectiveWarpdrive").textContent =
    effWarpdriveProduction > 0
      ? "+" + effWarpdriveProduction
      : String(effWarpdriveProduction);
  document.getElementById("reactorUsage").textContent =
    epsUsage + effEpsProduction + effWarpdriveProduction * flightCost;
}

var saveTimeout;

function saveWarpCoreSplit(shipId) {
  clearTimeout(saveTimeout);

  value = document.getElementById("warpdriveSplit").value;
  autoCarryOver = document.getElementById("autoCarryOver").checked ? 1 : 0;
  fleetSplit = document.getElementById("fleetSplit").checked ? 1 : 0;

  params = `B_SPLIT_REACTOR_OUTPUT=1&id=${shipId}&value=${value}&fleet=${fleetSplit}&autocarryover=${autoCarryOver}`;

  saveTimeout = setTimeout(function () {
    new Ajax.Updater("result", "station.php", {
      method: "post",
      parameters: params,
      evalScripts: true,
      onSuccess: function () {
        $("result").show();
      },
    });
  }, 150);
}

function updateSelectedSpacecraftId(spacecraftid) {
  var selshipid = document.getElementById("selshipid");
  if (selshipid) {
    selshipid.value = spacecraftid;
  }
}

function toggleTransferMessage(type) {
  var messageId = type + "TransferMessage";
  var message = document.getElementById(messageId);
  if (message.style.display === "none") {
    message.style.display = "block";
  } else {
    message.style.display = "none";
  }
}

function showSystemSettingsWindow(name) {
  closeAjaxWindow();
  openWindow('elt', 1, 300);
  ajax_update('elt', `?SHOW_SYSTEM_SETTINGS_AJAX=1&id=${spacecraftid}&system=${name}`);
}
function showLSSFilter() {
  elt = 'lssmode';
  openPJsWin(elt, 1);
  ajax_update(elt, '?SHOW_LSS_FILTER=1&id=' + spacecraftid);
}

function selectLssMode(mode) {
  actionToInnerContent(
    "B_SET_LSS_MODE",
    `id=${spacecraftid}&mode=${mode}&sstr=${sstr}`
  );
}
