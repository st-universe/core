var spacecraftid = null;
var sstr = null;
var tachyonFresh = false;
function setSpacecraftIdAndSstr(id, sessionString) {
  spacecraftid = id;
  sstr = sessionString;
}

function setTachyonFresh(fresh) {
  tachyonFresh = fresh;
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
    300, pos[0] - 300, pos[1], false
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
      800, pos[0] - 250, pos[1] - 250, false
    );
    if (loadSystemSensorScan) {
      ajax_update(
        "systemsensorscan",
        "station.php?id=" +
        spacecraftid +
        "&SHOW_SYSTEM_SENSOR_SCAN=1&x=" +
        x +
        "&y=" +
        y +
        (tachyonFresh ? "&tf=1" : "")
      );
    }
  } else {
    updatePopup("?id=" + spacecraftid + "&SHOW_SECTOR_SCAN=1",
      800, pos[0] - 250, pos[1] - 250, false
    );
  }
}
function openStarMap(obj, id) {
  var pos = findObject(obj);
  updatePopup("starmap.php?SHOW_STARMAP_POSITION=1&id=" + id,
    700, pos[0] + 300, pos[1] + 50, false
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
  if (reactorOutput === null || epsUsage === null || flightCost === null ||
    missingEps === null || currentWarpdrive === null || maxWarpdrive === null) {
    return;
  }

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
    const usage = epsUsage + effEpsProduction + effWarpdriveProduction * flightCost;
    reactorUsageEl.textContent = isNaN(usage) ? "0" : usage;
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

// =================================================================
// 3D SHIP VIEWER
// =================================================================
class Ship3DViewer {
  constructor(container) {
    this.container = container;
    this.modelPath = container.dataset.model;
    this.isWarped = container.dataset.warp === '1';
    this.isImpulse = container.dataset.impulse === '1';
    this.title = container.dataset.title;

    this.TARGET_WIDTH = 100;
    this.BORDER = 25;

    this.scene = null;
    this.camera = null;
    this.renderer = null;
    this.modelGroup = null;
    this.impulseLayer = null;
    this.warpLayer1 = null;
    this.warpLayer2 = null;
    this.textureLoader = null;
    this.needsWarpLayers = false;
    this.time = 0;

    this.isDragging = false;
    this.lastX = 0;
    this.lastY = 0;

    if (typeof THREE === 'undefined') {
      console.error('Three.js ist nicht verfügbar');
      this.showError();
      return;
    }

    this.init();
  }

  showError() {
    this.container.innerHTML = '<div class="ship3d-error">3D-Modell nicht verfügbar</div>';
  }

  init() {
    console.log('Erstelle 3D-Renderer für:', this.modelPath);
    this.createRenderer();
    this.createScene();
    this.createCamera();
    this.createLighting();
    this.createBackgroundLayers();

    if (this.isWarped) {
      this.createWarpLayers();
    }

    this.loadModel();
  }

  createRenderer() {
    this.renderer = new THREE.WebGLRenderer({
      antialias: true,
      alpha: true
    });
    this.renderer.setPixelRatio(window.devicePixelRatio);
    this.renderer.outputColorSpace = THREE.SRGBColorSpace;
    this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
    this.renderer.toneMappingExposure = 1.6;

    const canvas = this.renderer.domElement;
    canvas.className = 'ship3d-canvas';
    this.container.appendChild(canvas);

    this.setupInteraction(canvas);
  }

  createScene() {
    this.scene = new THREE.Scene();
    this.scene.background = null;
  }

  createCamera() {
    this.camera = new THREE.OrthographicCamera(-1, 1, 1, -1, 0.1, 200);
    this.camera.position.set(0, 0, 10);
    this.camera.lookAt(0, 0, 0);
  }

  createLighting() {
    this.scene.add(new THREE.AmbientLight(0xffffff, 1.0));
    this.scene.add(new THREE.HemisphereLight(0xffffff, 0x202030, 0.9));

    const dir = new THREE.DirectionalLight(0xffffff, 2.0);
    dir.position.set(5, 8, 12);
    this.scene.add(dir);
  }

  createBackgroundLayers() {
    this.textureLoader = new THREE.TextureLoader();
  }

  createWarpLayers() {
    this.needsWarpLayers = true;
  }

  createLayersWithSize(canvasWidth, canvasHeight, pixelToWorld) {
    const textureLoader = this.textureLoader || new THREE.TextureLoader();

    console.log('Erstelle Layer in Originalgröße');

    textureLoader.load('/assets/buttons/warp_1.png', (texture) => {
      const imgWidth = texture.image.width;
      const imgHeight = texture.image.height;

      const planeWidth = imgWidth * pixelToWorld;
      const planeHeight = imgHeight * pixelToWorld;

      texture.wrapS = THREE.ClampToEdgeWrapping;
      texture.wrapT = THREE.ClampToEdgeWrapping;
      texture.minFilter = THREE.LinearFilter;
      texture.magFilter = THREE.LinearFilter;

      const geometry = new THREE.PlaneGeometry(planeWidth, planeHeight);
      const material = new THREE.MeshBasicMaterial({
        map: texture,
        transparent: true,
        depthWrite: false,
        depthTest: false
      });

      const mesh = new THREE.Mesh(geometry, material);
      mesh.position.z = -5;
      mesh.rotation.z = Math.PI;
      mesh.renderOrder = -9999;
      this.scene.add(mesh);

      console.log('Hintergrund-Layer geladen (warp_1.png)', {
        imgPixels: { width: imgWidth, height: imgHeight },
        worldUnits: { width: planeWidth, height: planeHeight },
        pixelToWorld: pixelToWorld
      });
    });

    if (this.isImpulse) {
      textureLoader.load('/assets/buttons/warp_2.png', (texture) => {
        const imgWidth = texture.image.width;
        const imgHeight = texture.image.height;
        const planeWidth = imgWidth * pixelToWorld;
        const planeHeight = imgHeight * pixelToWorld;

        texture.wrapS = THREE.RepeatWrapping;
        texture.wrapT = THREE.ClampToEdgeWrapping;
        texture.minFilter = THREE.LinearFilter;
        texture.magFilter = THREE.LinearFilter;

        const geometry = new THREE.PlaneGeometry(planeWidth, planeHeight);
        const material = new THREE.MeshBasicMaterial({
          map: texture,
          transparent: true,
          depthWrite: false,
          depthTest: false
        });

        const mesh = new THREE.Mesh(geometry, material);
        mesh.position.z = -4;
        mesh.rotation.z = Math.PI;
        mesh.renderOrder = -9998;
        this.scene.add(mesh);

        this.impulseLayer = mesh;

        console.log('Impuls-Layer geladen (warp_2.png)', {
          imgPixels: { width: imgWidth, height: imgHeight },
          worldUnits: { width: planeWidth, height: planeHeight }
        });
      });
    }

    if (this.needsWarpLayers) {
      if (!this.isImpulse) {
        textureLoader.load('/assets/buttons/warp_2.png', (texture) => {
          const imgWidth = texture.image.width;
          const imgHeight = texture.image.height;
          const planeWidth = imgWidth * pixelToWorld;
          const planeHeight = imgHeight * pixelToWorld;

          texture.wrapS = THREE.RepeatWrapping;
          texture.wrapT = THREE.ClampToEdgeWrapping;
          texture.minFilter = THREE.LinearFilter;
          texture.magFilter = THREE.LinearFilter;

          const geometry = new THREE.PlaneGeometry(planeWidth, planeHeight);
          const material = new THREE.MeshBasicMaterial({
            map: texture,
            transparent: true,
            depthWrite: false,
            depthTest: false
          });

          const mesh = new THREE.Mesh(geometry, material);
          mesh.position.z = -4;
          mesh.rotation.z = Math.PI;
          mesh.renderOrder = -9998;
          this.scene.add(mesh);

          this.impulseLayer = mesh;

          console.log('Warp Base-Layer geladen (warp_2.png)', {
            imgPixels: { width: imgWidth, height: imgHeight },
            worldUnits: { width: planeWidth, height: planeHeight }
          });
        });
      }

      textureLoader.load('/assets/buttons/warp_3.png', (texture) => {
        const imgWidth = texture.image.width;
        const imgHeight = texture.image.height;
        const planeWidth = imgWidth * pixelToWorld;
        const planeHeight = imgHeight * pixelToWorld;

        texture.wrapS = THREE.RepeatWrapping;
        texture.wrapT = THREE.ClampToEdgeWrapping;
        texture.minFilter = THREE.LinearFilter;
        texture.magFilter = THREE.LinearFilter;

        const geometry = new THREE.PlaneGeometry(planeWidth, planeHeight);
        const material = new THREE.MeshBasicMaterial({
          map: texture,
          transparent: true,
          depthWrite: false,
          depthTest: false,
          blending: THREE.AdditiveBlending,
          opacity: 0.8
        });

        const mesh = new THREE.Mesh(geometry, material);
        mesh.position.z = -3;
        mesh.rotation.z = Math.PI;
        mesh.renderOrder = -9997;
        this.scene.add(mesh);

        this.warpLayer1 = mesh;

        console.log('Warp-Layer 1 geladen (warp_3.png)', {
          imgPixels: { width: imgWidth, height: imgHeight },
          worldUnits: { width: planeWidth, height: planeHeight }
        });
      });

      textureLoader.load('/assets/buttons/warp_4.png', (texture) => {
        const imgWidth = texture.image.width;
        const imgHeight = texture.image.height;
        const planeWidth = imgWidth * pixelToWorld;
        const planeHeight = imgHeight * pixelToWorld;

        texture.wrapS = THREE.RepeatWrapping;
        texture.wrapT = THREE.ClampToEdgeWrapping;
        texture.minFilter = THREE.LinearFilter;
        texture.magFilter = THREE.LinearFilter;

        const geometry = new THREE.PlaneGeometry(planeWidth, planeHeight);
        const material = new THREE.MeshBasicMaterial({
          map: texture,
          transparent: true,
          depthWrite: false,
          depthTest: false,
          blending: THREE.AdditiveBlending,
          opacity: 0.8
        });

        const mesh = new THREE.Mesh(geometry, material);
        mesh.position.z = 2;
        mesh.rotation.z = Math.PI;
        mesh.renderOrder = 9999;
        this.scene.add(mesh);

        this.warpLayer2 = mesh;

        console.log('Warp-Layer 2 geladen (warp_4.png)', {
          imgPixels: { width: imgWidth, height: imgHeight },
          worldUnits: { width: planeWidth, height: planeHeight }
        });
      });
    }
  }

  loadModel() {
    console.log('Lade 3D-Modell:', this.modelPath);
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'ship3d-loading';
    loadingDiv.textContent = 'Lade 3D-Modell...';
    this.container.appendChild(loadingDiv);

    const loader = new THREE.GLTFLoader();

    const dracoLoader = new THREE.DRACOLoader();
    dracoLoader.setDecoderPath('https://www.gstatic.com/draco/versioned/decoders/1.5.7/');
    loader.setDRACOLoader(dracoLoader);

    loader.load(
      this.modelPath,
      (gltf) => {
        console.log('3D-Modell erfolgreich geladen');
        loadingDiv.remove();
        this.onModelLoaded(gltf);
      },
      (progress) => {
        if (progress.lengthComputable) {
          const percent = Math.round((progress.loaded / progress.total) * 100);
          loadingDiv.textContent = `Lade 3D-Modell... ${percent}%`;
        }
      },
      (error) => {
        console.error('Fehler beim Laden des 3D-Modells:', error);
        loadingDiv.textContent = 'Fehler: Modell nicht gefunden';
        setTimeout(() => {
          loadingDiv.remove();
          this.showError();
        }, 2000);
      }
    );
  }

  onModelLoaded(gltf) {
    const model = gltf.scene;
    this.modelGroup = new THREE.Group();
    this.modelGroup.add(model);
    this.scene.add(this.modelGroup);

    model.traverse(obj => {
      if (obj.isMesh) {
        obj.material.transparent = true;
        obj.material.opacity = 1.0;
      }
    });

    const box = new THREE.Box3().setFromObject(this.modelGroup);
    const size = new THREE.Vector3();
    const center = new THREE.Vector3();
    box.getSize(size);
    box.getCenter(center);

    this.modelGroup.position.sub(center);

    this.modelGroup.rotation.y = Math.PI / 2;

    const rotatedBox = new THREE.Box3().setFromObject(this.modelGroup);
    const rotatedSize = new THREE.Vector3();
    rotatedBox.getSize(rotatedSize);

    const targetWidthPx = this.TARGET_WIDTH;

    const modelWidth = rotatedSize.x;
    const modelHeight = rotatedSize.y;
    const aspectRatio = modelHeight / modelWidth;
    const targetHeightPx = Math.round(targetWidthPx * aspectRatio);

    const canvasWidth = targetWidthPx + this.BORDER * 2;
    const canvasHeight = targetHeightPx + this.BORDER * 2;

    this.renderer.setSize(canvasWidth, canvasHeight);

    this.container.style.width = canvasWidth + 'px';
    this.container.style.height = canvasHeight + 'px';
    this.container.style.minWidth = canvasWidth + 'px';
    this.container.style.minHeight = canvasHeight + 'px';

    const pixelToWorld = modelWidth / targetWidthPx;

    const frustumWidth = canvasWidth * pixelToWorld;
    const frustumHeight = canvasHeight * pixelToWorld;

    this.camera.left = -frustumWidth / 2;
    this.camera.right = frustumWidth / 2;
    this.camera.top = frustumHeight / 2;
    this.camera.bottom = -frustumHeight / 2;
    this.camera.updateProjectionMatrix();

    const cell = this.container.closest('td');
    if (cell) {
      cell.style.minWidth = canvasWidth + 'px';
      cell.style.height = (canvasHeight + 10) + 'px';
      cell.style.textAlign = 'center';
      cell.style.verticalAlign = 'middle';
    }

    console.log('3D-Modell Größen:', {
      modelSize: { width: modelWidth, height: modelHeight },
      aspectRatio: aspectRatio,
      targetPx: { width: targetWidthPx, height: targetHeightPx },
      canvasPx: { width: canvasWidth, height: canvasHeight },
      frustumWorld: { width: frustumWidth, height: frustumHeight },
      pixelToWorld: pixelToWorld
    });

    this.createLayersWithSize(canvasWidth, canvasHeight, pixelToWorld);

    this.animate();
  }

  setupInteraction(canvas) {
    canvas.addEventListener('mousedown', (e) => {
      if (e.button !== 0) return;
      this.isDragging = true;
      this.lastX = e.clientX;
      this.lastY = e.clientY;
      e.preventDefault();
    });

    window.addEventListener('mouseup', () => {
      this.isDragging = false;
    });

    window.addEventListener('mousemove', (e) => {
      if (!this.isDragging || !this.modelGroup) return;

      const dx = e.clientX - this.lastX;
      const dy = e.clientY - this.lastY;
      this.lastX = e.clientX;
      this.lastY = e.clientY;

      this.modelGroup.rotation.y += dx * 0.01;
      this.modelGroup.rotation.x += dy * 0.01;
    });

    canvas.addEventListener('wheel', (e) => {
      if (!this.modelGroup) return;
      e.preventDefault();

      const delta = e.deltaY * -0.001;
      const scale = this.modelGroup.scale.x + delta;
      const clampedScale = Math.max(0.5, Math.min(2.0, scale));

      this.modelGroup.scale.set(clampedScale, clampedScale, clampedScale);
    }, { passive: false });
  }

  animate() {
    if (!this.renderer || !this.scene || !this.camera) return;

    requestAnimationFrame(() => this.animate());

    this.time += 0.016;

    if (this.impulseLayer) {
      const offset = (this.time * 0.02) % 1.0;
      this.impulseLayer.material.map.offset.x = -offset;
    }

    if (this.warpLayer1) {
      const offset1 = (this.time * 0.05) % 1.0;
      this.warpLayer1.material.map.offset.x = -offset1;
    }

    if (this.warpLayer2) {
      const offset2 = (this.time * 0.08) % 1.0;
      this.warpLayer2.material.map.offset.x = -offset2;
    }

    this.renderer.render(this.scene, this.camera);
  }

  dispose() {
    if (this.renderer) {
      this.renderer.dispose();
    }
    if (this.scene) {
      this.scene.traverse(obj => {
        if (obj.geometry) obj.geometry.dispose();
        if (obj.material) {
          if (Array.isArray(obj.material)) {
            obj.material.forEach(m => m.dispose());
          } else {
            obj.material.dispose();
          }
        }
      });
    }
  }
}

function init3DShipViewers() {
  const containers = document.querySelectorAll('.ship3d-container:not(.ship3d-initialized)');
  //console.log('Gefundene 3D-Container:', containers.length);
  containers.forEach(container => {
    container.classList.add('ship3d-initialized');
    new Ship3DViewer(container);
  });
}

function setupShip3DObserver() {
  if (typeof MutationObserver === 'undefined') return;

  const observer = new MutationObserver(function (mutations) {
    let needsInit = false;
    mutations.forEach(function (mutation) {
      if (mutation.addedNodes.length) {
        mutation.addedNodes.forEach(node => {
          if (node.nodeType === 1) {
            if (node.classList && node.classList.contains('ship3d-container')) {
              needsInit = true;
            } else if (node.querySelector && node.querySelector('.ship3d-container')) {
              needsInit = true;
            }
          }
        });
      }
    });
    if (needsInit) {
      console.log('Neue 3D-Container erkannt, initialisiere...');
      init3DShipViewers();
    }
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function () {
    init3DShipViewers();
    setupShip3DObserver();
  });
} else {
  init3DShipViewers();
  setupShip3DObserver();
}

window.Ship3DViewer = Ship3DViewer;
window.init3DShipViewers = init3DShipViewers;