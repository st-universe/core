document.addEventListener('mouseup', clearDragSelection);

var isDragging = false;
var draggedFields = new Set();

function clearDragSelection() {
	if (isDragging && draggedFields.size > 1) {
		if (confirm(`${draggedFields.size} Felder aktualisieren?`)) {
			draggedFields.forEach(fid => {
				var field = document.querySelector(`[onclick*="${fid}"]`);
				if (field) {
					updateField(field, fid);
				}
			});
		}
	}

	document.querySelectorAll('.starmap').forEach(field => {
		field.style.outline = "";
	});
	draggedFields.clear();
	isDragging = false;
}

function handleFieldClick(obj, fieldid) {
	if (isDragging && draggedFields.size > 1) {
		return;
	} else {
		updateField(obj, fieldid);
	}
}

function handleMouseDown(obj, fieldid, event) {
	event.preventDefault();
	isDragging = false;
	draggedFields.clear();
	draggedFields.add(fieldid);
	obj.parentNode.style.outline = "2px solid #4caf50";
}


function handleMouseEnter(obj, fieldid) {
	if (event.buttons === 1) {
		isDragging = true;
		draggedFields.add(fieldid);
		obj.parentNode.style.outline = "2px solid #4caf50";
	}
}

function toggleMapfieldType(obj) {
	if (selectedFieldType == 0) {
		return;
	}
	if (tmpfield == 0) {
		tmpfield = obj.parentNode.style.backgroundImage;
		obj.parentNode.style.backgroundImage =
			"url(" + gfx_path + "/map/" + selectedFieldType + ".png)";
		return;
	}
	obj.parentNode.style.backgroundImage = tmpfield;
	tmpfield = 0;
	return;
}

var selectedEffects = new Set();
function selectEffects() {
	selectedEffects.clear();

	const checkboxes = document.querySelectorAll('input[name="effects[]"]:checked');

	Array.from(checkboxes)
		.forEach(checkbox => selectedEffects.add(checkbox.value));

	$('reseteffects').checked = false;
}
function resetEffects() {
	selectedEffects.clear();

	document
		.querySelectorAll('input[name="effects[]"]:checked')
		.forEach(checkbox => checkbox.checked = false);
}

function updateField(obj, fieldid) {
	if (
		selectedFieldType == 0
		&& selectedSystemType == 0
		&& selectedRegion == 0
		&& adminregionselector == 0
		&& passableselector == 0
		&& borderselector == 0
		&& areaid == 0
		&& selectedEffects == null
		&& $('reseteffects').checked == false
	) {
		alert(
			"Es wurde weder ein Systemtyp, Region, AdminRegion, Border, Passiebarkeit noch ein Feldtyp ausgewählt"
		);
		return;
	}

	var parentTd = obj.parentNode;

	if (selectedFieldType != 0) {
		ajax_update(
			false,
			"/admin/?B_EDIT_FIELD=1&field=" + fieldid + "&type=" + selectedFieldType
		);
		obj.style.backgroundImage =
			"url(" + gfx_path + "/map/" + selectedFieldType + ".png)";
		tmpfield = obj.style.backgroundImage;
	}
	if (selectedSystemType != 0) {
		ajax_update(
			false,
			"/admin/?B_EDIT_SYSTEMTYPE_FIELD=1&field=" +
			fieldid +
			"&type=" +
			selectedSystemType
		);
		parentTd.setAttribute("data-system-type-id", selectedSystemType);
		if (fieldevent == 3) {
			createOverlay(parentTd, "rgba(255, 255, 0, 0.5)");
		}
	}
	if (selectedRegion != 0) {
		ajax_update(
			false,
			"/admin/?B_EDIT_REGION=1&field=" + fieldid + "&region=" + selectedRegion
		);
		parentTd.setAttribute("data-region", selectedRegion);
		if (fieldevent == 1) {
			createOverlay(parentTd, "rgba(255, 0, 0, 0.5)");
		}
	}
	if (areaid != 0) {
		ajax_update(
			false,
			"/admin/?B_EDIT_INFLUENCE_AREA=1&field=" + fieldid + "&area=" + areaid
		);
		parentTd.setAttribute("data-influence-area", areaid);

		if (fieldevent == 5) {
			var overlay = createOverlay(parentTd, getRandomColorForNumber(areaid));

			var existingSpan = obj.querySelector(".influence-id");
			if (existingSpan) {
				existingSpan.remove();
			}

			var span = document.createElement("span");
			span.className = "influence-id";
			span.style.position = "absolute";
			span.style.top = "50%";
			span.style.left = "50%";
			span.style.transform = "translate(-50%, -50%)";
			span.style.color = "white";
			span.style.fontWeight = "bold";
			span.innerText = areaid;

			if (overlay) {
				overlay.appendChild(span);
			}
		}
	}

	if (adminregionselector != 0) {
		ajax_update(
			false,
			"/admin/?B_EDIT_ADMIN_REGION=1&field=" +
			fieldid +
			"&adminregion=" +
			adminregionselector
		);
		parentTd.setAttribute("data-admin-region", adminregionselector);
		if (fieldevent == 2) {
			createOverlay(parentTd, "rgba(0, 0, 255, 0.5)");
		}
	}
	if (passableselector != 0) {
		ajax_update(
			false,
			"/admin/?B_EDIT_PASSABLE=1&field=" +
			fieldid +
			"&passable=" +
			passableselector
		);
		var passableValue = passableselector == 1 ? 1 : 0;
		parentTd.setAttribute("data-passable", passableValue);
		if (fieldevent == 4 && passableValue == 0) {
			createOverlay(parentTd, "rgba(255, 255, 0, 0.5)");
		}
	}
	if (borderselector != 0) {
		ajax_update(
			false,
			"/admin/?B_EDIT_BORDER=1&field=" + fieldid + "&border=" + borderselector
		);
		if (fieldevent == 7) {
			createOverlay(parentTd, bordercolor);
		}
	}
	if (selectedEffects.size > 0) {

		const joined = [...selectedEffects].join('&effects[]=');

		ajax_update(
			false,
			`/admin/?B_EDIT_EFFECTS=1&field=${fieldid}&effects[]=${joined}`
		);
		parentTd.setAttribute("data-effects", [...selectedEffects].join(','));

		if (fieldevent == 6) {
			parentTd.title = [...selectedEffects].join('\n');
			createOverlay(parentTd, "rgba(255, 174, 0, 0.5)");
		}
	}
	if ($('reseteffects').checked) {
		ajax_update(
			false,
			`/admin/?B_RESET_EFFECTS=1&field=${fieldid}`
		);
		parentTd.setAttribute("data-effects", "");
		if (fieldevent == 6) {
			parentTd.removeAttribute('title');
			removeOverlay(parentTd);
		}
	}
}

var selectedFieldType = 0;
var selectedSystemType = 0;
var fieldeventselector = 0;
var selectedRegion = 0;
var areaid = 0;
var adminregionselector = 0;
var passableselector = 0;
var borderselector = 0;
var tmpfield = 0;
var fieldevent = 0;
var bordercolor = "";

function openSystemFieldSelector(x, y, systemId) {
	updatePopup(
		`/admin/?SHOW_SYSTEM_EDITFIELD=1&systemid=${systemId}&x=${x}&y=${y}`
	);
}
function selectNewSystemMapField(fieldid, cx, cy, typeid, type) {
	ajax_update(
		false,
		"/admin/?B_EDIT_SYSTEM_FIELD=1&field=" + fieldid + "&type=" + typeid
	);
	field = $(cx + "_" + cy);
	field.style.backgroundImage = "url(" + gfx_path + "/map/" + type + ".png)";
	closeAjaxWindow();
}

function fieldEventSelector(type) {
	var cells = document.querySelectorAll(".starmap");
	fieldevent = type;

	cells.forEach(function (cell) {
		removeOverlay(cell);
	});

	if (type === 0) {
		console.log("Executing type 0");
	}

	if (type === 1) {
		cells.forEach(function (cell) {
			var regionValue = cell.getAttribute("data-region");

			if (regionValue > 1) {
				createOverlay(cell, "rgba(255, 0, 0, 0.5)");
			}
		});
	}

	if (type === 2) {
		cells.forEach(function (cell) {
			var regionValue = cell.getAttribute("data-admin-region");

			if (regionValue > 1) {
				createOverlay(cell, "rgba(0, 0, 255, 0.5)");
			}
		});
	}

	if (type === 3) {
		cells.forEach(function (cell) {
			var regionValue = cell.getAttribute("data-system-type-id");

			if (regionValue > 1) {
				createOverlay(cell, "rgba(255, 255, 0, 0.5)");
			}
		});
	}

	if (type === 4) {
		cells.forEach(function (cell) {
			var regionValue = cell.getAttribute("data-passable");

			if (regionValue == 0) {
				createOverlay(cell, "rgba(255, 255, 0, 0.5)");
			}
		});
	}

	if (type === 5) {
		cells.forEach(function (cell) {
			var regionValue = cell.getAttribute("data-influence-area");

			if (regionValue) {
				overlay = createOverlay(cell, getRandomColorForNumber(regionValue));

				var span = document.createElement("span");
				span.className = "influence-id";
				span.style.position = "absolute";
				span.style.top = "50%";
				span.style.left = "50%";
				span.style.transform = "translate(-50%, -50%)";
				span.style.color = "white";
				span.innerText = regionValue;

				overlay.appendChild(span);
			}
		});
	}

	if (type === 6) {
		cells.forEach(function (cell) {
			var effectsValue = cell.getAttribute("data-effects");

			if (effectsValue) {
				overlay = createOverlay(cell, "rgba(255, 174, 0, 0.5)");
				overlay.parentNode.parentNode.title = effectsValue;
			}
		});
	}

	if (type === 7) {
		cells.forEach(function (cell) {
			var borderValue = cell.getAttribute("data-border");

			if (borderValue) {
				overlay = createOverlay(cell, borderValue);
				overlay.parentNode.parentNode.title = borderValue;
			}
		});
	}
}

function createOverlay(cell, backgroundColor) {
	removeOverlay(cell);

	var divbody = cell.querySelector(".divbody");

	if (!divbody) return null;

	var overlay = document.createElement("div");
	overlay.className = "overlay";
	overlay.style.position = "absolute";
	overlay.style.top = 0;
	overlay.style.left = 0;
	overlay.style.width = "100%";
	overlay.style.height = "100%";
	overlay.style.backgroundColor = backgroundColor;
	divbody.style.position = "relative";
	divbody.appendChild(overlay);

	return overlay;
}

function removeOverlay(cell) {
	let overlay = cell.querySelector(".overlay");

	if (overlay) {
		overlay.remove();
	}
}

function hashNumberToColor(number) {
	const hash = number * 2654435761 % 2 ** 32;
	const r = (hash & 0xFF0000) >> 16;
	const g = (hash & 0x00FF00) >> 8;
	const b = hash & 0x0000FF;
	const a = 0.5;
	return `rgba(${r}, ${g}, ${b}, ${a})`;
}
function getRandomColorForNumber(number) {
	return hashNumberToColor(number);
}
function updateTransparency(value) {

	const overlays = document.querySelectorAll('.overlay');
	overlays.forEach(overlay => {
		const currentColor = overlay.style.backgroundColor;
		const newColor = currentColor.replace(/rgba\((\d+), (\d+), (\d+), [^)]+\)/, `rgba($1, $2, $3, ${value})`);
		overlay.style.backgroundColor = newColor;
	});
}
function selectMapFieldType(type) {
	if (type === 0) {
		$("fieldtypeselector").innerHTML = "Nichts gewählt";
	} else {
		$("fieldtypeselector").innerHTML =
			'<img src="' + gfx_path + "/map/" + type + '.png" />';
	}
	selectedFieldType = type;
}
function selectSystemType(type) {
	if (type === 0) {
		$("systemtypeselector").innerHTML = "Nichts gewählt";
	} else {
		$("systemtypeselector").innerHTML =
			'<img src="' + gfx_path + "/map/systemtypes/" + type + '.png" />';
	}
	selectedSystemType = type;
}
function selectRegion(type, name) {
	if (type === 0) {
		$("regionselector").innerHTML = "Nichts gewählt";
	} else {
		$("regionselector").innerHTML = name;
	}
	selectedRegion = type;
}
function selectAdminRegion(type, name) {
	if (type === 0) {
		$("adminregionselector").innerHTML = "Nichts gewählt";
	} else {
		$("adminregionselector").innerHTML = name;
	}
	adminregionselector = type;
}
function selectBorder(type, name, color) {
	if (type === 0) {
		$("borderselector").innerHTML = "Nichts gewählt";
	} else {
		$("borderselector").innerHTML = name;
	}
	borderselector = type;
	bordercolor = color;
}

function selectPassable(type) {
	if (type === 0) {
		$("passable").innerHTML = "Nichts gewählt";
	}
	if (type === 1) {
		$("passable").innerHTML = "True";
	}
	if (type === 2) {
		$("passable").innerHTML = "False";
	}
	passableselector = type;
}
function selectArea(id) {
	if (id === 0) {
		$("areaselector").innerHTML = "Nichts gewählt";
	} else {
		$("areaselector").innerHTML = id;
	}
	areaid = id;
}

function registerSystemEditorNavKeys(previousId, currentId, nextId) {
	document.addEventListener("keydown", (event) => {
		if (event.key === "ArrowLeft" && previousId > 0) {
			document.location.href = '?SHOW_SYSTEM=1&systemid=' + previousId;
		}
		if (event.key === "ArrowRight" && nextId > 0) {
			document.location.href = '?SHOW_SYSTEM=1&systemid=' + nextId;
		}
		if (event.key === "ArrowUp") {
			document.location.href = '?REGENERATE_SYSTEM=1&systemid=' + currentId;
		}
		if (event.key === "ArrowDown") {
			document.location.href = '?SHOW_SYSTEM=1&systemid=' + currentId;
		}
	});
}
