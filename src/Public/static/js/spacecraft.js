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

function showALvlWindow(element) {
  updatePopupAtElement(element, "?id=" + spacecraftid + "&SHOW_ALVL=1");
}

function showETransferWindow(element, target) {
  updatePopupAtElement(element,
    "?id=" + spacecraftid + "&SHOW_ETRANSFER=1&target=" + target
  );
}

function showSelfdestructWindow(element, target) {
  var pos = findObject(element);
  updatePopup(
    "?id=" + spacecraftid + "&SHOW_SELFDESTRUCT_AJAX=1&target=" + target,
    300, pos[0] - 300, pos[1]
  );
}
function showScanWindow(element, spacecraftid, target) {
  updatePopupAtElement(element, "?id=" + spacecraftid + "&SHOW_SCAN=1&target=" + target);
}
function showSectorScanWindow(obj, x, y, sysid, loadSystemSensorScan) {
  var pos = findObject(obj);
  if (x && y) {
    updatePopup(
      "station.php?id=" +
      spacecraftid +
      "&SHOW_SENSOR_SCAN=1&x=" +
      x +
      "&y=" +
      y +
      "&systemid=" +
      sysid,
      800, pos[0] - 250, pos[1] - 250
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
    updatePopup("?id=" + spacecraftid + "&SHOW_SECTOR_SCAN=1",
      800, pos[0] - 250, pos[1] - 250
    );
  }
}
function openStarMap(obj, id) {
  var pos = findObject(obj);
  updatePopup("starmap.php?SHOW_STARMAP_POSITION=1&id=" + id,
    700, pos[0], pos[1]
  );
}

storageTimer = null;
function openStorageInit(element, id) {
  storageTimer = setTimeout(() => openStorage(element, id), 1000); //wait 1 second
  closeAjaxCallbacks.push(() => {
    clearTimeout(storageTimer);
  });
  element.onmouseout = function () {
    clearTimeout(storageTimer);
  }; //remove timer
}
function openStorage(element, id) {
  updatePopupAtElement(element, "?SHOW_SPACECRAFTSTORAGE=1&id=" + id);
}
function closeStorage() {
  closeAjaxWindow();
}
function showSpacecraftDetails(element, id) {
  updatePopupAtElement(element, "?SHOW_SPACECRAFTDETAILS=1&id=" + id);
}
function showEpsUsage(element, id) {
  updatePopupAtElement(element, "?SHOW_EPS_USAGE=1&id=" + id);
}
function showCommunication(element, id) {
  updatePopupAtElement(element, "?SHOW_SPACECRAFT_COMMUNICATION=1&id=" + id, function () {
    // Nach dem AJAX-Update initialisieren
    var emergencyTextElement = document.getElementById('emergencytext');
    if (emergencyTextElement) {
      var limit = parseInt(emergencyTextElement.getAttribute('data-limit'));
      if (limit) {
        initEmergencyTextLimiter(limit);
      }
    }
  });
}
function openTradeMenu(element, postid) {
  updatePopupAtElement(element,
    "?SHOW_TRADEMENU=1&id=" + spacecraftid + "&postid=" + postid
  );
}
function openWasteMenu(element) {
  updatePopupAtElement(element,
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
function showRegionInfo(element, region) {
  updatePopupAtElement(element,
    "?SHOW_REGION_INFO=1&id=" + spacecraftid + "&regionid=" + region
  );
}
function showColonyScan(element) {
  updatePopupAtElement(element, "?SHOW_COLONY_SCAN=1&id=" + spacecraftid);
}
function showRepairOptions(element, spacecraftid) {
  updatePopupAtElement(element, "?id=" + spacecraftid + "&SHOW_REPAIR_OPTIONS=1");
}

function analyseBuoy(element, buoyId) {
  updatePopupAtElement(element, `?SHOW_ANALYSE_BUOY=1&id=${spacecraftid}&buoyid=${buoyId}`);
}
function showWormholeControl(element) {
  updatePopupAtElement(element, 'ship.php?SHOW_WORMHOLE_CONTROL=1&id=' + spacecraftid);
}
function showFightLog(element) {
  updatePopupAtElement(element, null);
  $("popupContent").innerHTML = $("fightlog").innerHTML;
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

let reactorOutput = null;
let epsUsage = null;
let flightCost = null;
let missingEps = null;
let currentWarpdrive = null;
let maxWarpdrive = null;

function setReactorSplitConstants(output, usage, cost, meps, wd, mwd) {
  reactorOutput = output;
  epsUsage = usage;
  flightCost = cost;
  missingEps = meps;
  currentWarpdrive = wd;
  maxWarpdrive = mwd;
}

function updateReactorValues() {

  // calculate absolute values
  const warpdriveSplit = Number.parseInt(document.getElementById("warpdriveSplit")?.value ?? 0);
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
  const calculatedEPSEl = document.getElementById("calculatedEPS");
  if (calculatedEPSEl) {
    calculatedEPSEl.textContent =
      epsProduction > 0 ? "+" + epsProduction : String(epsProduction);
  }
  const calculatedWarpDriveEl = document.getElementById("calculatedWarpDrive");
  if (calculatedWarpDriveEl) {
    calculatedWarpDriveEl.textContent =
      warpDriveProduction > 0 ? "+" + warpDriveProduction : "0";
  }

  // calculate effective values
  let epsChange = epsProduction - epsUsage;
  let missingWarpdrive = maxWarpdrive - currentWarpdrive;
  let effEpsProduction = Math.min(missingEps, epsChange);
  let effWarpdriveProduction = Math.min(missingWarpdrive, warpDriveProduction);

  if (document.getElementById("autoCarryOver")?.checked ?? true) {
    let excess = Math.max(
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
      warpDriveProduction + (excess == 0 ? 0 : Math.floor(excess / flightCost))
    );
  }

  // set effective labels
  const effectiveEpsEl = document.getElementById("effectiveEps");
  if (effectiveEpsEl) {
    effectiveEpsEl.textContent =
      effEpsProduction > 0 ? "+" + effEpsProduction : String(effEpsProduction);
  }
  const effectiveWarpdriveEl = document.getElementById("effectiveWarpdrive");
  if (effectiveWarpdriveEl) {
    effectiveWarpdriveEl.textContent =
      effWarpdriveProduction > 0
        ? "+" + effWarpdriveProduction
        : String(effWarpdriveProduction);
  }
  const reactorUsageEl = document.getElementById("reactorUsage");
  if (reactorUsageEl) {
    reactorUsageEl.textContent =
      epsUsage + effEpsProduction + effWarpdriveProduction * flightCost;
  }
}

var saveTimeout;

function saveWarpCoreSplit(shipId) {
  clearTimeout(saveTimeout);

  value = document.getElementById("warpdriveSplit").value;
  autoCarryOver = document.getElementById("autoCarryOver").checked ? 1 : 0;
  fleetSplit = document.getElementById("fleetSplit").checked ? 1 : 0;

  params = `B_SPLIT_REACTOR_OUTPUT=1&id=${shipId}&value=${value}&fleet=${fleetSplit}&autocarryover=${autoCarryOver}&sstr=${sstr}`;

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

function showSystemSettingsWindow(element, name) {
  updatePopupAtElement(element, `?SHOW_SYSTEM_SETTINGS_AJAX=1&id=${spacecraftid}&system=${name}`,
    400
  );
}
function showLSSFilter(element) {
  updatePopupAtElement(element, '?SHOW_LSS_FILTER=1&id=' + spacecraftid);
}

function selectLssMode(mode) {
  actionToInnerContent(
    "B_SET_LSS_MODE",
    `id=${spacecraftid}&mode=${mode}&sstr=${sstr}`
  );
}

function initEmergencyTextLimiter(limit) {
  var emergencyTextElement = document.getElementById('emergencytext');
  if (emergencyTextElement && !emergencyTextElement.hasAttribute('data-initialized')) {
    emergencyTextElement.setAttribute('data-initialized', 'true');
    emergencyTextElement.addEventListener(
      "keyup",
      function () {
        var length = this.value.length;
        document.getElementById('emergencyTextLength').innerHTML = length;
        if (length > limit) {
          document.getElementById('emergencyTextLength').classList.add('error');
          document.getElementById('startEmergencyButton').disabled = true;
        } else {
          document.getElementById('emergencyTextLength').classList.remove('error');
          document.getElementById('startEmergencyButton').disabled = false;
        }
      },
      false
    );
  }
}

function initCommunicationObserver() {
  var observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (mutation.type === 'childList') {
        var emergencyTextElement = document.getElementById('emergencytext');
        if (emergencyTextElement && !emergencyTextElement.hasAttribute('data-initialized')) {
          var limit = parseInt(emergencyTextElement.getAttribute('data-limit'));
          if (limit) {
            initEmergencyTextLimiter(limit);
          }
        }
      }
    });
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true
  });
}

function updateWarpSignatureCountdown() {
  const currentTime = Math.floor(Date.now() / 1000);
  const remainingSeconds = Math.max(0, warpSignatureEndTime - currentTime);

  if (remainingSeconds <= 0) {
    document.getElementById('warpSignatureCountdown').textContent = "00:00";
    return;
  }

  const minutes = Math.floor(remainingSeconds / 60);
  const seconds = remainingSeconds % 60;

  const formattedMinutes = minutes.toString().padStart(2, '0');
  const formattedSeconds = seconds.toString().padStart(2, '0');

  document.getElementById('warpSignatureCountdown').textContent = `${formattedMinutes}:${formattedSeconds}`;

  setTimeout(updateWarpSignatureCountdown, 1000);
}
function addWormholeRestriction(entryId) {
  // Das ist der Typ aus der Dropdown: 1=USER, 2=ALLIANCE, 3=FACTION
  var typeValue = $('wormhole_type_' + entryId).value;
  // Das ist das Ziel aus dem entsprechenden Input-Feld
  var targetValue = $('whtarget_' + typeValue + '_' + entryId).value;
  // Mode: 1=ALLOW, 2=DENY
  var modeValue = $('whmode_' + entryId).value;
  var sessionString = $('wormhole_sstr').value;

  ajax_update(
    'wormhole_restrictions_' + entryId,
    'ship.php?B_ADD_WORMHOLE_RESTRICTION=1&id=' + spacecraftid +
    "&entryId=" + entryId +
    "&type=" + typeValue +
    "&target=" + targetValue +
    "&mode=" + modeValue +
    "&sstr=" + sessionString
  );
}

function deleteWormholeRestriction(restrictionId, entryId, sstr) {
  if (!sstr) {
    sstr = $('wormhole_sstr').value;
  }

  ajax_update(
    'wormhole_restrictions_' + entryId,
    'ship.php?B_DELETE_WORMHOLE_RESTRICTION=1&id=' + spacecraftid +
    "&restrictionId=" + restrictionId +
    "&entryId=" + entryId +
    "&sstr=" + sstr
  );
}



document.addEventListener('DOMContentLoaded', function () {
  initCommunicationObserver();

  if (document.getElementById('warpSignatureCountdown')) {
    updateWarpSignatureCountdown();
  }
});
