function showMemberRumpInfo(obj, userid, rumpid) {
	var pos = findObject(obj);
	updatePopup('alliance.php?SHOW_MEMBER_RUMP_INFO=1&userid=' + userid + '&rumpid=' + rumpid,
		700, pos[0] - 400, pos[1]
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
			<input type="text" class="job-title-input" value="" placeholder="Rollenbezeichnung" style="width: 400px;" />
			<button type="button" class="button job-delete-btn" onclick="deleteAllianceJob('${newJobId}')">Löschen</button>
		</div>
		<div class="job-permissions">
			<label>
				<input type="checkbox" class="job-successor-check" />
				Vize
			</label>
			<label style="margin-left: 20px;">
				<input type="checkbox" class="job-diplomatic-check" />
				Diplomat
			</label>
		</div>
	`;

	container.appendChild(jobDiv);
	initializeDragAndDrop(jobDiv);
	initializeCheckboxLogic(jobDiv);
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

function saveAllianceJobs() {
	const container = document.getElementById('alliance-jobs-container');
	const jobItems = container.querySelectorAll('.alliance-job-item');

	const jobs = [];
	let hasError = false;

	jobItems.forEach((item, index) => {
		const jobId = item.getAttribute('data-job-id');
		const title = item.querySelector('.job-title-input').value.trim();
		const isSuccessor = item.querySelector('.job-successor-check')?.checked || false;
		const isDiplomatic = item.querySelector('.job-diplomatic-check')?.checked || false;

		if (title.length < 3) {
			alert('Alle Rollenbezeichnungen müssen mindestens 3 Zeichen lang sein!');
			hasError = true;
			return;
		}

		jobs.push({
			id: jobId,
			title: title,
			sort: index + 1,
			is_successor: isSuccessor,
			is_diplomatic: isDiplomatic
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

function initializeCheckboxLogic(item) {
	const successorCheck = item.querySelector('.job-successor-check');
	const diplomaticCheck = item.querySelector('.job-diplomatic-check');

	if (successorCheck && diplomaticCheck) {
		successorCheck.addEventListener('change', function () {
			if (this.checked) {
				diplomaticCheck.checked = false;
			}
		});

		diplomaticCheck.addEventListener('change', function () {
			if (this.checked) {
				successorCheck.checked = false;
			}
		});
	}
}

document.addEventListener('DOMContentLoaded', function () {
	const container = document.getElementById('alliance-jobs-container');
	if (container) {
		const jobItems = container.querySelectorAll('.alliance-job-item');
		jobItems.forEach(item => {
			initializeDragAndDrop(item);
			initializeCheckboxLogic(item);
		});
	}
});
