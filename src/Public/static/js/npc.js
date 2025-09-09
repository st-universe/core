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
        body: 'B_LOG_PLAYER_DETAILS=1&userId=' + userId + '&reason=' + encodeURIComponent(reason)
    })
        .catch(error => {
            console.error('Fehler beim Protokollieren des Zugriffs:', error);
        });
}



document.addEventListener('DOMContentLoaded', function () {
    initNpcLogData();
});
