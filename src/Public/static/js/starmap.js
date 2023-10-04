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

	sectionIdElement = $('sectionid');
	if (sectionIdElement != null) {
		$('sectionid').innerHTML = section;
	}
}

var canNavigateLeft = true;
var canNavigateRight = true;
var canNavigateUp = true;
var canNavigateDown = true;

function updateNavButtonVisibility(left, right, up, down) {
	canNavigateLeft = left;
	canNavigateRight = right;
	canNavigateUp = up;
	canNavigateDown = down;

	setVisibility('navleft', left ? 'block' : 'none');
	setVisibility('navright', right ? 'block' : 'none');
	setVisibility('navup', up ? 'block' : 'none');
	setVisibility('navdown', down ? 'block' : 'none');
}

function setVisibility(id, style) {
	$(id).style.display = style;
}

function refreshMapSection(direction, isRedirect) {

	if (direction === 1 && !canNavigateLeft) {
		return;
	}
	if (direction === 3 && !canNavigateRight) {
		return;
	}
	if (direction === 4 && !canNavigateUp) {
		return;
	}
	if (direction === 2 && !canNavigateDown) {
		return;
	}

	href = getMapUpdateHref(direction);

	if (isRedirect) {
		document.location.href = href;
	} else {
		ajax_update('starmapsectiontable', href);
	}
}

function getMapUpdateHref(direction) {

	return `${currentModule}?${currentView}=1&section=${currentSection}&layerid=${currentLayerId}&direction=${direction}&macro=${currentMacro}`;
}
