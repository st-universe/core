(function () {
	"use strict";

	const DATA_REFRESH_MS = 4000;
	const MOVE_ANIMATION_MS = 900;
	const SIGNATURE_MAX_AGE = 172800;
	const SIGNATURE_DEFAULT_AGE = 900;
	const SIGNATURE_DEFAULT_LIMIT = 10000;
	const SIGNATURE_MAX_LIMIT = 100000;
	const PANEL_ITEM_LIMIT = 80;
	const TOOLTIP_ITEM_LIMIT = 8;
	const CONTACT_COLOR = "#ffe06b";
	const TRACE_COLORS = {
		1: "#ff6b6b",
		2: "#f5c542",
		3: "#57d68d",
		4: "#6fb7ff",
	};

	function clamp(value, min, max) {
		return Math.max(min, Math.min(max, value));
	}

	function fieldKey(x, y) {
		return x + ":" + y;
	}

	function directionOffset(direction) {
		switch (direction) {
			case 1:
				return { x: -1, y: 0 };
			case 2:
				return { x: 0, y: 1 };
			case 3:
				return { x: 1, y: 0 };
			case 4:
				return { x: 0, y: -1 };
			default:
				return { x: 0, y: 0 };
		}
	}

	function directionLabel(direction) {
		switch (direction) {
			case 1:
				return "West";
			case 2:
				return "Süd";
			case 3:
				return "Ost";
			case 4:
				return "Nord";
			default:
				return "unbekannt";
		}
	}

	function initAdminLiveMap() {
		const root = document.getElementById("adminLiveMap");
		if (!root) {
			return;
		}

		const canvas = document.getElementById("adminLiveMapCanvas");
		const ctx = canvas.getContext("2d");

		const state = {
			root,
			canvas,
			ctx,
			loading: document.getElementById("adminLiveMapLoading"),
			tooltip: document.getElementById("adminLiveMapTooltip"),
			status: document.getElementById("adminLiveMapStatus"),
			selected: document.getElementById("adminLiveMapSelected"),
			fieldDetails: document.getElementById("adminLiveMapFieldDetails"),
			filterSummary: document.getElementById("adminLiveMapFilterSummary"),
			userFilterList: document.getElementById("adminLiveMapUserFilterList"),
			allianceFilterList: document.getElementById("adminLiveMapAllianceFilterList"),
			followInput: document.getElementById("adminLiveMapFollowSelected"),
			signatureAgeInput: document.getElementById("adminLiveMapSignatureAge"),
			signatureAgeLabel: document.getElementById("adminLiveMapSignatureAgeLabel"),
			signatureSinceLabel: document.getElementById("adminLiveMapSignatureSinceLabel"),
			contactMovementSinceLabel: document.getElementById("adminLiveMapContactMovementSinceLabel"),
			signatureLimitInput: document.getElementById("adminLiveMapSignatureLimit"),
			signatureColorModeInput: document.getElementById("adminLiveMapSignatureColorMode"),
			imageUrl: root.dataset.imageUrl,
			dataUrl: root.dataset.dataUrl,
			layerWidth: Number(root.dataset.layerWidth),
			layerHeight: Number(root.dataset.layerHeight),
			cellSize: Number(root.dataset.cellSize),
			mapImage: new Image(),
			mapLoaded: false,
			scale: 1,
			minScale: 0.1,
			maxScale: 5,
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
			spacecrafts: new Map(),
			flightSignatures: [],
			selectedShipSignatures: [],
			visibleSignatures: [],
			maxSignatureHeat: 0,
			territoryFields: [],
			impassableFields: [],
			territoryByField: new Map(),
			impassableByField: new Map(),
			fieldIndex: new Map(),
			liveDataInFlight: false,
			liveDataQueued: false,
			liveDataRequestSeq: 0,
			liveDataAppliedSeq: 0,
			liveDataStartedAt: 0,
			liveDataFinishedAt: 0,
			liveDataDurationMs: 0,
			liveDataStatus: "ausstehend",
			signatureRequestLimit: SIGNATURE_DEFAULT_LIMIT,
			selectedShipId: null,
			selectedField: null,
			selectedUserIds: new Set(),
			selectedAllianceIds: new Set(),
			followSelected: false,
			maxSignatureAge: SIGNATURE_DEFAULT_AGE,
			signatureSinceTime: 0,
			contactMovementSinceTime: 0,
			signatureColorMode: "age",
			showSpacecrafts: true,
			showSignatures: true,
			showTerritory: false,
			showImpassable: false,
			showCounts: true,
			stats: {
				spacecrafts: 0,
				flightSignatures: 0,
				signatureLimit: SIGNATURE_DEFAULT_LIMIT,
				signatureLimitHit: false,
				signatureFields: 0,
				coordX: 0,
				coordY: 0,
				generatedAt: 0,
			},
		};

		wireControls(state);

		resizeCanvas(state);
		loadMapImage(state, false);
		loadLiveData(state);
		setInterval(function () {
			if (document.hidden) {
				return;
			}
			loadLiveData(state);
		}, DATA_REFRESH_MS);
		document.addEventListener("visibilitychange", function () {
			if (!document.hidden) {
				loadLiveData(state);
			}
		});
		requestAnimationFrame(function frame() {
			draw(state);
			requestAnimationFrame(frame);
		});
	}

	function wireControls(state) {
		const layerSelect = document.getElementById("adminLiveMapLayer");
		const refreshImageButton = document.getElementById("adminLiveMapRefreshImage");
		const showSpacecraftsInput = document.getElementById("adminLiveMapShowSpacecrafts");
		const showSignaturesInput = document.getElementById("adminLiveMapShowSignatures");
		const showTerritoryInput = document.getElementById("adminLiveMapShowTerritory");
		const showImpassableInput = document.getElementById("adminLiveMapShowImpassable");
		const showCountsInput = document.getElementById("adminLiveMapShowCounts");
		const clearFiltersButton = document.getElementById("adminLiveMapClearFilters");
		const clearSelectionButton = document.getElementById("adminLiveMapClearSelection");
		const signatureAgeInput = document.getElementById("adminLiveMapSignatureAge");
		const setSignatureSinceButton = document.getElementById("adminLiveMapSetSignatureSince");
		const clearSignatureSinceButton = document.getElementById("adminLiveMapClearSignatureSince");
		const setContactMovementSinceButton = document.getElementById("adminLiveMapSetContactMovementSince");
		const clearContactMovementSinceButton = document.getElementById("adminLiveMapClearContactMovementSince");
		const signatureLimitInput = document.getElementById("adminLiveMapSignatureLimit");
		const reloadSignatureLimitButton = document.getElementById("adminLiveMapReloadSignatureLimit");
		const signatureColorModeInput = document.getElementById("adminLiveMapSignatureColorMode");

		layerSelect.addEventListener("change", function () {
			window.location.href = "/admin/?SHOW_ADMIN_LIVE_MAP=1&layerid=" + this.value;
		});
		refreshImageButton.addEventListener("click", function () {
			loadMapImage(state, true);
		});
		showSpacecraftsInput.addEventListener("change", function () {
			state.showSpacecrafts = this.checked;
			afterViewOptionChanged(state);
		});
		showSignaturesInput.addEventListener("change", function () {
			state.showSignatures = this.checked;
			afterViewOptionChanged(state);
		});
		showTerritoryInput.addEventListener("change", function () {
			state.showTerritory = this.checked;
			updateHover(state);
		});
		showImpassableInput.addEventListener("change", function () {
			state.showImpassable = this.checked;
			updateHover(state);
		});
		showCountsInput.addEventListener("change", function () {
			state.showCounts = this.checked;
		});
		signatureAgeInput.addEventListener("input", function () {
			state.maxSignatureAge = Number(this.value);
			updateSignatureAgeLabel(state);
			afterFilterChanged(state);
			renderFilterLists(state);
		});
		signatureAgeInput.addEventListener("change", function () {
			loadLiveData(state);
		});
		setSignatureSinceButton.addEventListener("click", function () {
			state.signatureSinceTime = getCurrentThresholdTime(state);
			afterFilterChanged(state);
			renderFilterLists(state);
			updateThresholdLabels(state);
			loadLiveData(state);
		});
		clearSignatureSinceButton.addEventListener("click", function () {
			state.signatureSinceTime = 0;
			afterFilterChanged(state);
			renderFilterLists(state);
			updateThresholdLabels(state);
			loadLiveData(state);
		});
		setContactMovementSinceButton.addEventListener("click", function () {
			state.contactMovementSinceTime = getCurrentThresholdTime(state);
			afterFilterChanged(state);
			renderFilterLists(state);
			updateThresholdLabels(state);
			loadLiveData(state);
		});
		clearContactMovementSinceButton.addEventListener("click", function () {
			state.contactMovementSinceTime = 0;
			afterFilterChanged(state);
			renderFilterLists(state);
			updateThresholdLabels(state);
			loadLiveData(state);
		});
		if (signatureLimitInput) {
			signatureLimitInput.addEventListener("change", function () {
				updateSignatureRequestLimitFromInput(state);
				loadLiveData(state);
			});
		}
		if (reloadSignatureLimitButton) {
			reloadSignatureLimitButton.addEventListener("click", function () {
				updateSignatureRequestLimitFromInput(state);
				loadLiveData(state);
			});
		}
		signatureColorModeInput.addEventListener("change", function () {
			state.signatureColorMode = this.value;
		});
		state.followInput.addEventListener("change", function () {
			state.followSelected = this.checked;
			followSelectedShip(state, true);
			updateSelectedLabel(state);
		});
		clearFiltersButton.addEventListener("click", function () {
			state.selectedUserIds.clear();
			state.selectedAllianceIds.clear();
			afterFilterChanged(state);
			renderFilterLists(state);
		});
		clearSelectionButton.addEventListener("click", function () {
			state.selectedShipId = null;
			state.selectedField = null;
			updateSelectedLabel(state);
			updateFieldPanel(state);
			updateHover(state);
		});
		state.userFilterList.addEventListener("change", function (event) {
			const target = event.target;
			if (!(target instanceof HTMLInputElement) || target.dataset.userId === undefined) {
				return;
			}
			updateSetFromCheckbox(state.selectedUserIds, Number(target.dataset.userId), target.checked);
			afterFilterChanged(state);
			renderFilterLists(state);
		});
		state.userFilterList.addEventListener("click", function (event) {
			toggleFilterRowFromTextClick(event);
		});
		state.allianceFilterList.addEventListener("change", function (event) {
			const target = event.target;
			if (!(target instanceof HTMLInputElement) || target.dataset.allianceId === undefined) {
				return;
			}
			updateSetFromCheckbox(state.selectedAllianceIds, Number(target.dataset.allianceId), target.checked);
			afterFilterChanged(state);
			renderFilterLists(state);
		});
		state.allianceFilterList.addEventListener("click", function (event) {
			toggleFilterRowFromTextClick(event);
		});
		state.fieldDetails.addEventListener("click", function (event) {
			if (!(event.target instanceof Element)) {
				return;
			}
			const button = event.target.closest("[data-select-ship-id]");
			if (!button) {
				return;
			}
			selectShip(state, Number(button.dataset.selectShipId), true);
		});

		state.canvas.addEventListener("mousedown", function (event) {
			state.dragging = true;
			state.dragMoved = false;
			state.dragStartX = event.clientX;
			state.dragStartY = event.clientY;
			state.dragViewX = state.viewX;
			state.dragViewY = state.viewY;
			state.canvas.classList.add("isDragging");
		});
		state.canvas.addEventListener("mouseup", function (event) {
			if (state.dragging && !state.dragMoved) {
				selectFieldAt(state, event.offsetX, event.offsetY);
			}
		});
		window.addEventListener("mouseup", function () {
			state.dragging = false;
			state.canvas.classList.remove("isDragging");
		});
		window.addEventListener("mousemove", function (event) {
			const rect = state.canvas.getBoundingClientRect();
			state.mouseX = event.clientX - rect.left;
			state.mouseY = event.clientY - rect.top;

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
			} else {
				updateHover(state);
			}
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
		window.addEventListener("resize", function () {
			resizeCanvas(state);
		});

		updateSignatureAgeLabel(state);
		updateThresholdLabels(state);
		updateSignatureRequestLimitFromInput(state);
	}

	function updateSetFromCheckbox(set, id, checked) {
		if (checked) {
			set.add(id);
			return;
		}
		set.delete(id);
	}

	function toggleFilterRowFromTextClick(event) {
		if (!(event.target instanceof Element) || event.target instanceof HTMLInputElement) {
			return;
		}

		const row = event.target.closest(".adminLiveMapFilterRow, .adminLiveMapFilterItem");
		if (!row) {
			return;
		}

		const input = row.querySelector("input[type='checkbox']");
		if (!(input instanceof HTMLInputElement)) {
			return;
		}

		input.checked = !input.checked;
		input.dispatchEvent(new Event("change", { bubbles: true }));
	}

	function updateSignatureAgeLabel(state) {
		if (!state.signatureAgeLabel) {
			return;
		}
		state.signatureAgeLabel.textContent = formatDuration(state.maxSignatureAge);
	}

	function updateThresholdLabels(state) {
		if (state.signatureSinceLabel) {
			state.signatureSinceLabel.textContent = state.signatureSinceTime > 0
				? "Spuren seit " + formatThresholdTime(state.signatureSinceTime)
				: "Spuren ohne Startzeit-Threshold";
		}
		if (state.contactMovementSinceLabel) {
			state.contactMovementSinceLabel.textContent = state.contactMovementSinceTime > 0
				? "Kontaktzahlen zählen nur Schiffe mit Bewegung seit " + formatThresholdTime(state.contactMovementSinceTime)
				: "Kontaktzahlen zählen alle sichtbaren Schiffe";
		}
	}

	function updateSignatureRequestLimitFromInput(state) {
		if (!state.signatureLimitInput) {
			return;
		}

		const value = clamp(
			Math.floor(Number(state.signatureLimitInput.value) || SIGNATURE_DEFAULT_LIMIT),
			1,
			SIGNATURE_MAX_LIMIT
		);
		state.signatureRequestLimit = value;
		state.signatureLimitInput.value = String(value);
		updateStatus(state);
	}

	function getCurrentThresholdTime(state) {
		if (Number(state.stats.generatedAt) > 0 && Number(state.liveDataFinishedAt) > 0) {
			return Number(state.stats.generatedAt) +
				Math.max(0, Math.floor((Date.now() - Number(state.liveDataFinishedAt)) / 1000));
		}

		return Math.floor(Date.now() / 1000);
	}

	function getDataRequestMaxSignatureAge(state) {
		const now = getCurrentThresholdTime(state);
		const thresholdAges = [state.signatureSinceTime, state.contactMovementSinceTime]
			.filter(function (timestamp) {
				return Number(timestamp) > 0;
			})
			.map(function (timestamp) {
				return Math.max(60, now - Number(timestamp) + DATA_REFRESH_MS / 1000);
			});

		return Math.min(
			SIGNATURE_MAX_AGE,
			Math.max(state.maxSignatureAge, 60, ...thresholdAges)
		);
	}

	function formatThresholdTime(timestamp) {
		return formatClockTime(Number(timestamp) * 1000);
	}

	function afterViewOptionChanged(state) {
		rebuildIndexes(state);
		updateStatus(state);
		updateFieldPanel(state);
		updateHover(state);
	}

	function afterFilterChanged(state) {
		rebuildIndexes(state);
		updateStatus(state);
		updateFieldPanel(state);
		updateHover(state);
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

	function loadMapImage(state, refresh) {
		state.mapLoaded = false;
		state.loading.style.display = "flex";
		state.loading.textContent = refresh ? "Erzeuge Basisgrafik..." : "Lade Karte...";

		const image = new Image();
		image.onload = function () {
			state.mapImage = image;
			state.mapLoaded = true;
			updateScaleBounds(state);
			state.scale = Math.max(state.minScale, Math.min(1, state.scale));
			state.viewX = (state.mapImage.width - state.canvas.clientWidth / state.scale) / 2;
			state.viewY = (state.mapImage.height - state.canvas.clientHeight / state.scale) / 2;
			clampView(state);
			state.loading.style.display = "none";
			updateStatus(state);
		};
		image.onerror = function () {
			state.loading.textContent = "Karte konnte nicht geladen werden";
		};

		const separator = state.imageUrl.indexOf("?") === -1 ? "?" : "&";
		image.src =
			state.imageUrl +
			separator +
			(refresh ? "refresh=1&" : "") +
			"ts=" +
			Date.now();
	}

	function loadLiveData(state) {
		if (state.liveDataInFlight) {
			state.liveDataQueued = true;
			updateStatus(state);
			return;
		}

		const requestSeq = ++state.liveDataRequestSeq;
		const startedAt = Date.now();
		state.liveDataInFlight = true;
		state.liveDataQueued = false;
		state.liveDataStartedAt = startedAt;
		state.liveDataStatus = "läuft";
		updateStatus(state);
		const requestMaxSignatureAge = getDataRequestMaxSignatureAge(state);
		const selectedShipParam = state.selectedShipId === null
			? ""
			: "&shipId=" + encodeURIComponent(state.selectedShipId);

		fetch(
			state.dataUrl +
			selectedShipParam +
			"&maxSignatureAge=" +
			encodeURIComponent(requestMaxSignatureAge) +
			"&signatureLimit=" +
			encodeURIComponent(state.signatureRequestLimit) +
			"&ts=" +
			Date.now(),
			{
				headers: {
					Accept: "application/json",
				},
			}
		)
			.then(function (response) {
				if (!response.ok) {
					throw new Error("HTTP " + response.status);
				}
				return response.json();
			})
			.then(function (payload) {
				state.liveDataFinishedAt = Date.now();
				state.liveDataDurationMs = state.liveDataFinishedAt - startedAt;
				if (requestSeq < state.liveDataAppliedSeq) {
					state.liveDataStatus = "veraltet";
					return;
				}
				if (payload.generatedAt && payload.generatedAt < state.stats.generatedAt) {
					state.liveDataStatus = "veraltet";
					return;
				}

				state.liveDataStatus = "ok";
				state.liveDataAppliedSeq = requestSeq;
				applyLiveDataPayload(state, payload);
			})
			.catch(function () {
				state.liveDataFinishedAt = Date.now();
				state.liveDataDurationMs = state.liveDataFinishedAt - startedAt;
				state.liveDataStatus = "Fehler";
			})
			.finally(function () {
				state.liveDataInFlight = false;
				updateStatus(state);
				if (state.liveDataQueued && !document.hidden) {
					loadLiveData(state);
				}
			});
	}

	function applyLiveDataPayload(state, payload) {
		const payloadSignatures = payload.flightSignatures || [];
		mergeSpacecrafts(state, payload.spacecrafts || []);
		state.flightSignatures = payloadSignatures;
		state.selectedShipSignatures = payload.selectedShipSignatures || [];
		state.territoryFields = (payload.overlays && payload.overlays.territory) || [];
		state.impassableFields = (payload.overlays && payload.overlays.impassable) || [];
		state.stats.generatedAt = payload.generatedAt || 0;
		state.stats.signatureLimit = Number(payload.signatureDetailLimit || state.signatureRequestLimit);
		state.stats.signatureLimitHit = payloadSignatures.length >= state.stats.signatureLimit;
		rebuildIndexes(state);
		pruneSelection(state);
		renderFilterLists(state);
		updateSelectedLabel(state);
		updateFieldPanel(state);
		updateStatus(state);
		updateHover(state);
		followSelectedShip(state, false);
	}

	function mergeSpacecrafts(state, rows) {
		const now = performance.now();
		const nextIds = new Set();

		rows.forEach(function (row) {
			const id = String(row.id);
			nextIds.add(id);
			const target = getCellCenter(state, row.x, row.y);
			const current = state.spacecrafts.get(id);
			if (!current) {
				state.spacecrafts.set(id, {
					...row,
					fromX: target.x,
					fromY: target.y,
					targetX: target.x,
					targetY: target.y,
					animationStarted: now,
				});
				return;
			}

			const position = getAnimatedPosition(current, now);
			state.spacecrafts.set(id, {
				...current,
				...row,
				fromX: position.x,
				fromY: position.y,
				targetX: target.x,
				targetY: target.y,
				animationStarted: now,
			});
		});

		Array.from(state.spacecrafts.keys()).forEach(function (id) {
			if (!nextIds.has(id)) {
				state.spacecrafts.delete(id);
			}
		});
	}

	function rebuildIndexes(state) {
		const fieldIndex = new Map();
		const territoryByField = new Map();
		const impassableByField = new Map();
		const movedSpacecraftIds = getMovedSpacecraftIdsSinceContactThreshold(state);
		const visibleSignatures = dedupeSignatures(
			state.flightSignatures.filter(function (item) {
				return passesEntityFilter(state, item) && signaturePassesAge(state, item);
			})
		);

		state.spacecrafts.forEach(function (item) {
			if (!passesEntityFilter(state, item)) {
				return;
			}
			if (!passesContactMovementThreshold(state, item, movedSpacecraftIds)) {
				return;
			}
			const entry = getFieldEntry(fieldIndex, item.x, item.y);
			entry.spacecrafts.push(item);
			entry.spacecraftCount = entry.spacecrafts.length;
		});
		visibleSignatures.forEach(function (item) {
			const entry = getFieldEntry(fieldIndex, item.x, item.y);
			item.displayIndex = entry.signatures.length;
			entry.signatures.push(item);
		});
		state.territoryFields.forEach(function (item) {
			territoryByField.set(fieldKey(item.x, item.y), item);
		});
		state.impassableFields.forEach(function (item) {
			impassableByField.set(fieldKey(item.x, item.y), item);
		});

		let signatureFields = 0;
		let signatureCount = 0;
		let spacecraftCount = 0;
		let maxSignatureHeat = 0;
		fieldIndex.forEach(function (entry) {
			entry.signatureCount = entry.signatures.length;
			if (entry.signatureCount > 0) {
				signatureFields++;
				signatureCount += entry.signatureCount;
				maxSignatureHeat = Math.max(maxSignatureHeat, entry.signatureCount);
			}
			spacecraftCount += entry.spacecraftCount;
		});

		state.fieldIndex = fieldIndex;
		state.visibleSignatures = visibleSignatures;
		state.territoryByField = territoryByField;
		state.impassableByField = impassableByField;
		state.maxSignatureHeat = maxSignatureHeat;
		state.stats.spacecrafts = spacecraftCount;
		state.stats.signatureFields = signatureFields;
		state.stats.flightSignatures = signatureCount;
	}

	function getFieldEntry(index, x, y) {
		const key = fieldKey(x, y);
		if (!index.has(key)) {
			index.set(key, {
				x,
				y,
				spacecrafts: [],
				signatures: [],
				spacecraftCount: 0,
				signatureCount: 0,
			});
		}

		return index.get(key);
	}

	function isEntityFilterActive(state) {
		return state.selectedUserIds.size > 0 || state.selectedAllianceIds.size > 0;
	}

	function passesEntityFilter(state, item) {
		if (!isEntityFilterActive(state)) {
			return true;
		}
		if (state.selectedUserIds.has(Number(item.userId))) {
			return true;
		}
		return item.allianceId !== null && state.selectedAllianceIds.has(Number(item.allianceId));
	}

	function signaturePassesAge(state, item) {
		if (Number(item.age) > state.maxSignatureAge) {
			return false;
		}
		return state.signatureSinceTime === 0 || Number(item.time) >= state.signatureSinceTime;
	}

	function passesContactMovementThreshold(state, item, movedSpacecraftIds) {
		return movedSpacecraftIds === null || movedSpacecraftIds.has(Number(item.id));
	}

	function getMovedSpacecraftIdsSinceContactThreshold(state) {
		if (state.contactMovementSinceTime === 0) {
			return null;
		}

		const movedSpacecraftIds = new Set();
		state.flightSignatures.concat(state.selectedShipSignatures).forEach(function (item) {
			if (Number(item.time) >= state.contactMovementSinceTime && getTraceDirection(item) !== 0) {
				movedSpacecraftIds.add(Number(item.shipId));
			}
		});

		return movedSpacecraftIds;
	}

	function getTraceDirection(item) {
		const direction = Number(item.toDirection);
		return TRACE_COLORS[direction] ? direction : 0;
	}

	function dedupeSignatures(signatures) {
		const byShipFieldAndDirection = new Map();

		signatures.forEach(function (item) {
			const direction = getTraceDirection(item);
			if (direction === 0) {
				return;
			}

			const key = item.shipId + ":" + item.x + ":" + item.y + ":" + direction;
			const current = byShipFieldAndDirection.get(key);
			if (!current || Number(item.time) > Number(current.time)) {
				byShipFieldAndDirection.set(key, item);
			}
		});

		return Array.from(byShipFieldAndDirection.values());
	}

	function pruneSelection(state) {
		if (state.selectedShipId === null) {
			return;
		}
		if (state.spacecrafts.has(String(state.selectedShipId))) {
			return;
		}
		if (state.flightSignatures.some(function (item) {
			return Number(item.shipId) === state.selectedShipId;
		})) {
			return;
		}
		state.selectedShipId = null;
	}

	function getAnimatedPosition(item, now) {
		const progress = clamp((now - item.animationStarted) / MOVE_ANIMATION_MS, 0, 1);
		const eased = 1 - Math.pow(1 - progress, 3);

		return {
			x: item.fromX + (item.targetX - item.fromX) * eased,
			y: item.fromY + (item.targetY - item.fromY) * eased,
		};
	}

	function clampView(state) {
		if (!state.mapLoaded) {
			return;
		}

		const visibleWidth = state.canvas.clientWidth / state.scale;
		const visibleHeight = state.canvas.clientHeight / state.scale;
		const maxX = state.mapImage.width - visibleWidth;
		const maxY = state.mapImage.height - visibleHeight;

		state.viewX = maxX < 0 ? maxX / 2 : clamp(state.viewX, 0, maxX);
		state.viewY = maxY < 0 ? maxY / 2 : clamp(state.viewY, 0, maxY);
	}

	function zoomAt(state, screenX, screenY, factor) {
		if (!state.mapLoaded) {
			return;
		}

		const before = screenToMap(state, screenX, screenY);
		state.scale = clamp(state.scale * factor, state.minScale, state.maxScale);
		state.viewX = before.x - screenX / state.scale;
		state.viewY = before.y - screenY / state.scale;
		clampView(state);
		updateStatus(state);
	}

	function screenToMap(state, screenX, screenY) {
		return {
			x: state.viewX + screenX / state.scale,
			y: state.viewY + screenY / state.scale,
		};
	}

	function getCellCenter(state, x, y) {
		return {
			x: (x - 0.5) * state.cellSize,
			y: (y - 0.5) * state.cellSize,
		};
	}

	function getCellFromMap(state, mapPosition) {
		return {
			x: clamp(Math.floor(mapPosition.x / state.cellSize) + 1, 1, state.layerWidth),
			y: clamp(Math.floor(mapPosition.y / state.cellSize) + 1, 1, state.layerHeight),
		};
	}

	function draw(state) {
		const ctx = state.ctx;
		const canvasWidth = state.canvas.clientWidth;
		const canvasHeight = state.canvas.clientHeight;
		const dpr = window.devicePixelRatio || 1;

		if (state.followSelected) {
			followSelectedShip(state, false);
		}

		ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
		ctx.clearRect(0, 0, canvasWidth, canvasHeight);
		ctx.fillStyle = "#050608";
		ctx.fillRect(0, 0, canvasWidth, canvasHeight);

		if (!state.mapLoaded) {
			return;
		}

		ctx.save();
		ctx.translate(-state.viewX * state.scale, -state.viewY * state.scale);
		ctx.scale(state.scale, state.scale);
		ctx.imageSmoothingEnabled = false;
		ctx.drawImage(state.mapImage, 0, 0);

		if (state.showTerritory) {
			drawTerritoryOverlay(state);
		}
		if (state.showImpassable) {
			drawImpassableOverlay(state);
		}
		if (state.showSignatures) {
			drawSelectedCourse(state);
		}
		if (state.scale > 0.8) {
			drawGrid(state);
		}
		if (state.showCounts) {
			drawFieldCounts(state);
		}
		if (state.showSignatures) {
			drawFlightSignatures(state);
		}

		ctx.restore();
	}

	function getVisibleCellBounds(state) {
		const size = state.cellSize;
		const maxVisibleX = state.viewX + state.canvas.clientWidth / state.scale;
		const maxVisibleY = state.viewY + state.canvas.clientHeight / state.scale;

		return {
			minX: clamp(Math.floor(state.viewX / size) + 1, 1, state.layerWidth),
			maxX: clamp(Math.ceil(maxVisibleX / size), 1, state.layerWidth),
			minY: clamp(Math.floor(state.viewY / size) + 1, 1, state.layerHeight),
			maxY: clamp(Math.ceil(maxVisibleY / size), 1, state.layerHeight),
		};
	}

	function isCellVisible(bounds, item) {
		return item.x >= bounds.minX && item.x <= bounds.maxX && item.y >= bounds.minY && item.y <= bounds.maxY;
	}

	function isMapPointVisible(state, x, y, padding) {
		const maxX = state.viewX + state.canvas.clientWidth / state.scale;
		const maxY = state.viewY + state.canvas.clientHeight / state.scale;

		return x >= state.viewX - padding && x <= maxX + padding && y >= state.viewY - padding && y <= maxY + padding;
	}

	function drawTerritoryOverlay(state) {
		const ctx = state.ctx;
		const size = state.cellSize;
		const bounds = getVisibleCellBounds(state);

		ctx.save();
		ctx.lineWidth = 1 / state.scale;
		state.territoryFields.forEach(function (item) {
			if (!isCellVisible(bounds, item)) {
				return;
			}
			const x = (item.x - 1) * size;
			const y = (item.y - 1) * size;

			ctx.globalAlpha = 0.24;
			ctx.fillStyle = item.color;
			ctx.fillRect(x, y, size, size);
			ctx.globalAlpha = 0.82;
			ctx.strokeStyle = item.color;
			ctx.strokeRect(x + 0.5 / state.scale, y + 0.5 / state.scale, size - 1 / state.scale, size - 1 / state.scale);
		});
		ctx.restore();
	}

	function drawImpassableOverlay(state) {
		const ctx = state.ctx;
		const size = state.cellSize;
		const bounds = getVisibleCellBounds(state);

		ctx.save();
		ctx.lineWidth = 1.6 / state.scale;
		state.impassableFields.forEach(function (item) {
			if (!isCellVisible(bounds, item)) {
				return;
			}
			const x = (item.x - 1) * size;
			const y = (item.y - 1) * size;

			ctx.globalAlpha = 0.34;
			ctx.fillStyle = item.color;
			ctx.fillRect(x, y, size, size);
			ctx.globalAlpha = 0.9;
			ctx.strokeStyle = item.color;
			ctx.beginPath();
			ctx.moveTo(x + 3 / state.scale, y + 3 / state.scale);
			ctx.lineTo(x + size - 3 / state.scale, y + size - 3 / state.scale);
			ctx.moveTo(x + size - 3 / state.scale, y + 3 / state.scale);
			ctx.lineTo(x + 3 / state.scale, y + size - 3 / state.scale);
			ctx.stroke();
		});
		ctx.restore();
	}

	function drawSelectedCourse(state) {
		if (state.selectedShipId === null) {
			return;
		}

		const ctx = state.ctx;
		const selectedShipId = Number(state.selectedShipId);
		const points = getSelectedCoursePoints(state);

		const spacecraft = state.spacecrafts.get(String(selectedShipId));
		if (spacecraft) {
			const last = points[points.length - 1] || null;
			if (last === null || last.cellX !== Number(spacecraft.x) || last.cellY !== Number(spacecraft.y)) {
				points.push({
					cellX: Number(spacecraft.x),
					cellY: Number(spacecraft.y),
					point: getAnimatedPosition(spacecraft, performance.now()),
				});
			}
		}

		if (points.length < 2) {
			return;
		}

		ctx.save();
		ctx.globalAlpha = 0.92;
		ctx.strokeStyle = "#ffbf47";
		ctx.fillStyle = "#ffbf47";
		ctx.lineWidth = 2.4 / state.scale;
		ctx.lineCap = "round";
		ctx.lineJoin = "round";

		for (let i = 1; i < points.length; i++) {
			const from = points[i - 1];
			const to = points[i];
			if (!isDrawableCourseStep(state, from, to)) {
				continue;
			}

			ctx.beginPath();
			ctx.moveTo(from.point.x, from.point.y);
			ctx.lineTo(to.point.x, to.point.y);
			ctx.stroke();
			drawArrowHead(ctx, from.point, to.point, 7 / state.scale);
		}
		ctx.restore();
	}

	function getSelectedCoursePoints(state) {
		const points = [];
		getSelectedCourseSignatures(state)
			.sort(sortCourseSignatures)
			.forEach(function (item) {
				const cellX = Number(item.x);
				const cellY = Number(item.y);
				const last = points[points.length - 1] || null;
				if (last !== null && last.cellX === cellX && last.cellY === cellY) {
					return;
				}

				points.push({
					cellX,
					cellY,
					point: getCellCenter(state, cellX, cellY),
				});
			});

		return points;
	}

	function isDrawableCourseStep(state, from, to) {
		const cellDx = to.cellX - from.cellX;
		const cellDy = to.cellY - from.cellY;
		const isNeighbor = Math.abs(cellDx) + Math.abs(cellDy) === 1;
		if (!isNeighbor) {
			return false;
		}

		const tolerance = state.cellSize * 0.12;
		if (cellDx !== 0) {
			return Math.abs(to.point.y - from.point.y) <= tolerance;
		}

		return Math.abs(to.point.x - from.point.x) <= tolerance;
	}

	function sortCourseSignatures(a, b) {
		const timeDiff = Number(a.time) - Number(b.time);
		if (timeDiff !== 0) {
			return timeDiff;
		}

		return Number(a.id) - Number(b.id);
	}

	function drawArrowHead(ctx, from, to, size) {
		const angle = Math.atan2(to.y - from.y, to.x - from.x);
		if (!Number.isFinite(angle)) {
			return;
		}

		ctx.beginPath();
		ctx.moveTo(to.x, to.y);
		ctx.lineTo(to.x - Math.cos(angle - Math.PI / 6) * size, to.y - Math.sin(angle - Math.PI / 6) * size);
		ctx.lineTo(to.x - Math.cos(angle + Math.PI / 6) * size, to.y - Math.sin(angle + Math.PI / 6) * size);
		ctx.closePath();
		ctx.fill();
	}

	function drawGrid(state) {
		const ctx = state.ctx;
		const size = state.cellSize;
		const minX = Math.max(0, Math.floor(state.viewX / size) * size);
		const minY = Math.max(0, Math.floor(state.viewY / size) * size);
		const maxX = Math.min(state.mapImage.width, state.viewX + state.canvas.clientWidth / state.scale);
		const maxY = Math.min(state.mapImage.height, state.viewY + state.canvas.clientHeight / state.scale);

		ctx.lineWidth = 1 / state.scale;
		ctx.strokeStyle = "rgba(255,255,255,0.16)";
		ctx.beginPath();
		for (let x = minX; x <= maxX; x += size) {
			ctx.moveTo(x, minY);
			ctx.lineTo(x, maxY);
		}
		for (let y = minY; y <= maxY; y += size) {
			ctx.moveTo(minX, y);
			ctx.lineTo(maxX, y);
		}
		ctx.stroke();
	}

	function drawFieldCounts(state) {
		if (state.scale < 0.24) {
			return;
		}

		const bounds = getVisibleCellBounds(state);
		state.fieldIndex.forEach(function (entry) {
			if (!isCellVisible(bounds, entry)) {
				return;
			}

			const x = (entry.x - 1) * state.cellSize;
			const y = (entry.y - 1) * state.cellSize;
			if (state.showSpacecrafts && entry.spacecraftCount > 0) {
				drawContactCount(state, x, y, entry.spacecraftCount);
			}
		});
	}

	function drawContactCount(state, x, y, count) {
		const ctx = state.ctx;
		const text = String(count);
		const fontSize = 18 / state.scale;
		const centerX = x + state.cellSize / 2;
		const centerY = y + state.cellSize / 2;
		const radius = Math.max(10 / state.scale, state.cellSize * 0.28);

		ctx.save();
		ctx.globalAlpha = 0.9;
		ctx.fillStyle = "rgba(8,10,14,0.78)";
		ctx.beginPath();
		ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
		ctx.fill();
		ctx.strokeStyle = CONTACT_COLOR;
		ctx.lineWidth = 1.6 / state.scale;
		ctx.stroke();
		ctx.font = "bold " + fontSize + "px sans-serif";
		ctx.textAlign = "center";
		ctx.textBaseline = "middle";
		ctx.fillStyle = CONTACT_COLOR;
		ctx.strokeStyle = "rgba(0,0,0,0.9)";
		ctx.lineWidth = 3 / state.scale;
		ctx.strokeText(text, centerX, centerY + 0.5 / state.scale);
		ctx.fillText(text, centerX, centerY + 0.5 / state.scale);
		ctx.restore();
	}

	function drawFlightSignatures(state) {
		const ctx = state.ctx;

		state.visibleSignatures.forEach(function (item) {
			const selected = state.selectedShipId !== null && Number(item.shipId) === Number(state.selectedShipId);
			const center = getCellCenter(state, item.x, item.y);
			if (!isMapPointVisible(state, center.x, center.y, state.cellSize)) {
				return;
			}

			const ageRatio = clamp(item.age / SIGNATURE_MAX_AGE, 0, 1);
			const alpha = selected ? 0.95 : (1 - ageRatio) * (item.isCloaked ? 0.42 : 0.72);
			const direction = directionOffset(getTraceDirection(item));
			const length = Math.max(13 / state.scale, state.cellSize * 0.45);
			const offset = getTraceOffset(state, item);
			const start = {
				x: center.x + offset.x - direction.x * length * 0.42,
				y: center.y + offset.y - direction.y * length * 0.42,
			};
			const end = {
				x: center.x + offset.x + direction.x * length * 0.42,
				y: center.y + offset.y + direction.y * length * 0.42,
			};

			ctx.save();
			ctx.globalAlpha = alpha;
			ctx.strokeStyle = selected ? "#ffbf47" : getSignatureColor(state, item);
			ctx.fillStyle = ctx.strokeStyle;
			ctx.lineWidth = (selected ? 2.4 : 1.9) / state.scale;
			ctx.lineCap = "round";
			ctx.beginPath();
			ctx.moveTo(start.x, start.y);
			ctx.lineTo(end.x, end.y);
			ctx.stroke();
			drawArrowHead(ctx, start, end, Math.max(6 / state.scale, 3));
			ctx.restore();
		});
	}

	function getTraceOffset(state, item) {
		const offsetSlots = [
			{ x: 0, y: 0 },
			{ x: -0.18, y: -0.18 },
			{ x: 0.18, y: -0.18 },
			{ x: -0.18, y: 0.18 },
			{ x: 0.18, y: 0.18 },
			{ x: 0, y: -0.28 },
			{ x: 0.28, y: 0 },
			{ x: 0, y: 0.28 },
			{ x: -0.28, y: 0 },
		];
		const slot = offsetSlots[(Number(item.displayIndex) || 0) % offsetSlots.length];

		return {
			x: slot.x * state.cellSize,
			y: slot.y * state.cellSize,
		};
	}

	function getSignatureColor(state, item) {
		if (state.signatureColorMode === "heatmap") {
			return getHeatmapSignatureColor(state, item);
		}
		if (item.isCloaked) {
			return "#d98cff";
		}

		switch (state.signatureColorMode) {
			case "direction":
				return TRACE_COLORS[getTraceDirection(item)] || "#f8f8ff";
			case "user":
				return hashColor(Number(item.userId));
			case "alliance":
				return item.allianceId === null ? "#f8f8ff" : hashColor(Number(item.allianceId));
			case "age":
			default: {
				const ratio = clamp(Number(item.age) / state.maxSignatureAge, 0, 1);
				const hue = Math.round(135 - ratio * 95);
				return "hsl(" + hue + ", 88%, 62%)";
			}
		}
	}

	function getHeatmapSignatureColor(state, item) {
		const entry = state.fieldIndex.get(fieldKey(item.x, item.y));
		const count = entry ? Math.max(1, Number(entry.signatureCount) || 1) : 1;
		const max = Math.max(1, Number(state.maxSignatureHeat) || 1);
		if (max <= 1) {
			return "hsl(128, 88%, 56%)";
		}

		const ratio = clamp((count - 1) / (max - 1), 0, 1);
		const hue = Math.round(128 - ratio * 128);
		const lightness = Math.round(56 - ratio * 8);

		return "hsl(" + hue + ", 88%, " + lightness + "%)";
	}

	function hashColor(value) {
		const hue = Math.abs((value * 47) % 360);
		return "hsl(" + hue + ", 78%, 62%)";
	}

	function updateHover(state) {
		if (!state.mapLoaded || state.dragging) {
			return;
		}

		if (
			state.mouseX < 0 ||
			state.mouseY < 0 ||
			state.mouseX > state.canvas.clientWidth ||
			state.mouseY > state.canvas.clientHeight
		) {
			hideTooltip(state);
			return;
		}

		const mapPosition = screenToMap(state, state.mouseX, state.mouseY);
		const cell = getCellFromMap(state, mapPosition);
		state.stats.coordX = cell.x;
		state.stats.coordY = cell.y;
		updateStatus(state);

		const html = buildFieldTooltip(state, cell);
		if (html === "") {
			hideTooltip(state);
			return;
		}

		state.tooltip.style.display = "block";
		state.tooltip.innerHTML = html;
		positionTooltip(state);
	}

	function buildFieldTooltip(state, cell) {
		const data = getFieldDisplayData(state, cell);
		if (
			data.spacecrafts.length === 0 &&
			data.signatures.length === 0 &&
			!data.territory &&
			!data.impassable
		) {
			return "";
		}

		const html = ["<strong>Feld " + cell.x + " | " + cell.y + "</strong>"];
		appendFieldMetaHtml(html, data);

		if (data.spacecrafts.length > 0) {
			html.push("<br /><br /><strong>Kontakte: " + data.spacecrafts.length + "</strong>");
			data.spacecrafts.slice(0, TOOLTIP_ITEM_LIMIT).forEach(function (item) {
				html.push(buildEntitySummaryHtml(item, item.nameHtml, typeLabel(item.type)));
			});
			appendRemainder(html, data.spacecrafts.length, TOOLTIP_ITEM_LIMIT);
		}

		if (data.signatures.length > 0) {
			html.push("<br /><br /><strong>Spuren</strong>");
			data.signatures.slice(0, TOOLTIP_ITEM_LIMIT).forEach(function (item) {
				html.push(buildSignatureSummaryHtml(item));
			});
			appendRemainder(html, data.signatures.length, TOOLTIP_ITEM_LIMIT);
		}

		return html.join("");
	}

	function getFieldDisplayData(state, cell) {
		const key = fieldKey(cell.x, cell.y);
		const entry = state.fieldIndex.get(key) || {
			spacecrafts: [],
			signatures: [],
			spacecraftCount: 0,
			signatureCount: 0,
		};
		const spacecrafts = state.showSpacecrafts ? entry.spacecrafts.slice().sort(sortById) : [];
		const signatures = state.showSignatures ? entry.signatures.slice().sort(sortByNewestTime) : [];

		return {
			spacecrafts,
			signatures,
			signatureCount: state.showSignatures ? Math.max(entry.signatureCount || 0, signatures.length) : 0,
			territory: state.showTerritory ? state.territoryByField.get(key) : null,
			impassable: state.showImpassable ? state.impassableByField.get(key) : null,
		};
	}

	function appendFieldMetaHtml(html, data) {
		if (data.territory) {
			html.push("<br />Territorium: " + escapeHtml(data.territory.name || data.territory.color));
		}
		if (data.impassable) {
			html.push("<br />Unpassierbar");
		}
	}

	function buildOwnerMetaHtml(item) {
		const userNameText = item.userNameText || item.userName || "User";
		const userName = item.userNameHtml || escapeHtml(userNameText);

		return userName +
			" (" +
			escapeHtml(item.userId) +
			")";
	}

	function buildEntitySummaryHtml(item, nameHtml, detail) {
		return (
			"<div class=\"adminLiveMapTooltipItem\">" +
			"<img src=\"" +
			escapeHtml(item.rumpImage) +
			"\" alt=\"\" />" +
			"<span>" +
			(nameHtml || escapeHtml(item.nameText || item.name || item.shipNameText || item.shipName)) +
			"<br /><span class=\"adminLiveMapTooltipMeta\">" +
			escapeHtml(item.rumpName) +
			" | " +
			escapeHtml(detail) +
			" | " +
			buildOwnerMetaHtml(item) +
			(item.inSystem ? " | " + escapeHtml(item.systemName || "System") : "") +
			(item.isCloaked ? " | getarnt" : "") +
			"</span></span></div>"
		);
	}

	function buildSignatureSummaryHtml(item) {
		return (
			"<div class=\"adminLiveMapTooltipItem\">" +
			"<img src=\"" +
			escapeHtml(item.rumpImage) +
			"\" alt=\"\" />" +
			"<span>" +
			(item.shipNameHtml || escapeHtml(item.shipNameText || item.shipName)) +
			"<br /><span class=\"adminLiveMapTooltipMeta\">" +
			escapeHtml(item.rumpName) +
			" | " +
			buildOwnerMetaHtml(item) +
			" | " +
			formatAge(item.age) +
			" | Richtung " +
			directionLabel(getTraceDirection(item)) +
			(item.inSystem ? " | " + escapeHtml(item.systemName || "System") : "") +
			(item.isCloaked ? " | getarnt" : "") +
			"</span></span></div>"
		);
	}

	function appendRemainder(html, length, limit) {
		if (length > limit) {
			html.push("<br /><span class=\"adminLiveMapTooltipMeta\">+" + (length - limit) + " weitere</span>");
		}
	}

	function positionTooltip(state) {
		const parent = state.canvas.parentElement;
		const padding = 8;
		let left = state.mouseX + 14;
		let top = state.mouseY + 14;

		state.tooltip.style.left = left + "px";
		state.tooltip.style.top = top + "px";

		if (left + state.tooltip.offsetWidth + padding > parent.clientWidth) {
			left = state.mouseX - state.tooltip.offsetWidth - 14;
		}
		if (top + state.tooltip.offsetHeight + padding > parent.clientHeight) {
			top = state.mouseY - state.tooltip.offsetHeight - 14;
		}

		state.tooltip.style.left = Math.max(padding, left) + "px";
		state.tooltip.style.top = Math.max(padding, top) + "px";
	}

	function selectFieldAt(state, screenX, screenY) {
		if (!state.mapLoaded) {
			return;
		}

		const mapPosition = screenToMap(state, screenX, screenY);
		state.selectedField = getCellFromMap(state, mapPosition);
		updateFieldPanel(state);
		updateSelectedLabel(state);
		updateHover(state);
	}

	function updateFieldPanel(state) {
		if (!state.fieldDetails) {
			return;
		}
		if (state.selectedField === null) {
			state.fieldDetails.innerHTML = "<div class=\"adminLiveMapEmpty\">Feld anklicken</div>";
			return;
		}

		const cell = state.selectedField;
		const data = getFieldDisplayData(state, cell);
		const html = ["<div class=\"adminLiveMapFieldTitle\">Feld " + cell.x + " | " + cell.y + "</div>"];
		html.push("<div class=\"adminLiveMapFieldCounts\">Kontakte " + data.spacecrafts.length + "</div>");
		appendPanelMetaHtml(html, data);
		appendEntityButtons(html, "Kontakte", data.spacecrafts, function (item) {
			return {
				shipId: item.id,
				image: item.rumpImage,
				nameHtml: item.nameHtml || escapeHtml(item.nameText || item.name),
				metaHtml: escapeHtml(item.rumpName) + " | " + typeLabel(item.type) + " | " + buildOwnerMetaHtml(item),
			};
		});
		appendEntityButtons(html, "Spuren", data.signatures, function (item) {
			return {
				shipId: item.shipId,
				image: item.rumpImage,
				nameHtml: item.shipNameHtml || escapeHtml(item.shipNameText || item.shipName),
				metaHtml: escapeHtml(item.rumpName) + " | " + buildOwnerMetaHtml(item) + " | " + formatAge(item.age) + " | Richtung " + directionLabel(getTraceDirection(item)),
			};
		});
		if (data.spacecrafts.length === 0 && data.signatures.length === 0) {
			html.push("<div class=\"adminLiveMapEmpty\">Keine Kontakte oder Spuren für die aktuelle Filterung</div>");
		}

		state.fieldDetails.innerHTML = html.join("");
	}

	function appendPanelMetaHtml(html, data) {
		const meta = [];
		if (data.territory) {
			meta.push("Territorium: " + escapeHtml(data.territory.name || data.territory.color));
		}
		if (data.impassable) {
			meta.push("Unpassierbar");
		}
		if (meta.length > 0) {
			html.push("<div class=\"adminLiveMapFieldMeta\">" + meta.join("<br />") + "</div>");
		}
	}

	function appendEntityButtons(html, title, items, mapper) {
		if (items.length === 0) {
			return;
		}

		html.push("<div class=\"adminLiveMapPanelSubTitle\">" + title + "</div>");
		items.slice(0, PANEL_ITEM_LIMIT).forEach(function (item) {
			const data = mapper(item);
			html.push(
				"<button type=\"button\" class=\"adminLiveMapEntityButton\" data-select-ship-id=\"" +
				escapeHtml(data.shipId) +
				"\">" +
				"<img src=\"" +
				escapeHtml(data.image) +
				"\" alt=\"\" />" +
				"<span class=\"adminLiveMapEntityText\"><span class=\"adminLiveMapEntityName\">" +
				data.nameHtml +
				"</span> <span class=\"adminLiveMapEntityMeta\">" +
				data.metaHtml +
				"</span></span></button>"
			);
		});
		if (items.length > PANEL_ITEM_LIMIT) {
			html.push("<div class=\"adminLiveMapEmpty\">+" + (items.length - PANEL_ITEM_LIMIT) + " weitere</div>");
		}
	}

	function getSelectedShipSignatures(state) {
		const byId = new Map();

		state.flightSignatures.forEach(function (item) {
			byId.set(Number(item.id), item);
		});
		state.selectedShipSignatures.forEach(function (item) {
			byId.set(Number(item.id), item);
		});

		return Array.from(byId.values());
	}

	function getSelectedCourseSignatures(state) {
		if (state.selectedShipId === null) {
			return [];
		}

		return getSelectedShipSignatures(state).filter(function (item) {
			return Number(item.shipId) === Number(state.selectedShipId) &&
				signaturePassesAge(state, item) &&
				getTraceDirection(item) !== 0;
		});
	}

	function selectShip(state, shipId, centerNow) {
		state.selectedShipId = Number(shipId);
		if (centerNow) {
			followSelectedShip(state, true);
		}
		updateSelectedLabel(state);
		loadLiveData(state);
	}

	function followSelectedShip(state, snap) {
		if (!state.mapLoaded || state.selectedShipId === null) {
			return;
		}

		const position = getSelectedPosition(state);
		if (position === null) {
			return;
		}

		const targetX = position.x - state.canvas.clientWidth / state.scale / 2;
		const targetY = position.y - state.canvas.clientHeight / state.scale / 2;
		if (snap) {
			state.viewX = targetX;
			state.viewY = targetY;
		} else {
			state.viewX += (targetX - state.viewX) * 0.12;
			state.viewY += (targetY - state.viewY) * 0.12;
		}
		clampView(state);
	}

	function getSelectedPosition(state) {
		const spacecraft = state.spacecrafts.get(String(state.selectedShipId));
		if (spacecraft) {
			return getAnimatedPosition(spacecraft, performance.now());
		}

		const latestSignature = getSelectedCourseSignatures(state).sort(sortByNewestTime)[0];

		return latestSignature ? getCellCenter(state, latestSignature.x, latestSignature.y) : null;
	}

	function updateSelectedLabel(state) {
		if (!state.selected) {
			return;
		}

		if (state.selectedShipId === null) {
			state.selected.textContent = state.selectedField === null
				? "Keine Auswahl"
				: "Feld " + state.selectedField.x + "|" + state.selectedField.y;
			return;
		}

		const spacecraft = state.spacecrafts.get(String(state.selectedShipId));
		const signatures = getSelectedCourseSignatures(state);
		const latestSignature = signatures.slice().sort(sortByNewestTime)[0];
		const label = spacecraft
			? (spacecraft.nameText || spacecraft.name)
			: latestSignature
				? (latestSignature.shipNameText || latestSignature.shipName)
				: "Schiff";
		const position = spacecraft
			? "Position " + spacecraft.x + "|" + spacecraft.y
			: latestSignature
				? "letzte Spur " + latestSignature.x + "|" + latestSignature.y
				: "keine Position";

		state.selected.textContent =
			label +
			" | " +
			position +
			" | Kurspunkte " +
			signatures.length +
			" | Follow " +
			(state.followSelected ? "an" : "aus") +
			" | Update " +
			DATA_REFRESH_MS / 1000 +
			"s";
	}

	function renderFilterLists(state) {
		const filters = buildFilterData(state);
		renderAllianceFilters(state, filters.alliances);
		renderUserFilters(state, filters.users);
		renderFilterSummary(state);
	}

	function buildFilterData(state) {
		const users = new Map();
		const alliances = new Map();
		const movedSpacecraftIds = getMovedSpacecraftIdsSinceContactThreshold(state);

		state.spacecrafts.forEach(function (item) {
			if (!passesContactMovementThreshold(state, item, movedSpacecraftIds)) {
				return;
			}
			addFilterEntity(users, alliances, item, true);
		});
		dedupeSignatures(state.flightSignatures.filter(function (item) {
			return signaturePassesAge(state, item);
		})).forEach(function (item) {
			addFilterEntity(users, alliances, item, false);
		});

		return {
			users: Array.from(users.values()).sort(sortByName),
			alliances: Array.from(alliances.values()).sort(sortByName),
		};
	}

	function addFilterEntity(users, alliances, item, contact) {
		const userId = Number(item.userId);
		if (!users.has(userId)) {
			users.set(userId, {
				id: userId,
				name: item.userNameText || item.userName || ("User " + userId),
				nameHtml: item.userNameHtml || escapeHtml(item.userNameText || item.userName || ("User " + userId)),
				suffix: " (" + userId + ")",
				contacts: 0,
				traces: 0,
			});
		}
		const user = users.get(userId);
		if (contact) {
			user.contacts++;
		} else {
			user.traces++;
		}

		if (item.allianceId === null) {
			return;
		}

		const allianceId = Number(item.allianceId);
		if (!alliances.has(allianceId)) {
			alliances.set(allianceId, {
				id: allianceId,
				name: item.allianceNameText || item.allianceName || ("Allianz " + allianceId),
				nameHtml: item.allianceNameHtml || escapeHtml(item.allianceNameText || item.allianceName || ("Allianz " + allianceId)),
				suffix: "",
				contacts: 0,
				traces: 0,
			});
		}
		const alliance = alliances.get(allianceId);
		if (contact) {
			alliance.contacts++;
		} else {
			alliance.traces++;
		}
	}

	function renderAllianceFilters(state, alliances) {
		if (alliances.length === 0) {
			state.allianceFilterList.innerHTML = "<div class=\"adminLiveMapEmpty\">Keine Allianzen in den Daten</div>";
			return;
		}

		state.allianceFilterList.innerHTML = "<table class=\"adminLiveMapFilterTable\"><tbody>" + alliances.map(function (item) {
			return buildFilterCheckboxHtml(
				"allianceId",
				item.id,
				item.nameHtml,
				item.suffix,
				item.contacts,
				item.traces,
				state.selectedAllianceIds.has(item.id)
			);
		}).join("") + "</tbody></table>";
	}

	function renderUserFilters(state, users) {
		if (users.length === 0) {
			state.userFilterList.innerHTML = "<div class=\"adminLiveMapEmpty\">Keine User in den Daten</div>";
			return;
		}

		state.userFilterList.innerHTML = "<table class=\"adminLiveMapFilterTable\"><tbody>" + users.map(function (item) {
			return buildFilterCheckboxHtml(
				"userId",
				item.id,
				item.nameHtml,
				item.suffix,
				item.contacts,
				item.traces,
				state.selectedUserIds.has(item.id)
			);
		}).join("") + "</tbody></table>";
	}

	function buildFilterCheckboxHtml(dataName, id, nameHtml, suffix, contacts, traces, checked) {
		const inputId = "admin-live-map-filter-" + dataName + "-" + id;

		return (
			"<tr class=\"adminLiveMapFilterRow\" style=\"vertical-align: top;\">" +
			"<td style=\"width: 18px; padding: 4px 7px 5px 0; vertical-align: top; border-bottom: 1px solid #202834;\">" +
			"<input id=\"" +
			escapeHtml(inputId) +
			"\" type=\"checkbox\" data-" +
			dataName.replace(/[A-Z]/g, function (letter) {
				return "-" + letter.toLowerCase();
			}) +
			"=\"" +
			id +
			"\"" +
			(checked ? " checked=\"checked\"" : "") +
			" />" +
			"</td>" +
			"<td style=\"padding: 3px 0 5px 0; vertical-align: top; border-bottom: 1px solid #202834;\">" +
			"<div class=\"adminLiveMapFilterText\" style=\"display: block; line-height: normal; min-width: 0;\">" +
			"<div class=\"adminLiveMapFilterName\" style=\"display: block; line-height: 1.35; min-height: 1.35em; overflow-wrap: anywhere; word-break: break-word;\">" +
			nameHtml +
			escapeHtml(suffix) +
			"</div>" +
			"<div class=\"adminLiveMapFilterStats\" style=\"display: block; padding-top: 1px; line-height: 1.3; color: #b9c6d8;\">Kontakte " +
			contacts +
			" | Spuren " +
			traces +
			"</div></div></td></tr>"
		);
	}

	function renderFilterSummary(state) {
		const userCount = state.selectedUserIds.size;
		const allianceCount = state.selectedAllianceIds.size;
		if (userCount === 0 && allianceCount === 0) {
			state.filterSummary.textContent = "Alle Kontakte und Spuren sichtbar";
			return;
		}

		state.filterSummary.textContent = "Filter: " + userCount + " User, " + allianceCount + " Allianzen";
	}

	function hideTooltip(state) {
		state.tooltip.style.display = "none";
	}

	function updateStatus(state) {
		state.status.textContent =
			"X " +
			state.stats.coordX +
			" | Y " +
			state.stats.coordY +
			" | Zoom " +
			Math.round(state.scale * 100) +
			"% | Kontakte " +
			state.stats.spacecrafts +
			" | Spuren " +
			state.stats.flightSignatures +
			(state.stats.signatureLimitHit
				? " (Limit " + state.stats.signatureLimit + " erreicht)"
				: "") +
			" auf " +
			state.stats.signatureFields +
			" Feldern | Refresh " +
			DATA_REFRESH_MS / 1000 +
			"s | " +
			getLiveDataRequestStatus(state);
	}

	function getLiveDataRequestStatus(state) {
		if (state.liveDataInFlight) {
			return "Request läuft seit " +
				formatClockTime(state.liveDataStartedAt) +
				(state.liveDataQueued ? ", nächster wartet" : "");
		}

		if (state.liveDataFinishedAt === 0) {
			return "Request ausstehend";
		}

		return "Request " +
			state.liveDataStatus +
			" " +
			formatClockTime(state.liveDataFinishedAt) +
			" (" +
			formatRequestDuration(state.liveDataDurationMs) +
			")";
	}

	function formatClockTime(timestamp) {
		if (!timestamp) {
			return "-";
		}

		return new Date(timestamp).toLocaleTimeString("de-DE", {
			hour: "2-digit",
			minute: "2-digit",
			second: "2-digit",
		});
	}

	function formatRequestDuration(milliseconds) {
		if (milliseconds >= 1000) {
			return (milliseconds / 1000).toFixed(milliseconds < 10000 ? 1 : 0) + "s";
		}

		return Math.max(0, Math.round(milliseconds)) + "ms";
	}

	function sortById(a, b) {
		return Number(a.id) - Number(b.id);
	}

	function sortByNewestTime(a, b) {
		return Number(b.time) - Number(a.time);
	}

	function sortByName(a, b) {
		return a.name.localeCompare(b.name, "de", { sensitivity: "base" });
	}

	function typeLabel(type) {
		switch (type) {
			case "SHIP":
				return "Schiff";
			case "STATION":
				return "Station";
			case "THOLIAN_WEB":
				return "Energienetz";
			default:
				return String(type);
		}
	}

	function formatAge(age) {
		const seconds = Math.max(0, Number(age) || 0);
		if (seconds < 60) {
			return seconds + "s";
		}
		if (seconds < 3600) {
			return Math.floor(seconds / 60) + "m";
		}
		return Math.floor(seconds / 3600) + "h";
	}

	function formatDuration(seconds) {
		const value = Math.max(0, Number(seconds) || 0);
		if (value < 3600) {
			return Math.floor(value / 60) + "m";
		}
		if (value < 86400) {
			return Math.floor(value / 3600) + "h";
		}
		return Math.floor(value / 86400) + "d";
	}

	function escapeHtml(value) {
		return String(value)
			.replace(/&/g, "&amp;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;")
			.replace(/"/g, "&quot;")
			.replace(/'/g, "&#039;");
	}

	document.observe("dom:loaded", initAdminLiveMap);
})();
