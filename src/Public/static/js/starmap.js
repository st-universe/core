var navKeysRegistered = false;
var currentModule = '';
var currentView = '';
var currentMacro = '';

function registerNavKeys(module, view, macro, isRedirect) {

	if (navKeysRegistered) {
		return;
	}

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

	navKeysRegistered = true;
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

function refreshMapSection(direction, isRedirect) {

	if (!isDirectionAllowed(direction)) {
		return;
	}

	href = getMapUpdateHref(direction);

	if (isRedirect) {
		document.location.href = href;
	} else {
		ajax_update($('starmapsectioncontent') ? 'starmapsectioncontent' : 'starmapsectiontable', href);
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

function initStarmapSectionInfo() {
	document.querySelectorAll('[data-starmap-section-info]').forEach(function (root) {
		if (root.dataset.starmapInfoInitialized !== '1') {
			bindStarmapSectionInfo(root);
			root.dataset.starmapInfoInitialized = '1';
		}

		syncStarmapSectionFilters(root);
	});
}

function bindStarmapSectionInfo(root) {
	var isTouchTooltipMode = window.matchMedia
		? window.matchMedia('(hover: none), (pointer: coarse)').matches
		: false;
	var pinnedCell = null;

	root.querySelectorAll('[data-starmap-filter]').forEach(function (control) {
		control.addEventListener('change', function () {
			syncStarmapSectionFilters(root);
		});
	});

	function getTooltip() {
		return root.querySelector('[data-starmap-tooltip]:not(td)');
	}

	function getTooltipCell(target) {
		if (!target) {
			return null;
		}
		if (target.nodeType !== 1) {
			target = target.parentElement;
		}
		if (!target || typeof target.closest !== 'function') {
			return null;
		}

		var cell = target.closest('.starmapSectionTable [data-starmap-tooltip]');
		return cell && root.contains(cell) ? cell : null;
	}

	function showTooltip(cell) {
		var tooltip = getTooltip();
		var text = cell ? cell.getAttribute('data-starmap-tooltip') : '';

		if (!tooltip || !text) {
			hideTooltip();
			return false;
		}

		tooltip.textContent = text;
		tooltip.style.display = 'block';
		return true;
	}

	function positionTooltipByMouse(event) {
		var tooltip = getTooltip();
		if (!tooltip) {
			return;
		}

		var margin = 12;
		var left = event.clientX + margin;
		var top = event.clientY + margin;

		tooltip.style.left = left + 'px';
		tooltip.style.top = top + 'px';

		var rect = tooltip.getBoundingClientRect();
		var maxLeft = window.innerWidth - rect.width - margin;
		var maxTop = window.innerHeight - rect.height - margin;

		if (left > maxLeft) {
			left = event.clientX - rect.width - margin;
		}
		if (top > maxTop) {
			top = event.clientY - rect.height - margin;
		}

		tooltip.style.left = Math.max(margin, left) + 'px';
		tooltip.style.top = Math.max(margin, top) + 'px';
	}

	function positionTooltipByCell(cell) {
		var tooltip = getTooltip();
		if (!tooltip) {
			return;
		}

		var margin = 12;
		var cellRect = cell.getBoundingClientRect();
		var tooltipRect = tooltip.getBoundingClientRect();
		var left = cellRect.right + margin;
		var top = cellRect.top;

		if (left + tooltipRect.width + margin > window.innerWidth) {
			left = cellRect.left - tooltipRect.width - margin;
		}
		if (left < margin) {
			left = margin;
			top = cellRect.bottom + margin;
		}
		if (top + tooltipRect.height + margin > window.innerHeight) {
			top = cellRect.top - tooltipRect.height - margin;
		}

		tooltip.style.left = Math.max(margin, Math.min(left, window.innerWidth - tooltipRect.width - margin)) + 'px';
		tooltip.style.top = Math.max(margin, Math.min(top, window.innerHeight - tooltipRect.height - margin)) + 'px';
	}

	function hideTooltip() {
		var tooltip = getTooltip();
		if (tooltip) {
			tooltip.style.display = 'none';
		}
		pinnedCell = null;
	}

	if (isTouchTooltipMode) {
		root.addEventListener('click', function (event) {
			var cell = getTooltipCell(event.target);

			if (!cell) {
				return;
			}

			var link = event.target.closest && event.target.closest('a');
			if (link && pinnedCell === cell) {
				hideTooltip();
				return;
			}

			event.preventDefault();
			event.stopPropagation();

			if (pinnedCell === cell) {
				hideTooltip();
				return;
			}

			pinnedCell = cell;
			if (showTooltip(cell)) {
				positionTooltipByCell(cell);
			}
		});

		document.addEventListener('click', function (event) {
			if (!root.contains(event.target)) {
				hideTooltip();
			}
		});

		window.addEventListener('resize', hideTooltip);
		window.addEventListener('scroll', hideTooltip, true);
	} else {
		root.addEventListener('mousemove', function (event) {
			var cell = getTooltipCell(event.target);

			if (!cell || !showTooltip(cell)) {
				hideTooltip();
				return;
			}

			positionTooltipByMouse(event);
		});

		root.addEventListener('mouseleave', hideTooltip);
	}
}

function syncStarmapSectionFilters(root) {
	var territory = root.querySelector('[data-starmap-filter="territory"]');
	var impassable = root.querySelector('[data-starmap-filter="impassable"]');
	var effects = root.querySelector('[data-starmap-filter="effects"]');

	root.classList.toggle('starmapSectionShowTerritory', territory !== null && territory.checked);
	root.classList.toggle('starmapSectionShowImpassable', impassable !== null && impassable.checked);
	root.classList.toggle('starmapSectionShowEffects', effects !== null && effects.checked);
}
