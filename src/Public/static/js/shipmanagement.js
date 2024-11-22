var selbg = '';
function shipSelectorHover(obj) {
	selbg = obj.style.backgroundColor;
	obj.style.backgroundColor = '#7c85a8';
	obj.style.cursor = 'pointer';
}
function shipSelectorHoverEnd(obj) {
	obj.style.backgroundColor = selbg;
	obj.style.cursor = 'auto';
	selbg = '';
}
function switchToShip(obj) {
	link = Element.select(obj, 'a');
	if (link.length == 0) {
		return;
	}
	link[0].click();
}
function shipSelectorChoose(obj) {
	shiplist = document.getElementById("shiplist");
	if (!shiplist) {
		switchToShip(obj);
		return;
	}
	shipSelectorHoverEnd(obj);
	sel = $('shipselector');
	sel.innerHTML = '';
	sel.appendChild(obj.parentNode);
	closeAjaxWindow();
	Element.remove($('shiplist'));
}

function decreaseShuttleAmount(cid) {
	old = parseInt($('shuttleAmount_' + cid).value);

	if (old > 0) {
		$('shuttleAmount_' + cid).value = old - 1;

		current = parseInt($('storedshuttles').innerHTML);
		$('storedshuttles').innerHTML = current - 1;
	}
}

function increaseShuttleAmount(cid, maxOf, maxTotal) {
	old = parseInt($('shuttleAmount_' + cid).value);
	current = parseInt($('storedshuttles').innerHTML);

	if (old < maxOf && current < maxTotal) {
		$('shuttleAmount_' + cid).value = old + 1;
		$('storedshuttles').innerHTML = current + 1;
	}
}

function openShuttleManagement(obj, ship, entity) {
	closeAjaxWindow();

	var pos = findObject(obj);
	openWindowPosition('elt', 1, 200, pos[0] - 200, pos[1]);
	ajax_update('elt', `?SHOW_SHUTTLE_MANAGEMENT=1&id=${entity}&shipid=${ship}`);
}

function toggleCrew(shipId, currentCrew, neededCrew) {
	var isToggleChecked = $(`crewToggle_${shipId}`).checked;
	var isManned = currentCrew > 0;
	var setToZero = isManned === isToggleChecked;
	var crew = setToZero ? 0 : (isManned ? currentCrew : neededCrew);

	$(`crewRange_${shipId}`).value = crew;
	$(`crewCount_${shipId}`).innerText = crew;

	setCrewSliderColor(shipId, neededCrew);
}

function setCrew(obj, shipId, neededCrew) {

	$(`crewCount_${shipId}`).innerText = obj.value;
	$(`crewToggle_${shipId}`).checked = false;

	setCrewSliderColor(shipId, neededCrew);
}

function setCrewSliderColor(shipId, neededCrew) {
	var obj = $(`crewRange_${shipId}`);

	if (obj.value < neededCrew) {
		obj.style = 'accent-color: red;';
	} else {
		obj.style = 'accent-color: green;';
	}
}
