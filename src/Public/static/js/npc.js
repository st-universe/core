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

function initNpcLogData() {
    var logContainer = document.getElementById('npcLogContainer');
    if (logContainer) {
        window.normalLogsData = logContainer.getAttribute('data-normal-logs');
        window.factionLogsData = logContainer.getAttribute('data-faction-logs');
    }
}

function switchNpcLog(logType) {
    var normalBtn = document.getElementById('npcLogBtn');
    var factionBtn = document.getElementById('factionLogBtn');
    var logTitle = document.getElementById('logTitle');
    var logTableBody = document.getElementById('logTableBody');
    var noLogsMessage = document.getElementById('noLogsMessage');

    if (logType === 'faction') {
        normalBtn.style.backgroundColor = '';
        factionBtn.style.backgroundColor = '#3c3c3c';
        logTitle.innerHTML = 'Fraktions-Log';

        if (window.factionLogsData && window.factionLogsData.trim().length > 0) {
            logTableBody.innerHTML = window.factionLogsData;
            logTableBody.parentNode.style.display = 'table';
            noLogsMessage.style.display = 'none';
        } else {
            logTableBody.parentNode.style.display = 'none';
            noLogsMessage.style.display = 'block';
            noLogsMessage.innerHTML = 'Keine Fraktions-Logs vorhanden';
        }
    } else {
        normalBtn.style.backgroundColor = '#3c3c3c';
        factionBtn.style.backgroundColor = '';
        logTitle.innerHTML = 'NPC Log';

        if (window.normalLogsData && window.normalLogsData.trim().length > 0) {
            logTableBody.innerHTML = window.normalLogsData;
            logTableBody.parentNode.style.display = 'table';
            noLogsMessage.style.display = 'none';
        } else {
            logTableBody.parentNode.style.display = 'none';
            noLogsMessage.style.display = 'block';
            noLogsMessage.innerHTML = 'Keine NPC-Logs vorhanden';
        }
    }
}

function showNPCMemberRumpInfo(obj, userid, rumpid) {
    var pos = findObject(obj);
    updatePopup('/npc/?SHOW_MEMBER_RUMP_INFO=1&userid=' + userid + '&rumpid=' + rumpid,
        900, pos[0] - 400, pos[1]
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
    initNpcLogData();
    updateRemoveCommodityRewardButton();
    updateRemoveSpacecraftRewardButton();
});

function toggleQuestCreator() {
    const questCreator = document.getElementById('questCreator');
    const header = questCreator.previousElementSibling;

    if (questCreator.style.display === 'none') {
        questCreator.style.display = 'block';
        header.innerHTML = '▼ Neue Quest erstellen';
    } else {
        questCreator.style.display = 'none';
        header.innerHTML = '▶ Neue Quest erstellen';
    }
}

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
