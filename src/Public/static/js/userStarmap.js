(function () {
	"use strict";

	const VIEW_PADDING_CELLS = 3;
	const MIN_SCALE = 0.08;
	const MAX_SCALE = 8;
	const AXIS_TOP_HEIGHT = 24;
	const AXIS_LEFT_WIDTH = 42;
	const AXIS_MIN_LABEL_SPACING = 26;
	const AXIS_MIN_TICK_SPACING = 6;

	function clamp(value, min, max) {
		return Math.max(min, Math.min(max, value));
	}

	function fieldKey(x, y) {
		return x + ":" + y;
	}

	function escapeHtml(value) {
		return String(value)
			.replace(/&/g, "&amp;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;")
			.replace(/"/g, "&quot;")
			.replace(/'/g, "&#039;");
	}

	function getCssColor(name, fallback) {
		const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
		return value || fallback;
	}

	function getMapViewportWidth(state) {
		return Math.max(1, state.canvasWidth - AXIS_LEFT_WIDTH);
	}

	function getMapViewportHeight(state) {
		return Math.max(1, state.canvasHeight - AXIS_TOP_HEIGHT);
	}

	function getCanvasPoint(state, clientX, clientY) {
		const rect = state.canvas.getBoundingClientRect();
		return {
			x: clientX - rect.left,
			y: clientY - rect.top
		};
	}

	function initUserStarmap() {
		const root = document.getElementById("userStarmap");
		if (!root) {
			return;
		}
		if (root.dataset.userStarmapInitialized === "1") {
			return;
		}
		root.dataset.userStarmapInitialized = "1";

		const canvas = document.getElementById("userStarmapCanvas");
		const state = {
			root,
			canvas,
			ctx: canvas.getContext("2d"),
			loading: document.getElementById("userStarmapLoading"),
			tooltip: document.getElementById("userStarmapTooltip"),
			status: document.getElementById("userStarmapStatus"),
			fieldDetails: document.getElementById("userStarmapFieldDetails"),
			imageUrl: root.dataset.imageUrl,
			dataUrl: root.dataset.dataUrl,
			layerId: Number(root.dataset.layerId),
			layerWidth: Number(root.dataset.layerWidth),
			layerHeight: Number(root.dataset.layerHeight),
			cellSize: Number(root.dataset.cellSize),
			fieldsPerSection: Number(root.dataset.fieldsPerSection),
			canvasWidth: 1,
			canvasHeight: 1,
			mapPixelWidth: 1,
			mapPixelHeight: 1,
			mapImage: new Image(),
			mapLoaded: false,
			dataLoaded: false,
			drawQueued: false,
			viewInitialized: false,
			scale: 1,
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
			lastTouchAt: 0,
			pinching: false,
			pinchStartDistance: 0,
			pinchStartScale: 1,
			pinchWorldX: 0,
			pinchWorldY: 0,
			showTerritory: true,
			showImpassable: false,
			showEffects: false,
			showGrid: true,
			selectedSectorId: 0,
			fields: [],
			fieldByKey: new Map(),
			territoryFields: [],
			effectFields: [],
			impassableFields: [],
			iconFields: [],
			visibleRuns: [],
			iconCache: new Map(),
			selectedField: null,
			loadingLabel: "Lade Karte",
			loadingProgress: 0,
			loadingDots: 1,
			loadingTimer: 0,
			colors: {
				grid: "rgba(255, 255, 255, 0.16)",
				effects: getCssColor("--color-35", "#d8b34a"),
				impassable: getCssColor("--color-25", "#ff4040"),
				sectorHighlight: getCssColor("--color-35", "#d8b34a"),
				selection: getCssColor("--color-56", "#ffffff"),
				axisBackground: getCssColor("--color-29", "#101010"),
				axisBorder: getCssColor("--color-53", "#333333"),
				axisTick: getCssColor("--color-16", "#555555"),
				axisText: getCssColor("--color-56", "#ffffff")
			}
		};

		wireControls(state);
		resizeCanvas(state);
		loadData(state);

		window.addEventListener("resize", function () {
			resizeCanvas(state);
			if (state.viewInitialized) {
				clampView(state);
			}
			scheduleDraw(state);
		});
	}

	function wireControls(state) {
		document.getElementById("userStarmapLayer").addEventListener("change", function () {
			window.location.href = "starmap.php?layerid=" + this.value;
		});

		document.getElementById("userStarmapRefresh").addEventListener("click", function () {
			loadData(state);
		});

		document.getElementById("userStarmapFit").addEventListener("click", function () {
			fitToVisibleBounds(state);
			scheduleDraw(state);
		});

		wireCheckbox(state, "userStarmapShowTerritory", "showTerritory");
		wireCheckbox(state, "userStarmapShowImpassable", "showImpassable");
		wireCheckbox(state, "userStarmapShowEffects", "showEffects");
		wireCheckbox(state, "userStarmapShowGrid", "showGrid");
		wireSectorSelect(state);

		state.canvas.addEventListener("mousedown", function (event) {
			if (event.button !== 0) {
				return;
			}

			if (Date.now() - state.lastTouchAt < 700) {
				return;
			}

			beginDrag(state, event.clientX, event.clientY);
		});

		window.addEventListener("mousemove", function (event) {
			const point = getCanvasPoint(state, event.clientX, event.clientY);
			state.mouseX = point.x;
			state.mouseY = point.y;

			if (state.dragging) {
				updateDrag(state, event.clientX, event.clientY);
				return;
			}

			updateTooltip(state);
		});

		window.addEventListener("mouseup", function (event) {
			if (!state.dragging) {
				return;
			}

			finishDrag(state, event.clientX, event.clientY);
		});

		state.canvas.addEventListener("mouseleave", function () {
			if (!state.dragging) {
				hideTooltip(state);
			}
		});

		state.canvas.addEventListener("wheel", function (event) {
			event.preventDefault();
			const point = getCanvasPoint(state, event.clientX, event.clientY);
			const mapScreenX = clamp(point.x - AXIS_LEFT_WIDTH, 0, getMapViewportWidth(state));
			const mapScreenY = clamp(point.y - AXIS_TOP_HEIGHT, 0, getMapViewportHeight(state));
			const worldX = state.viewX + mapScreenX / state.scale;
			const worldY = state.viewY + mapScreenY / state.scale;
			const zoomFactor = event.deltaY < 0 ? 1.15 : 1 / 1.15;
			const nextScale = clamp(state.scale * zoomFactor, MIN_SCALE, MAX_SCALE);

			state.scale = nextScale;
			state.viewX = worldX - mapScreenX / state.scale;
			state.viewY = worldY - mapScreenY / state.scale;
			clampView(state);
			updateTooltip(state);
			scheduleDraw(state);
		}, { passive: false });

		state.canvas.addEventListener("touchstart", function (event) {
			state.lastTouchAt = Date.now();
			if (event.touches.length === 1) {
				event.preventDefault();
				state.pinching = false;
				beginDrag(state, event.touches[0].clientX, event.touches[0].clientY);
				return;
			}

			if (event.touches.length === 2) {
				event.preventDefault();
				beginPinch(state, event.touches[0], event.touches[1]);
			}
		}, { passive: false });

		state.canvas.addEventListener("touchmove", function (event) {
			state.lastTouchAt = Date.now();
			if (event.touches.length === 1 && state.dragging) {
				event.preventDefault();
				updateDrag(state, event.touches[0].clientX, event.touches[0].clientY);
				return;
			}

			if (event.touches.length === 2 && state.pinching) {
				event.preventDefault();
				updatePinch(state, event.touches[0], event.touches[1]);
			}
		}, { passive: false });

		state.canvas.addEventListener("touchend", function (event) {
			state.lastTouchAt = Date.now();
			event.preventDefault();

			if (state.pinching) {
				state.pinching = false;
				if (event.touches.length === 1) {
					beginDrag(state, event.touches[0].clientX, event.touches[0].clientY);
					state.dragMoved = true;
					return;
				}
				state.canvas.classList.remove("isDragging");
				return;
			}

			if (state.dragging && event.changedTouches.length > 0) {
				finishDrag(state, event.changedTouches[0].clientX, event.changedTouches[0].clientY);
			}
		}, { passive: false });

		state.canvas.addEventListener("touchcancel", function (event) {
			state.lastTouchAt = Date.now();
			event.preventDefault();
			state.dragging = false;
			state.pinching = false;
			state.canvas.classList.remove("isDragging");
			hideTooltip(state);
		}, { passive: false });
	}

	function wireCheckbox(state, id, property) {
		const input = document.getElementById(id);
		state[property] = input.checked;
		input.addEventListener("change", function () {
			state[property] = this.checked;
			scheduleDraw(state);
		});
	}

	function wireSectorSelect(state) {
		const select = document.getElementById("userStarmapSectorHighlight");
		if (!select) {
			return;
		}

		state.selectedSectorId = Number(select.value) || 0;
		select.addEventListener("change", function () {
			state.selectedSectorId = Number(this.value) || 0;
			scheduleDraw(state);
		});
	}

	function resizeCanvas(state) {
		const rect = state.canvas.parentElement.getBoundingClientRect();
		const dpr = window.devicePixelRatio || 1;
		state.canvasWidth = Math.max(1, Math.floor(rect.width));
		state.canvasHeight = Math.max(1, Math.floor(rect.height));
		state.canvas.width = Math.floor(state.canvasWidth * dpr);
		state.canvas.height = Math.floor(state.canvasHeight * dpr);
		state.canvas.style.width = state.canvasWidth + "px";
		state.canvas.style.height = state.canvasHeight + "px";
		state.ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
	}

	function loadData(state) {
		setStatus(state, "Lade Kartendaten...");
		startLoading(state, "Lade Kartendaten", 5);

		fetch(state.dataUrl + "&ts=" + Date.now(), { cache: "no-store" })
			.then(function (response) {
				if (!response.ok) {
					throw new Error("Daten konnten nicht geladen werden");
				}
				setLoadingProgress(state, 20);
				return response.json();
			})
			.then(function (data) {
				setLoadingProgress(state, 35);
				applyData(state, data);
				loadMapImage(state, data.imageVersion);
			})
			.catch(function () {
				setStatus(state, "Fehler beim Laden");
				stopLoading(state);
			});
	}

	function applyData(state, data) {
		state.layerWidth = Number(data.layer.width);
		state.layerHeight = Number(data.layer.height);
		state.cellSize = Number(data.cellSize);
		state.fieldsPerSection = Number(data.fieldsPerSection || state.fieldsPerSection);
		state.mapPixelWidth = state.layerWidth * state.cellSize;
		state.mapPixelHeight = state.layerHeight * state.cellSize;
		state.visibleRuns = Array.isArray(data.visibleRuns) ? data.visibleRuns : [];
		state.fields = Array.isArray(data.fields) ? data.fields : [];
		state.fieldByKey = new Map();
		state.territoryFields = [];
		state.effectFields = [];
		state.impassableFields = [];
		state.iconFields = [];

		state.fields.forEach(function (field) {
			field.x = Number(field.x);
			field.y = Number(field.y);
			state.fieldByKey.set(fieldKey(field.x, field.y), field);

			if (field.hasTerritory && field.territoryColor) {
				state.territoryFields.push(field);
			}
			if (field.hasEffects) {
				state.effectFields.push(field);
			}
			if (field.isImpassable) {
				state.impassableFields.push(field);
			}
			if (field.icon) {
				state.iconFields.push(field);
				ensureIcon(state, field.icon);
			}
		});

		state.dataLoaded = true;
		state.selectedField = null;
		updateFieldDetails(state, null);
	}

	function loadMapImage(state, imageVersion) {
		state.mapLoaded = false;
		startLoading(state, "Lade Kartengrafik", Math.max(35, state.loadingProgress));

		const xhr = new XMLHttpRequest();
		xhr.open("GET", state.imageUrl + "&v=" + encodeURIComponent(imageVersion || "0") + "&download=" + Date.now(), true);
		xhr.responseType = "blob";
		xhr.onprogress = function (event) {
			if (event.lengthComputable && event.total > 0) {
				setLoadingProgress(state, 35 + Math.round((event.loaded / event.total) * 60));
			}
		};
		xhr.onload = function () {
			if (xhr.status !== 200) {
				stopLoading(state);
				setStatus(state, "Bild konnte nicht geladen werden");
				return;
			}

			setLoadingProgress(state, 96);
			const image = new Image();
			const objectUrl = URL.createObjectURL(xhr.response);
			image.onload = function () {
				URL.revokeObjectURL(objectUrl);
				state.mapImage = image;
				state.mapLoaded = true;
				if (!state.viewInitialized) {
					fitToVisibleBounds(state);
					state.viewInitialized = true;
				}
				setLoadingProgress(state, 100);
				stopLoading(state);
				setStatus(state, "Karte geladen");
				scheduleDraw(state);
			};
			image.onerror = function () {
				URL.revokeObjectURL(objectUrl);
				stopLoading(state);
				setStatus(state, "Bild konnte nicht geladen werden");
			};
			image.src = objectUrl;
		};
		xhr.onerror = function () {
			stopLoading(state);
			setStatus(state, "Bild konnte nicht geladen werden");
		};
		xhr.send();
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
		const dpr = window.devicePixelRatio || 1;
		const ctx = state.ctx;
		ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
		ctx.clearRect(0, 0, state.canvasWidth, state.canvasHeight);
		ctx.fillStyle = "#000000";
		ctx.fillRect(0, 0, state.canvasWidth, state.canvasHeight);

		ctx.save();
		ctx.beginPath();
		ctx.rect(AXIS_LEFT_WIDTH, AXIS_TOP_HEIGHT, getMapViewportWidth(state), getMapViewportHeight(state));
		ctx.clip();
		ctx.translate(AXIS_LEFT_WIDTH, AXIS_TOP_HEIGHT);
		ctx.scale(state.scale, state.scale);
		ctx.translate(-state.viewX, -state.viewY);

		if (state.mapLoaded) {
			ctx.drawImage(state.mapImage, 0, 0, state.mapPixelWidth, state.mapPixelHeight);
		}

		if (state.showTerritory) {
			drawTerritory(state, ctx);
		}
		if (state.showEffects) {
			drawEffects(state, ctx);
		}
		if (state.showImpassable) {
			drawImpassable(state, ctx);
		}
		drawIcons(state, ctx);
		if (state.showGrid) {
			drawGrid(state, ctx);
		}
		if (state.selectedSectorId > 0) {
			drawSelectedSector(state, ctx);
		}
		if (state.selectedField) {
			drawSelection(state, ctx, state.selectedField);
		}

		ctx.restore();
		drawAxes(state, ctx);
	}

	function drawTerritory(state, ctx) {
		const lineWidth = Math.max(1, 2 / state.scale);
		ctx.lineWidth = lineWidth;
		state.territoryFields.forEach(function (field) {
			const rect = getFieldRect(state, field);
			ctx.strokeStyle = field.territoryColor;
			ctx.strokeRect(rect.x + lineWidth / 2, rect.y + lineWidth / 2, rect.size - lineWidth, rect.size - lineWidth);
		});
	}

	function drawEffects(state, ctx) {
		const lineWidth = Math.max(1, 2 / state.scale);
		ctx.strokeStyle = state.colors.effects;
		ctx.lineWidth = lineWidth;
		state.effectFields.forEach(function (field) {
			const rect = getFieldRect(state, field);
			const inset = Math.max(2, 3 / state.scale);
			ctx.strokeRect(rect.x + inset, rect.y + inset, rect.size - inset * 2, rect.size - inset * 2);
		});
	}

	function drawImpassable(state, ctx) {
		ctx.strokeStyle = state.colors.impassable;
		ctx.lineWidth = Math.max(1, 2 / state.scale);
		state.impassableFields.forEach(function (field) {
			const rect = getFieldRect(state, field);
			const inset = Math.max(3, 4 / state.scale);
			ctx.beginPath();
			ctx.moveTo(rect.x + inset, rect.y + inset);
			ctx.lineTo(rect.x + rect.size - inset, rect.y + rect.size - inset);
			ctx.moveTo(rect.x + rect.size - inset, rect.y + inset);
			ctx.lineTo(rect.x + inset, rect.y + rect.size - inset);
			ctx.stroke();
		});
	}

	function drawIcons(state, ctx) {
		state.iconFields.forEach(function (field) {
			const icon = state.iconCache.get(field.icon);
			if (!icon || !icon.complete || icon.naturalWidth === 0) {
				return;
			}

			const rect = getFieldRect(state, field);
			ctx.drawImage(icon, rect.x + 1, rect.y + 1, rect.size - 2, rect.size - 2);
		});
	}

	function drawGrid(state, ctx) {
		if (state.scale * state.cellSize < 5) {
			return;
		}

		const startX = clamp(Math.floor(state.viewX / state.cellSize), 0, state.layerWidth);
		const endX = clamp(Math.ceil((state.viewX + getMapViewportWidth(state) / state.scale) / state.cellSize), 0, state.layerWidth);
		const startY = clamp(Math.floor(state.viewY / state.cellSize), 0, state.layerHeight);
		const endY = clamp(Math.ceil((state.viewY + getMapViewportHeight(state) / state.scale) / state.cellSize), 0, state.layerHeight);

		ctx.strokeStyle = state.colors.grid;
		ctx.lineWidth = 1 / state.scale;
		ctx.beginPath();
		for (let x = startX; x <= endX; x++) {
			const px = x * state.cellSize;
			ctx.moveTo(px, startY * state.cellSize);
			ctx.lineTo(px, endY * state.cellSize);
		}
		for (let y = startY; y <= endY; y++) {
			const py = y * state.cellSize;
			ctx.moveTo(startX * state.cellSize, py);
			ctx.lineTo(endX * state.cellSize, py);
		}
		ctx.stroke();
	}

	function drawSelection(state, ctx, field) {
		const rect = getFieldRect(state, field);
		ctx.strokeStyle = state.colors.selection;
		ctx.lineWidth = Math.max(1, 2 / state.scale);
		ctx.strokeRect(rect.x + 1, rect.y + 1, rect.size - 2, rect.size - 2);
	}

	function drawSelectedSector(state, ctx) {
		const sector = getSectorById(state, state.selectedSectorId);
		if (!sector) {
			return;
		}

		const x = (sector.startX - 1) * state.cellSize;
		const y = (sector.startY - 1) * state.cellSize;
		const width = (sector.endX - sector.startX + 1) * state.cellSize;
		const height = (sector.endY - sector.startY + 1) * state.cellSize;
		const lineWidth = Math.max(2, 3 / state.scale);

		ctx.save();
		ctx.strokeStyle = state.colors.sectorHighlight;
		ctx.lineWidth = lineWidth;
		ctx.setLineDash([8 / state.scale, 4 / state.scale]);
		ctx.strokeRect(x + lineWidth / 2, y + lineWidth / 2, width - lineWidth, height - lineWidth);
		ctx.restore();
	}

	function drawAxes(state, ctx) {
		if (!state.dataLoaded || state.canvasWidth <= AXIS_LEFT_WIDTH || state.canvasHeight <= AXIS_TOP_HEIGHT) {
			return;
		}

		const cellScreenSize = state.cellSize * state.scale;
		if (cellScreenSize <= 0) {
			return;
		}

		const tickStep = getAxisTickStep(cellScreenSize);
		const visibleStartX = Math.max(1, Math.floor(state.viewX / state.cellSize) + 1);
		const visibleEndX = Math.min(state.layerWidth, Math.ceil((state.viewX + getMapViewportWidth(state) / state.scale) / state.cellSize));
		const visibleStartY = Math.max(1, Math.floor(state.viewY / state.cellSize) + 1);
		const visibleEndY = Math.min(state.layerHeight, Math.ceil((state.viewY + getMapViewportHeight(state) / state.scale) / state.cellSize));

		ctx.save();
		ctx.setTransform(window.devicePixelRatio || 1, 0, 0, window.devicePixelRatio || 1, 0, 0);
		ctx.fillStyle = state.colors.axisBackground;
		ctx.fillRect(AXIS_LEFT_WIDTH, 0, state.canvasWidth - AXIS_LEFT_WIDTH, AXIS_TOP_HEIGHT);
		ctx.fillRect(0, AXIS_TOP_HEIGHT, AXIS_LEFT_WIDTH, state.canvasHeight - AXIS_TOP_HEIGHT);
		ctx.fillRect(0, 0, AXIS_LEFT_WIDTH, AXIS_TOP_HEIGHT);

		ctx.strokeStyle = state.colors.axisBorder;
		ctx.lineWidth = 1;
		ctx.beginPath();
		ctx.moveTo(AXIS_LEFT_WIDTH, 0);
		ctx.lineTo(AXIS_LEFT_WIDTH, state.canvasHeight);
		ctx.moveTo(0, AXIS_TOP_HEIGHT);
		ctx.lineTo(state.canvasWidth, AXIS_TOP_HEIGHT);
		ctx.stroke();

		ctx.fillStyle = state.colors.axisText;
		ctx.font = "11px sans-serif";
		ctx.textBaseline = "middle";
		const labelStepX = getAxisLabelStep(
			cellScreenSize,
			Math.max(AXIS_MIN_LABEL_SPACING, ctx.measureText(String(visibleEndX)).width + 9)
		);
		const labelStepY = getAxisLabelStep(
			cellScreenSize,
			Math.max(AXIS_MIN_LABEL_SPACING, ctx.measureText(String(visibleEndY)).width + 10)
		);

		drawAxisTicks(state, ctx, visibleStartX, visibleEndX, visibleStartY, visibleEndY, tickStep, labelStepX, labelStepY);

		ctx.textAlign = "center";
		for (let x = visibleStartX; x <= visibleEndX; x++) {
			if ((x - 1) % labelStepX !== 0 && x !== 1) {
				continue;
			}

			const screenX = AXIS_LEFT_WIDTH + ((x - 0.5) * state.cellSize - state.viewX) * state.scale;
			if (screenX < AXIS_LEFT_WIDTH - AXIS_MIN_LABEL_SPACING || screenX > state.canvasWidth + AXIS_MIN_LABEL_SPACING) {
				continue;
			}

			ctx.fillText(String(x), screenX, AXIS_TOP_HEIGHT / 2);
		}

		ctx.textAlign = "right";
		for (let y = visibleStartY; y <= visibleEndY; y++) {
			if ((y - 1) % labelStepY !== 0 && y !== 1) {
				continue;
			}

			const screenY = AXIS_TOP_HEIGHT + ((y - 0.5) * state.cellSize - state.viewY) * state.scale;
			if (screenY < AXIS_TOP_HEIGHT - AXIS_MIN_LABEL_SPACING || screenY > state.canvasHeight + AXIS_MIN_LABEL_SPACING) {
				continue;
			}

			ctx.fillText(String(y), AXIS_LEFT_WIDTH - 6, screenY);
		}

		ctx.restore();
	}

	function drawAxisTicks(state, ctx, visibleStartX, visibleEndX, visibleStartY, visibleEndY, tickStep, labelStepX, labelStepY) {
		ctx.lineWidth = 1;
		ctx.strokeStyle = state.colors.axisTick;
		ctx.beginPath();

		const firstBoundaryX = Math.max(0, Math.floor((visibleStartX - 1) / tickStep) * tickStep);
		for (let x = firstBoundaryX; x <= visibleEndX; x += tickStep) {
			const screenX = AXIS_LEFT_WIDTH + (x * state.cellSize - state.viewX) * state.scale;
			if (screenX < AXIS_LEFT_WIDTH || screenX > state.canvasWidth) {
				continue;
			}

			const isMajor = x % labelStepX === 0;
			ctx.moveTo(Math.round(screenX) + 0.5, isMajor ? 0 : AXIS_TOP_HEIGHT - 7);
			ctx.lineTo(Math.round(screenX) + 0.5, AXIS_TOP_HEIGHT);
		}

		const firstBoundaryY = Math.max(0, Math.floor((visibleStartY - 1) / tickStep) * tickStep);
		for (let y = firstBoundaryY; y <= visibleEndY; y += tickStep) {
			const screenY = AXIS_TOP_HEIGHT + (y * state.cellSize - state.viewY) * state.scale;
			if (screenY < AXIS_TOP_HEIGHT || screenY > state.canvasHeight) {
				continue;
			}

			const isMajor = y % labelStepY === 0;
			ctx.moveTo(isMajor ? 0 : AXIS_LEFT_WIDTH - 7, Math.round(screenY) + 0.5);
			ctx.lineTo(AXIS_LEFT_WIDTH, Math.round(screenY) + 0.5);
		}

		ctx.stroke();
	}

	function getAxisTickStep(cellScreenSize) {
		return getNiceAxisStep(Math.max(1, Math.ceil(AXIS_MIN_TICK_SPACING / cellScreenSize)));
	}

	function getAxisLabelStep(cellScreenSize, minSpacing) {
		return getNiceAxisStep(Math.max(1, Math.ceil(minSpacing / cellScreenSize)));
	}

	function getNiceAxisStep(rawStep) {
		const magnitude = Math.pow(10, Math.floor(Math.log10(rawStep)));
		const normalized = rawStep / magnitude;

		if (normalized <= 1) {
			return magnitude;
		}
		if (normalized <= 2) {
			return 2 * magnitude;
		}
		if (normalized <= 5) {
			return 5 * magnitude;
		}

		return 10 * magnitude;
	}

	function getFieldRect(state, field) {
		return {
			x: (field.x - 1) * state.cellSize,
			y: (field.y - 1) * state.cellSize,
			size: state.cellSize
		};
	}

	function getSectorsHorizontal(state) {
		return Math.max(1, Math.ceil(state.layerWidth / getFieldsPerSection(state)));
	}

	function getSectorsVertical(state) {
		return Math.max(1, Math.ceil(state.layerHeight / getFieldsPerSection(state)));
	}

	function getFieldsPerSection(state) {
		return Math.max(1, Number(state.fieldsPerSection) || 20);
	}

	function getSectorInfoForField(state, field) {
		const fieldsPerSection = getFieldsPerSection(state);
		const sectorX = Math.ceil(field.x / fieldsPerSection);
		const sectorY = Math.ceil(field.y / fieldsPerSection);
		const id = sectorX + (sectorY - 1) * getSectorsHorizontal(state);

		return buildSectorInfo(state, id, sectorX, sectorY);
	}

	function getSectorById(state, id) {
		const sectorId = Number(id);
		const sectorsHorizontal = getSectorsHorizontal(state);
		const sectorsVertical = getSectorsVertical(state);
		const sectorCount = sectorsHorizontal * sectorsVertical;

		if (!Number.isFinite(sectorId) || sectorId < 1 || sectorId > sectorCount) {
			return null;
		}

		const sectorX = ((sectorId - 1) % sectorsHorizontal) + 1;
		const sectorY = Math.floor((sectorId - 1) / sectorsHorizontal) + 1;

		return buildSectorInfo(state, sectorId, sectorX, sectorY);
	}

	function buildSectorInfo(state, id, sectorX, sectorY) {
		const fieldsPerSection = getFieldsPerSection(state);
		const startX = (sectorX - 1) * fieldsPerSection + 1;
		const startY = (sectorY - 1) * fieldsPerSection + 1;
		const endX = Math.min(sectorX * fieldsPerSection, state.layerWidth);
		const endY = Math.min(sectorY * fieldsPerSection, state.layerHeight);

		return {
			id,
			x: sectorX,
			y: sectorY,
			startX,
			startY,
			endX,
			endY
		};
	}

	function getSectorLabel(sector) {
		return "Sektor " + sector.id + " (" + sector.startX + "|" + sector.startY + " bis " + sector.endX + "|" + sector.endY + ")";
	}

	function ensureIcon(state, src) {
		if (state.iconCache.has(src)) {
			return;
		}

		const image = new Image();
		image.onload = function () {
			scheduleDraw(state);
		};
		image.src = src;
		state.iconCache.set(src, image);
	}

	function beginDrag(state, clientX, clientY) {
		state.dragging = true;
		state.dragMoved = false;
		state.dragStartX = clientX;
		state.dragStartY = clientY;
		state.dragViewX = state.viewX;
		state.dragViewY = state.viewY;
		state.canvas.classList.add("isDragging");
		hideTooltip(state);
	}

	function updateDrag(state, clientX, clientY) {
		const deltaX = clientX - state.dragStartX;
		const deltaY = clientY - state.dragStartY;
		if (Math.abs(deltaX) > 3 || Math.abs(deltaY) > 3) {
			state.dragMoved = true;
		}
		state.viewX = state.dragViewX - deltaX / state.scale;
		state.viewY = state.dragViewY - deltaY / state.scale;
		clampView(state);
		hideTooltip(state);
		scheduleDraw(state);
	}

	function finishDrag(state, clientX, clientY) {
		state.dragging = false;
		state.canvas.classList.remove("isDragging");

		if (!state.dragMoved) {
			const point = getCanvasPoint(state, clientX, clientY);
			const field = getFieldAtScreen(state, point.x, point.y);
			selectField(state, field);
		}
	}

	function beginPinch(state, touchA, touchB) {
		const center = getTouchCenter(state, touchA, touchB);
		const mapScreenX = clamp(center.x - AXIS_LEFT_WIDTH, 0, getMapViewportWidth(state));
		const mapScreenY = clamp(center.y - AXIS_TOP_HEIGHT, 0, getMapViewportHeight(state));

		state.dragging = false;
		state.pinching = true;
		state.pinchStartDistance = getTouchDistance(touchA, touchB);
		state.pinchStartScale = state.scale;
		state.pinchWorldX = state.viewX + mapScreenX / state.scale;
		state.pinchWorldY = state.viewY + mapScreenY / state.scale;
		state.canvas.classList.add("isDragging");
		hideTooltip(state);
	}

	function updatePinch(state, touchA, touchB) {
		const distance = getTouchDistance(touchA, touchB);
		if (state.pinchStartDistance <= 0 || distance <= 0) {
			return;
		}

		const center = getTouchCenter(state, touchA, touchB);
		const mapScreenX = clamp(center.x - AXIS_LEFT_WIDTH, 0, getMapViewportWidth(state));
		const mapScreenY = clamp(center.y - AXIS_TOP_HEIGHT, 0, getMapViewportHeight(state));

		state.scale = clamp(state.pinchStartScale * (distance / state.pinchStartDistance), MIN_SCALE, MAX_SCALE);
		state.viewX = state.pinchWorldX - mapScreenX / state.scale;
		state.viewY = state.pinchWorldY - mapScreenY / state.scale;
		clampView(state);
		hideTooltip(state);
		scheduleDraw(state);
	}

	function getTouchCenter(state, touchA, touchB) {
		return getCanvasPoint(
			state,
			(touchA.clientX + touchB.clientX) / 2,
			(touchA.clientY + touchB.clientY) / 2
		);
	}

	function getTouchDistance(touchA, touchB) {
		const deltaX = touchA.clientX - touchB.clientX;
		const deltaY = touchA.clientY - touchB.clientY;

		return Math.sqrt(deltaX * deltaX + deltaY * deltaY);
	}

	function getFieldAtScreen(state, screenX, screenY) {
		if (screenX < AXIS_LEFT_WIDTH || screenY < AXIS_TOP_HEIGHT || screenX > state.canvasWidth || screenY > state.canvasHeight) {
			return null;
		}

		const worldX = state.viewX + (screenX - AXIS_LEFT_WIDTH) / state.scale;
		const worldY = state.viewY + (screenY - AXIS_TOP_HEIGHT) / state.scale;
		const x = Math.floor(worldX / state.cellSize) + 1;
		const y = Math.floor(worldY / state.cellSize) + 1;

		if (x < 1 || y < 1 || x > state.layerWidth || y > state.layerHeight) {
			return null;
		}

		return state.fieldByKey.get(fieldKey(x, y)) || null;
	}

	function updateTooltip(state) {
		if (!state.dataLoaded || state.dragging) {
			hideTooltip(state);
			return;
		}

		const field = getFieldAtScreen(state, state.mouseX, state.mouseY);
		if (!field) {
			hideTooltip(state);
			return;
		}

		const lines = ["Feld " + field.x + " | " + field.y];
		const sector = getSectorInfoForField(state, field);
		lines.push(getSectorLabel(sector));
		if (field.tooltip) {
			lines.push(field.tooltip);
		}

		state.tooltip.textContent = lines.join("\n");
		state.tooltip.style.display = "block";
		positionTooltip(state);
	}

	function positionTooltip(state) {
		const margin = 12;
		let left = state.mouseX + margin;
		let top = state.mouseY + margin;

		state.tooltip.style.left = left + "px";
		state.tooltip.style.top = top + "px";

		const rect = state.tooltip.getBoundingClientRect();
		const stageRect = state.canvas.parentElement.getBoundingClientRect();
		const maxLeft = stageRect.width - rect.width - margin;
		const maxTop = stageRect.height - rect.height - margin;

		if (left > maxLeft) {
			left = state.mouseX - rect.width - margin;
		}
		if (top > maxTop) {
			top = state.mouseY - rect.height - margin;
		}

		state.tooltip.style.left = Math.max(margin, left) + "px";
		state.tooltip.style.top = Math.max(margin, top) + "px";
	}

	function hideTooltip(state) {
		state.tooltip.style.display = "none";
	}

	function selectField(state, field) {
		state.selectedField = field;
		updateFieldDetails(state, field);
		scheduleDraw(state);
	}

	function updateFieldDetails(state, field) {
		if (!field) {
			state.fieldDetails.innerHTML = '<div class="userStarmapEmpty">Feld anklicken</div>';
			return;
		}

		const tooltip = field.tooltip
			? '<div class="userStarmapFieldText">' + escapeHtml(field.tooltip).replace(/\n/g, "<br />") + "</div>"
			: "";
		const sector = getSectorInfoForField(state, field);
		const sectorText = '<div class="userStarmapFieldText">' + escapeHtml(getSectorLabel(sector)) + "</div>";
		const systemButton = field.databaseId
			? '<button type="button" data-user-starmap-system="' + Number(field.databaseId) + '">Systemkarte</button>'
			: "";

		state.fieldDetails.innerHTML =
			'<div class="userStarmapFieldTitle">Feld ' + field.x + ' | ' + field.y + "</div>" +
			sectorText +
			tooltip +
			systemButton;

		const button = state.fieldDetails.querySelector("[data-user-starmap-system]");
		if (button) {
			button.addEventListener("click", function () {
				const databaseId = Number(this.dataset.userStarmapSystem);
				if (typeof switchInnerContent === "function") {
					switchInnerContent("SHOW_ENTRY", "Systemkarte", "cat=7&ent=" + databaseId, "database.php");
				}
			});
		}
	}

	function fitToVisibleBounds(state) {
		const bounds = getVisibleBounds(state);
		const padding = VIEW_PADDING_CELLS * state.cellSize;
		const width = Math.max(state.cellSize, bounds.maxX - bounds.minX + state.cellSize + padding * 2);
		const height = Math.max(state.cellSize, bounds.maxY - bounds.minY + state.cellSize + padding * 2);
		const viewportWidth = getMapViewportWidth(state);
		const viewportHeight = getMapViewportHeight(state);
		const scale = Math.min(viewportWidth / width, viewportHeight / height);

		state.scale = clamp(scale, MIN_SCALE, MAX_SCALE);
		state.viewX = bounds.minX + (bounds.maxX - bounds.minX + state.cellSize) / 2 - viewportWidth / (2 * state.scale);
		state.viewY = bounds.minY + (bounds.maxY - bounds.minY + state.cellSize) / 2 - viewportHeight / (2 * state.scale);
		clampView(state);
	}

	function getVisibleBounds(state) {
		if (state.fields.length === 0) {
			return {
				minX: 0,
				minY: 0,
				maxX: state.mapPixelWidth,
				maxY: state.mapPixelHeight
			};
		}

		let minX = state.layerWidth;
		let maxX = 1;
		let minY = state.layerHeight;
		let maxY = 1;

		state.fields.forEach(function (field) {
			minX = Math.min(minX, field.x);
			maxX = Math.max(maxX, field.x);
			minY = Math.min(minY, field.y);
			maxY = Math.max(maxY, field.y);
		});

		return {
			minX: (minX - 1) * state.cellSize,
			minY: (minY - 1) * state.cellSize,
			maxX: (maxX - 1) * state.cellSize,
			maxY: (maxY - 1) * state.cellSize
		};
	}

	function clampView(state) {
		const visibleWidth = getMapViewportWidth(state) / state.scale;
		const visibleHeight = getMapViewportHeight(state) / state.scale;
		const maxX = state.mapPixelWidth - visibleWidth;
		const maxY = state.mapPixelHeight - visibleHeight;

		state.viewX = maxX < 0 ? maxX / 2 : clamp(state.viewX, 0, maxX);
		state.viewY = maxY < 0 ? maxY / 2 : clamp(state.viewY, 0, maxY);
	}

	function startLoading(state, label, progress) {
		state.loadingLabel = label;
		state.loadingProgress = clamp(progress, 0, 100);
		showLoading(state);
		updateLoadingText(state);
		if (state.loadingTimer === 0) {
			state.loadingTimer = window.setInterval(function () {
				state.loadingDots = state.loadingDots >= 3 ? 1 : state.loadingDots + 1;
				updateLoadingText(state);
			}, 350);
		}
	}

	function setLoadingProgress(state, progress) {
		state.loadingProgress = clamp(progress, 0, 100);
		updateLoadingText(state);
	}

	function updateLoadingText(state) {
		state.loading.textContent = state.loadingLabel + ".".repeat(state.loadingDots) + " " + Math.round(state.loadingProgress) + "%";
	}

	function showLoading(state) {
		state.loading.style.display = "flex";
	}

	function stopLoading(state) {
		if (state.loadingTimer !== 0) {
			window.clearInterval(state.loadingTimer);
			state.loadingTimer = 0;
		}
		state.loading.style.display = "none";
	}

	function setStatus(state, text) {
		state.status.textContent = text;
	}

	window.initUserStarmap = initUserStarmap;

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", initUserStarmap);
	} else {
		initUserStarmap();
	}
})();
