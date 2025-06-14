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

document.addEventListener('DOMContentLoaded', function () {
    initNpcLogData();
});
