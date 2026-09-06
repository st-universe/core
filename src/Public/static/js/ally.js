function showMemberRumpInfo(obj, userid, rumpid) {
	var pos = findObject(obj);
	updatePopup('alliance.php?SHOW_MEMBER_RUMP_INFO=1&userid=' + userid + '&rumpid=' + rumpid,
		700, pos[0] - 200, pos[1] + 30, false
	);
}

function showMemberColonyInfo(obj, colonyid) {
	var pos = findObject(obj);
	updatePopup('alliance.php?SHOW_MEMBER_COLONY_INFO=1&colonyid=' + colonyid,
		850, pos[0] - 200, pos[1] + 50, false
	);
}

function showRelationText(relationid) {
	var viewportWidth = window.innerWidth || document.documentElement.clientWidth;
	var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
	var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
	var scrollTop = window.pageYOffset || document.documentElement.scrollTop;

	var posX = scrollLeft + (viewportWidth * 0.2);
	var posY = scrollTop + (viewportHeight * 0.2);

	updatePopup('alliance.php?SHOW_RELATION_TEXT=1&relationid=' + relationid, 650, posX, posY, false);
}

function editRelationText(relationid) {
	var viewportWidth = window.innerWidth || document.documentElement.clientWidth;
	var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
	var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
	var scrollTop = window.pageYOffset || document.documentElement.scrollTop;

	var posX = scrollLeft + (viewportWidth * 0.2);
	var posY = scrollTop + (viewportHeight * 0.2);

	updatePopup('alliance.php?EDIT_RELATION_TEXT=1&relationid=' + relationid, 650, posX, posY, false);
}

function initializeRelationTargetSelection() {
	const targetType = document.getElementById('relation-target-type');
	if (targetType !== null && targetType.dataset.initialized !== '1') {
		const allianceTarget = document.getElementById('alliance-relation-target');
		const allianceTargetId = document.getElementById('alliance-relation-target-id');
		const userTarget = document.getElementById('user-relation-target');
		const userTargetId = document.getElementById('user-relation-target-id');
		const userAction = document.getElementById('relation-user-action');
		const createButton = document.getElementById('relation-create-button');

		const updateRelationTarget = () => {
			const isAlliance = targetType.value === 'alliance';
			allianceTarget.style.display = isAlliance ? '' : 'none';
			allianceTargetId.disabled = !isAlliance;
			userTarget.style.display = isAlliance ? 'none' : '';
			userTargetId.disabled = isAlliance;
			userAction.disabled = isAlliance;
			createButton.name = isAlliance ? 'B_NEW_RELATION' : 'B_MANAGE_USER_RELATION';
		};

		targetType.addEventListener('change', updateRelationTarget);
		targetType.dataset.initialized = '1';
		updateRelationTarget();
	}

	const contactTargetType = document.getElementById('contact-relation-target-type');
	if (contactTargetType !== null && contactTargetType.dataset.initialized !== '1') {
		const userTarget = document.getElementById('contact-user-relation-target');
		const userTargetId = document.getElementById('contact-user-relation-target-id');
		const allianceTarget = document.getElementById('contact-alliance-relation-target');
		const allianceTargetId = document.getElementById('contact-alliance-relation-target-id');

		const updateContactRelationTarget = () => {
			const isUser = contactTargetType.value === '1';
			userTarget.style.display = isUser ? '' : 'none';
			userTargetId.disabled = !isUser;
			allianceTarget.style.display = isUser ? 'none' : '';
			allianceTargetId.disabled = isUser;
		};

		contactTargetType.addEventListener('change', updateContactRelationTarget);
		contactTargetType.dataset.initialized = '1';
		updateContactRelationTarget();
	}
}

function initializeRelationPermissionSelection() {
	document.querySelectorAll('[data-relation-permission-selector]').forEach((relationType) => {
		if (relationType.dataset.initialized === '1') {
			return;
		}

		const permissionField = document.getElementById(relationType.dataset.relationPermissionSelector);
		const permissionContainer = document.querySelector(
			`[data-relation-permissions="${relationType.dataset.relationPermissionSelector}"]`
		);
		const form = relationType.closest('form');
		if (permissionField === null || permissionContainer === null || form === null) {
			return;
		}

		const updatePermissions = () => {
			const relationTypeId = relationType.value;
			let hasVisiblePermission = false;
			permissionContainer.querySelectorAll('[data-relation-permission-types]').forEach((label) => {
				const isAvailable = label.dataset.relationPermissionTypes.split(',').includes(relationTypeId);
				label.style.display = isAvailable ? '' : 'none';
				const checkbox = label.querySelector('[data-relation-permission]');
				checkbox.disabled = !isAvailable;
				if (!isAvailable) {
					checkbox.checked = false;
				}
				hasVisiblePermission = hasVisiblePermission || isAvailable;
			});
			permissionContainer.style.display = hasVisiblePermission ? '' : 'none';
		};

		form.addEventListener('submit', () => {
			let permissions = 0;
			permissionContainer.querySelectorAll('[data-relation-permission]:checked').forEach((checkbox) => {
				permissions |= Number(checkbox.value);
			});
			permissionField.value = permissions.toString();
		});
		relationType.addEventListener('change', updatePermissions);
		relationType.dataset.initialized = '1';
		updatePermissions();
	});

	document.querySelectorAll('[data-static-relation-permission-form]').forEach((form) => {
		if (form.dataset.initialized === '1') {
			return;
		}

		const permissionField = document.getElementById(form.dataset.staticRelationPermissionForm);
		if (permissionField === null) {
			return;
		}

		form.addEventListener('submit', () => {
			let permissions = 0;
			form.querySelectorAll('[data-relation-permission]:checked').forEach((checkbox) => {
				permissions |= Number(checkbox.value);
			});
			permissionField.value = permissions.toString();
		});
		form.dataset.initialized = '1';
	});
}

document.addEventListener('DOMContentLoaded', () => {
	initializeRelationTargetSelection();
	initializeRelationPermissionSelection();
});

let newJobCounter = 0;

function addNewAllianceJob() {
	const container = document.getElementById('alliance-jobs-container');
	const newJobId = 'new_' + (++newJobCounter);

	const jobDiv = document.createElement('div');
	jobDiv.className = 'alliance-job-item';
	jobDiv.setAttribute('data-job-id', newJobId);
	jobDiv.setAttribute('data-sort', '999');
	jobDiv.setAttribute('draggable', 'true');

	jobDiv.innerHTML = `
		<div class="job-header">
			<span class="drag-handle">&#9776;</span>
			<input type="text" class="job-title-input" value="" placeholder="Rollenbezeichnung" style="width: 60%;" />
			<button type="button" class="button job-toggle-permissions" onclick="toggleJobPermissions('${newJobId}')">▼</button>
			<button type="button" class="button job-delete-btn" onclick="deleteAllianceJob('${newJobId}')">Löschen</button>
		</div>
		<div class="job-permissions-container" id="permissions-${newJobId}" style="display: none;">
			<div class="job-permissions-section">
				<div class="job-permissions-header">Diplomatische Rechte</div>
				<div class="job-permissions-list">
					<label>
						<input type="checkbox" class="job-permission-check parent-permission" data-permission="3" data-parent="true" />
						<strong>Diplomat</strong>
					</label>
					<label style="margin-left: 20px;">
						<input type="checkbox" class="job-permission-check child-permission diplomatic-child" data-permission="4" data-parent-permission="3" />
						Kann Abkommen erstellen
					</label>
					<label style="margin-left: 20px;">
						<input type="checkbox" class="job-permission-check child-permission diplomatic-child" data-permission="5" data-parent-permission="3" />
						Kann Vertragswerk ändern
					</label>
				</div>
			</div>
			<div class="job-permissions-section">
				<div class="job-permissions-header">Verwaltungsrechte</div>
				<div class="job-permissions-list">
					<label>
						<input type="checkbox" class="job-permission-check parent-permission" data-permission="2" data-parent="true" />
						<strong>Vize</strong>
					</label>
					<label style="margin-left: 20px;">
						<input type="checkbox" class="job-permission-check child-permission management-child" data-permission="6" data-parent-permission="2" />
						Gehört zur Allianzleitung
					</label>
					<label style="margin-left: 20px;">
						<input type="checkbox" class="job-permission-check child-permission management-child" data-permission="7" data-parent-permission="2" />
						Kann Allianz editieren
					</label>
					<label style="margin-left: 20px;">
						<input type="checkbox" class="job-permission-check child-permission management-child" data-permission="8" data-parent-permission="2" />
						Kann Bewerbungen bearbeiten
					</label>
					<label style="margin-left: 20px;">
						<input type="checkbox" class="job-permission-check child-permission management-child" data-permission="9" data-parent-permission="2" />
						Kann Jobs verwalten
					</label>
					<label style="margin-left: 20px;">
						<input type="checkbox" class="job-permission-check child-permission management-child" data-permission="10" data-parent-permission="2" />
						Kann Kolonien einsehen
					</label>
					<label style="margin-left: 20px;">
						<input type="checkbox" class="job-permission-check child-permission management-child" data-permission="11" data-parent-permission="2" />
						Kann Mitgliederdaten einsehen
					</label>
					<label style="margin-left: 20px;">
						<input type="checkbox" class="job-permission-check child-permission management-child" data-permission="12" data-parent-permission="2" />
						Kann Schiffe einsehen
					</label>
					<label style="margin-left: 20px;">
						<input type="checkbox" class="job-permission-check child-permission management-child" data-permission="13" data-parent-permission="2" />
						Kann Allianzdepots einsehen
					</label>
					<label style="margin-left: 20px;">
						<input type="checkbox" class="job-permission-check child-permission management-child" data-permission="14" data-parent-permission="2" />
						Kann Allianz-History einsehen
					</label>
				</div>
			</div>
		</div>
	`;

	container.appendChild(jobDiv);
	initializeDragAndDrop(jobDiv);
	initializePermissionLogic(jobDiv);
}

function deleteAllianceJob(jobId) {
	if (!confirm('Möchtest du diese Rolle wirklich löschen?')) {
		return;
	}

	const jobItem = document.querySelector(`[data-job-id="${jobId}"]`);
	if (jobItem) {
		jobItem.remove();
	}
}
function initializeDragAndDrop(item) {
	if (!item.draggable) return;

	item.addEventListener('dragstart', function (e) {
		if (this.querySelector('.drag-handle')) {
			e.dataTransfer.effectAllowed = 'move';
			e.dataTransfer.setData('text/html', this.innerHTML);

			const dragImage = this.cloneNode(true);
			dragImage.style.position = 'absolute';
			dragImage.style.top = '-9999px';
			dragImage.style.opacity = '1';
			dragImage.style.backgroundColor = 'var(--color-29)';
			dragImage.style.border = '2px solid #c2b942';
			document.body.appendChild(dragImage);
			e.dataTransfer.setDragImage(dragImage, 0, 0);

			setTimeout(() => {
				document.body.removeChild(dragImage);
			}, 0);

			this.classList.add('dragging');
		}
	});

	item.addEventListener('dragend', function (e) {
		this.classList.remove('dragging');
		document.querySelectorAll('.alliance-job-item').forEach(i => {
			i.classList.remove('over');
		});
	});

	item.addEventListener('dragover', function (e) {
		if (e.preventDefault) {
			e.preventDefault();
		}
		e.dataTransfer.dropEffect = 'move';

		const dragging = document.querySelector('.dragging');
		if (dragging && dragging !== this && this.querySelector('.drag-handle')) {
			const container = this.parentNode;
			const allItems = Array.from(container.querySelectorAll('.alliance-job-item'));
			const draggedIndex = allItems.indexOf(dragging);
			const targetIndex = allItems.indexOf(this);

			if (draggedIndex !== -1 && targetIndex !== -1 && draggedIndex !== targetIndex) {
				if (draggedIndex < targetIndex) {
					this.parentNode.insertBefore(dragging, this.nextSibling);
				} else {
					this.parentNode.insertBefore(dragging, this);
				}
			}
		}

		return false;
	});

	item.addEventListener('dragenter', function (e) {
		if (!this.querySelector('.drag-handle')) return;
		this.classList.add('over');
	});

	item.addEventListener('dragleave', function (e) {
		this.classList.remove('over');
	});

	item.addEventListener('drop', function (e) {
		if (e.stopPropagation) {
			e.stopPropagation();
		}
		this.classList.remove('over');
		return false;
	});
}

function saveAllianceJobs() {
	const container = document.getElementById('alliance-jobs-container');
	const jobItems = container.querySelectorAll('.alliance-job-item');

	const jobs = [];
	let hasError = false;

	jobItems.forEach((item, index) => {
		const jobId = item.getAttribute('data-job-id');
		const title = item.querySelector('.job-title-input').value.trim();

		if (title.length < 3) {
			alert('Alle Rollenbezeichnungen müssen mindestens 3 Zeichen lang sein!');
			hasError = true;
			return;
		}

		const permissions = [];
		const permissionCheckboxes = item.querySelectorAll('.job-permission-check:checked:not([disabled])');
		permissionCheckboxes.forEach(checkbox => {
			permissions.push(parseInt(checkbox.getAttribute('data-permission')));
		});

		jobs.push({
			id: jobId,
			title: title,
			sort: index + 1,
			permissions: permissions
		});
	});

	if (hasError) {
		return;
	}

	const form = document.createElement('form');
	form.method = 'POST';
	form.action = 'alliance.php';

	const sstrInput = document.createElement('input');
	sstrInput.type = 'hidden';
	sstrInput.name = 'sstr';
	sstrInput.value = document.querySelector('input[name="sstr"]').value;
	form.appendChild(sstrInput);

	const actionInput = document.createElement('input');
	actionInput.type = 'hidden';
	actionInput.name = 'B_SAVE_ALLIANCE_JOBS';
	actionInput.value = '1';
	form.appendChild(actionInput);

	const jobsInput = document.createElement('input');
	jobsInput.type = 'hidden';
	jobsInput.name = 'jobs';
	jobsInput.value = JSON.stringify(jobs);
	form.appendChild(jobsInput);

	document.body.appendChild(form);
	form.submit();
}

function toggleJobPermissions(jobId) {
	const permissionsContainer = document.getElementById('permissions-' + jobId);
	const toggleButton = document.querySelector(`[data-job-id="${jobId}"] .job-toggle-permissions`);

	if (permissionsContainer.style.display === 'none') {
		permissionsContainer.style.display = 'block';
		toggleButton.textContent = '▲';
	} else {
		permissionsContainer.style.display = 'none';
		toggleButton.textContent = '▼';
	}
}

function initializePermissionLogic(jobItem) {
	const parentCheckboxes = jobItem.querySelectorAll('.parent-permission');

	parentCheckboxes.forEach(parentCheckbox => {
		const parentPermission = parentCheckbox.getAttribute('data-permission');
		const childCheckboxes = jobItem.querySelectorAll(`.child-permission[data-parent-permission="${parentPermission}"]`);

		parentCheckbox.addEventListener('change', function () {
			childCheckboxes.forEach(child => {
				child.checked = this.checked;
				child.disabled = this.checked;
			});
		});

		childCheckboxes.forEach(childCheckbox => {
			childCheckbox.addEventListener('change', function () {
				const allChecked = Array.from(childCheckboxes).every(cb => cb.checked);

				if (allChecked && childCheckboxes.length > 0) {
					parentCheckbox.checked = true;
					childCheckboxes.forEach(cb => cb.disabled = true);
				} else {
					if (parentCheckbox.checked) {
						parentCheckbox.checked = false;
					}
				}
			});
		});

		if (parentCheckbox.checked) {
			childCheckboxes.forEach(child => {
				child.checked = true;
				child.disabled = true;
			});
		}
	});
}

document.addEventListener('DOMContentLoaded', function () {
	const container = document.getElementById('alliance-jobs-container');
	if (container) {
		const jobItems = container.querySelectorAll('.alliance-job-item');
		jobItems.forEach(item => {
			initializeDragAndDrop(item);
			initializePermissionLogic(item);
		});
	}
});
