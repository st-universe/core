var currentModule = '';
var currentView = '';

function registerNavKeys(module, view) {

	currentModule = module;
	currentView = view;

	document.addEventListener("keydown", (event) => {

		if (event.key === "ArrowUp") {
			refreshMapSectionTable(4);
		}
		if (event.key === "ArrowDown") {
			refreshMapSectionTable(2);
		}
		if (event.key === "ArrowLeft") {
			refreshMapSectionTable(1);
		}
		if (event.key === "ArrowRight") {
			refreshMapSectionTable(3);
		}
	});
}

var currentSection = 0;
var currentLayerId = 0;

function updateSectionAndLayer(section, layerId) {
	currentSection = section;
	currentLayerId = layerId;
}

function updateNavButtonVisibility(left, right, up, down) {
	setVisibility('navleft', left ? 'block' : 'none');
	setVisibility('navright', right ? 'block' : 'none');
	setVisibility('navup', up ? 'block' : 'none');
	setVisibility('navdown', down ? 'block' : 'none');
}

function setVisibility(id, style) {
	$(id).style.display = style;
}

function refreshMapSectionTable(direction) {
	ajax_update('starmapsectiontable', getMapUpdateHref(direction));
}

function getMapUpdateHref(direction) {

	return `${currentModule}.php?${currentView}=1&section=${currentSection}&layerid=${currentLayerId}&direction=` + direction;
}
