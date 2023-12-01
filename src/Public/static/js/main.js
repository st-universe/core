var faction = 0;
var varcheck = 0;
function toggleFaction(fid) {
	if (faction != 0) {
		Effect.SlideUp('facinfo_' + faction);
	}
	Effect.SlideDown('facinfo_' + fid);
	faction = fid;
}
function selectFaction(faction_id) {
	$('ch_faction').innerHTML = $('fachead_' + faction_id).innerHTML;
	$('factionid').value = faction_id;
	$('factionerror').hide();
}
function checkEmail(el, value) {
	if (value.length < 8) {
		$('emailok').hide();
		$('emailerror').show();
		$('emaildup').hide();
		$('emailblk').hide();
		return false;
	}
	var varcheck = regVarCheck('email', value);
	if (varcheck == 0) {
		$('emailok').hide();
		$('emailerror').show();
		$('emaildup').hide();
		$('emailblk').hide();
		return false;
	}
	if (varcheck == 2) {
		$('emailok').hide();
		$('emailerror').hide();
		$('emaildup').show();
		$('emailblk').hide();
		return false;
	}
	if (varcheck == 5) {
		$('emailok').hide();
		$('emailerror').hide();
		$('emaildup').hide();
		$('emailblk').show();
		return false;
	}
	$('emailerror').hide();
	$('emaildup').hide();
	$('emailblk').hide();
	$('emailok').show();
	return true;
}
function checkMobile(el, number) {
	var countryCode = document.getElementById('countryCodeSelect').value;
	number = number.replace(/\s+/g, '');

	var prefixesToRemove = ["+49", "+43", "+41"];
	for (var i = 0; i < prefixesToRemove.length; i++) {
		if (number.startsWith(prefixesToRemove[i])) {
			number = number.substring(prefixesToRemove[i].length);
			break;
		}
	}
	number = number.replace(/^0+/, '');

	value = countryCode + number;
	if (value.length < 10) {
		$('mobileok').hide();
		$('mobileerror').show();
		$('mobiledup').hide();
		$('mobileucp').hide();
		$('mobileupd').hide();
		$('mobileblk').hide();
		return false;
	}
	var varcheck = regVarCheck('mobile', value.replace('+', '00'));
	if (varcheck == 0) {
		$('mobileok').hide();
		$('mobileerror').show();
		$('mobiledup').hide();
		$('mobileucp').hide();
		$('mobileupd').hide();
		$('mobileblk').hide();
		return false;
	}
	if (varcheck == 2) {
		$('mobileok').hide();
		$('mobileerror').hide();
		$('mobiledup').show();
		$('mobileucp').hide();
		$('mobileupd').hide();
		$('mobileblk').hide();
		return false;
	}
	if (varcheck == 3) {
		$('mobileok').hide();
		$('mobileerror').hide();
		$('mobiledup').hide();
		$('mobileucp').show();
		$('mobileupd').hide();
		$('mobileblk').hide();
		return false;
	}
	if (varcheck == 4) {
		$('mobileok').hide();
		$('mobileerror').hide();
		$('mobiledup').hide();
		$('mobileucp').hide();
		$('mobileupd').show();
		$('mobileblk').hide();
		return false;
	}
	if (varcheck == 5) {
		$('mobileok').hide();
		$('mobileerror').hide();
		$('mobiledup').hide();
		$('mobileucp').hide();
		$('mobileupd').hide();
		$('mobileblk').show();
		return false;
	}
	$('mobileerror').hide();
	$('mobiledup').hide();
	$('mobileucp').hide();
	$('mobileupd').hide();
	$('mobileblk').hide();
	$('mobileok').show();
	return true;
}
function checkLogin(el, value) {
	if (value.length < 6) {
		$('loginok').hide();
		$('loginerror').show();
		$('logindup').hide();
		return false;
	}
	var varcheck = regVarCheck('loginname', value);
	if (varcheck == 0) {
		$('loginok').hide();
		$('loginerror').show();
		$('logindup').hide();
		return false;
	}
	if (varcheck == 2) {
		$('loginok').hide();
		$('loginerror').hide();
		$('logindup').show();
		return false;
	}
	$('loginerror').hide();
	$('logindup').hide();
	$('loginok').show();
	return true;
}
function checkToken(el, value) {
	var varcheck = regVarCheck('token', value);
	if (varcheck == 0) {
		$('tokenok').hide();
		$('tokenerror').show();
		return false;
	}
	$('tokenerror').hide();
	$('tokenok').show();
	return true;
}
function checkSubmit(doCheckMobile = false) {
	if (faction < 1 || faction > 6) {
		$('factionerror').show();
		return;
	}
	$('factionerror').hide();
	if (!checkLogin('dummy', $('loginname').value)) {
		return;
	}
	if (!checkEmail('dummy', $('email').value)) {
		return;
	}
	if (doCheckMobile && !checkMobile('dummy', $('mobile').value)) {
		return;
	}
	if (!checkToken('dummy', $('token').value)) {
		return;
	}
	if (!$('asb').checked) {
		$('asberror').show();
		return;
	}
	$('registerform').submit();
}
function regVarCheck(vari, value) {
	varcheck = 0;
	var url = 'index.php?B_CHECK_REGVAR=1&var=' + vari + '&value=' + value;
	new Ajax.Request(url, {
		asynchronous: false,
		method: 'get',
		onSuccess: function (transport) {
			if (transport.responseText.match(/OK/)) {
				varcheck = 1;
			} else {
				varcheck = 0;
			}
			if (transport.responseText.match(/DUP/)) {
				varcheck = 2;
			}
			if (transport.responseText.match(/UCP/)) {
				varcheck = 3;
			}
			if (transport.responseText.match(/UPD/)) {
				varcheck = 4;
			}
			if (transport.responseText.match(/BLK/)) {
				varcheck = 5;
			}
		}
	});
	return varcheck;
}
function updateMobileValue() {
	var countryCode = document.getElementById('countryCodeSelect').value;
	var mobileNumber = document.getElementById('mobile').value;

	mobileNumber = mobileNumber.replace(/\s+/g, '');

	var prefixesToRemove = ["+49", "+43", "+41"];
	for (var i = 0; i < prefixesToRemove.length; i++) {
		if (mobileNumber.startsWith(prefixesToRemove[i])) {
			mobileNumber = mobileNumber.substring(prefixesToRemove[i].length);
			break;
		}
	}
	mobileNumber = mobileNumber.replace(/^0+/, '');

	var combinedValue = countryCode + mobileNumber;
	document.getElementById('combinedMobileValue').textContent = combinedValue;
}

function updateBannerImage() {
	var currentDate = new Date();
	var year = currentDate.getFullYear();
	var startHoliday = new Date(year, 11, 1);
	var endHoliday = new Date(year + 1, 0, 6);
	if ((currentDate >= startHoliday && currentDate <= new Date(year, 11, 31)) ||
		(currentDate >= new Date(year + 1, 0, 1) && currentDate <= endHoliday)) {
		document.getElementById('bannerImg').src = "/assets/main/banner_x_mas.png";
	}
}

