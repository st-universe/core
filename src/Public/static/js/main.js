var faction = 0;
var varcheck = 0;

function toggleFaction(fid) {
  if (faction === fid) {
    // Falls dieselbe Fraktion erneut geklickt wird -> schließen und zurücksetzen
    $("facinfo_" + faction).classList.add('hidden');
    faction = 0;
  } else {
    if (faction !== 0) {
      $("facinfo_" + faction).classList.add('hidden');
    }
    $("facinfo_" + fid).classList.remove('hidden');
    faction = fid;
  }
}

function selectFaction(faction_id) {
  $("ch_faction").innerHTML = $("fachead_" + faction_id).innerHTML;
  $("factionid").value = faction_id;
  $("factionerror").hide();
}
function checkEmail(el, value) {
  if (value.length < 8) {
    $("emailok").hide();
    $("emailerror").show();
    $("emaildup").hide();
    $("emailblk").hide();
    return false;
  }
  var varcheck = regVarCheck("email", value);
  if (varcheck == 0) {
    $("emailok").hide();
    $("emailerror").show();
    $("emaildup").hide();
    $("emailblk").hide();
    return false;
  }
  if (varcheck == 2) {
    $("emailok").hide();
    $("emailerror").hide();
    $("emaildup").show();
    $("emailblk").hide();
    return false;
  }
  if (varcheck == 5) {
    $("emailok").hide();
    $("emailerror").hide();
    $("emaildup").hide();
    $("emailblk").show();
    return false;
  }
  $("emailerror").hide();
  $("emaildup").hide();
  $("emailblk").hide();
  $("emailok").show();
  return true;
}
function checkMobile(el, number) {
  var countryCode = document.getElementById("countryCodeSelect").value;
  number = number.replace(/\s+/g, "");

  var prefixesToRemove = ["+49", "+43", "+41"];
  for (var i = 0; i < prefixesToRemove.length; i++) {
    if (number.startsWith(prefixesToRemove[i])) {
      number = number.substring(prefixesToRemove[i].length);
      break;
    }
  }
  number = number.replace(/^0+/, "");

  value = countryCode + number;
  if (value.length < 10) {
    $("mobileok").hide();
    $("mobileerror").show();
    $("mobiledup").hide();
    $("mobileucp").hide();
    $("mobileupd").hide();
    $("mobileblk").hide();
    return false;
  }
  var varcheck = regVarCheck("mobile", value.replace("+", "00"));
  if (varcheck == 0) {
    $("mobileok").hide();
    $("mobileerror").show();
    $("mobiledup").hide();
    $("mobileucp").hide();
    $("mobileupd").hide();
    $("mobileblk").hide();
    return false;
  }
  if (varcheck == 2) {
    $("mobileok").hide();
    $("mobileerror").hide();
    $("mobiledup").show();
    $("mobileucp").hide();
    $("mobileupd").hide();
    $("mobileblk").hide();
    return false;
  }
  if (varcheck == 3) {
    $("mobileok").hide();
    $("mobileerror").hide();
    $("mobiledup").hide();
    $("mobileucp").show();
    $("mobileupd").hide();
    $("mobileblk").hide();
    return false;
  }
  if (varcheck == 4) {
    $("mobileok").hide();
    $("mobileerror").hide();
    $("mobiledup").hide();
    $("mobileucp").hide();
    $("mobileupd").show();
    $("mobileblk").hide();
    return false;
  }
  if (varcheck == 5) {
    $("mobileok").hide();
    $("mobileerror").hide();
    $("mobiledup").hide();
    $("mobileucp").hide();
    $("mobileupd").hide();
    $("mobileblk").show();
    return false;
  }
  $("mobileerror").hide();
  $("mobiledup").hide();
  $("mobileucp").hide();
  $("mobileupd").hide();
  $("mobileblk").hide();
  $("mobileok").show();
  return true;
}
function checkLogin(el, value) {
  if (value.length < 6) {
    $("loginok").hide();
    $("loginerror").show();
    $("logindup").hide();
    return false;
  }
  var varcheck = regVarCheck("loginname", value);
  if (varcheck == 0) {
    $("loginok").hide();
    $("loginerror").show();
    $("logindup").hide();
    return false;
  }
  if (varcheck == 2) {
    $("loginok").hide();
    $("loginerror").hide();
    $("logindup").show();
    return false;
  }
  $("loginerror").hide();
  $("logindup").hide();
  $("loginok").show();
  return true;
}
function checkToken(el, value) {
  var varcheck = regVarCheck("token", value);
  if (varcheck == 0) {
    $("tokenok").hide();
    $("tokenerror").show();
    return false;
  }
  $("tokenerror").hide();
  $("tokenok").show();
  return true;
}
function checkSubmit(doCheckMobile = false) {
  if (faction < 1 || faction > 6) {
    $("factionerror").show();
    return;
  }
  $("factionerror").hide();
  if (!checkLogin("dummy", $("loginname").value)) {
    return;
  }
  if (!checkEmail("dummy", $("email").value)) {
    return;
  }
  if (!checkPassword($("password").value)) {
    return;
  }
  if (!checkPasswordMatch()) {
    return;
  }
  if (doCheckMobile && !checkMobile("dummy", $("mobile").value)) {
    return;
  }
  if (!checkToken("dummy", $("token").value)) {
    return;
  }
  if (!$("asb").checked) {
    $("asberror").show();
    return;
  }
  $("registerform").submit();
}
function regVarCheck(vari, value) {
  varcheck = 0;
  var url = "index.php?B_CHECK_REGVAR=1&var=" + vari + "&value=" + value;
  new Ajax.Request(url, {
    asynchronous: false,
    method: "get",
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
    },
  });
  return varcheck;
}
function verifyVarCheck(vari, value) {
  varcheck = 0;
  var url = "maindesk.php?B_CHECK_REGVAR=1&var=" + vari + "&value=" + value;
  new Ajax.Request(url, {
    asynchronous: false,
    method: "get",
    onSuccess: function (transport) {
      var response = transport.responseText.trim();
      if (response.match(/OK/)) {
        varcheck = 1;
      } else {
        varcheck = 0;
      }
      if (response.match(/DUP/)) {
        varcheck = 2;
      }
      if (response.match(/UCP/)) {
        varcheck = 3;
      }
      if (response.match(/UPD/)) {
        varcheck = 4;
      }
      if (response.match(/BLK/)) {
        varcheck = 5;
      }
    },
    onFailure: function (transport) {
      console.log("Request failed:", transport.status, transport.statusText);
    }
  });
  return varcheck;
}


function updateMobileValue() {
  const countryCode = document.getElementById("countryCodeSelect").value;
  const mobileNumber = document.getElementById("mobile").value.trim();
  const combinedMobileValue = document.getElementById("combinedMobileValue");

  if (mobileNumber) {
    combinedMobileValue.textContent = `${countryCode} ${mobileNumber}`;
  } else {
    combinedMobileValue.textContent = "";
  }
}

function togglePasswordVisibility(inputId, toggleId) {
  const input = document.getElementById(inputId);
  const toggle = document.getElementById(toggleId);

  if (input.type === 'password') {
    input.type = 'text';
    toggle.textContent = '🔒';
  } else {
    input.type = 'password';
    toggle.textContent = '👁';
  }
}

function checkPassword(password) {
  const requirements = document.getElementById('passwordRequirements');

  if (password.length === 0) {
    requirements.style.display = 'none';
    document.getElementById('passwordok').style.display = 'none';
    document.getElementById('passworderror').style.display = 'none';
    return false;
  }

  requirements.style.display = 'block';

  const hasLength = password.length >= 6;
  const hasUppercase = /[A-Z]/.test(password);
  const hasLowercase = /[a-z]/.test(password);
  const hasSpecial = /[^A-Za-z0-9]/.test(password);

  updateRequirement('req-length', hasLength);
  updateRequirement('req-uppercase', hasUppercase);
  updateRequirement('req-lowercase', hasLowercase);
  updateRequirement('req-special', hasSpecial);

  const allValid = hasLength && hasUppercase && hasLowercase && hasSpecial;

  if (allValid) {
    document.getElementById('passwordok').style.display = 'inline';
    document.getElementById('passworderror').style.display = 'none';
  } else {
    document.getElementById('passwordok').style.display = 'none';
    document.getElementById('passworderror').style.display = 'inline';
  }

  checkPasswordMatch();
  return allValid;
}

function updateRequirement(reqId, isValid) {
  const req = document.getElementById(reqId);
  const icon = req.querySelector('.req-icon');

  if (isValid) {
    req.classList.add('valid');
    icon.textContent = '✔️';
  } else {
    req.classList.remove('valid');
    icon.textContent = '❌';
  }
}

function checkPasswordMatch() {
  const password = document.getElementById('password').value;
  const password2 = document.getElementById('password2').value;
  const matchDiv = document.getElementById('passwordMatch');
  const matchIcon = document.getElementById('match-icon');

  if (password2.length === 0) {
    matchDiv.style.display = 'none';
    document.getElementById('password2ok').style.display = 'none';
    document.getElementById('password2error').style.display = 'none';
    return false;
  }

  matchDiv.style.display = 'block';

  const passwordsMatch = password === password2;

  if (passwordsMatch) {
    matchDiv.classList.add('valid');
    matchIcon.textContent = '✔️';
    document.getElementById('password2ok').style.display = 'inline';
    document.getElementById('password2error').style.display = 'none';
  } else {
    matchDiv.classList.remove('valid');
    matchIcon.textContent = '❌';
    document.getElementById('password2ok').style.display = 'none';
    document.getElementById('password2error').style.display = 'inline';
  }

  return passwordsMatch;
}


document.addEventListener("DOMContentLoaded", function () {
  var bannerImg = document.getElementById("bannerImg");
  var currentDate = new Date();
  var year = currentDate.getFullYear();
  var startHoliday = new Date(year, 11, 1);
  var endHoliday = new Date(year + 1, 0, 6);

  if (
    (currentDate >= startHoliday && currentDate <= new Date(year, 11, 31)) ||
    (currentDate >= new Date(year + 1, 0, 1) && currentDate <= endHoliday)
  ) {
    bannerImg.src = "/assets/main/banner_x_mas.PNG";
  } else {
    bannerImg.src = "/assets/main/banner.PNG";
  }
});

const debouncedCheckMobile = debounce(checkMobile, 1000);

function debounce(func, wait) {
  let timeout;
  return function (...args) {
    const context = this;
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(context, args), wait);
  };
}

function toggleEmailHelp() {
  const content = document.getElementById('emailHelpContent');
  const header = document.querySelector('.email-help-header');

  if (content.classList.contains('show')) {
    content.classList.remove('show');
    header.innerHTML = 'Keine E-Mail erhalten? <span style="float: right;">▼</span>';
  } else {
    content.classList.add('show');
    header.innerHTML = 'Keine E-Mail erhalten? <span style="float: right;">▲</span>';
  }
}


function checkEmailUpdate(inputElement) {
  const email = inputElement.value.trim();
  const originalEmail = document.getElementById('originalEmail').value;
  const statusDiv = document.getElementById('emailUpdateStatus');
  const button = document.getElementById('emailUpdateButton');

  if (email === '') {
    statusDiv.style.display = 'none';
    button.value = 'E-Mail erneut versenden';
    return;
  }

  if (email === originalEmail) {
    statusDiv.className = 'email-status valid';
    statusDiv.textContent = '✔️ E-Mail-Adresse stimmt überein';
    statusDiv.style.display = 'block';
    button.value = 'E-Mail erneut versenden';
    return;
  }

  if (email.length < 8) {
    statusDiv.className = 'email-status invalid';
    statusDiv.textContent = '❌ E-Mail-Adresse zu kurz (mindestens 8 Zeichen)';
    statusDiv.style.display = 'block';
    button.value = 'E-Mail erneut versenden';
    return;
  }

  var varcheck = verifyVarCheck("email", email);
  if (varcheck == 0) {
    statusDiv.className = 'email-status invalid';
    statusDiv.textContent = '❌ Ungültige E-Mail-Adresse';
    statusDiv.style.display = 'block';
    button.value = 'E-Mail erneut versenden';
  } else if (varcheck == 2) {
    statusDiv.className = 'email-status invalid';
    statusDiv.textContent = '❌ Diese E-Mail-Adresse ist bereits registriert';
    statusDiv.style.display = 'block';
    button.value = 'E-Mail erneut versenden';
  } else if (varcheck == 5) {
    statusDiv.className = 'email-status invalid';
    statusDiv.textContent = '❌ Diese E-Mail-Adresse ist blockiert';
    statusDiv.style.display = 'block';
    button.value = 'E-Mail erneut versenden';
  } else {
    statusDiv.className = 'email-status valid';
    statusDiv.textContent = '✔️ E-Mail-Adresse verfügbar';
    statusDiv.style.display = 'block';
    button.value = 'E-Mail-Adresse aktualisieren und neu versenden';
  }
}

function maskEmail(email) {
  if (!email || email.indexOf('@') === -1) {
    return '';
  }

  const parts = email.split('@');
  const localPart = parts[0];
  const domain = parts[1];

  if (localPart.length <= 2) {
    return localPart[0] + '*@' + domain;
  }

  const maskedLocal = localPart[0] + '*'.repeat(localPart.length - 2) + localPart[localPart.length - 1];
  return maskedLocal + '@' + domain;
}


document.querySelectorAll('.btn-header-link').forEach((button) => {
  button.addEventListener('click', function (e) {
    e.preventDefault();

    const targetSelector = button.getAttribute('data-target');
    const target = document.querySelector(targetSelector);

    if (target) {
      if (target.classList.contains('show')) {
        target.classList.remove('show');
        button.classList.add('collapsed');
        button.setAttribute('aria-expanded', 'false');
      } else {
        const parentSelector = target.getAttribute('data-parent');
        if (parentSelector) {
          document.querySelectorAll(`${parentSelector} .collapse.show`).forEach((openCollapse) => {
            openCollapse.classList.remove('show');
            const relatedButton = document.querySelector(
              `[data-target="#${openCollapse.id}"]`
            );
            if (relatedButton) {
              relatedButton.classList.add('collapsed');
              relatedButton.setAttribute('aria-expanded', 'false');
            }
          });
        }
        target.classList.add('show');
        button.classList.remove('collapsed');
        button.setAttribute('aria-expanded', 'true');
      }
    }
  });
});


document.addEventListener("DOMContentLoaded", function () {
  document.querySelector("#dropdownMenuButton").addEventListener("click", function (event) {
    event.stopPropagation();
    let dropdown = document.querySelector(".dropdown-menu");
    dropdown.classList.toggle("show");
  });

  document.addEventListener("click", function (event) {
    let dropdown = document.querySelector(".dropdown-menu");
    if (!event.target.closest(".dropdown")) {
      dropdown.classList.remove("show");
    }
  });
});

const debouncedCheckEmailUpdate = debounce(checkEmailUpdate, 1000);


function toggleSmsHelp() {
  const content = document.getElementById('smsHelpContent');
  const header = content.previousElementSibling;

  if (content.classList.contains('show')) {
    content.classList.remove('show');
    header.innerHTML = 'Keine SMS erhalten? <span style="float: right;">▼</span>';
  } else {
    content.classList.add('show');
    header.innerHTML = 'Keine SMS erhalten? <span style="float: right;">▲</span>';
  }
}

function updateMobileValueUpdate() {
  const countryCode = document.getElementById("countryCodeSelectUpdate").value;
  let mobileNumber = document.getElementById("mobileUpdate").value.trim();
  const combinedMobileValue = document.getElementById("combinedMobileValueUpdate");

  const prefixesToRemove = ["+49", "+43", "+41"];
  for (var i = 0; i < prefixesToRemove.length; i++) {
    if (mobileNumber.startsWith(prefixesToRemove[i])) {
      mobileNumber = mobileNumber.substring(prefixesToRemove[i].length);
      break;
    }
  }

  if (mobileNumber) {
    combinedMobileValue.textContent = `${countryCode} ${mobileNumber}`;
  } else {
    combinedMobileValue.textContent = "";
  }
}

function checkMobileUpdate(inputElement) {
  const countryCode = document.getElementById("countryCodeSelectUpdate").value;
  let number = inputElement.value.trim().replace(/\s+/g, "");
  const statusDiv = document.getElementById('mobileUpdateStatus');
  const button = document.getElementById('smsUpdateButton');

  if (number === '') {
    statusDiv.style.display = 'none';
    button.value = 'SMS erneut versenden';
    return;
  }

  const prefixesToRemove = ["+49", "+43", "+41"];
  for (var i = 0; i < prefixesToRemove.length; i++) {
    if (number.startsWith(prefixesToRemove[i])) {
      number = number.substring(prefixesToRemove[i].length);
      break;
    }
  }
  number = number.replace(/^0+/, "");

  const processedValue = countryCode.replace("+", "00") + number;

  if (processedValue.length < 10) {
    statusDiv.className = 'email-status invalid';
    statusDiv.textContent = '❌ Mobilnummer zu kurz';
    statusDiv.style.display = 'block';
    button.value = 'SMS erneut versenden';
    return;
  }

  var varcheck = verifyVarCheck("mobile", processedValue);
  if (varcheck == 0) {
    statusDiv.className = 'email-status invalid';
    statusDiv.textContent = '❌ Ungültige Mobilnummer';
    statusDiv.style.display = 'block';
    button.value = 'SMS erneut versenden';
  } else if (varcheck == 2) {
    statusDiv.className = 'email-status invalid';
    statusDiv.textContent = '❌ Diese Mobilnummer ist bereits registriert';
    statusDiv.style.display = 'block';
    button.value = 'SMS erneut versenden';
  } else if (varcheck == 3) {
    statusDiv.className = 'email-status invalid';
    statusDiv.textContent = '❌ Nur deutsche, österreichische und schweizer Nummern werden unterstützt';
    statusDiv.style.display = 'block';
    button.value = 'SMS erneut versenden';
  } else if (varcheck == 4) {
    statusDiv.className = 'email-status invalid';
    statusDiv.textContent = '❌ Ungültiges Mobilnummer-Format';
    statusDiv.style.display = 'block';
    button.value = 'SMS erneut versenden';
  } else if (varcheck == 5) {
    statusDiv.className = 'email-status invalid';
    statusDiv.textContent = '❌ Diese Mobilnummer ist blockiert';
    statusDiv.style.display = 'block';
    button.value = 'SMS erneut versenden';
  } else {
    statusDiv.className = 'email-status valid';
    statusDiv.textContent = '✔️ Mobilnummer verfügbar';
    statusDiv.style.display = 'block';
    button.value = 'Mobilnummer aktualisieren und SMS neu versenden';
  }
}

const debouncedCheckMobileUpdate = debounce(checkMobileUpdate, 1000);

function toggleSupportHelp() {
  const content = document.getElementById('supportHelpContent');
  const header = content.previousElementSibling;

  if (content.classList.contains('show')) {
    content.classList.remove('show');
    header.innerHTML = 'Weitere Hilfe benötigt? <span style="float: right;">▼</span>';
  } else {
    content.classList.add('show');
    header.innerHTML = 'Weitere Hilfe benötigt? <span style="float: right;">▲</span>';
  }
}
