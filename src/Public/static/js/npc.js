function loadPlayerDetails(userId) {
    const reasonInput = document.getElementById('reason-' + userId);
    const reason = reasonInput.value.trim();

    if (!reason) {
        alert('Bitte gib einen Grund für die Einsicht an.');
        return;
    }

    document.getElementById('detailsForm-' + userId).style.display = 'none';

    document.getElementById('playerDetailsInfo-' + userId).style.display = 'block';
    document.getElementById('playerColonies-' + userId).style.display = 'block';
    document.getElementById('playerShips-' + userId).style.display = 'block';

    fetch('/npc/?SHOW_PLAYER_DETAILS=1&userid=' + userId + '&reason=' + encodeURIComponent(reason))
        .then(response => response.text())
        .then(html => {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;

            const columns = tempDiv.querySelector('div').children;

            document.getElementById('playerDetailsInfo-' + userId).innerHTML = columns[0].innerHTML;
            document.getElementById('playerColonies-' + userId).innerHTML = columns[1].innerHTML;
            document.getElementById('playerShips-' + userId).innerHTML = columns[2].innerHTML;

            fetch('/npc/index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'B_LOG_PLAYER_DETAILS=1&userid=' + userId + '&reason=' + encodeURIComponent(reason)
            })
                .catch(error => {
                    console.error('Fehler beim Protokollieren des Zugriffs:', error);
                });
        })
        .catch(error => {
            console.error('Fehler beim Laden der Spielerdetails:', error);
        });
}

function showNPCMemberRumpInfo(obj, userid, rumpid) {
    var pos = findObject(obj);
    updatePopup('/npc/?SHOW_MEMBER_RUMP_INFO=1&userid=' + userid + '&rumpid=' + rumpid,
        900, pos[0] - 100, pos[1] + 50, false
    );
}

function showPlayerDetails(userId) {
    const reasonInput = document.getElementById('reason-' + userId);
    const reason = reasonInput.value.trim();

    if (!reason) {
        alert('Bitte gib einen Grund für die Einsicht an.');
        return;
    }
    document.getElementById('detailsForm-' + userId).style.display = 'none';

    document.getElementById('playerDetails-' + userId).style.display = 'block';
    document.getElementById('playerColonies-' + userId).style.display = 'flex';
    document.getElementById('playerShips-' + userId).style.display = 'block';
    fetch('/npc/index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'B_LOG_PLAYER_DETAILS=1&userid=' + userId + '&reason=' + encodeURIComponent(reason)
    })
        .catch(error => {
            console.error('Fehler beim Protokollieren des Zugriffs:', error);
        });
}



function addCommodityRow() {
    const container = document.getElementById('commodityRowsContainer');
    const rows = container.querySelectorAll('.commodity-row');
    const newIndex = rows.length;

    const newRow = document.createElement('div');
    newRow.className = 'commodity-row';
    newRow.setAttribute('data-row-index', newIndex);

    const firstSelect = document.querySelector('.commoditySelect');
    let optionsHtml = '';
    for (let i = 0; i < firstSelect.options.length; i++) {
        optionsHtml += '<option value="' + firstSelect.options[i].value + '">' + firstSelect.options[i].text + '</option>';
    }

    newRow.innerHTML = 'Ware: <select name="commodities[' + newIndex + '][id]" class="commoditySelect" style="width:10%;">' +
        optionsHtml +
        '</select> ' +
        'Anzahl: <input type="text" size="5" name="commodities[' + newIndex + '][amount]" class="commodityAmount" />' +
        '<br /><br />';

    container.appendChild(newRow);

    updateRemoveButton();
}

function removeLastCommodityRow() {
    const container = document.getElementById('commodityRowsContainer');
    const rows = container.querySelectorAll('.commodity-row');

    if (rows.length > 1) {
        rows[rows.length - 1].remove();
        updateRemoveButton();
    }
}

function updateRemoveButton() {
    const container = document.getElementById('commodityRowsContainer');
    const rows = container.querySelectorAll('.commodity-row');
    const removeBtn = document.getElementById('removeLastCommodityBtn');

    if (rows.length > 1) {
        removeBtn.style.display = 'inline';
    } else {
        removeBtn.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    updateRemoveCommodityRewardButton();
    updateRemoveSpacecraftRewardButton();
    updateDealForm();
});

function addCommodityRewardRow() {
    const container = document.getElementById('commodityRewardContainer');
    const rows = container.querySelectorAll('.commodity-reward-row');
    const newIndex = rows.length;

    const newRow = document.createElement('div');
    newRow.className = 'commodity-reward-row';
    newRow.setAttribute('data-row-index', newIndex);

    const firstSelect = document.querySelector('.commoditySelect');
    let optionsHtml = '';
    for (let i = 0; i < firstSelect.options.length; i++) {
        optionsHtml += '<option value="' + firstSelect.options[i].value + '">' + firstSelect.options[i].text + '</option>';
    }

    newRow.innerHTML = 'Ware: <select name="commodities[' + newIndex + '][id]" class="commoditySelect" style="width:15%;">' +
        optionsHtml +
        '</select> ' +
        'Anzahl: <input type="number" name="commodities[' + newIndex + '][amount]" min="1" max="10000" style="width: 80px;" />' +
        '<br /><br />';

    container.appendChild(newRow);
    updateRemoveCommodityRewardButton();
}

function removeLastCommodityRewardRow() {
    const container = document.getElementById('commodityRewardContainer');
    const rows = container.querySelectorAll('.commodity-reward-row');

    if (rows.length > 1) {
        rows[rows.length - 1].remove();
        updateRemoveCommodityRewardButton();
    }
}

function updateRemoveCommodityRewardButton() {
    const container = document.getElementById('commodityRewardContainer');
    const rows = container.querySelectorAll('.commodity-reward-row');
    const removeBtn = document.getElementById('removeLastCommodityRewardBtn');

    if (rows.length > 1) {
        removeBtn.style.display = 'inline';
    } else {
        removeBtn.style.display = 'none';
    }
}

function addSpacecraftRewardRow() {
    const container = document.getElementById('spacecraftRewardContainer');
    const rows = container.querySelectorAll('.spacecraft-reward-row');
    const newIndex = rows.length;

    const newRow = document.createElement('div');
    newRow.className = 'spacecraft-reward-row';
    newRow.setAttribute('data-row-index', newIndex);

    newRow.innerHTML = 'Bauplan ID: <input type="number" name="spacecrafts[' + newIndex + '][buildplan_id]" min="1" style="width: 80px;" /> ' +
        'Anzahl: <input type="number" name="spacecrafts[' + newIndex + '][amount]" min="1" max="100" style="width: 80px;" />' +
        '<br /><br />';

    container.appendChild(newRow);
    updateRemoveSpacecraftRewardButton();
}

function removeLastSpacecraftRewardRow() {
    const container = document.getElementById('spacecraftRewardContainer');
    const rows = container.querySelectorAll('.spacecraft-reward-row');

    if (rows.length > 1) {
        rows[rows.length - 1].remove();
        updateRemoveSpacecraftRewardButton();
    }
}

function updateRemoveSpacecraftRewardButton() {
    const container = document.getElementById('spacecraftRewardContainer');
    const rows = container.querySelectorAll('.spacecraft-reward-row');
    const removeBtn = document.getElementById('removeLastSpacecraftRewardBtn');

    if (rows.length > 1) {
        removeBtn.style.display = 'inline';
    } else {
        removeBtn.style.display = 'none';
    }
}

function toggleQuestCreator() {
    const creator = document.getElementById('questCreator');
    const header = document.getElementById('questCreatorHeader');
    if (creator.style.display === 'none') {
        creator.style.display = 'block';
        header.innerHTML = '▼ Neue Quest erstellen';
    } else {
        creator.style.display = 'none';
        header.innerHTML = '▶ Neue Quest erstellen';
    }
}

function toggleMyQuests() {
    const myQuests = document.getElementById('myQuests');
    const header = document.getElementById('myQuestsHeader');
    if (myQuests.style.display === 'none') {
        myQuests.style.display = 'block';
        header.innerHTML = '▼ Meine Quests';
    } else {
        myQuests.style.display = 'none';
        header.innerHTML = '▶ Meine Quests';
    }
}

function toggleQuestDetails(questId) {
    const details = document.getElementById('questDetails' + questId);
    const header = document.getElementById('questHeader' + questId);
    if (details.style.display === 'none') {
        details.style.display = 'block';
        header.innerHTML = header.innerHTML.replace('▶', '▼');
    } else {
        details.style.display = 'none';
        header.innerHTML = header.innerHTML.replace('▼', '▶');
    }
}

function toggleQuestUserManagement(questId) {
    const management = document.getElementById('questUserManagement' + questId);
    if (management.style.display === 'none') {
        management.style.display = 'block';
    } else {
        management.style.display = 'none';
    }
}

function postQuestLogEntry(questId) {
    const textArea = document.getElementById('questLogText' + questId);
    const text = textArea.value.trim();

    if (text.length < 3) {
        alert('Der Eintrag muss mindestens 3 Zeichen lang sein.');
        return;
    }

    const formData = new FormData();
    formData.append('B_ADD_QUEST_LOG_ENTRY', '1');
    formData.append('quest_id', questId);
    formData.append('log_text', text);

    fetch('/npc/index.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(html => {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;

            const newQuestDetails = tempDiv.querySelector('#questDetails' + questId);
            if (newQuestDetails) {
                const currentQuestDetails = document.getElementById('questDetails' + questId);
                currentQuestDetails.innerHTML = newQuestDetails.innerHTML;
            }

            textArea.value = '';
        })
        .catch(error => {
            console.error('Fehler beim Hinzufügen des Log-Eintrags:', error);
            alert('Fehler beim Hinzufügen des Eintrags.');
        });
}

function acceptQuestApplication(questId, questUserId) {
    const formData = new FormData();
    formData.append('B_ACCEPT_QUEST_APPLICATION', '1');
    formData.append('quest_id', questId);
    formData.append('quest_user_id', questUserId);

    fetch('/npc/index.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(html => {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;

            const newQuestDetails = tempDiv.querySelector('#questDetails' + questId);
            if (newQuestDetails) {
                const currentQuestDetails = document.getElementById('questDetails' + questId);
                currentQuestDetails.innerHTML = newQuestDetails.innerHTML;
            }
        })
        .catch(error => {
            console.error('Fehler beim Annehmen der Bewerbung:', error);
        });
}

function rejectQuestApplication(questId, questUserId) {
    const formData = new FormData();
    formData.append('B_REJECT_QUEST_APPLICATION', '1');
    formData.append('quest_id', questId);
    formData.append('quest_user_id', questUserId);

    fetch('/npc/index.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(html => {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;

            const newQuestDetails = tempDiv.querySelector('#questDetails' + questId);
            if (newQuestDetails) {
                const currentQuestDetails = document.getElementById('questDetails' + questId);
                currentQuestDetails.innerHTML = newQuestDetails.innerHTML;
            }
        })
        .catch(error => {
            console.error('Fehler beim Ablehnen der Bewerbung:', error);
        });
}

function inviteQuestUsers(questId) {
    const userIdsInput = document.getElementById('inviteUserIds' + questId);
    const userIds = userIdsInput.value.trim();

    if (!userIds) {
        alert('Bitte User-IDs eingeben.');
        return;
    }

    const formData = new FormData();
    formData.append('B_INVITE_QUEST_USERS', '1');
    formData.append('quest_id', questId);
    formData.append('user_ids', userIds);

    fetch('/npc/index.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(html => {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;

            const newQuestDetails = tempDiv.querySelector('#questDetails' + questId);
            if (newQuestDetails) {
                const currentQuestDetails = document.getElementById('questDetails' + questId);
                currentQuestDetails.innerHTML = newQuestDetails.innerHTML;
            }

            userIdsInput.value = '';
        })
        .catch(error => {
            console.error('Fehler beim Einladen von Usern:', error);
        });
}

function excludeQuestUsers(questId) {
    const userIdsInput = document.getElementById('excludeUserIds' + questId);
    const userIds = userIdsInput.value.trim();

    if (!userIds) {
        alert('Bitte User-IDs eingeben.');
        return;
    }

    const formData = new FormData();
    formData.append('B_EXCLUDE_QUEST_USERS', '1');
    formData.append('quest_id', questId);
    formData.append('user_ids', userIds);

    fetch('/npc/index.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(html => {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;

            const newQuestDetails = tempDiv.querySelector('#questDetails' + questId);
            if (newQuestDetails) {
                const currentQuestDetails = document.getElementById('questDetails' + questId);
                currentQuestDetails.innerHTML = newQuestDetails.innerHTML;
            }

            userIdsInput.value = '';
        })
        .catch(error => {
            console.error('Fehler beim Ausschließen von Usern:', error);
        });
}

function endNPCQuest(questId) {
    if (!confirm('Möchtest du diese Quest wirklich beenden?')) {
        return;
    }

    const formData = new FormData();
    formData.append('B_END_NPC_QUEST', '1');
    formData.append('quest_id', questId);

    fetch('/npc/index.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(html => {
            location.reload();
        })
        .catch(error => {
            console.error('Fehler beim Beenden der Quest:', error);
        });
}

function updateDealForm() {
    const isDeal = document.getElementById('deal_type_deal').checked;
    const dealCount = document.getElementById('deal_count');

    const wantCommodityId = document.getElementById('want_commodity_id');
    const wantCommodityAmount = document.getElementById('want_commodity_amount');
    const wantPrestige = document.getElementById('want_prestige');

    const giveCommodityId = document.getElementById('give_commodity_id');
    const giveCommodityAmount = document.getElementById('give_commodity_amount');
    const giveBuildplanId = document.getElementById('give_buildplan_id');
    const giveTypeShip = document.getElementById('give_type_ship');
    const giveTypeBuildplan = document.getElementById('give_type_buildplan');

    if (isDeal) {
        dealCount.disabled = false;
    } else {
        dealCount.disabled = true;
        dealCount.value = '';
    }

    const hasWantCommodity = wantCommodityId.value !== '0' && wantCommodityAmount.value !== '';
    const hasWantPrestige = wantPrestige.value !== '' && wantPrestige.value !== '0';

    if (hasWantCommodity) {
        wantPrestige.disabled = true;
        wantPrestige.value = '';
    } else if (hasWantPrestige) {
        wantCommodityId.disabled = true;
        wantCommodityAmount.disabled = true;
        wantCommodityId.value = '0';
        wantCommodityAmount.value = '';
    } else {
        wantCommodityId.disabled = false;
        wantCommodityAmount.disabled = false;
        wantPrestige.disabled = false;
    }

    const hasGiveCommodity = giveCommodityId.value !== '0' && giveCommodityAmount.value !== '';
    const hasGiveBuildplan = giveBuildplanId.value !== '' && giveBuildplanId.value !== '0';

    if (hasGiveCommodity) {
        giveBuildplanId.disabled = true;
        giveTypeShip.disabled = true;
        giveTypeBuildplan.disabled = true;
        giveBuildplanId.value = '';
    } else if (hasGiveBuildplan) {
        giveCommodityId.disabled = true;
        giveCommodityAmount.disabled = true;
        giveCommodityId.value = '0';
        giveCommodityAmount.value = '';
    } else {
        giveBuildplanId.disabled = false;
        giveTypeShip.disabled = false;
        giveTypeBuildplan.disabled = false;
        giveCommodityId.disabled = false;
        giveCommodityAmount.disabled = false;
    }
}

(function () {
    var currentNpcBuildplanSelector = null;

    function toNumber(value) {
        var parsed = parseInt(value || '0', 10);
        return isNaN(parsed) ? 0 : parsed;
    }

    function getRoot() {
        return document.getElementById('npcBuildplanCreator');
    }

    function updateCrewAndSpecialCount() {
        var root = getRoot();
        if (!root) {
            return;
        }

        var baseCrew = toNumber(root.getAttribute('data-base-crew'));
        var maxCrew = toNumber(root.getAttribute('data-max-crew'));
        var specialSlots = toNumber(root.getAttribute('data-special-slots'));
        var crew = baseCrew;
        var selectedSpecials = 0;

        root.querySelectorAll('[data-npc-buildplan-normal]:checked').forEach(function (input) {
            crew += toNumber(input.getAttribute('data-crew'));
        });

        root.querySelectorAll('[data-npc-buildplan-special]').forEach(function (input) {
            if (input.checked) {
                selectedSpecials++;
                crew += toNumber(input.getAttribute('data-crew'));
            }
        });

        document.getElementById('npcBuildplanCrewCurrent').textContent = crew.toString();
        document.getElementById('npcBuildplanSpecialCount').textContent = selectedSpecials.toString();
        document.getElementById('npc_module_tab_info_9').textContent = selectedSpecials + ' / ' + specialSlots;

        if (crew > maxCrew) {
            root.classList.add('npc-buildplan-crew-exceeded');
            document.getElementById('npcBuildplanCrewWarning').style.display = 'block';
        } else {
            root.classList.remove('npc-buildplan-crew-exceeded');
            document.getElementById('npcBuildplanCrewWarning').style.display = 'none';
        }

        document.getElementById('npcBuildplanSpecialWarning').style.display =
            selectedSpecials > specialSlots ? 'block' : 'none';
    }

    function updateNormalModule(type, input) {
        var tab = document.getElementById('npc_module_tab_' + type);
        var tabImage = document.getElementById('npc_tab_image_mod_' + type);
        var effectTarget = document.getElementById('npc_module_type_' + type);
        var moduleId = input.getAttribute('data-module-id');
        var commodityId = input.getAttribute('data-commodity-id');

        tab.classList.remove('module_selector_unselected');
        tab.classList.remove('module_selector_skipped');

        if (moduleId === '0') {
            tabImage.src = '/assets/buttons/modul_screen_' + type + '.png';
            tab.classList.add('module_selector_skipped');
            effectTarget.innerHTML = '';
            effectTarget.style.display = 'none';
        } else {
            tabImage.src = '/assets/commodities/' + commodityId + '.png';
            effectTarget.innerHTML = document.getElementById('npc_' + moduleId + '_content').innerHTML;
            effectTarget.style.display = 'block';
        }

        updateCrewAndSpecialCount();
    }

    function updateSpecialModules() {
        var root = getRoot();
        if (!root) {
            return;
        }

        var selectedEffects = '';
        var checkedCount = 0;

        root.querySelectorAll('[data-npc-buildplan-special]').forEach(function (input) {
            var moduleId = input.getAttribute('data-module-id');
            var tabImage = document.getElementById('npc_tab_image_special_mod_' + moduleId);

            if (input.checked) {
                checkedCount++;
                selectedEffects += document.getElementById('npc_' + moduleId + '_content').innerHTML;
                tabImage.style.display = 'inline';
            } else {
                tabImage.style.display = 'none';
            }
        });

        document.getElementById('npc_tab_image_mod_9').style.display = checkedCount > 0 ? 'none' : 'inline';
        document.getElementById('npc_module_type_9').innerHTML = selectedEffects;
        document.getElementById('npc_module_type_9').style.display = checkedCount > 0 ? 'block' : 'none';

        updateCrewAndSpecialCount();
    }

    window.npcShowBuildplanModuleSelector = function (type) {
        var root = getRoot();
        if (!root) {
            return;
        }

        var selector = document.getElementById('npc_selector_' + type);
        var tab = document.getElementById('npc_module_tab_' + type);

        root.querySelectorAll('[data-npc-buildplan-tab]').forEach(function (tabElement) {
            tabElement.classList.remove('module_selector_current');
        });

        if (currentNpcBuildplanSelector) {
            currentNpcBuildplanSelector.style.display = 'none';
        }

        selector.style.display = 'block';
        tab.classList.add('module_selector_current');
        currentNpcBuildplanSelector = selector;
    };

    function initNpcBuildplanCreator() {
        var root = getRoot();
        if (!root || root.getAttribute('data-js-initialized') === '1') {
            return;
        }

        root.setAttribute('data-js-initialized', '1');

        root.querySelectorAll('[data-npc-buildplan-normal]').forEach(function (input) {
            input.addEventListener('change', function () {
                updateNormalModule(input.getAttribute('data-module-type'), input);
            });

            if (input.checked) {
                updateNormalModule(input.getAttribute('data-module-type'), input);
            }
        });

        root.querySelectorAll('[data-npc-buildplan-special]').forEach(function (input) {
            input.addEventListener('change', updateSpecialModules);
        });
        updateSpecialModules();

        var firstTab = root.querySelector('[data-npc-buildplan-tab]');
        if (firstTab) {
            window.npcShowBuildplanModuleSelector(firstTab.getAttribute('data-npc-buildplan-tab'));
        }
    }

    window.initNpcBuildplanCreator = initNpcBuildplanCreator;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNpcBuildplanCreator);
    } else {
        initNpcBuildplanCreator();
    }
})();
