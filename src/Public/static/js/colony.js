var colonyid = null;
var hostid = null;
var hosttype = null;
var sstr = null;
var scrollOffset = 6;
var colonySubMenu = 2;

function initializeJsVars(id, type, sessionString) {
	colonyid = id;
	hostid = id;
	hosttype = type;
	sstr = sessionString;
}

var fieldType = 0;

function buildMenuScrollUp(menu, offset) {
	if (offset - scrollOffset < 0) {
		var newOffset = 0;
	} else {
		var newOffset = offset - scrollOffset;
	}
	buildMenuScroll(menu, newOffset);
}

function buildMenuScrollDown(menu, offset) {
	var newOffset = offset + scrollOffset;
	buildMenuScroll(menu, newOffset);
}

function buildMenuScroll(menu, offset) {
	ajax_update('buildmenu' + menu, createHostUri('B_SCROLL_BUILDMENU', `&menu=${menu}&offset=${offset}&fieldtype=${fieldType}`));
}

function switchColonyMenu(menu, func, fid) {
	switchMenu(menu, 'colonymenu', func, fid);
}

function switchColonySubmenu(menu, params, func, fid) {
	document.querySelectorAll('.colmenubutton').forEach(function (elem) {
		Element.removeClassName(elem, 'selected');
	});
	switchMenu(menu, 'submenu', func, fid, params, true);

	var menuButton = $('colmenubutton_' + menu);
	if (menuButton) {
		menuButton.addClassName('selected');
	}

	colonySubMenu = menu;
}

function switchMenu(menu, id, func, fid, params, doPreserveResult) {
	if (!doPreserveResult) {
		$('result').hide();
	}

	closeAjaxWindow();
	url = createHostUri('B_SWITCH_COLONYMENU', `&menu=${menu}`);
	if (func) {
		url += `&func=${func}`;
	}
	if (fid) {
		url += `&fid=${fid}`;
	}
	if (params) {
		url += `&${params}`;
	}

	ajax_update(id, url);

	if (menu == 1) {
		setTimeout('initBuildmenuMouseEvent()', 1000);
	}
}

var selectedbuilding = 0;

function openBuildingInfo(buildingId) {
	closeAjaxWindow();
	elt = 'buildinginfo';
	openPJsWin(elt);
	ajax_update(elt, createHostUri('SHOW_BUILDING', '&bid=' + buildingId));
	ajax_update('colsurface', createHostUri('SHOW_SURFACE', '&bid=' + buildingId));
	buildmode = 1;
	selectedbuilding = buildingId;

	closeAjaxCallbacksMandatory.push(() => {
		closeBuildingInfo();
	});
}

function closeBuildingInfo() {
	ajax_update('colsurface', createHostUri('SHOW_SURFACE'));
	buildmode = 0;
	selectedbuilding = 0;
}

var oldimg = 0;

function fieldMouseOver(obj, building, fieldtype) {
	document.body.style.cursor = 'pointer';
	if (buildmode == 1 && building == 0) {
		if (obj.parentNode.parentNode.parentNode.parentNode.className == 'cfb') {
			oldimg = obj.src;
			obj.parentNode.style.backgroundImage = 'url(' + oldimg + ')';
			obj.src = gfx_path + "/generated/buildings/" + selectedbuilding + "/0at.png";
		}
	}
	if (buildmode == 1 && $('building_preview_' + fieldtype)) {
		$('building_preview_default').style.display = 'none';
		$('building_preview_' + fieldtype).style.display = 'block';
	}
}

function fieldMouseOut(obj, fieldtype) {
	if (buildmode == 1 && oldimg != 0) {
		if (obj.parentNode.parentNode.parentNode.parentNode.className == 'cfb') {
			obj.src = oldimg;
			obj.parentNode.style.backgroundImage = '';
			oldimg = 0;
		}
	}
	if (buildmode == 1 && $('building_preview_' + fieldtype)) {
		$('building_preview_' + fieldtype).style.display = 'none';
		$('building_preview_default').style.display = 'block';
	}
	document.body.style.cursor = 'auto';
}

function fieldMouseClick(obj, fieldId, buildingId, buildingName) {
	if (buildmode == 1) {
		if (obj.parentNode.className == 'cfb') {
			if (buildingId > 0) {
				if (confirm(`Soll das Gebäude "${buildingName}" auf diesem Feld abgerissen werden?`)) {
					buildOnField('B_BUILD', fieldId);
				}
			} else {
				buildOnField('B_BUILD', fieldId);
			}
		}
	} else {
		if (colonySubMenu == 1) {
			switchColonySubmenu(1, `fid=${fieldId}`);
			closeAjaxCallbacks.push(() => {
				switchColonySubmenu(1);
			});
		}
		showField(fieldId);
	}
}

function showField(fieldId) {
	elt = 'fieldaction';
	openPJsWin(elt);
	ajax_update(elt, '/colony.php?fid=' + fieldId + '&SHOW_FIELD=1');
}
function buildOnField(action, fieldId, buildingId) {

	if (buildingId) {
		bid = buildingId;
	} else {
		bid = selectedbuilding;
	}

	performActionAndUpdateResult(action, `fid=${fieldId}&bid=${bid}`);
}

function terraformOnField(fieldId, terraformId) {
	performActionAndUpdateResult('B_TERRAFORM', `fid=${fieldId}&tfid=${terraformId}`);
}

function removeOnField(fieldId) {
	performActionAndUpdateResult('B_REMOVE_BUILDING', `fid=${fieldId}`);
}

function performActionAndUpdateResult(action, params) {

	cClick();

	new Ajax.Updater('result', '/colony.php', {
		method: 'post',
		parameters: `${action}=1&${params}`,
		evalScripts: true,
		onSuccess: function (transport) {
			var counter = document.getElementById("counter");
			if (counter) {
				counter.innerHTML = Math.max((counter.innerText - 1), 0);
			}

			$('result').show();
		}
	});
}

function refreshHost() {
	ajax_update('colsurface', createHostUri('SHOW_SURFACE', '&bid=' + selectedbuilding));
	ajax_update('colonyeps', createHostUri('SHOW_EPSBAR_AJAX'));
	ajax_update('colonyshields', createHostUri('SHOW_SHIELDBAR_AJAX'));
	ajax_update('colonystorage', createHostUri('SHOW_STORAGE_AJAX'));

	//reload info submenu if selected
	if (colonySubMenu == 2) {
		switchColonySubmenu(2);
	}
}

function createHostUri(IDENTIFIER, extra) {
	uri = `?id=${hostid}&hosttype=${hosttype}&${IDENTIFIER}=1`;

	if (extra) {
		uri += extra;
	}

	return uri;
}

function getOrbitShipList(colonyId) {
	elt = 'shiplist';
	openPJsWin(elt);
	ajax_update(elt, 'colony.php?id=' + colonyId + '&SHOW_ORBIT_SHIPLIST=1');
}

function showBToSWindow() {
	var shipid = $('selshipid').value;
	elt = 'beam'
	openPJsWin(elt, 1);
	ajax_update(elt, 'colony.php?id=' + colonyid + '&SHOW_BEAMTO=1&target=' + shipid);
}

function showBFromSWindow() {
	var shipid = $('selshipid').value;
	elt = 'beam'
	openPJsWin(elt, 1);
	ajax_update(elt, 'colony.php?id=' + colonyid + '&SHOW_BEAMFROM=1&target=' + shipid);
}

function showColonySectorScanWindow(id) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'colony.php?id=' + id + '&SHOW_SECTOR_SCAN=1');
}

function showPodLocationWindow() {
	elt = 'podlocations';
	openPJsWin(elt, 1);
	ajax_update(elt, 'colony.php?SHOW_PODS_LOCATIONS=1');
}

function triggerBeamToShip() {
	var shipid = $('selshipid').value;
	ajax_update(elt, 'colony.php?id=' + colonyid + '&SHOW_BEAMTO=1&target=' + shipid);
}

function triggerBeamFromShip() {
	var shipid = $('selshipid').value;
	ajax_update(elt, 'colony.php?id=' + colonyid + '&SHOW_BEAMFROM=1&target=' + shipid);
}

function toggleMaxEmpty(elem, max) {
	var input = elem.up('tr').down('.commodityAmount');
	var value = input.value;

	if (value) {
		input.value = '';
	} else {
		input.value = max;
	}
}

function initBuildmenuMouseEvent() {
	onmousewheel($('buildmenu1'), function (delta) {
		scrollBuildmenuByMouse(1, delta);
	});
	onmousewheel($('buildmenu2'), function (delta) {
		scrollBuildmenuByMouse(2, delta);
	});
	onmousewheel($('buildmenu3'), function (delta) {
		scrollBuildmenuByMouse(3, delta);
	});
	onmousewheel($('buildmenu4'), function (delta) {
		scrollBuildmenuByMouse(4, delta);
	});
}
function scrollBuildmenuByMouse(menu, delta) {
	offset = parseInt($('buildmenu' + menu + '_offset').value);
	if (delta < 0) {
		buildMenuScrollDown(menu, offset);
	}
	if (delta > 0) {
		buildMenuScrollUp(menu, offset);
	}
}

currentTab = false;
function showModuleSelector(obj, type) {
	$('module_select_tabs').select('div').each(function (tab) {
		Element.removeClassName(tab, 'module_select_base_selected');
	});
	Element.addClassName(obj, 'module_select_base_selected');
	if (currentTab) {
		currentTab.hide();
	}
	$('selector_' + type).show();
	currentTab = $('selector_' + type);
}

function toggleTorpedoInfo(module_crew) {
	if (module_crew == 0) {
		$('torpedo_info').hide();
	} else {
		$('torpedo_info').show();
	}
}

function replaceTabImage(type, moduleId, commodityId, module_crew, amount) {

	if (moduleId == 0) {
		$('tab_image_mod_' + type).src = 'assets/buttons/modul_' + type + '.png';
		$('module_type_' + type).innerHTML = '';
		updateCrewCount(type, 0);
	} else {
		if (amount > 0) {
			Element.removeClassName($('module_tab_' + type), 'module_select_base_mandatory');
		}
		$('tab_image_mod_' + type).src = 'assets/commodities/' + commodityId + '.png';
		$('module_type_' + type).innerHTML = $(moduleId + '_content').innerHTML;
		$('module_type_' + type).show();
		updateCrewCount(type, module_crew);
	}
	if (amount > 0) {
		enableShipBuildButton();
	}
	else {
		checkCrewCount();
	}
}
var disabledSlots = new Set();
function toggleSpecialModuleDisplay(type, module_id, module_crew, amount) {
	let innerHTML = '';
	let checkedCount = 0;

	//count and set tab images
	Element.select($('selector_' + type), '.specialModuleRadio').each(function (elem) {
		if (elem.checked) {
			innerHTML = innerHTML.concat($(elem.value + '_content').innerHTML);
			if (elem.value == module_id) {
				updateCrewCount(elem.value, module_crew);
			}
			checkedCount++;
			$('tab_image_special_mod_' + elem.value).style.display = 'block';
		} else {
			updateCrewCount(elem.value, 0);
			$('tab_image_special_mod_' + elem.value).style.display = 'none';
		}
	});
	$('module_tab_info_' + type).innerHTML = `${checkedCount} / ${specialSlots}`;

	//enable/disable default image
	if (checkedCount != 0) {
		$('tab_image_mod_9').style.display = 'none';
	} else {
		$('tab_image_mod_9').style.display = 'block';
	}

	//check for maximum amount
	if (checkedCount == specialSlots) {
		Element.select($('selector_' + type), '.specialModuleRadio').each(function (elem) {
			if (!elem.checked && !elem.disabled) {
				elem.disabled = true;
				disabledSlots.add(elem);
			}
		});
	}
	else {
		disabledSlots.forEach(function (elem) {
			elem.disabled = false;
		});
	}
	$('module_type_' + type).innerHTML = innerHTML;
	$('module_type_' + type).show();

	if (amount > 0) {
		enableShipBuildButton();
	}
	else {
		checkCrewCount();
	}
}
var maxCrew;
var baseCrew;
var specialSlots;
function setFixValues(base_crew, max_crew, special_slots) {
	baseCrew = base_crew;
	maxCrew = max_crew;
	specialSlots = special_slots;
}
var crew_type = new Hash();
function updateCrewCount(type, module_crew) {
	crew_type.set(type, module_crew);

	if (type == 8) {
		toggleTorpedoInfo(module_crew);
	}
}
function checkCrewCount() {
	crewSum = baseCrew;
	crew_type.each(function (pair) {
		if (pair.value >= 0) {
			crewSum += pair.value;
		}
	});
	$('crewdisplay').select('div').each(function (elem) {
		elem.hide();
	});
	if (crewSum > maxCrew) {
		Form.Element.disable('buildbutton');
		$('crewerr').show();
		return false;
	} else {
		$('crewSum').show();
		$('crewMax').show();
		$('crewSum').innerHTML = "Benötigte Crew: " + crewSum;
		return true;
	}
}
function enableShipBuildButton() {
	if (!checkCrewCount()) {
		return;
	}
	mandatory = false;
	$('module_select_tabs').select('div').each(function (tab) {
		if (Element.hasClassName(tab, 'module_select_base_mandatory')) {
			mandatory = true;
		}
	});
	if (mandatory) {
		return;
	}
	Form.Element.enable('buildbutton');
	new Effect.Highlight($('buildbutton'));
}
function cancelModuleQueueEntries(module_id) {
	ajaxPostUpdate(
		`module_${module_id}_action`,
		'colony.php', `B_CANCEL_MODULECREATION=1&id=${colonyid}&module=${module_id}&func=${$('func').value}&count=${$('module_' + module_id + '_count').value}`
	);
	document.querySelectorAll(`[id^="module_${module_id}_action"]`).forEach(function (element) {
		element.innerHTML = '<div>-</div>';
	});

	document.querySelectorAll(`[id^="module_${module_id}_count"]`).forEach(function (input) {
		input.value = 0;
	});

	document.querySelectorAll(`[name^="cancelModuleList${module_id}"]`).forEach(function (img) {
		img.src = '/assets/buttons/x1.png';
	});
}

function cp(elementName, imageName) {
	document.getElementsByName(elementName).forEach(function (element) {
		element.src = `/assets/${imageName}.png`;
	});
}

function showGiveUpWindow(target) {
	elt = 'giveup';
	openWindow(elt, 1, 300);
	ajax_update(elt, 'colony.php?id=' + colonyid + '&SHOW_GIVEUP_AJAX=1&target=' + target);
}

function getCommodityLocations(commodityId) {
	closeAjaxWindow();
	openPJsWin('elt', 1);
	ajax_update('elt', 'database.php?commodityId=' + commodityId + '&SHOW_COMMODITIES_LOCATIONS=1');
}

var colonyMapX = null;
var colonyMapY = null;
function setColonyMapCoordinates(mapX, mapY) {
	colonyMapX = mapX;
	colonyMapY = mapY;
}

function calculateScanCost(cx, cy) {
	var difX = Math.abs(cx - colonyMapX);
	var difY = Math.abs(cy - colonyMapY);
	var diagonal = Math.ceil(Math.sqrt(difX * difX + difY * difY));

	var neededEnergy = 20 + (diagonal / 169) * 180;
	return Math.round(neededEnergy);
}
function updateTelescopeEnergy(cx, cy) {
	$('needed_energy').innerHTML = calculateScanCost(cx, cy);

	if (parseInt($('needed_energy').innerHTML) > parseInt($('current_energy').innerHTML)) {
		$('needed_energy').style.color = 'red';
	} else {
		$('needed_energy').style.color = '#dddddd';
	}
}
function showTelescopeScan(cx, cy) {
	closeAjaxWindow();
	openPJsWin('elt', 1);

	if (calculateScanCost(cx, cy) <= parseInt($('current_energy').innerHTML)) {
		ajax_update('elt', 'colony.php?SHOW_TELESCOPE_SCAN=1&id=' + colonyid + '&cx=' + cx + '&cy=' + cy);
	}

	//refresh current colony eps
	ajax_update('current_energy', 'colony.php?REFRESH_COLONY_EPS=1&id=' + colonyid);
}
function syncInputs(id1, id2) {
	const value = document.getElementById(id1).value;
	document.getElementById(id2).value = value;
}
function calculateLocalCrew() {
	const primaryPositive = Math.max(0, parseInt(document.getElementById('primaryPositive').value) || 0);
	const secondaryPositive = Math.max(0, parseInt(document.getElementById('secondaryPositive').value) || 0);
	const population = Math.max(0, parseInt(document.getElementById('population').value) || 0);
	const workers = Math.max(0, parseInt(document.getElementById('workers').value) || 0);
	let lifeStandard = Math.max(0, parseInt(document.getElementById('lifeStandard').value) || 0);
	const negativEffect = Math.ceil(population / 70);

	const term1 = Math.max(0, negativEffect - secondaryPositive);
	const term2 = Math.max(primaryPositive - (4 * term1), 0);
	const term3 = Math.min(term2, workers) / 5;

	if (lifeStandard > population) {
		lifeStandard = population;
	}

	let term4;
	if (population > 0) {
		term4 = Math.floor(lifeStandard * 100 / population);
	} else {
		term4 = 0;
	}
	const term5 = Math.floor(10 + term3 * (term4 / 100));
	let result = term5;

	document.getElementById('calculatedCrew').innerText = result.toString();
	document.getElementById('calculatedCrewResponsive').innerText = result.toString();
}

/**
 * All module production functionality
 */

var moduleProductionInputs = new Map();
function clearModuleInputs() {
	moduleProductionInputs.clear();
}

function setModuleInput(input) {
	moduleProductionInputs.set(input.getAttribute('data-module-id'), input.value);
	syncAllInputFields(input);
}

function syncAllInputFields(input) {
	const moduleId = input.getAttribute('data-module-id');
	const value = input.value;
	const inputs = document.querySelectorAll(`input[data-module-id="${moduleId}"]`);
	inputs.forEach(inp => {
		inp.value = value;
	});
}

function startModuleProduction() {

	let colonyId = document.getElementById('colony-id').value;
	let func = document.getElementById('func').value;
	let moduleIds = [...moduleProductionInputs.keys()].join("&moduleids[]=");
	let values = [...moduleProductionInputs.values()].join("&values[]=");

	actionToInnerContent('B_CREATE_MODULES', `id=${colonyId}&func=${func}&moduleids[]=${moduleIds}&values[]=${values}&sstr=${sstr}`);
}

function filterByRump(selectedRump) {
	const isSelected = selectedRump !== '0' && selectedRump !== '';

	applyFilter(isSelected, `.rump_${selectedRump}`, false);

	updateBuildplanDropdown(selectedRump);
}

function filterByBuildplan(selectedBuildplan) {
	const isSelected = selectedBuildplan !== '0' && selectedBuildplan !== '';

	if (isSelected) {
		applyFilter(isSelected, `.buildplan_${selectedBuildplan}`, true);
	} else {
		filterByRump(document.getElementById('rump-select').value);
	}
}

function applyFilter(isSelected, querySelector, expandAll) {
	hideAllModulesAndLevelButtons(isSelected);

	if (isSelected) {
		const modules = document.querySelectorAll(querySelector);
		modules.forEach(module => {
			module.style.display = 'table-row';
			enableLevelButton(module);
			if (expandAll) {
				showModuleLevel(module);
			}
		});
	}
}

function updateBuildplanDropdown(rumpId) {
	const buildplanSelect = document.getElementById('buildplan-select');
	const allOptions = buildplanSelect.querySelectorAll('option');

	allOptions.forEach(option => {
		if (option.getAttribute('data-rump-id') === rumpId || option.value === '0') {
			option.style.display = 'block';
		} else {
			option.style.display = 'none';
		}
	});

	buildplanSelect.value = '0';
}

function toggleModuleType(type) {
	const levelBox = document.getElementById(`level-box-${type}`);
	const moduleLevels = document.querySelectorAll(`.module-level`);

	if (levelBox.style.display === 'none') {
		levelBox.style.display = 'flex';
	} else {
		levelBox.style.display = 'none';

		moduleLevels.forEach(moduleLevel => {
			if (moduleLevel.id.startsWith(`module-level-${type}-`)) {
				moduleLevel.style.display = 'none';

				const levelButton = document.querySelector(`#level-box-${type} button.active`);
				if (levelButton) {
					levelButton.classList.remove('active');
				}
			}
		});
	}
}

function toggleModuleLevel(type, level, element) {
	const moduleLevelDiv = document.getElementById(`module-level-${type}-${level}`);

	if (moduleLevelDiv.style.display === 'none') {
		element.classList.add('active');
		moduleLevelDiv.style.display = 'block';
	} else {
		element.classList.remove('active');
		moduleLevelDiv.style.display = 'none';
	}
}

function hideAllModulesAndLevelButtons(isFilterActive) {
	const allModules = document.querySelectorAll('.modules');
	const allLevelButtons = document.querySelectorAll('.level-button');
	const allModuleLevels = document.querySelectorAll('.module-level');
	const allLevelBoxes = document.querySelectorAll('.level-box');

	allModules.forEach(module => {
		module.style.display = isFilterActive ? 'none' : 'table-row'
	});
	allLevelButtons.forEach(button => {
		button.style.display = isFilterActive ? 'none' : 'block';
		button.classList.remove('active');
	});
	allModuleLevels.forEach(moduleLevel => {
		moduleLevel.style.display = 'none';
	});
	allLevelBoxes.forEach(levelBox => {
		levelBox.style.display = 'none';
	});
}

function enableLevelButton(module) {
	const type = module.getAttribute('data-module-type');
	const level = module.getAttribute('data-module-level');
	document.getElementById(`level-button-${type}-${level}`).style.display = 'block';
}

function showModuleLevel(module) {
	const type = module.getAttribute('data-module-type');
	const level = module.getAttribute('data-module-level');
	document.getElementById(`module-level-${type}-${level}`).style.display = 'block';
}

// #### TUTORIAL STUFF: DO NOT TOUCH sonst Finger ab ####

let currentStepIndex = 0;
let hasSlidIn = false;
let hasInnerUpdate = false;
let originalFunction = null;

function openPaddPopup(tutorialSteps, newStepIndex) {
    currentStepIndex = newStepIndex;
    
    const currentStep = tutorialSteps[currentStepIndex];
	const hasInnerUpdate = currentStep.innerUpdate;
	const title = currentStep.title;
	const text = currentStep.text;

    let padd = document.getElementById('padd-popup');
    let nextButton;

    if (!padd) {
        padd = document.createElement('div');
        padd.id = 'padd-popup';
        padd.style.position = 'fixed';
        padd.style.top = '50%';
        padd.style.left = '100%';
        padd.style.transform = 'translate(-50%, -50%)';
        padd.style.width = '350px';
        padd.style.height = '500px';
        padd.style.backgroundColor = '#A6A6A6'; 
        padd.style.borderRadius = '20px';
        padd.style.boxShadow = 'inset 0 0 0 5px #666666'; 
        padd.style.zIndex = '1003';
        padd.style.padding = '10px';
        padd.style.display = 'flex';
        padd.style.flexDirection = 'column';
        padd.style.alignItems = 'center';
        padd.style.cursor = 'move';
        padd.style.transition = 'left 0.5s ease-out';

        
        const screen = document.createElement('div');
        screen.id = 'padd-screen';
        screen.style.backgroundColor = '#000000';
        screen.style.width = '95%';
        screen.style.height = '60%';
        screen.style.border = '2px solid #666666';
        screen.style.marginBottom = '10px';
        screen.style.display = 'flex';
        screen.style.flexDirection = 'column';
        screen.style.alignItems = 'center';
        screen.style.justifyContent = 'center';
        screen.style.color = '#FFFFFF';
        screen.style.padding = '10px';
        screen.style.borderRadius = '10px';

        const titleField = document.createElement('div');
        titleField.id = 'padd-title';
        titleField.style.fontFamily = 'LCARS';
        titleField.style.fontSize = '24px';
        titleField.style.color = '#FFCC00'; 
        titleField.style.marginBottom = '10px';

        const textField = document.createElement('div');
        textField.id = 'padd-text';
        textField.style.fontFamily = 'LCARS';
        textField.style.fontSize = '18px';
        textField.style.color = '#FFFFFF';
        textField.style.textAlign = 'center';

        screen.appendChild(titleField);
        screen.appendChild(textField);


        const buttonPanel = document.createElement('div');
        buttonPanel.style.display = 'flex';
        buttonPanel.style.flexDirection = 'row';
        buttonPanel.style.width = '95%';
        buttonPanel.style.justifyContent = 'space-between';
        buttonPanel.style.marginTop = '10px';

        backButton = document.createElement('div');
        backButton.innerText = '◀';
        backButton.style.backgroundColor = '#FF6A00';
        backButton.style.color = '#FFFFFF';
        backButton.style.width = '60px';
        backButton.style.height = '40px';
        backButton.style.borderRadius = '10px';
        backButton.style.display = 'flex';
        backButton.style.alignItems = 'center';
        backButton.style.justifyContent = 'center';
        backButton.style.cursor = 'pointer';
        backButton.style.fontSize = '24px';
        backButton.style.fontFamily = 'LCARS';
        backButton.addEventListener('click', () => {
            if (currentStepIndex > 0) {
				currentStepIndex--;
				console.log('Current Step Index Backbutton:', currentStepIndex);
				console.log('Back original Function 1:', originalFunction);
				originalFunction = null;
				console.log('Back original Function 2:', originalFunction);
                updateTutorialStep(tutorialSteps, null, currentStepIndex);
				saveTutorialStep('colony', currentStepIndex);
            }
        });

        nextButton = document.createElement('div');
        nextButton.id = 'next-button';
        nextButton.innerText = '▶';
        nextButton.style.width = '60px';
        nextButton.style.height = '40px';
        nextButton.style.borderRadius = '10px';
        nextButton.style.display = 'flex';
        nextButton.style.alignItems = 'center';
        nextButton.style.justifyContent = 'center';
        nextButton.style.fontSize = '24px';
        nextButton.style.fontFamily = 'LCARS';

        buttonPanel.appendChild(backButton);
        buttonPanel.appendChild(nextButton);

        padd.appendChild(screen);
        padd.appendChild(buttonPanel);

        document.body.appendChild(padd);

  
        addDragAndDrop(padd);


        setTimeout(() => {
            padd.style.left = '50%';
            hasSlidIn = true;
        }, 10);
    } else {
        nextButton = document.getElementById('next-button');
    }

   
    if (hasInnerUpdate) {
        nextButton.style.backgroundColor = '#666666'; 
        nextButton.style.cursor = 'not-allowed';
        nextButton.onclick = null; 
    } else {
        nextButton.style.backgroundColor = '#FF6A00';
        nextButton.style.cursor = 'pointer';
        nextButton.onclick = () => {
            if (currentStepIndex < tutorialSteps.length - 1) {
				currentStepIndex++;
				console.log('Current Step Index Nextbutton:', currentStepIndex);
				console.log('originalFunction Nextbutton 1:', originalFunction);
				originalFunction = null;
				console.log('originalFunction Nextbutton 1:', originalFunction);
                updateTutorialStep(tutorialSteps, null, currentStepIndex);
                saveTutorialStep('colony', currentStepIndex);
            }
        };
    }


    document.getElementById('padd-title').innerText = title;
    document.getElementById('padd-text').innerText = text;
}




function addDragAndDrop(element) {
    let isDragging = false;
    let offsetX, offsetY;

    element.addEventListener('mousedown', (e) => {
        isDragging = true;
        offsetX = e.clientX - element.getBoundingClientRect().left;
        offsetY = e.clientY - element.getBoundingClientRect().top;
        element.style.cursor = 'grabbing';
        element.style.transition = 'none'; 
    });

    document.addEventListener('mousemove', (e) => {
        if (isDragging) {
            element.style.left = `${e.clientX - offsetX}px`;
            element.style.top = `${e.clientY - offsetY}px`;
            element.style.transform = 'none';
        }
    });

    document.addEventListener('mouseup', () => {
        isDragging = false;
        element.style.cursor = 'move';
    });
}


function initOverlay(innerContentElement) {
	const innerRect = innerContentElement.getBoundingClientRect();
    let overlay = document.getElementById('tutorial-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'tutorial-overlay';
        overlay.style.position = 'fixed';
        overlay.style.top = `${innerRect.top}px`;
        overlay.style.left = `${innerRect.left}px`;	
        overlay.style.width = '100vw';
        overlay.style.height = '100vh';
        overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.4)';
		overlay.style.zIndex = '1000';
		

        document.body.appendChild(overlay);
    }
    return overlay;
}

let frames = [];


window.addEventListener('scroll', updateFramesPositions);
window.addEventListener('resize', updateFramesPositions);


function updateFramePosition(frame, targetElement) {
    const rect = targetElement.getBoundingClientRect();
    const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

    frame.style.top = `${rect.top + scrollTop}px`;
    frame.style.left = `${rect.left + scrollLeft}px`;
    frame.style.width = `${rect.width}px`;
    frame.style.height = `${rect.height}px`;
}

function updateFramesPositions() {
    frames.forEach(({ frame, target }) => {
        updateFramePosition(frame, target);
    });
}

const originalFunctions = {};

function updateTutorialStep(tutorialSteps, startIndex, currentStepIndex) {
 
    if (startIndex != null) {
        currentStepIndex = startIndex;
        const fallbackIndex = tutorialSteps[currentStepIndex].fallbackIndex;
        if (fallbackIndex != null) {
            currentStepIndex = fallbackIndex;
        }
    }
    const currentStep = tutorialSteps[currentStepIndex];
    const elementIds = currentStep.elementIds;
    var innerUpdate = currentStep.innerUpdate;
    const elements = elementIds.map(id => document.getElementById(id));
    const innerContentElement = document.getElementById('innerContent');

    console.log('Updating tutorial step:', currentStepIndex, currentStep);
    console.log('InnerUpdate:', innerUpdate);
    console.log('window InnerUpdate:', window[innerUpdate]);

    
    tutorialSteps.forEach(step => {
        const stepElements = step.elementIds.map(id => document.getElementById(id));
        stepElements.forEach(element => {
            if (element) {
                removeHighlightFromElement(element);
            }
        });
    });


    const overlay = initOverlay(innerContentElement);


    elements.forEach(element => {
        addHighlightToElement(element);
    });

 
    initCloseButton(overlay, elements, innerContentElement);

    if (innerUpdate) {
        if (!originalFunctions[innerUpdate]) {
            originalFunctions[innerUpdate] = window[innerUpdate];
        }

        if (!window[innerUpdate].isModified) {
            window[innerUpdate] = function(...args) {
                window[innerUpdate].isModified = true;

            
                originalFunctions[innerUpdate].apply(this, args);

           
                if (currentStepIndex < tutorialSteps.length - 1) {
                    setTimeout(() => {
                        currentStepIndex++;
                        updateTutorialStep(tutorialSteps, null, currentStepIndex);
                        saveTutorialStep('colony', currentStepIndex);
                    }, 500); 
                }

            
                window[innerUpdate] = originalFunctions[innerUpdate];
                delete window[innerUpdate].isModified;
            };
        }
    }

    openPaddPopup(tutorialSteps, currentStepIndex);
}




function removeHighlightFromElement(element) {
    element.style.border = ''; 
    element.style.zIndex = ''; 
    element.style.position = ''; 
    element.style.animation = ''; 
}

function addHighlightToElement(element) {
    
    if (!document.getElementById('pulse-animation')) {
        const style = document.createElement('style');
        style.id = 'pulse-animation'; 
        style.innerHTML = `
        @keyframes pulse {
            0% {
                border-color: white;
            }
            50% {
                border-color: yellow;
            }
            100% {
                border-color: white;
            }
        }
        `;
        document.head.appendChild(style);
    }


    element.style.zIndex = '1001'; 
    element.style.position = 'relative'; 
    element.style.border = '2px solid white'; 
    element.style.animation = 'pulse 2s infinite'; 
}



function initCloseButton(overlay, elements, innerContentElement) {
    const innerRect = innerContentElement.getBoundingClientRect();
    let closeButton = document.getElementById('tutorial-close-button');
    if (!closeButton) {
        closeButton = document.createElement('button');
        closeButton.id = 'tutorial-close-button';
        closeButton.innerHTML = '<strong>&#10005;</strong>';
        closeButton.style.position = 'absolute';
        closeButton.style.top = `${innerRect.top + 10}px`;
        closeButton.style.left = `${innerRect.left + 10}px`;
        closeButton.style.zIndex = '1002';


        closeButton.style.backgroundColor = '#FF6A00'; 
        closeButton.style.color = '#FFFFFF';
        closeButton.style.border = 'none';
        closeButton.style.padding = '0';
        closeButton.style.cursor = 'pointer';
        closeButton.style.fontSize = '20px';
        closeButton.style.width = '60px';
        closeButton.style.height = '60px';
        closeButton.style.display = 'flex';
        closeButton.style.alignItems = 'center';
        closeButton.style.justifyContent = 'center';
        closeButton.style.boxShadow = '0 0 15px rgba(255, 106, 0, 0.7)';
        closeButton.style.transition = 'all 0.3s ease';


        closeButton.style.clipPath = 'polygon(0% 0%, 100% 0%, 85% 100%, 0% 100%)';


        closeButton.addEventListener('mouseover', () => {
            closeButton.style.backgroundColor = '#FF8C00';
            closeButton.style.boxShadow = '0 0 25px rgba(255, 140, 0, 1)';
            closeButton.style.transform = 'translateX(5px)';
        });

        closeButton.addEventListener('mouseout', () => {
            closeButton.style.backgroundColor = '#FF6A00';
            closeButton.style.boxShadow = '0 0 15px rgba(255, 106, 0, 0.7)';
            closeButton.style.transform = 'translateX(0)';
        });

      
        closeButton.addEventListener('mousedown', () => {
            closeButton.style.transform = 'translateX(5px) scale(0.95)';
            closeButton.style.boxShadow = '0 0 10px rgba(255, 140, 0, 0.5)';
        });

        closeButton.addEventListener('mouseup', () => {
            closeButton.style.transform = 'translateX(5px) scale(1)';
            closeButton.style.boxShadow = '0 0 25px rgba(255, 140, 0, 1)';
        });

      
        closeButton.addEventListener('click', () => {
        
            overlay.remove();

                elements.forEach(element => {
                element.style.border = ''; 
                element.style.zIndex = ''; 
                element.style.position = '';
            });

           
            const padd = document.getElementById('padd-popup');
            if (padd) {
                padd.remove();
            }

          
            closeButton.remove();
        });

        document.body.appendChild(closeButton);
    }
    return closeButton;
}

var saveTimeout;
function saveTutorialStep(module, currentStepIndex) {
    clearTimeout(saveTimeout);

    saveTimeout = setTimeout(function () {
        new Ajax.Request('game.php', {
            method: 'post',
            parameters: {
                B_SET_TUTORIAL: 1,
                module: module,
                nextstep: currentStepIndex
            },
            evalScripts: true,
            onSuccess: function(response) {
                console.log('Tutorial step saved successfully.');
            },
            onFailure: function(response) {
                console.error('Failed to save tutorial step:', response.statusText);
            }
        });
    }, 150);
}
