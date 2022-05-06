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
		return false;
	}
	var varcheck = regVarCheck('email', value.replace('+', '%2B'));
	if (varcheck == 0) {
		$('emailok').hide();
		$('emailerror').show();
		$('emaildup').hide();
		return false;
	}
	if (varcheck == 2) {
		$('emailok').hide();
		$('emailerror').hide();
		$('emaildup').show();
		return false;
	}
	$('emailerror').hide();
	$('emaildup').hide();
	$('emailok').show();
	return true;
}
function checkMobile(el, value) {
	if (value.length < 10) {
		$('mobileok').hide();
		$('mobileerror').show();
		$('mobiledup').hide();
		$('mobileucp').hide();
		$('mobileupd').hide();
		return false;
	}
	var varcheck = regVarCheck('mobile', value);
	if (varcheck == 0) {
		$('mobileok').hide();
		$('mobileerror').show();
		$('mobiledup').hide();
		$('mobileucp').hide();
		$('mobileupd').hide();
		return false;
	}
	if (varcheck == 2) {
		$('mobileok').hide();
		$('mobileerror').hide();
		$('mobiledup').show();
		$('mobileucp').hide();
		$('mobileupd').hide();
		return false;
	}
	if (varcheck == 3) {
		$('mobileok').hide();
		$('mobileerror').hide();
		$('mobiledup').hide();
		$('mobileucp').show();
		$('mobileupd').hide();
		return false;
	}
	if (varcheck == 4) {
		$('mobileok').hide();
		$('mobileerror').hide();
		$('mobiledup').hide();
		$('mobileucp').hide();
		$('mobileupd').show();
		return false;
	}
	$('mobileerror').hide();
	$('mobiledup').hide();
	$('mobileucp').hide();
	$('mobileupd').hide();
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
		}
	});
	return varcheck;

}
