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
	ajax_update(elt, createHostUri('SHOW_BUILDING', '&buildingid=' + buildingId));
	ajax_update('COLONY_SURFACE', createHostUri('SHOW_SURFACE', '&buildingid=' + buildingId));
	buildmode = 1;
	selectedbuilding = buildingId;

	closeAjaxCallbacksMandatory.push(() => {
		closeBuildingInfo();
	});
}

function closeBuildingInfo() {
	ajax_update('COLONY_SURFACE', createHostUri('SHOW_SURFACE'));
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

	performActionAndUpdateResult(action, `fid=${fieldId}&buildingid=${bid}`);
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
	ajax_update('COLONY_SURFACE', createHostUri('SHOW_SURFACE', '&buildingid=' + selectedbuilding));

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
		Element.removeClassName(tab, 'module_selector_current');
	});
	Element.addClassName(obj, 'module_selector_current');
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

	tabElement = $('module_tab_' + type);
	Element.removeClassName(tabElement, 'module_selector_unselected');
	Element.removeClassName(tabElement, 'module_selector_skipped');

	if (moduleId == 0) {
		$('tab_image_mod_' + type).src = 'assets/buttons/modul_' + type + '.png';
		$('module_type_' + type).innerHTML = '';
		Element.addClassName(tabElement, 'module_selector_skipped');
		updateCrewCount(type, 0);
	} else {
		if (amount < 1) {
			Element.addClassName(tabElement, 'module_selector_unselected');
		}
		$('tab_image_mod_' + type).src = 'assets/commodities/' + commodityId + '.png';
		$('module_type_' + type).innerHTML = $(moduleId + '_content').innerHTML;
		$('module_type_' + type).show();
		updateCrewCount(type, module_crew);
	}

	enableShipBuildButton();
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

	enableShipBuildButton();
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

	if (isShipBuildPossible()) {
		Form.Element.enable('buildbutton');
		new Effect.Highlight($('buildbutton'));
	} else {
		Form.Element.disable('buildbutton');
	}
}
function isShipBuildPossible() {
	if (!checkCrewCount()) {
		return false;
	}
	unselected = false;
	$('module_select_tabs').select('div').each(function (tab) {
		if (Element.hasClassName(tab, 'module_selector_unselected')) {
			unselected = true;
		}
	});
	return !unselected;
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
	ajax_update('elt', 'database.php?commodityid=' + commodityId + '&SHOW_COMMODITIES_LOCATIONS=1');
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
		ajax_update('elt', 'colony.php?SHOW_TELESCOPE_SCAN=1&id=' + colonyid + '&x=' + cx + '&y=' + cy);
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

function showNewSandboxWindow() {
	elt = 'elt'
	openPJsWin(elt, 1);
	ajax_update(elt, '?SHOW_NEW_SANDBOX=1');
}

function createNewSandbox(sstr) {

	let colonyId = document.getElementById('colony-id').value;
	let name = document.getElementById('sandbox-name').value;

	actionToInnerContent('B_CREATE_SANDBOX', `cid=${colonyId}&name=${name}&sstr=${sstr}`);
}
