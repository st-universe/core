function getCommodityLocations(element, commodityId, offsetX = 0, offsetY = 0) {
	var pos = findObject(element);

	updatePopup(
		'database.php?commodityid=' + commodityId + '&SHOW_COMMODITIES_LOCATIONS=1',
		null, pos[0] + offsetX, pos[1] + offsetY, false
	);
}

function showColonySurface(element, id) {
	updatePopupAtElement(element, 'database.php?SHOW_SURFACE=1&id=' + id);
}

function openStatistics(period) {

	params = '';

	if (period) {
		params += `&period=${period}`;
	}

	switchInnerContent('SHOW_STATISTICS', 'Statistiken', params);
}

let crewManagementDragSelectionActive = false;
let crewManagementDragSelectionValue = false;

document.addEventListener('pointerup', function () {
	crewManagementDragSelectionActive = false;
});

document.addEventListener('pointercancel', function () {
	crewManagementDragSelectionActive = false;
});

function updateCrewManagementDismissSelection(management) {
	const selectedCount = management.querySelectorAll('.crew-management-card-selected').length;
	const confirm = management.querySelector('[data-crew-dismiss-confirm]');

	if (!confirm) {
		return;
	}

	confirm.style.display = selectedCount > 0 ? '' : 'none';
	confirm.value = 'Bestätigen' + (selectedCount > 0 ? ' (' + selectedCount + ')' : '');
}

function setCrewManagementCardSelection(card, selected) {
	const management = card.closest('#crewManagement');
	if (!management) {
		return;
	}

	card.classList.toggle('crew-management-card-selected', selected);
	card.setAttribute('aria-selected', selected ? 'true' : 'false');
	updateCrewManagementDismissSelection(management);
}

function handleCrewManagementCardClick(event, crewId) {
	const card = event.currentTarget && event.currentTarget.classList.contains('crew-management-card')
		? event.currentTarget
		: event.target.closest('.crew-management-card');
	const management = card ? card.closest('#crewManagement') : null;

	if (!card || !management) {
		return;
	}

	if (!management.classList.contains('crew-management-dismiss-mode')) {
		showCrewmanDetailsFromCrewManagement(crewId);
		return;
	}

	event.preventDefault();
	if (card.dataset.crewSelectionHandled === '1') {
		delete card.dataset.crewSelectionHandled;
		return;
	}

	setCrewManagementCardSelection(card, !card.classList.contains('crew-management-card-selected'));
}

function startCrewManagementSelection(event, card) {
	const management = card.closest('#crewManagement');
	if (!management || !management.classList.contains('crew-management-dismiss-mode')) {
		return;
	}

	if (event.target.closest('input, button')) {
		return;
	}

	event.preventDefault();
	crewManagementDragSelectionActive = true;
	crewManagementDragSelectionValue = !card.classList.contains('crew-management-card-selected');
	setCrewManagementCardSelection(card, crewManagementDragSelectionValue);

	if (!event.target.closest('.crew-management-name-container')) {
		card.dataset.crewSelectionHandled = '1';
	}
}

function continueCrewManagementSelection(card) {
	const management = card.closest('#crewManagement');
	if (!crewManagementDragSelectionActive || !management || !management.classList.contains('crew-management-dismiss-mode')) {
		return;
	}

	setCrewManagementCardSelection(card, crewManagementDragSelectionValue);
}

function initializeCrewManagement() {
	const management = document.getElementById('crewManagement');
	if (!management || management.dataset.initialized === '1') {
		return;
	}

	management.dataset.initialized = '1';

	const list = management.querySelector('[data-crew-list]');
	const cards = Array.from(management.querySelectorAll('.crew-management-card'));
	const nameInput = management.querySelector('[data-crew-filter-name]');
	const shipInput = management.querySelector('[data-crew-filter-ship]');
	const colonyInput = management.querySelector('[data-crew-filter-colony]');
	const sortInput = management.querySelector('[data-crew-sort]');
	const rankInputs = Array.from(management.querySelectorAll('[data-crew-filter-rank]'));
	const positionInputs = Array.from(management.querySelectorAll('[data-crew-filter-position]'));
	const escapePodInput = management.querySelector('[data-crew-filter-escape-pod]');
	const foreignStationInactiveInput = management.querySelector('[data-crew-filter-foreign-station-inactive]');
	const foreignStationInput = management.querySelector('[data-crew-filter-foreign-station]');
	const count = management.querySelector('[data-crew-count]');
	const reset = management.querySelector('[data-crew-filter-reset]');
	const apply = management.querySelector('[data-crew-filter-apply]');
	const dismissMode = management.querySelector('[data-crew-dismiss-mode]');
	const dismissConfirm = management.querySelector('[data-crew-dismiss-confirm]');

	if (!list || !nameInput || !shipInput || !colonyInput || !sortInput || !escapePodInput || !foreignStationInactiveInput || !foreignStationInput || !count || !reset || !apply || !dismissMode || !dismissConfirm) {
		return;
	}

	const getSelectedValues = function (inputs) {
		return new Set(inputs.filter(function (input) {
			return input.checked;
		}).map(function (input) {
			return input.value;
		}));
	};

	const compareText = function (left, right) {
		return left.localeCompare(right, 'de');
	};

	const sortCards = function () {
		const sortBy = sortInput.value;

		cards.sort(function (left, right) {
			if (sortBy === 'expertise') {
				const difference = Number(right.dataset.crewExpertise) - Number(left.dataset.crewExpertise);
				return difference || compareText(left.dataset.crewName, right.dataset.crewName);
			}
			if (sortBy === 'rank') {
				const difference = Number(right.dataset.crewRankExpertise) - Number(left.dataset.crewRankExpertise);
				return difference || compareText(left.dataset.crewName, right.dataset.crewName);
			}
			if (sortBy === 'ship') {
				return compareText(left.dataset.crewShip, right.dataset.crewShip) || compareText(left.dataset.crewName, right.dataset.crewName);
			}
			if (sortBy === 'position') {
				return compareText(left.dataset.crewPositionName, right.dataset.crewPositionName) || compareText(left.dataset.crewName, right.dataset.crewName);
			}

			return compareText(left.dataset.crewName, right.dataset.crewName);
		});

		cards.forEach(function (card) {
			list.appendChild(card);
		});
	};

	const applyFilters = function () {
		const name = nameInput.value.trim().toLocaleLowerCase('de-DE');
		const ship = shipInput.value.trim().toLocaleLowerCase('de-DE');
		const colony = colonyInput.value.trim().toLocaleLowerCase('de-DE');
		const ranks = getSelectedValues(rankInputs);
		const positions = getSelectedValues(positionInputs);
		const escapePodOnly = escapePodInput.checked;
		let visibleCount = 0;

		cards.forEach(function (card) {
			const isVisible =
				(!name || card.dataset.crewName.includes(name)) &&
				(escapePodOnly || (!ship || card.dataset.crewShip.includes(ship))) &&
				(escapePodOnly || (!colony || card.dataset.crewColony.includes(colony))) &&
				(!escapePodOnly || card.dataset.crewEscapePod === '1') &&
				(!ranks.size || ranks.has(card.dataset.crewRank)) &&
				(!positions.size || positions.has(card.dataset.crewPosition)) &&
				(!foreignStationInactiveInput.checked || card.dataset.crewForeignStationInactive === '1') &&
				(!foreignStationInput.checked || card.dataset.crewForeignStation === '1');

			card.hidden = !isVisible;
			if (isVisible) {
				visibleCount++;
			}
		});

		sortCards();
		count.textContent = visibleCount + ' von ' + cards.length + ' Crewmitgliedern';
	};

	sortInput.addEventListener('change', sortCards);
	apply.addEventListener('click', applyFilters);
	reset.addEventListener('click', function () {
		nameInput.value = '';
		shipInput.value = '';
		colonyInput.value = '';
		sortInput.value = 'expertise';
		rankInputs.concat(positionInputs, [escapePodInput, foreignStationInactiveInput, foreignStationInput]).forEach(function (input) {
			input.checked = false;
		});
		applyFilters();
	});
	dismissMode.addEventListener('click', function () {
		const isActive = management.classList.toggle('crew-management-dismiss-mode');
		dismissMode.value = isActive ? 'Abbrechen' : 'Entlassen';
		if (!isActive) {
			cards.forEach(function (card) {
				setCrewManagementCardSelection(card, false);
			});
		}
		updateCrewManagementDismissSelection(management);
	});
	dismissConfirm.addEventListener('click', function () {
		const crewIds = cards
			.filter(function (card) {
				return card.classList.contains('crew-management-card-selected');
			})
			.map(function (card) {
				return card.dataset.crewId;
			});

		if (crewIds.length === 0) {
			return;
		}

		dismissConfirm.disabled = true;
		const parameters = crewIds.map(function (crewId) {
			return 'crew_ids[]=' + encodeURIComponent(crewId);
		}).join('&') + '&sstr=' + encodeURIComponent(management.dataset.sessionString);

		new Ajax.Request('/database.php?B_DISMISS_CREW=1', {
			method: 'post',
			parameters: parameters,
			onSuccess: function () {
				switchInnerContent('SHOW_CREW_MANAGEMENT', 'Crew', null, '/database.php');
			},
			onFailure: function () {
				dismissConfirm.disabled = false;
			}
		});
	});

	applyFilters();
}

function showCrewmanDetailsFromCrewManagement(crewId) {
	switchInnerContent('SHOW_CREWMAN_DETAILS', 'Crewman Details', 'id=' + crewId, '/ship.php');
}

function showCrewManagementRename(event, crewId) {
	event.stopPropagation();
	const management = document.getElementById('crewManagement');
	if (management && management.classList.contains('crew-management-dismiss-mode')) {
		return;
	}

	const name = document.getElementById('crew-management-name-' + crewId);
	const inputContainer = document.getElementById('crew-management-name-input-' + crewId);
	const input = document.getElementById('crew-management-name-value-' + crewId);

	if (!name || !inputContainer || !input) {
		return;
	}

	name.style.display = 'none';
	inputContainer.style.display = 'flex';
	input.focus();
	input.select();
}

function renameCrewManagement(event, crewId) {
	event.stopPropagation();

	const management = document.getElementById('crewManagement');
	const name = document.getElementById('crew-management-name-' + crewId);
	const inputContainer = document.getElementById('crew-management-name-input-' + crewId);
	const input = document.getElementById('crew-management-name-value-' + crewId);
	const card = input ? input.closest('.crew-management-card') : null;
	const crewName = input ? input.value.trim() : '';

	if (!management || !name || !inputContainer || !input || !card || !crewName) {
		return;
	}

	new Ajax.Request('/ship.php?B_RENAME_CREWMAN=1&id=' + crewId, {
		method: 'post',
		parameters: {
			name: crewName,
			sstr: management.dataset.sessionString
		},
		onSuccess: function () {
			name.textContent = crewName;
			card.dataset.crewName = crewName.toLocaleLowerCase('de-DE');
			inputContainer.style.display = 'none';
			name.style.display = '';
		}
	});
}

document.addEventListener('DOMContentLoaded', function () {
	const pieChart = document.getElementById('pieChart');
	const progressBar = document.getElementById('progressBar');

	if (pieChart) {

		const chartContainer = document.getElementById('factionChart');
		const factionDataJson = chartContainer.getAttribute('data-factions');
		const maxPrestige = parseInt(chartContainer.getAttribute('data-max-prestige')) || 0;
		const actualPrestige = parseInt(chartContainer.getAttribute('data-actual-prestige')) || 0;

		initializePirateRoundChart(factionDataJson, maxPrestige, actualPrestige);
	}
});

function initializePirateRoundChart(factionDataJson, maxPrestige, actualPrestige) {
	const factionData = JSON.parse(factionDataJson);

	function lightenColor(color, percent) {
		color = color.replace('#', '');
		const num = parseInt(color, 16);
		const R = (num >> 16);
		const G = (num >> 8 & 0x00FF);
		const B = (num & 0x0000FF);

		const newR = Math.min(255, Math.round(R + (255 - R) * (percent / 100)));
		const newG = Math.min(255, Math.round(G + (255 - G) * (percent / 100)));
		const newB = Math.min(255, Math.round(B + (255 - B) * (percent / 100)));

		return "#" + ((1 << 24) + (newR << 16) + (newG << 8) + newB).toString(16).slice(1);
	}

	function startScannerAnimation() {
		const scannerLine = document.getElementById('scannerLine');
		scannerLine.style.animation = 'scannerSweep 3s ease-in-out';

		setTimeout(() => {
			scannerLine.style.opacity = '0';
			scannerLine.style.animation = '';
			if (factionData.length > 0) {
				hideCentralDisplay();
			} else {
				showScanFailure();
			}
		}, 3000);
	}

	function hideCentralDisplay() {
		const centralDisplay = document.getElementById('centralDisplay');
		setTimeout(() => {
			centralDisplay.style.opacity = '0';
			setTimeout(() => {
				centralDisplay.style.display = 'none';
			}, 1000);
		}, 500);
	}

	function showScanFailure() {
		const centralDisplay = document.getElementById('centralDisplay');
		centralDisplay.innerHTML = `
            <div style="text-align: center;">
                <div style="font-weight: bold; color: #ff0000;">FEHLER</div>
                <div style="font-size: 10px; color: #ff6600;">KEINE DATEN</div>
            </div>
        `;
		centralDisplay.style.borderColor = '#ff0000';
		centralDisplay.style.animation = 'errorPulse 1s ease-in-out infinite alternate';

	}

	const canvas = document.getElementById('pieChart');
	if (!canvas) return;

	const ctx = canvas.getContext('2d');
	const centerX = canvas.width / 2;
	const centerY = canvas.height / 2;
	const radius = 140;

	let currentAngle = -Math.PI / 2;
	const slices = [];
	const loadedImages = {};
	let hoveredSliceIndex = -1;
	let animationProgress = 0;
	let isAnimating = true;

	function preloadImages() {
		if (factionData.length === 0) {
			startScannerAnimation();
			return;
		}

		const promises = factionData.map((faction, index) => {
			return new Promise((resolve) => {
				const img = new Image();
				img.onload = function () {
					loadedImages[index] = img;
					resolve();
				};
				img.onerror = function () {
					resolve();
				};
				img.src = '/assets/rassen/' + faction.id + 'kn.png';
			});
		});

		Promise.all(promises).then(() => {
			startInitialAnimation();
		});
	}

	function startInitialAnimation() {
		const animationDuration = 2000;
		const startTime = Date.now();

		function animate() {
			const elapsed = Date.now() - startTime;
			animationProgress = Math.min(elapsed / animationDuration, 1);

			const easeOutCubic = 1 - Math.pow(1 - animationProgress, 3);

			drawChart(easeOutCubic);

			if (animationProgress < 1) {
				requestAnimationFrame(animate);
			} else {
				isAnimating = false;
				startScannerAnimation();
			}
		}

		animate();
	}

	function drawChart(progress = 1) {
		ctx.clearRect(0, 0, canvas.width, canvas.height);

		if (factionData.length === 0) return;

		currentAngle = -Math.PI / 2;
		slices.length = 0;

		factionData.forEach(function (faction, index) {
			const fullSliceAngle = (faction.percentage / 100) * 2 * Math.PI;
			const sliceAngle = fullSliceAngle * progress;
			const isHovered = index === hoveredSliceIndex && !isAnimating;

			slices.push({
				startAngle: currentAngle,
				endAngle: currentAngle + fullSliceAngle,
				faction: faction,
				index: index
			});

			const drawRadius = isHovered ? radius + 10 : radius;

			ctx.beginPath();
			ctx.moveTo(centerX, centerY);
			ctx.arc(centerX, centerY, drawRadius, currentAngle, currentAngle + sliceAngle);
			ctx.closePath();

			let fillColor = faction.color || '#666666';
			if (isHovered) {
				fillColor = lightenColor(fillColor, 20);
			}

			ctx.shadowColor = fillColor;
			ctx.shadowBlur = isHovered ? 20 : 10;
			ctx.fillStyle = fillColor;
			ctx.fill();

			ctx.shadowBlur = 0;
			ctx.strokeStyle = isHovered ? '#00ffff' : '#000000';
			ctx.lineWidth = isHovered ? 3 : 2;
			ctx.stroke();

			if (faction.percentage > 8 && loadedImages[index] && progress > 0.8) {
				const logoSize = 28;
				const logoRadius = drawRadius * 0.65;
				const logoAngle = currentAngle + (fullSliceAngle * progress) / 2;
				const logoX = centerX + Math.cos(logoAngle) * logoRadius - logoSize / 2;
				const logoY = centerY + Math.sin(logoAngle) * logoRadius - logoSize / 2;

				ctx.shadowColor = '#ffffff';
				ctx.shadowBlur = 5;
				ctx.drawImage(loadedImages[index], logoX, logoY, logoSize, logoSize);
				ctx.shadowBlur = 0;
			}

			currentAngle += fullSliceAngle;
		});

		if (progress > 0.9) {
			drawGridOverlay();
		}
	}

	function drawGridOverlay() {
		ctx.strokeStyle = 'rgba(113, 230, 230, 0.56)';
		ctx.lineWidth = 1;
		ctx.setLineDash([5, 5]);

		for (let r = 40; r < radius; r += 40) {
			ctx.beginPath();
			ctx.arc(centerX, centerY, r, 0, 2 * Math.PI);
			ctx.stroke();
		}

		for (let i = 0; i < 8; i++) {
			const angle = (i * Math.PI) / 4;
			ctx.beginPath();
			ctx.moveTo(centerX, centerY);
			ctx.lineTo(
				centerX + Math.cos(angle) * radius,
				centerY + Math.sin(angle) * radius
			);
			ctx.stroke();
		}

		ctx.setLineDash([]);
	}

	const tooltip = document.getElementById('chartTooltip');

	canvas.addEventListener('mousemove', function (e) {
		if (isAnimating || factionData.length === 0) return;

		const rect = canvas.getBoundingClientRect();
		const x = e.clientX - rect.left - 20;
		const y = e.clientY - rect.top - 20;

		const dx = x - centerX;
		const dy = y - centerY;
		const distance = Math.sqrt(dx * dx + dy * dy);

		if (distance <= radius + 10) {
			let angle = Math.atan2(dy, dx);
			if (angle < -Math.PI / 2) angle += 2 * Math.PI;

			const hoveredSlice = slices.find(slice =>
				angle >= slice.startAngle && angle <= slice.endAngle
			);

			if (hoveredSlice) {
				const newHoveredIndex = hoveredSlice.index;

				if (newHoveredIndex !== hoveredSliceIndex) {
					hoveredSliceIndex = newHoveredIndex;
					drawChart();
				}

				tooltip.innerHTML = `
                    <div style="text-align: center;">
                        <img src="/assets/rassen/${hoveredSlice.faction.id}kn.png" style="width: 40px; height: 40px; margin-bottom: 5px; filter: drop-shadow(0 0 5px #00ffff);"><br>
                        <strong style="color: #00ffff; text-shadow: 0 0 5px #00ffff;">${hoveredSlice.faction.name}</strong><br>
                        <span style="color: #ffff00;">Platz ${hoveredSlice.faction.rank}</span><br>
                        <span style="color: #00ff00;">${hoveredSlice.faction.percentage.toFixed(1)}% Prestige Anteil</span><br>
                        <span style="color: #ff6600;">${hoveredSlice.faction.ships} Schiffe zerstört</span>
                    </div>
                `;
				tooltip.style.left = (e.clientX + 10) + 'px';
				tooltip.style.top = (e.clientY - 10) + 'px';
				tooltip.style.display = 'block';
				canvas.style.cursor = 'pointer';
				return;
			}
		}

		if (hoveredSliceIndex !== -1) {
			hoveredSliceIndex = -1;
			drawChart();
		}

		tooltip.style.display = 'none';
		canvas.style.cursor = 'default';
	});

	canvas.addEventListener('mouseleave', function () {
		if (hoveredSliceIndex !== -1) {
			hoveredSliceIndex = -1;
			drawChart();
		}
		tooltip.style.display = 'none';
		canvas.style.cursor = 'default';
	});

	preloadImages();
	initializeSpacyProgressBar(maxPrestige, actualPrestige);
}

function initializeSpacyProgressBar(maxPrestige, actualPrestige) {
	const progressBar = document.getElementById('progressBar');
	const progressText = document.getElementById('progressText');
	const progressContainer = document.getElementById('progressContainer');

	if (!progressBar || !progressText || !progressContainer) return;

	const percentage = ((maxPrestige - actualPrestige) / maxPrestige) * 100;

	let gradient, glowColor;
	if (percentage >= 75) {
		gradient = 'linear-gradient(45deg, #00ff41, #00d4aa, #00ff41, #00d4aa)';
		glowColor = '#00ff41';
	} else if (percentage >= 50) {
		gradient = 'linear-gradient(45deg, #ffff00, #ffa500, #ffff00, #ffa500)';
		glowColor = '#ffff00';
	} else if (percentage >= 25) {
		gradient = 'linear-gradient(45deg, #ff8c00, #ff4500, #ff8c00, #ff4500)';
		glowColor = '#ff8c00';
	} else {
		gradient = 'linear-gradient(45deg, #ff0040, #8b0000, #ff0040, #8b0000)';
		glowColor = '#ff0040';
	}

	progressBar.style.background = gradient;
	progressBar.style.backgroundSize = '200% 100%';
	progressBar.style.animation = 'progressShimmer 2s linear 1';
	progressBar.style.position = 'relative';
	progressBar.style.overflow = 'hidden';

	progressBar.innerHTML = `
        <div style="
            position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: progressScan 3s ease-in-out infinite;
        "></div>
        <div style="
            position: absolute; top: 2px; left: 2px; right: 2px; bottom: 2px;
            border: 1px solid rgba(0,255,255,0.3);
            background: linear-gradient(180deg, rgba(255,255,255,0.1) 0%, transparent 50%, rgba(0,0,0,0.2) 100%);
        "></div>
    `;

	setTimeout(function () {
		progressBar.style.width = percentage + '%';
		progressBar.style.boxShadow = `
            0 0 10px ${glowColor},
            inset 0 0 10px rgba(255,255,255,0.1),
            inset 0 2px 0 rgba(255,255,255,0.3),
            inset 0 -2px 0 rgba(0,0,0,0.3)
        `;
		progressText.textContent = percentage.toFixed(1) + '%';
		progressText.style.textShadow = `0 0 10px ${glowColor}`;
		progressText.style.color = '#ffffff';
	}, 1000);

	progressContainer.addEventListener('mouseenter', function () {
		progressBar.style.transform = 'scaleY(1.2)';
		progressBar.style.transition = 'transform 0.3s ease, width 2s ease-in-out';
		progressBar.style.boxShadow = `
            0 0 20px ${glowColor},
            0 0 40px ${glowColor},
            inset 0 0 15px rgba(255,255,255,0.2),
            inset 0 3px 0 rgba(255,255,255,0.4),
            inset 0 -3px 0 rgba(0,0,0,0.4)
        `;
		progressBar.style.filter = 'brightness(1.2)';
	});

	progressContainer.addEventListener('mouseleave', function () {
		progressBar.style.transform = 'scaleY(1)';
		progressBar.style.boxShadow = `
            0 0 10px ${glowColor},
            inset 0 0 10px rgba(255,255,255,0.1),
            inset 0 2px 0 rgba(255,255,255,0.3),
            inset 0 -2px 0 rgba(0,0,0,0.3)
        `;
		progressBar.style.filter = 'brightness(1)';
	});
}
