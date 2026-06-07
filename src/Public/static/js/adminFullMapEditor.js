(function () {
	"use strict";

	const IMAGE_REFRESH_DEBOUNCE_MS = 600;
	const SELECTED_COLOR = "#ffe06b";

	function clamp(value, min, max) {
		return Math.max(min, Math.min(max, value));
	}

	function fieldKey(x, y) {
		return x + ":" + y;
	}

	function initAdminFullMapEditor() {
		const root = document.getElementById("adminFullMapEditor");
		if (!root) {
			return;
		}

		const canvas = document.getElementById("adminFullMapEditorCanvas");
		const state = {
			root,
			canvas,
			ctx: canvas.getContext("2d"),
			loading: document.getElementById("adminFullMapEditorLoading"),
			tooltip: document.getElementById("adminFullMapEditorTooltip"),
			status: document.getElementById("adminFullMapEditorStatus"),
			details: document.getElementById("adminFullMapEditorDetails"),
			selectedValue: document.getElementById("adminFullMapEditorSelectedValue"),
			imageUrl: root.dataset.imageUrl,
			dataUrl: root.dataset.dataUrl,
			editUrl: root.dataset.editUrl,
			layerId: Number(root.dataset.layerId),
			layerWidth: Number(root.dataset.layerWidth),
			layerHeight: Number(root.dataset.layerHeight),
			cellSize: Number(root.dataset.cellSize),
			mapImage: new Image(),
			mapLoaded: false,
			dataLoaded: false,
			scale: 1,
			minScale: 0.1,
			maxScale: 8,
			viewX: 0,
			viewY: 0,
			dragging: false,
			dragMoved: false,
			dragStartX: 0,
			dragStartY: 0,
			dragViewX: 0,
			dragViewY: 0,
			mouseX: 0,
			mouseY: 0,
			fields: [],
			fieldByKey: new Map(),
			fieldById: new Map(),
			selectedField: null,
			selectedFields: [],
			activeTool: "none",
			selectedFieldTypeId: 0,
			editInFlight: false,
			imageRefreshTimer: 0,
			drawQueued: false,
			selecting: false,
			selectionStart: null,
			selectionEnd: null,
			selectionLastCell: null,
			selectionPathKeys: new Set(),
			selectionModeRectangle: false,
			selectedFieldsAreRectangle: false,
			showGrid: true,
			showRegion: false,
			showAdminRegion: false,
			showSystemType: false,
			showPassable: false,
			showInfluence: false,
			showBorder: true,
			showEffects: true,
		};

		wireControls(state);
		resizeCanvas(state);
		loadMapImage(state, false, false);
		loadEditorData(state);
		window.addEventListener("resize", function () {
			resizeCanvas(state);
			scheduleDraw(state);
		});
	}

	function wireControls(state) {
		document.getElementById("adminFullMapEditorLayer").addEventListener("change", function () {
			window.location.href = "/admin/?SHOW_ADMIN_FULL_MAP_EDITOR=1&layerid=" + this.value;
		});

		document.getElementById("adminFullMapEditorRefreshImage").addEventListener("click", function () {
			loadMapImage(state, true, true);
		});

		document.getElementById("adminFullMapEditorRefreshData").addEventListener("click", function () {
			loadEditorData(state);
		});
		const rectangleSelectionInput = document.getElementById("adminFullMapEditorRectangleSelection");
		state.selectionModeRectangle = rectangleSelectionInput.checked;
		rectangleSelectionInput.addEventListener("change", function () {
			state.selectionModeRectangle = this.checked;
			setStatus(state, state.selectionModeRectangle ? "Rechteckauswahl aktiv" : "Pinselauswahl aktiv");
		});

		wireCheckbox(state, "adminFullMapEditorShowGrid", "showGrid");
		wireCheckbox(state, "adminFullMapEditorShowRegion", "showRegion");
		wireCheckbox(state, "adminFullMapEditorShowAdminRegion", "showAdminRegion");
		wireCheckbox(state, "adminFullMapEditorShowSystemType", "showSystemType");
		wireCheckbox(state, "adminFullMapEditorShowPassable", "showPassable");
		wireCheckbox(state, "adminFullMapEditorShowInfluence", "showInfluence");
		wireCheckbox(state, "adminFullMapEditorShowBorder", "showBorder");
		wireCheckbox(state, "adminFullMapEditorShowEffects", "showEffects");

		document.querySelectorAll('input[name="adminFullMapEditorTool"]').forEach(function (input) {
			input.addEventListener("change", function () {
				if (this.checked) {
					state.activeTool = this.value;
					updateSelectedValueLabel(state);
				}
			});
		});

		document.getElementById("adminFullMapEditorFieldTypes").addEventListener("click", function (event) {
			const button = event.target instanceof Element
				? event.target.closest("[data-field-type-id]")
				: null;
			if (!button) {
				return;
			}

			state.selectedFieldTypeId = Number(button.dataset.fieldTypeId);
			document.querySelectorAll("#adminFullMapEditorFieldTypes .adminFullMapEditorTileButton").forEach(function (entry) {
				entry.classList.toggle("isSelected", entry === button);
			});
			selectTool(state, "fieldType");
			updateSelectedValueLabel(state);
		});

		[
			["adminFullMapEditorSystemTypeValue", "systemType"],
			["adminFullMapEditorRegionValue", "region"],
			["adminFullMapEditorAdminRegionValue", "adminRegion"],
			["adminFullMapEditorInfluenceAreaValue", "influenceArea"],
			["adminFullMapEditorPassableValue", "passable"],
			["adminFullMapEditorBorderValue", "border"],
			["adminFullMapEditorEffectsMode", "effects"],
		].forEach(function (entry) {
			const input = document.getElementById(entry[0]);
			input.addEventListener("change", function () {
				selectTool(state, entry[1]);
				updateSelectedValueLabel(state);
			});
		});

		document.getElementById("adminFullMapEditorEffects").addEventListener("change", function () {
			selectTool(state, "effects");
			updateSelectedValueLabel(state);
		});

		document.getElementById("adminFullMapEditorApplySelected").addEventListener("click", function () {
			const fields = state.selectedFields.length > 0
				? state.selectedFields
				: (state.selectedField ? [state.selectedField] : []);
			if (fields.length === 0) {
				setStatus(state, "Kein Feld gewählt");
				return;
			}
			applyActiveToolToFields(state, fields);
		});

		state.canvas.addEventListener("mousedown", function (event) {
			if (event.button !== 0) {
				return;
			}
			if ((event.ctrlKey || event.metaKey) && state.dataLoaded) {
				if (startFieldSelection(state, event)) {
					event.preventDefault();
					return;
				}
			}
			state.dragging = true;
			state.dragMoved = false;
			state.dragStartX = event.clientX;
			state.dragStartY = event.clientY;
			state.dragViewX = state.viewX;
			state.dragViewY = state.viewY;
			state.canvas.classList.add("isDragging");
		});

		state.canvas.addEventListener("mouseup", function (event) {
			if (state.selecting) {
				return;
			}
			if (state.dragging && !state.dragMoved) {
				const field = getFieldAtScreen(state, event.offsetX, event.offsetY);
				if (field) {
					selectField(state, field);
					if (state.activeTool !== "none") {
						applyActiveToolToFields(state, [field]);
					}
				}
			}
		});

		window.addEventListener("mouseup", function (event) {
			if (state.selecting) {
				finishFieldSelection(state, event);
				return;
			}

			state.dragging = false;
			state.canvas.classList.remove("isDragging");
		});

		window.addEventListener("mousemove", function (event) {
			const rect = state.canvas.getBoundingClientRect();
			state.mouseX = event.clientX - rect.left;
			state.mouseY = event.clientY - rect.top;

			if (state.selecting) {
				updateFieldSelection(state, event);
				hideTooltip(state);
				return;
			}

			if (state.dragging) {
				const deltaX = event.clientX - state.dragStartX;
				const deltaY = event.clientY - state.dragStartY;
				if (Math.abs(deltaX) > 3 || Math.abs(deltaY) > 3) {
					state.dragMoved = true;
				}
				state.viewX = state.dragViewX - deltaX / state.scale;
				state.viewY = state.dragViewY - deltaY / state.scale;
				clampView(state);
				hideTooltip(state);
				scheduleDraw(state);
				return;
			}

			updateTooltip(state);
		});

		state.canvas.addEventListener("mouseleave", function () {
			hideTooltip(state);
		});

		state.canvas.addEventListener(
			"wheel",
			function (event) {
				event.preventDefault();
				zoomAt(state, event.offsetX, event.offsetY, event.deltaY < 0 ? 1.18 : 0.85);
			},
			{ passive: false }
		);

		updateSelectedValueLabel(state);
	}

	function wireCheckbox(state, id, stateKey) {
		const input = document.getElementById(id);
		state[stateKey] = input.checked;
		input.addEventListener("change", function () {
			state[stateKey] = this.checked;
			updateTooltip(state);
			scheduleDraw(state);
		});
	}

	function selectTool(state, tool) {
		const input = document.querySelector('input[name="adminFullMapEditorTool"][value="' + tool + '"]');
		if (input instanceof HTMLInputElement) {
			input.checked = true;
			state.activeTool = tool;
		}
	}

	function resizeCanvas(state) {
		const rect = state.canvas.parentElement.getBoundingClientRect();
		const dpr = window.devicePixelRatio || 1;
		state.canvas.width = Math.max(1, Math.floor(rect.width * dpr));
		state.canvas.height = Math.max(1, Math.floor(rect.height * dpr));
		state.canvas.style.width = rect.width + "px";
		state.canvas.style.height = rect.height + "px";
		state.ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
		if (state.mapLoaded) {
			updateScaleBounds(state);
			clampView(state);
		}
	}

	function updateScaleBounds(state) {
		const canvasWidth = state.canvas.clientWidth;
		const canvasHeight = state.canvas.clientHeight;
		state.minScale = Math.min(
			canvasWidth / state.mapImage.width,
			canvasHeight / state.mapImage.height
		);
		state.maxScale = Math.max(8, state.minScale * 80);
		state.scale = clamp(state.scale, state.minScale, state.maxScale);
	}

	function loadMapImage(state, refresh, preserveView) {
		state.mapLoaded = false;
		state.loading.style.display = "flex";
		state.loading.textContent = refresh ? "Erzeuge Basisgrafik..." : "Lade Karte...";
		setStatus(state, refresh ? "Basisgrafik wird neu erzeugt" : "Basisgrafik wird geladen");

		const oldScale = state.scale;
		const oldViewX = state.viewX;
		const oldViewY = state.viewY;
		const image = new Image();
		image.onload = function () {
			state.mapImage = image;
			state.mapLoaded = true;
			updateScaleBounds(state);
			if (preserveView) {
				state.scale = clamp(oldScale, state.minScale, state.maxScale);
				state.viewX = oldViewX;
				state.viewY = oldViewY;
			} else {
				state.scale = Math.max(state.minScale, Math.min(1, state.scale));
				state.viewX = (state.mapImage.width - state.canvas.clientWidth / state.scale) / 2;
				state.viewY = (state.mapImage.height - state.canvas.clientHeight / state.scale) / 2;
			}
			clampView(state);
			state.loading.style.display = "none";
			setStatus(state, "Basisgrafik geladen");
			scheduleDraw(state);
		};
		image.onerror = function () {
			state.loading.style.display = "none";
			setStatus(state, "Basisgrafik konnte nicht geladen werden");
		};

		image.src = state.imageUrl + (refresh ? "&refresh=1" : "") + "&ts=" + Date.now();
	}

	function loadEditorData(state) {
		state.loading.style.display = "flex";
		state.loading.textContent = "Lade Kartendaten...";
		setStatus(state, "Kartendaten werden geladen");

		fetch(state.dataUrl + "&ts=" + Date.now(), {
			headers: { "X-Requested-With": "XMLHttpRequest" },
			cache: "no-store",
		})
			.then(function (response) {
				if (!response.ok) {
					throw new Error("HTTP " + response.status);
				}
				return response.json();
			})
			.then(function (data) {
				state.fields = Array.isArray(data.fields) ? data.fields : [];
				rebuildFieldIndex(state);
				state.dataLoaded = true;
				if (state.selectedField) {
					state.selectedField = state.fieldById.get(state.selectedField.id) || null;
				}
				if (state.selectedFields.length > 0) {
					state.selectedFields = state.selectedFields
						.map(function (field) {
							return state.fieldById.get(field.id) || null;
						})
						.filter(Boolean);
				}
				updateDetails(state);
				state.loading.style.display = state.mapLoaded ? "none" : "flex";
				setStatus(state, state.fields.length + " Felder geladen");
				updateTooltip(state);
				scheduleDraw(state);
			})
			.catch(function (error) {
				state.loading.style.display = state.mapLoaded ? "none" : "flex";
				setStatus(state, "Kartendaten konnten nicht geladen werden: " + error.message);
			});
	}

	function rebuildFieldIndex(state) {
		state.fieldByKey = new Map();
		state.fieldById = new Map();
		state.fields.forEach(function (field) {
			state.fieldByKey.set(fieldKey(field.x, field.y), field);
			state.fieldById.set(field.id, field);
		});
	}

	function setStatus(state, text) {
		state.status.textContent = text;
	}

	function scheduleDraw(state) {
		if (state.drawQueued) {
			return;
		}
		state.drawQueued = true;
		requestAnimationFrame(function () {
			state.drawQueued = false;
			draw(state);
		});
	}

	function draw(state) {
		const ctx = state.ctx;
		const canvasWidth = state.canvas.clientWidth;
		const canvasHeight = state.canvas.clientHeight;

		ctx.save();
		ctx.setTransform(window.devicePixelRatio || 1, 0, 0, window.devicePixelRatio || 1, 0, 0);
		ctx.clearRect(0, 0, canvasWidth, canvasHeight);
		ctx.fillStyle = "#000000";
		ctx.fillRect(0, 0, canvasWidth, canvasHeight);

		if (!state.mapLoaded) {
			ctx.restore();
			return;
		}

		ctx.scale(state.scale, state.scale);
		ctx.translate(-state.viewX, -state.viewY);
		ctx.imageSmoothingEnabled = false;
		ctx.drawImage(state.mapImage, 0, 0);

		if (state.dataLoaded) {
			drawOverlays(state);
		}
		if (state.showGrid) {
			drawGrid(state);
		}
		drawSelectedField(state);
		drawFieldSelection(state);
		ctx.restore();
	}

	function drawOverlays(state) {
		const bounds = getVisibleCellBounds(state);
		for (let y = bounds.startY; y <= bounds.endY; y++) {
			for (let x = bounds.startX; x <= bounds.endX; x++) {
				const field = state.fieldByKey.get(fieldKey(x, y));
				if (!field) {
					continue;
				}
				drawFieldOverlay(state, field);
			}
		}
	}

	function drawFieldOverlay(state, field) {
		const ctx = state.ctx;
		const cell = state.cellSize;
		const x = (field.x - 1) * cell;
		const y = (field.y - 1) * cell;
		const labels = [];

		if (state.showPassable && field.passable === false) {
			ctx.fillStyle = "rgba(170, 18, 18, 0.42)";
			ctx.fillRect(x, y, cell, cell);
			labels.push("!");
		}

		if (state.showInfluence && field.influenceAreaId) {
			ctx.fillStyle = hashColor(field.influenceAreaId, 0.42);
			ctx.fillRect(x, y, cell, cell);
			labels.push(String(field.influenceAreaId));
		}

		if (state.showRegion && field.regionId) {
			ctx.fillStyle = "rgba(255, 76, 96, 0.32)";
			ctx.fillRect(x, y, cell, cell);
			labels.push("R" + field.regionId);
		}

		if (state.showAdminRegion && field.adminRegionId) {
			ctx.fillStyle = "rgba(83, 155, 255, 0.34)";
			ctx.fillRect(x, y, cell, cell);
			labels.push("A" + field.adminRegionId);
		}

		if (state.showSystemType && field.systemTypeId) {
			ctx.fillStyle = "rgba(255, 224, 107, 0.36)";
			ctx.fillRect(x, y, cell, cell);
			labels.push("S" + field.systemTypeId);
		}

		if (state.showEffects && field.effects.length > 0) {
			ctx.fillStyle = "rgba(255, 154, 31, 0.38)";
			ctx.fillRect(x, y, cell, cell);
			labels.push("E" + field.effects.length);
		}

		if (state.showBorder && field.borderColor) {
			ctx.strokeStyle = field.borderColor;
			ctx.lineWidth = Math.max(1.5 / state.scale, 0.2);
			ctx.strokeRect(x + ctx.lineWidth / 2, y + ctx.lineWidth / 2, cell - ctx.lineWidth, cell - ctx.lineWidth);
		}

		if (labels.length > 0 && state.scale * cell >= 13) {
			drawCellLabel(state, labels.join(" "), x, y);
		}
	}

	function drawCellLabel(state, label, x, y) {
		const ctx = state.ctx;
		const fontSize = Math.max(8 / state.scale, Math.min(12, 11 / state.scale));
		ctx.font = "bold " + fontSize + "px sans-serif";
		ctx.textBaseline = "top";
		ctx.lineWidth = Math.max(2 / state.scale, 1);
		ctx.strokeStyle = "rgba(0, 0, 0, 0.9)";
		ctx.fillStyle = "#ffffff";
		ctx.strokeText(label, x + 2 / state.scale, y + 2 / state.scale);
		ctx.fillText(label, x + 2 / state.scale, y + 2 / state.scale);
	}

	function drawGrid(state) {
		if (state.scale * state.cellSize < 5) {
			return;
		}

		const ctx = state.ctx;
		const bounds = getVisibleCellBounds(state);
		const cell = state.cellSize;
		ctx.beginPath();
		ctx.strokeStyle = "rgba(255, 255, 255, 0.22)";
		ctx.lineWidth = Math.max(1 / state.scale, 0.15);

		for (let x = bounds.startX; x <= bounds.endX + 1; x++) {
			const px = (x - 1) * cell;
			ctx.moveTo(px, (bounds.startY - 1) * cell);
			ctx.lineTo(px, bounds.endY * cell);
		}
		for (let y = bounds.startY; y <= bounds.endY + 1; y++) {
			const py = (y - 1) * cell;
			ctx.moveTo((bounds.startX - 1) * cell, py);
			ctx.lineTo(bounds.endX * cell, py);
		}
		ctx.stroke();
	}

	function drawSelectedField(state) {
		if (!state.selectedField) {
			return;
		}

		const ctx = state.ctx;
		const cell = state.cellSize;
		const x = (state.selectedField.x - 1) * cell;
		const y = (state.selectedField.y - 1) * cell;
		ctx.strokeStyle = SELECTED_COLOR;
		ctx.lineWidth = Math.max(2 / state.scale, 0.25);
		ctx.strokeRect(x + ctx.lineWidth / 2, y + ctx.lineWidth / 2, cell - ctx.lineWidth, cell - ctx.lineWidth);
	}

	function drawFieldSelection(state) {
		if (state.selectedFields.length <= 1) {
			return;
		}

		if (state.selectedFieldsAreRectangle) {
			drawSelectionRectangle(state);
			return;
		}

		const ctx = state.ctx;
		const cell = state.cellSize;
		ctx.fillStyle = "rgba(255, 224, 107, 0.18)";
		ctx.strokeStyle = SELECTED_COLOR;
		ctx.lineWidth = Math.max(1.5 / state.scale, 0.2);
		state.selectedFields.forEach(function (field) {
			const x = (field.x - 1) * cell;
			const y = (field.y - 1) * cell;
			ctx.fillRect(x, y, cell, cell);
			ctx.strokeRect(x + ctx.lineWidth / 2, y + ctx.lineWidth / 2, cell - ctx.lineWidth, cell - ctx.lineWidth);
		});
	}

	function drawSelectionRectangle(state) {
		const bounds = getSelectionBounds(state);
		if (!bounds) {
			return;
		}

		const ctx = state.ctx;
		const cell = state.cellSize;
		const x = (bounds.startX - 1) * cell;
		const y = (bounds.startY - 1) * cell;
		const width = (bounds.endX - bounds.startX + 1) * cell;
		const height = (bounds.endY - bounds.startY + 1) * cell;
		ctx.fillStyle = "rgba(255, 224, 107, 0.18)";
		ctx.fillRect(x, y, width, height);
		ctx.strokeStyle = SELECTED_COLOR;
		ctx.lineWidth = Math.max(2 / state.scale, 0.25);
		ctx.strokeRect(x + ctx.lineWidth / 2, y + ctx.lineWidth / 2, width - ctx.lineWidth, height - ctx.lineWidth);
	}

	function getVisibleCellBounds(state) {
		const visibleWidth = state.canvas.clientWidth / state.scale;
		const visibleHeight = state.canvas.clientHeight / state.scale;
		return {
			startX: clamp(Math.floor(state.viewX / state.cellSize) + 1, 1, state.layerWidth),
			endX: clamp(Math.ceil((state.viewX + visibleWidth) / state.cellSize), 1, state.layerWidth),
			startY: clamp(Math.floor(state.viewY / state.cellSize) + 1, 1, state.layerHeight),
			endY: clamp(Math.ceil((state.viewY + visibleHeight) / state.cellSize), 1, state.layerHeight),
		};
	}

	function getFieldAtScreen(state, screenX, screenY) {
		const cell = getCellAtScreen(state, screenX, screenY, false);
		return cell ? state.fieldByKey.get(fieldKey(cell.x, cell.y)) || null : null;
	}

	function getCellAtScreen(state, screenX, screenY, clampToMap) {
		const worldX = state.viewX + screenX / state.scale;
		const worldY = state.viewY + screenY / state.scale;
		let x = Math.floor(worldX / state.cellSize) + 1;
		let y = Math.floor(worldY / state.cellSize) + 1;

		if (clampToMap) {
			x = clamp(x, 1, state.layerWidth);
			y = clamp(y, 1, state.layerHeight);
			return { x, y };
		}

		if (x < 1 || y < 1 || x > state.layerWidth || y > state.layerHeight) {
			return null;
		}

		return { x, y };
	}

	function startFieldSelection(state, event) {
		const cell = getCellAtScreen(state, event.offsetX, event.offsetY, false);
		if (!cell) {
			return false;
		}

		state.selecting = true;
		state.dragging = false;
		state.dragMoved = false;
		state.selectionStart = cell;
		state.selectionEnd = cell;
		state.selectionLastCell = cell;
		state.selectionPathKeys = new Set();
		state.selectedFieldsAreRectangle = state.selectionModeRectangle;
		state.selectedFields = [];
		addCellToSelection(state, cell);
		state.canvas.classList.add("isSelecting");
		setStatus(state, "Auswahl: 1 Feld");
		hideTooltip(state);
		scheduleDraw(state);
		return true;
	}

	function updateFieldSelection(state, event) {
		const rect = state.canvas.getBoundingClientRect();
		const cell = getCellAtScreen(
			state,
			event.clientX - rect.left,
			event.clientY - rect.top,
			true
		);
		state.selectionEnd = cell;
		state.dragMoved = state.selectionStart.x !== cell.x || state.selectionStart.y !== cell.y;
		if (state.selectionModeRectangle) {
			state.selectedFields = getRectangleSelectionFields(state);
			state.selectedFieldsAreRectangle = true;
		} else {
			addCellLineToSelection(state, state.selectionLastCell, cell);
			state.selectionLastCell = cell;
			state.selectedFieldsAreRectangle = false;
		}
		setStatus(state, "Auswahl: " + getFieldsInSelection(state).length + " Felder");
		scheduleDraw(state);
	}

	function finishFieldSelection(state, event) {
		updateFieldSelection(state, event);
		const fields = getFieldsInSelection(state);
		state.selecting = false;
		state.canvas.classList.remove("isSelecting");

		if (fields.length === 0) {
			state.selectionStart = null;
			state.selectionEnd = null;
			state.selectionLastCell = null;
			state.selectionPathKeys.clear();
			setStatus(state, "Keine Felder gewählt");
			scheduleDraw(state);
			return;
		}

		state.selectedFields = fields;
		state.selectedField = fields[fields.length - 1];
		updateDetails(state);

		if (state.activeTool !== "none") {
			applyActiveToolToFields(state, fields);
			return;
		}

		setStatus(state, fields.length === 1 ? "1 Feld gewählt" : fields.length + " Felder gewählt");
		scheduleDraw(state);
	}

	function getSelectionBounds(state) {
		if (!state.selectionStart || !state.selectionEnd) {
			return null;
		}

		return {
			startX: Math.min(state.selectionStart.x, state.selectionEnd.x),
			endX: Math.max(state.selectionStart.x, state.selectionEnd.x),
			startY: Math.min(state.selectionStart.y, state.selectionEnd.y),
			endY: Math.max(state.selectionStart.y, state.selectionEnd.y),
		};
	}

	function getFieldsInSelection(state) {
		return state.selectionModeRectangle && state.selectedFieldsAreRectangle
			? getRectangleSelectionFields(state)
			: state.selectedFields;
	}

	function getRectangleSelectionFields(state) {
		const bounds = getSelectionBounds(state);
		if (!bounds) {
			return [];
		}
		const fields = [];
		for (let y = bounds.startY; y <= bounds.endY; y++) {
			for (let x = bounds.startX; x <= bounds.endX; x++) {
				const field = state.fieldByKey.get(fieldKey(x, y));
				if (field) {
					fields.push(field);
				}
			}
		}

		return fields;
	}

	function addCellLineToSelection(state, fromCell, toCell) {
		if (!fromCell) {
			addCellToSelection(state, toCell);
			return;
		}

		const steps = Math.max(Math.abs(toCell.x - fromCell.x), Math.abs(toCell.y - fromCell.y), 1);
		for (let step = 0; step <= steps; step++) {
			addCellToSelection(state, {
				x: Math.round(fromCell.x + (toCell.x - fromCell.x) * step / steps),
				y: Math.round(fromCell.y + (toCell.y - fromCell.y) * step / steps),
			});
		}
	}

	function addCellToSelection(state, cell) {
		const key = fieldKey(cell.x, cell.y);
		if (state.selectionPathKeys.has(key)) {
			return;
		}

		const field = state.fieldByKey.get(key);
		if (!field) {
			return;
		}

		state.selectionPathKeys.add(key);
		state.selectedFields.push(field);
		state.selectedField = field;
	}

	function selectField(state, field) {
		state.selectedField = field;
		state.selectedFields = [field];
		state.selectionStart = null;
		state.selectionEnd = null;
		state.selectionLastCell = null;
		state.selectionPathKeys.clear();
		state.selectedFieldsAreRectangle = false;
		updateDetails(state);
		scheduleDraw(state);
	}

	function updateDetails(state) {
		if (state.selectedFields.length > 1) {
			const firstField = state.selectedFields[0];
			const lastField = state.selectedFields[state.selectedFields.length - 1];
			const details = [
				"<strong>" + state.selectedFields.length + " Felder gewählt</strong>",
				"Werkzeug: " + escapeHtml(getToolLabel(state)),
			];
			if (state.selectedFieldsAreRectangle) {
				details.splice(1, 0, "Rechteck " + firstField.x + "|" + firstField.y + " bis " + lastField.x + "|" + lastField.y);
			} else {
				details.splice(1, 0, "Pinselauswahl");
			}
			state.details.innerHTML = details.join("<br>");
			return;
		}

		if (!state.selectedField) {
			state.details.textContent = "Feld anklicken";
			return;
		}

		const field = state.selectedField;
		state.details.innerHTML = [
			"<strong>Feld " + field.x + "|" + field.y + "</strong>",
			"ID " + field.id,
			"Feldtyp: " + escapeHtml(field.fieldName) + " (" + field.fieldTypeId + ")",
			"Passierbar: " + (field.passable ? "Ja" : "Nein"),
			"Systemtyp: " + escapeHtml(formatNullable(field.systemTypeName, field.systemTypeId)),
			"System: " + escapeHtml(formatNullable(field.systemName, field.systemId)),
			"Region: " + escapeHtml(formatNullable(field.regionName, field.regionId)),
			"Admin-Region: " + escapeHtml(formatNullable(field.adminRegionName, field.adminRegionId)),
			"Einflussbereich: " + escapeHtml(formatNullable(field.influenceAreaName, field.influenceAreaId)),
			"Grenze: " + escapeHtml(formatNullable(field.borderDescription, field.borderTypeId)),
			"Effekte: " + (field.effects.length > 0 ? escapeHtml(field.effects.join(", ")) : "Keine"),
		].join("<br>");
	}

	function updateTooltip(state) {
		const field = getFieldAtScreen(state, state.mouseX, state.mouseY);
		if (!field || state.dragging || state.selecting) {
			hideTooltip(state);
			return;
		}

		state.tooltip.innerHTML = [
			"<strong>Feld " + field.x + "|" + field.y + "</strong>",
			escapeHtml(field.fieldName),
			"Passierbar: " + (field.passable ? "Ja" : "Nein"),
			field.regionId ? "Region: " + escapeHtml(formatNullable(field.regionName, field.regionId)) : "",
			field.adminRegionId ? "Admin: " + escapeHtml(formatNullable(field.adminRegionName, field.adminRegionId)) : "",
			field.influenceAreaId ? "Area: " + escapeHtml(formatNullable(field.influenceAreaName, field.influenceAreaId)) : "",
			field.effects.length > 0 ? "Effekte: " + escapeHtml(field.effects.join(", ")) : "",
		].filter(Boolean).join("<br>");

		positionTooltip(state);
		state.tooltip.style.display = "block";
	}

	function positionTooltip(state) {
		const margin = 12;
		const stageRect = state.canvas.parentElement.getBoundingClientRect();
		const tooltipRect = state.tooltip.getBoundingClientRect();
		let left = state.mouseX + margin;
		let top = state.mouseY + margin;
		if (left + tooltipRect.width > stageRect.width) {
			left = state.mouseX - tooltipRect.width - margin;
		}
		if (top + tooltipRect.height > stageRect.height) {
			top = state.mouseY - tooltipRect.height - margin;
		}
		state.tooltip.style.left = Math.max(4, left) + "px";
		state.tooltip.style.top = Math.max(4, top) + "px";
	}

	function hideTooltip(state) {
		state.tooltip.style.display = "none";
	}

	function updateSelectedValueLabel(state) {
		const tool = state.activeTool;
		let label = "Nur Feldauswahl";
		if (tool === "fieldType") {
			const selectedButton = document.querySelector("#adminFullMapEditorFieldTypes .adminFullMapEditorTileButton.isSelected");
			label = selectedButton ? "Feldtyp: " + selectedButton.dataset.label : "Feldtyp: keine Auswahl";
		}
		if (tool === "systemType") {
			label = "Systemtyp: " + getSelectedOptionLabel("adminFullMapEditorSystemTypeValue");
		}
		if (tool === "region") {
			label = "Region: " + getSelectedOptionLabel("adminFullMapEditorRegionValue");
		}
		if (tool === "adminRegion") {
			label = "Admin-Region: " + getSelectedOptionLabel("adminFullMapEditorAdminRegionValue");
		}
		if (tool === "influenceArea") {
			label = "Einflussbereich: " + getSelectedOptionLabel("adminFullMapEditorInfluenceAreaValue");
		}
		if (tool === "passable") {
			label = "Passierbar: " + getSelectedOptionLabel("adminFullMapEditorPassableValue");
		}
		if (tool === "border") {
			label = "Grenze: " + getSelectedOptionLabel("adminFullMapEditorBorderValue");
		}
		if (tool === "effects") {
			const mode = getSelectedOptionLabel("adminFullMapEditorEffectsMode");
			label = "Effekte: " + mode + ", " + getSelectedEffects().length + " gewählt";
		}
		state.selectedValue.textContent = label;
	}

	function getSelectedOptionLabel(id) {
		const select = document.getElementById(id);
		return select.options[select.selectedIndex] ? select.options[select.selectedIndex].textContent.trim() : "keine Auswahl";
	}

	function getToolLabel(state) {
		switch (state.activeTool) {
			case "fieldType":
				return "Feldtyp";
			case "systemType":
				return "Systemtyp";
			case "region":
				return "Region";
			case "adminRegion":
				return "Admin-Region";
			case "influenceArea":
				return "Einflussbereich";
			case "passable":
				return "Passierbarkeit";
			case "border":
				return "Grenze";
			case "effects":
				return "Effekte";
			default:
				return "Nur Auswahl";
		}
	}

	function getSelectedEffects() {
		return Array.from(document.querySelectorAll('input[name="adminFullMapEditorEffects[]"]:checked'))
			.map(function (input) {
				return input.value;
			});
	}

	function applyActiveTool(state, field) {
		applyActiveToolToFields(state, [field]);
	}

	function applyActiveToolToFields(state, fields) {
		if (state.editInFlight) {
			setStatus(state, "Speichern läuft bereits");
			return;
		}
		if (state.activeTool === "none") {
			setStatus(state, fields.length === 1
				? "Feld " + fields[0].x + "|" + fields[0].y + " gewählt"
				: fields.length + " Felder gewählt");
			scheduleDraw(state);
			return;
		}
		if (fields.length === 0) {
			setStatus(state, "Keine Felder gewählt");
			return;
		}
		const payload = new URLSearchParams();
		payload.set("field", String(fields[0].id));
		fields.forEach(function (field) {
			payload.append("fields[]", String(field.id));
		});
		payload.set("operation", state.activeTool);

		if (!appendToolPayload(state, payload)) {
			return;
		}

		state.editInFlight = true;
		setStatus(state, fields.length === 1
			? "Speichere Feld " + fields[0].x + "|" + fields[0].y + "..."
			: "Speichere " + fields.length + " Felder...");

		fetch(state.editUrl, {
			method: "POST",
			headers: {
				"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
				"X-Requested-With": "XMLHttpRequest",
			},
			body: payload.toString(),
		})
			.then(function (response) {
				return response.json().then(function (data) {
					if (!response.ok || data.success !== true) {
						throw new Error(data.message || "HTTP " + response.status);
					}
					return data;
				});
			})
			.then(function (data) {
				applyEditResponse(state, data);
				const changedCount = data.changedFieldCount || fields.length;
				setStatus(state, changedCount === 1 ? "Feld gespeichert" : changedCount + " Felder gespeichert");
			})
			.catch(function (error) {
				setStatus(state, "Speichern fehlgeschlagen: " + error.message);
			})
			.finally(function () {
				state.editInFlight = false;
			});
	}

	function appendToolPayload(state, payload) {
		switch (state.activeTool) {
			case "fieldType":
				if (state.selectedFieldTypeId <= 0) {
					setStatus(state, "Kein Feldtyp gewählt");
					return false;
				}
				payload.set("value", String(state.selectedFieldTypeId));
				return true;
			case "systemType":
				payload.set("value", document.getElementById("adminFullMapEditorSystemTypeValue").value);
				return true;
			case "region":
				payload.set("value", document.getElementById("adminFullMapEditorRegionValue").value);
				return true;
			case "adminRegion":
				payload.set("value", document.getElementById("adminFullMapEditorAdminRegionValue").value);
				return true;
			case "influenceArea":
				payload.set("value", document.getElementById("adminFullMapEditorInfluenceAreaValue").value);
				return true;
			case "passable":
				payload.set("value", document.getElementById("adminFullMapEditorPassableValue").value);
				return true;
			case "border":
				payload.set("value", document.getElementById("adminFullMapEditorBorderValue").value);
				return true;
			case "effects":
				return appendEffectsPayload(payload);
			default:
				setStatus(state, "Unbekanntes Werkzeug");
				return false;
		}
	}

	function appendEffectsPayload(payload) {
		const mode = document.getElementById("adminFullMapEditorEffectsMode").value;
		const effects = getSelectedEffects();
		if (effects.length === 0 && mode !== "replace") {
			const status = document.getElementById("adminFullMapEditorStatus");
			status.textContent = "Keine Effekte gewählt";
			return false;
		}
		payload.set("effectsMode", mode);
		effects.forEach(function (effect) {
			payload.append("effects[]", effect);
		});
		return true;
	}

	function applyEditResponse(state, data) {
		if (Array.isArray(data.fieldTypeUpdates)) {
			data.fieldTypeUpdates.forEach(function (fieldTypeUpdate) {
				applyFieldTypeUpdate(state, fieldTypeUpdate);
			});
		} else if (data.fieldTypeUpdate) {
			applyFieldTypeUpdate(state, data.fieldTypeUpdate);
		}

		const changedFields = Array.isArray(data.fields)
			? data.fields
			: (data.field ? [data.field] : []);
		if (changedFields.length > 0) {
			changedFields.forEach(function (field) {
				replaceField(state, field);
			});
			state.selectedFields = changedFields;
			state.selectedField = changedFields[changedFields.length - 1];
			if (changedFields.length === 1) {
				state.selectionStart = null;
				state.selectionEnd = null;
			}
		}
		if (data.baseImageChanged === true) {
			scheduleBaseImageRefresh(state);
		}
		updateDetails(state);
		updateTooltip(state);
		scheduleDraw(state);
	}

	function replaceField(state, field) {
		const oldField = state.fieldById.get(field.id);
		if (oldField) {
			const index = state.fields.indexOf(oldField);
			if (index >= 0) {
				state.fields[index] = field;
			}
			state.fieldByKey.delete(fieldKey(oldField.x, oldField.y));
		} else {
			state.fields.push(field);
		}
		state.fieldById.set(field.id, field);
		state.fieldByKey.set(fieldKey(field.x, field.y), field);
	}

	function applyFieldTypeUpdate(state, fieldTypeUpdate) {
		state.fields.forEach(function (field) {
			if (field.fieldTypeId !== fieldTypeUpdate.id) {
				return;
			}
			field.passable = fieldTypeUpdate.passable;
			field.effects = Array.isArray(fieldTypeUpdate.effects) ? fieldTypeUpdate.effects : [];
		});
	}

	function scheduleBaseImageRefresh(state) {
		if (state.imageRefreshTimer) {
			window.clearTimeout(state.imageRefreshTimer);
		}
		state.imageRefreshTimer = window.setTimeout(function () {
			state.imageRefreshTimer = 0;
			loadMapImage(state, true, true);
		}, IMAGE_REFRESH_DEBOUNCE_MS);
	}

	function zoomAt(state, screenX, screenY, factor) {
		if (!state.mapLoaded) {
			return;
		}
		const worldX = state.viewX + screenX / state.scale;
		const worldY = state.viewY + screenY / state.scale;
		state.scale = clamp(state.scale * factor, state.minScale, state.maxScale);
		state.viewX = worldX - screenX / state.scale;
		state.viewY = worldY - screenY / state.scale;
		clampView(state);
		updateTooltip(state);
		scheduleDraw(state);
	}

	function clampView(state) {
		if (!state.mapLoaded) {
			return;
		}

		const visibleWidth = state.canvas.clientWidth / state.scale;
		const visibleHeight = state.canvas.clientHeight / state.scale;
		const maxViewX = state.mapImage.width - visibleWidth;
		const maxViewY = state.mapImage.height - visibleHeight;

		state.viewX = maxViewX < 0 ? maxViewX / 2 : clamp(state.viewX, 0, maxViewX);
		state.viewY = maxViewY < 0 ? maxViewY / 2 : clamp(state.viewY, 0, maxViewY);
	}

	function formatNullable(name, id) {
		if (name && id) {
			return name + " (" + id + ")";
		}
		if (id) {
			return String(id);
		}
		return "Keine";
	}

	function hashColor(value, alpha) {
		const hash = (Number(value) * 2654435761) >>> 0;
		const r = (hash & 0xff0000) >> 16;
		const g = (hash & 0x00ff00) >> 8;
		const b = hash & 0x0000ff;
		return "rgba(" + r + ", " + g + ", " + b + ", " + alpha + ")";
	}

	function escapeHtml(value) {
		return String(value)
			.replace(/&/g, "&amp;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;")
			.replace(/"/g, "&quot;")
			.replace(/'/g, "&#039;");
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", initAdminFullMapEditor);
	} else {
		initAdminFullMapEditor();
	}
})();
