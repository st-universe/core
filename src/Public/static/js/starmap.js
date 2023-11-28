var currentModule = '';
var currentView = '';
var currentMacro = '';

function registerNavKeys(module, view, macro, isRedirect) {

	currentModule = module;
	currentView = view;
	currentMacro = macro;

	document.addEventListener("keydown", (event) => {

		if (event.key === "ArrowUp") {
			refreshMapSection(4, isRedirect);
		}
		if (event.key === "ArrowDown") {
			refreshMapSection(2, isRedirect);
		}
		if (event.key === "ArrowLeft") {
			refreshMapSection(1, isRedirect);
		}
		if (event.key === "ArrowRight") {
			refreshMapSection(3, isRedirect);
		}
	});
}

var currentSection = 0;
var currentLayerId = 0;

function updateSectionAndLayer(section, layerId) {
	currentSection = section;
	currentLayerId = layerId;

	updateSectionId();
}

function updateSectionId() {

	element = $('sectionid');

	if (currentSection != 0 && element != null) {
		element.innerHTML = currentSection;
	}
}

var canNavigateLeft = true;
var canNavigateRight = true;
var canNavigateUp = true;
var canNavigateDown = true;

function updateNavButtons(left, right, up, down) {
	canNavigateLeft = left;
	canNavigateRight = right;
	canNavigateUp = up;
	canNavigateDown = down;

	updateNavButtonsVisibility();
}

function updateNavButtonsVisibility() {
	setVisibility('navleft', canNavigateLeft ? 'block' : 'none');
	setVisibility('navright', canNavigateRight ? 'block' : 'none');
	setVisibility('navup', canNavigateUp ? 'block' : 'none');
	setVisibility('navdown', canNavigateDown ? 'block' : 'none');
}

function setVisibility(id, style) {
	element = $(id);
	if (element != null) {
		element.style.display = style;
	}
}

function refreshMapContent(direction) {

	if (!isDirectionAllowed(direction)) {
		return;
	}

	params = `section=${currentSection}&layerid=${currentLayerId}&direction=${direction}&macro=${currentMacro}`;
	switchInnerContent(currentView, 'Sektion anzeigen', params, currentModule);
}

function refreshMapSection(direction, isRedirect) {

	if (!isDirectionAllowed(direction)) {
		return;
	}

	href = getMapUpdateHref(direction);

	if (isRedirect) {
		document.location.href = href;
	} else {
		ajax_update('starmapsectiontable', href);
	}
}

function isDirectionAllowed(direction) {

	if (direction === 1 && !canNavigateLeft) {
		return false;
	}
	if (direction === 3 && !canNavigateRight) {
		return false;
	}
	if (direction === 4 && !canNavigateUp) {
		return false;
	}
	if (direction === 2 && !canNavigateDown) {
		return false;
	}

	return true;
}

function getMapUpdateHref(direction) {

	return `${currentModule}?${currentView}=1&section=${currentSection}&layerid=${currentLayerId}&direction=${direction}&macro=${currentMacro}`;
}

function updateNavigation() {
	updateSectionId();
	updateNavButtonsVisibility();
}
