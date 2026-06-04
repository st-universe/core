function showUserLock(element, userid) {
	updatePopupAtElement(element, '/admin/?SHOW_USER_LOCK=1&id=' + userid);
}

function setSpacecraftChargeMax() {
	var valueInput = document.getElementById('spacecraft_charge_value');

	if (valueInput) {
		valueInput.value = 'max';
		valueInput.focus();
	}
}

function confirmSpacecraftChargeSubmit(form) {
	var confirmedInput = document.getElementById('spacecraft_charge_confirmed');
	if (confirmedInput && confirmedInput.value === '1') {
		return true;
	}

	var userIds = form.elements.spacecraft_charge_user_ids.value.strip();
	var spacecraftIds = form.elements.spacecraft_charge_spacecraft_ids.value.strip();
	var value = form.elements.spacecraft_charge_value.value.strip();
	var shipsChecked = form.elements.spacecraft_charge_ships.checked;
	var stationsChecked = form.elements.spacecraft_charge_stations.checked;

	if (userIds === '' && spacecraftIds === '' && value !== '' && (shipsChecked || stationsChecked)) {
		var selectedOption = form.elements.spacecraft_charge_target.options[form.elements.spacecraft_charge_target.selectedIndex].text;
		var scope = shipsChecked && stationsChecked ? 'Schiffe und Stationen' : (shipsChecked ? 'Schiffe' : 'Stationen');
		var confirmText = document.getElementById('spacecraft_charge_confirm_text');
		var confirmBox = document.getElementById('spacecraft_charge_confirm');

		if (confirmText) {
			confirmText.textContent = 'Möchtest du wirklich bei allen Spielern die ausgewählten ' + scope + ' für ' + selectedOption + ' auf ' + value + ' auffüllen?';
		}
		if (confirmBox) {
			confirmBox.style.display = '';
		}

		return false;
	}

	return true;
}

function confirmSpacecraftChargeBulk() {
	var form = document.getElementById('spacecraftChargeForm');
	var confirmedInput = document.getElementById('spacecraft_charge_confirmed');

	if (confirmedInput) {
		confirmedInput.value = '1';
	}
	if (form) {
		form.submit();
	}
}

function cancelSpacecraftChargeBulk() {
	var confirmedInput = document.getElementById('spacecraft_charge_confirmed');
	var confirmBox = document.getElementById('spacecraft_charge_confirm');

	if (confirmedInput) {
		confirmedInput.value = '0';
	}
	if (confirmBox) {
		confirmBox.style.display = 'none';
	}
}
