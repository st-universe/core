// Warp Trace Analysis Minigame

(function () {
    if (window.WarpTraceAnalyzer) {
        if (window.currentAnalyzer) {
            window.currentAnalyzer.initializeAnalysisTimer();
        }
        return;
    }

    class WarpTraceAnalyzer {
        constructor() {
            this.viewsFound = false;
            this.gameState = {
                isActive: false,
                targetShipId: null,
                targetShipName: '',
                targetFlightSigId: null,
                timeRemaining: 180,
                timeSaved: 0,
                gameProgress: 0,
                completedAnalyses: 0,
                currentPhaseIndex: 0
            };
            this.phases = [
                { name: 'frequency', title: 'Frequenzkalibrierung', timeReduction: 45 },
                { name: 'interference', title: 'Interferenzmuster', timeReduction: 30 },
                { name: 'resonance', title: 'Resonanzanalyse', timeReduction: 45 },
                { name: 'signature', title: 'Warp-Signatur', timeReduction: 30 }
            ];
            this.gameTimer = null;
            this.init();
            this.initializeAnalysisTimer();
        }

        init() {
            document.addEventListener('click', (e) => {
                const el = e.target.closest('.ship-name-clickable');
                if (!el) return;

                const shipId = el.dataset.shipId;
                const shipName = el.dataset.shipName;
                const flightSigId = el.dataset.flightSigId;

                const template = document.getElementById(`ship-html-${shipId}`);
                const shipNameHtml = template ? template.innerHTML : shipName;

                this.openAnalysisView(shipId, shipName, flightSigId, shipNameHtml);
            });

        }

        checkForViews() {
            const analysisView = document.getElementById('warp-analysis-view');
            const scannerView = document.getElementById('scanner-main-view');

            if (analysisView && scannerView && !this.viewsFound) {
                this.viewsFound = true;
                this.setupEventHandlers();
            }

            if (!this.viewsFound) {
                setTimeout(() => this.checkForViews(), 500);
            }
        }

        setupEventHandlers() {
            const backBtn = document.querySelector('secondary-button#back-to-scanner');
            const skipBtn = document.getElementById('skip-game-btn');

            if (backBtn) {
                backBtn.addEventListener('click', () => this.showScannerView());
            }

            if (skipBtn) {
                skipBtn.addEventListener('click', () => this.skipGame());
            }
        }

        openAnalysisView(shipId, shipName, flightSigId, shipNameHtml) {
            this.gameState.targetShipId = shipId;
            this.gameState.targetShipName = shipName;
            this.gameState.targetFlightSigId = flightSigId;

            const targetNameEl = document.getElementById('target-ship-name');
            const shipIdEl = document.getElementById('analysis-ship-id');
            const flightSigIdEl = document.getElementById('flight-sig-id');

            if (targetNameEl) targetNameEl.innerHTML = shipNameHtml;
            if (shipIdEl) shipIdEl.value = shipId;
            if (flightSigIdEl) flightSigIdEl.value = flightSigId;

            this.generateRandomValues(parseInt(shipId));

            this.resetGame();
            this.showAnalysisView();
            this.startGame();
        }


        generateRandomValues(shipId) {
            const seed = shipId;

            this.frequencyDeviation = ((seed * 7) % 71) - 35;

            const interferenceCount = 4 + (seed % 3);
            this.interferencePattern = [];
            for (let i = 0; i < interferenceCount; i++) {
                let pos;
                do {
                    pos = (seed * (i + 13) * 17) % 16;
                } while (this.interferencePattern.includes(pos));
                this.interferencePattern.push(pos);
            }
            this.isConstructive = (seed % 2) === 0;
            this.targetPhase = this.isConstructive ? 0 : 180;
            this.waveAmplitude = 30 + ((seed * 3) % 31);

            const signatureCount = 5 + (seed % 3);
            this.signaturePattern = [];
            for (let i = 0; i < signatureCount; i++) {
                let pos;
                do {
                    pos = (seed * (i + 23) * 19) % 25;
                } while (this.signaturePattern.includes(pos));
                this.signaturePattern.push(pos);
            }
        }


        showScannerView() {
            const scannerView = document.getElementById('scanner-main-view');
            const analysisView = document.getElementById('warp-analysis-view');

            if (scannerView) scannerView.style.display = 'block';
            if (analysisView) analysisView.style.display = 'none';

            this.stopGame();
        }

        showAnalysisView() {
            const scannerView = document.getElementById('scanner-main-view');
            const analysisView = document.getElementById('warp-analysis-view');

            if (scannerView) scannerView.style.display = 'none';
            if (analysisView) analysisView.style.display = 'block';
        }

        skipGame() {
            this.gameState.timeRemaining = 180;
            this.updateFinalTime();
            this.showScannerView();
        }

        resetGame() {
            this.gameState.isActive = false;
            this.gameState.timeRemaining = 180;
            this.gameState.timeSaved = 0;
            this.gameState.gameProgress = 0;
            this.gameState.completedAnalyses = 0;
            this.gameState.currentPhaseIndex = 0;

            this.updateUI();
            this.createScientificInterface();
        }

        startGame() {
            this.gameState.isActive = true;
            this.startCurrentPhase();
            this.startGameTimer();
        }

        stopGame() {
            this.gameState.isActive = false;
            if (this.gameTimer) {
                clearInterval(this.gameTimer);
            }
        }

        startGameTimer() {
            this.gameTimer = setInterval(() => {
                if (this.gameState.gameProgress >= 100) {
                    this.completeGame();
                    return;
                }

                this.gameState.gameProgress += 0.8;
                this.updateUI();
            }, 1000);
        }

        createScientificInterface() {
            const grid = document.getElementById('frequency-grid');
            if (!grid) return;

            grid.innerHTML = '';
            grid.style.gridTemplateColumns = 'repeat(1, 1fr)';
            grid.style.maxWidth = '500px';

            const currentPhase = this.phases[this.gameState.currentPhaseIndex];

            if (currentPhase.name === 'frequency') {
                this.createFrequencyCalibration(grid);
            } else if (currentPhase.name === 'interference') {
                this.createInterferenceElimination(grid);
            } else if (currentPhase.name === 'resonance') {
                this.createResonanceAnalysis(grid);
            } else if (currentPhase.name === 'signature') {
                this.createSignatureExtraction(grid);
            }
        }

        createFrequencyCalibration(container) {
            const targetFreq = 2847.3;
            const startFreq = targetFreq + this.frequencyDeviation;

            container.innerHTML = `
            <div class="analysis-panel">
                <div class="phase-title">Subspace-Frequenzkalibrierung</div>
                <div class="frequency-display">
                    <div>Zielfrequenz: <span class="target-freq">${targetFreq}</span> THz</div>
                    <div>Aktuelle Frequenz: <span id="current-freq">${startFreq}</span> THz</div>
                    <div>Abweichung: <span id="deviation">${this.frequencyDeviation}</span> THz</div>
                </div>
                <div class="frequency-controls">
                    <button class="freq-btn" data-adjust="-5">-5 THz</button>
                    <button class="freq-btn" data-adjust="-1">-1 THz</button>
                    <button class="freq-btn" data-adjust="-0.1">-0.1 THz</button>
                    <button class="freq-btn" data-adjust="0.1">+0.1 THz</button>
                    <button class="freq-btn" data-adjust="1">+1 THz</button>
                    <button class="freq-btn" data-adjust="5">+5 THz</button>
                </div>
                <div class="accuracy-bar">
                    <div class="accuracy-fill" id="accuracy-fill"></div>
                </div>
                <button class="calibrate-btn" id="calibrate-btn">Kalibrierung bestätigen</button>
            </div>
        `;

            this.setupFrequencyControls(startFreq, targetFreq);
        }

        createInterferenceElimination(container) {
            container.innerHTML = `
            <div class="analysis-panel">
                <div class="phase-title">Interferenzmuster-Elimination</div>
                <div class="interference-grid">
                    ${Array(16).fill(0).map((_, i) =>
                `<div class="interference-cell" data-index="${i}"></div>`
            ).join('')}
                </div>
                <div class="interference-info">Eliminiere störende Interferenzmuster durch Klicken</div>
            </div>
        `;

            this.setupInterferenceControls();
        }

        createResonanceAnalysis(container) {
            container.innerHTML = `
            <div class="analysis-panel">
                <div class="phase-title">Harmonische Resonanzanalyse</div>
                <div class="waveform-display">
                    <canvas id="waveform-canvas" width="400" height="200"></canvas>
                </div>
                <div class="phase-controls">
                    <label>Phasenverschiebung: <input type="range" id="phase-slider" min="0" max="360" value="0"></label>
                    <span id="phase-value">0°</span>
                </div>
                <div class="target-info">Ziel: ${this.targetPhase}° für optimale Resonanz</div>
                <button class="sync-btn" id="sync-waves">Wellen synchronisieren</button>
            </div>
        `;

            this.setupResonanceControls();
        }

        createSignatureExtraction(container) {
            container.innerHTML = `
            <div class="analysis-panel">
                <div class="phase-title">Warp-Signatur-Extraktion</div>
                <div class="signature-matrix">
                    ${Array(25).fill(0).map((_, i) =>
                `<div class="signature-cell" data-index="${i}"></div>`
            ).join('')}
                </div>
                <div class="extraction-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" id="extraction-progress"></div>
                    </div>
                </div>
            </div>
        `;

            this.setupSignatureControls();
        }

        setupFrequencyControls(startFreq, targetFreq) {
            let currentFreq = startFreq;

            document.querySelectorAll('.freq-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const adjust = parseFloat(btn.dataset.adjust);
                    currentFreq += adjust;
                    currentFreq = Math.round(currentFreq * 10) / 10;

                    const deviation = Math.round((currentFreq - targetFreq) * 10) / 10;
                    const accuracy = Math.max(0, 100 - Math.abs(deviation) * 5);

                    document.getElementById('current-freq').textContent = currentFreq;
                    document.getElementById('deviation').textContent = deviation > 0 ? `+${deviation}` : deviation;
                    document.getElementById('accuracy-fill').style.width = `${accuracy}%`;

                    if (accuracy > 95) {
                        document.getElementById('calibrate-btn').style.background = 'var(--color-62)';
                        document.getElementById('calibrate-btn').addEventListener('click', () => {
                            this.completePhase();
                        });
                    }
                });
            });

        }

        setupInterferenceControls() {
            const cells = document.querySelectorAll('.interference-cell');

            cells.forEach((cell, index) => {
                if (this.interferencePattern.includes(index)) {
                    cell.classList.add('interference-active');
                }

                cell.addEventListener('click', () => {
                    if (cell.classList.contains('interference-active')) {
                        cell.classList.remove('interference-active');
                        cell.classList.add('interference-eliminated');

                        if (document.querySelectorAll('.interference-active').length === 0) {
                            setTimeout(() => this.completePhase(), 500);
                        }
                    }
                });
            });
        }

        setupResonanceControls() {
            const canvas = document.getElementById('waveform-canvas');
            const ctx = canvas.getContext('2d');
            const slider = document.getElementById('phase-slider');
            const phaseValue = document.getElementById('phase-value');

            const drawWaveforms = (phaseDegrees) => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                ctx.strokeStyle = '#1b1b1f';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(0, 100);
                ctx.lineTo(canvas.width, 100);
                ctx.stroke();

                for (let x = 0; x < canvas.width; x += 50) {
                    ctx.beginPath();
                    ctx.moveTo(x, 0);
                    ctx.lineTo(x, canvas.height);
                    ctx.stroke();
                }

                ctx.lineWidth = 2;

                ctx.strokeStyle = '#4CAF50';
                ctx.beginPath();
                for (let x = 0; x < canvas.width; x++) {
                    const y = 100 + this.waveAmplitude * Math.sin((x / 80) * 2 * Math.PI);
                    if (x === 0) ctx.moveTo(x, y);
                    else ctx.lineTo(x, y);
                }
                ctx.stroke();

                ctx.strokeStyle = '#2196F3';
                ctx.beginPath();
                const phaseRadians = (phaseDegrees * Math.PI) / 180;
                for (let x = 0; x < canvas.width; x++) {
                    const y = 100 + this.waveAmplitude * Math.sin((x / 80) * 2 * Math.PI + phaseRadians);
                    if (x === 0) ctx.moveTo(x, y);
                    else ctx.lineTo(x, y);
                }
                ctx.stroke();

                ctx.strokeStyle = '#ff6666';
                ctx.lineWidth = 3;
                ctx.beginPath();
                for (let x = 0; x < canvas.width; x++) {
                    const wave1 = this.waveAmplitude * Math.sin((x / 80) * 2 * Math.PI);
                    const wave2 = this.waveAmplitude * Math.sin((x / 80) * 2 * Math.PI + phaseRadians);
                    const resultY = 100 + (wave1 + wave2);
                    if (x === 0) ctx.moveTo(x, resultY);
                    else ctx.lineTo(x, resultY);
                }
                ctx.stroke();

                ctx.font = '12px Courier New';
                ctx.fillStyle = '#4CAF50';
                ctx.fillText('Referenz', 10, 20);
                ctx.fillStyle = '#2196F3';
                ctx.fillText('Analyse', 10, 35);
                ctx.fillStyle = '#ff6666';
                ctx.fillText('Resultat', 10, 50);

                const phaseDiff = Math.abs(phaseDegrees - this.targetPhase);
                const amplitudeFactor = Math.abs(Math.cos(phaseRadians / 2));

                if (phaseDiff < 15) {
                    ctx.fillStyle = '#4CAF50';
                    if (this.isConstructive) {
                        ctx.fillText('✓ KONSTRUKTIVE', canvas.width - 120, 20);
                        ctx.fillText('INTERFERENZ!', canvas.width - 120, 35);
                    } else {
                        ctx.fillText('✓ DESTRUKTIVE', canvas.width - 120, 20);
                        ctx.fillText('INTERFERENZ!', canvas.width - 120, 35);
                    }
                } else {
                    ctx.fillStyle = '#ff6666';
                    ctx.fillText(`Δ ${phaseDiff.toFixed(1)}°`, canvas.width - 100, 20);
                    ctx.fillText(`Amp: ${(amplitudeFactor * 100).toFixed(0)}%`, canvas.width - 100, 35);
                }
            };

            slider.addEventListener('input', () => {
                const phase = parseInt(slider.value);
                phaseValue.textContent = `${phase}°`;
                drawWaveforms(phase);
            });

            document.getElementById('sync-waves').addEventListener('click', () => {
                const currentPhase = parseInt(slider.value);
                const phaseDiff = Math.abs(currentPhase - this.targetPhase);

                if (phaseDiff < 15) {
                    const btn = document.getElementById('sync-waves');
                    btn.style.background = '#4CAF50';
                    btn.textContent = this.isConstructive ? 'Verstärkung erreicht!' : 'Auslöschung erreicht!';
                    setTimeout(() => {
                        this.completePhase();
                    }, 1000);
                } else {
                    const btn = document.getElementById('sync-waves');
                    btn.style.background = '#f44336';
                    btn.textContent = `Abweichung: ${phaseDiff.toFixed(1)}°`;
                    setTimeout(() => {
                        btn.style.background = '';
                        btn.textContent = 'Wellen synchronisieren';
                    }, 1500);
                }
            });

            drawWaveforms(0);
        }

        setupSignatureControls() {
            const cells = document.querySelectorAll('.signature-cell');
            let extractedCount = 0;

            cells.forEach((cell, index) => {
                if (this.signaturePattern.includes(index)) {
                    cell.classList.add('signature-target');
                }

                cell.addEventListener('click', () => {
                    if (cell.classList.contains('signature-target') && !cell.classList.contains('extracted')) {
                        cell.classList.add('extracted');
                        extractedCount++;

                        const progress = (extractedCount / this.signaturePattern.length) * 100;
                        document.getElementById('extraction-progress').style.width = `${progress}%`;

                        if (extractedCount === this.signaturePattern.length) {
                            setTimeout(() => this.completePhase(), 500);
                        }
                    }
                });
            });
        }

        startCurrentPhase() {
            const currentPhase = this.phases[this.gameState.currentPhaseIndex];
            document.querySelector('.game-title').textContent = currentPhase.title;
            this.createScientificInterface();
        }

        completePhase() {
            if (this.gameState.gameProgress >= 100) return;

            const currentPhase = this.phases[this.gameState.currentPhaseIndex];

            this.gameState.timeSaved += currentPhase.timeReduction;
            this.gameState.timeRemaining = Math.max(30, 180 - this.gameState.timeSaved);
            this.gameState.completedAnalyses++;
            this.gameState.gameProgress += 25;

            this.gameState.gameProgress = Math.min(this.gameState.gameProgress, 100);

            this.updateUI();

            if (this.gameState.currentPhaseIndex < this.phases.length - 1 && this.gameState.gameProgress < 100) {
                this.gameState.currentPhaseIndex++;
                setTimeout(() => this.startCurrentPhase(), 1000);
            } else {
                this.completeGame();
            }
        }

        completeGame() {
            this.stopGame();
            this.updateFinalTime();
            this.showCompletionScreen();
        }

        showCompletionScreen() {
            const grid = document.getElementById('frequency-grid');
            if (!grid) return;

            grid.innerHTML = `
            <div class="analysis-panel">
                <div class="phase-title">Warpspurenanalyse Abgeschlossen</div>
                <div class="completion-stats">
                    <div class="stat-row">
                        <span>Zielschiff:</span>
                        <span>${this.gameState.targetShipName}</span>
                    </div>
                    <div class="stat-row">
                        <span>Analysephasen:</span>
                        <span>${this.gameState.completedAnalyses}/4</span>
                    </div>
                    <div class="stat-row">
                        <span>Fortschritt:</span>
                        <span>${Math.floor(this.gameState.gameProgress)}%</span>
                    </div>
                    <div class="stat-row">
                        <span>Zeit gespart:</span>
                        <span>${this.gameState.timeSaved} Sekunden</span>
                    </div>
                    <div class="stat-row highlight">
                        <span>Finale Analysezeit:</span>
                        <span>${this.gameState.timeRemaining} Sekunden</span>
                    </div>
                </div>
            <div class="completion-buttons">
                <button class="calibrate-btn" id="start-analysis-btn">Analyse starten</button>
                <button class="calibrate-btn" id="back-to-list-btn">Zurück</button>
            </div>
        </div>
    `;

            document.getElementById('start-analysis-btn').addEventListener('click', () => {
                const analyzeButton = document.querySelector('input[type="button"][value="Analyse starten"]');
                if (analyzeButton) {
                    analyzeButton.click();
                }
            });

            document.getElementById('back-to-list-btn').addEventListener('click', () => {
                this.showScannerView();
            });
        }

        updateUI() {
            const timeEl = document.getElementById('analysis-time');
            const progressEl = document.getElementById('game-progress');
            const sequencesEl = document.getElementById('correct-sequences');
            const savedEl = document.getElementById('time-saved');

            if (timeEl) timeEl.textContent = this.gameState.timeRemaining;
            if (progressEl) progressEl.textContent = Math.floor(this.gameState.gameProgress);
            if (sequencesEl) sequencesEl.textContent = this.gameState.completedAnalyses;
            if (savedEl) savedEl.textContent = this.gameState.timeSaved;
        }

        updateFinalTime() {
            const finalTimeEl = document.getElementById('analysis-final-time');
            if (finalTimeEl) {
                finalTimeEl.value = this.gameState.timeRemaining;
            }
        }

        initializeAnalysisTimer() {
            const analysisTimeElement = document.getElementById('analysis-status-container');
            if (!analysisTimeElement) {
                return;
            }

            const analyzeTime = parseInt(analysisTimeElement.dataset.analyzeTime) * 1000;
            const threeMinutes = 3 * 60 * 1000;

            this.updateAnalysisStatus(analyzeTime, threeMinutes);
        }

        Apply
        updateAnalysisStatus(analyzeTime, threeMinutes) {
            const now = new Date().getTime();
            const timePassed = now - analyzeTime;
            const timeRemaining = threeMinutes - timePassed;
            const availabilityTime = 10 * 60 * 1000;
            const availabilityRemaining = (analyzeTime + availabilityTime) - now;

            const statusText = document.getElementById('status-text');
            const countdownElement = document.getElementById('analysis-countdown');
            const refreshButton = document.getElementById('refresh-button');

            if (availabilityRemaining <= 0) {
                if (statusText) {
                    statusText.textContent = `Warpspur nicht mehr verfügbar`;
                }
                if (countdownElement) {
                    countdownElement.style.display = 'none';
                }
                if (refreshButton) {
                    refreshButton.style.display = 'none';
                }
            } else if (timeRemaining <= 0) {
                if (statusText) {
                    const shipName = statusText.textContent;
                    statusText.textContent = `Warpspur von ${shipName} wurde analysiert`;
                }
                if (countdownElement) {
                    countdownElement.style.display = 'inline';
                    this.startAvailabilityCountdown(availabilityRemaining);
                }
                if (refreshButton) {
                    refreshButton.style.display = 'block';
                }
            } else {
                if (statusText) {
                    const shipName = statusText.textContent;
                    statusText.textContent = `Warpspur von ${shipName} wird analysiert...`;
                }
                if (countdownElement) {
                    this.startCountdown(timeRemaining);
                }
                if (refreshButton) {
                    refreshButton.style.display = 'none';
                }
            }
        }

        startCountdown(timeRemaining) {
            const countdownElement = document.getElementById('analysis-countdown');
            if (!countdownElement) return;

            let remainingMs = timeRemaining;

            const updateCountdown = () => {
                if (remainingMs <= 0) {
                    clearInterval(this.countdownInterval);

                    const statusText = document.getElementById('status-text');
                    const shipName = statusText.textContent.replace('Warpspur von ', '').replace(' wird analysiert...', '');

                    if (statusText) {
                        statusText.textContent = `Warpspur von ${shipName} wurde analysiert`;
                    }

                    const refreshLink = document.querySelector('#refresh-button a');
                    if (refreshLink) {
                        const clickEvent = new MouseEvent('click', {
                            bubbles: true,
                            cancelable: true,
                            button: 0
                        });
                        refreshLink.dispatchEvent(clickEvent);
                    }

                    const availabilityTime = 10 * 60 * 1000;
                    this.startAvailabilityCountdown(availabilityTime - 3 * 60 * 1000);
                    return;
                }

                const minutes = Math.floor(remainingMs / 60000);
                const seconds = Math.floor((remainingMs % 60000) / 1000);

                countdownElement.textContent = ` (${minutes}:${seconds.toString().padStart(2, '0')})`;
                remainingMs -= 1000;
            };

            updateCountdown();
            this.countdownInterval = setInterval(updateCountdown, 1000);
        }

        startAvailabilityCountdown(timeRemaining) {
            const countdownElement = document.getElementById('analysis-countdown');
            if (!countdownElement) return;

            let remainingMs = timeRemaining;

            const updateAvailabilityCountdown = () => {
                if (remainingMs <= 0) {
                    clearInterval(this.availabilityCountdownInterval);
                    const statusText = document.getElementById('status-text');

                    if (statusText) {
                        statusText.textContent = `Warpspur nicht mehr verfügbar`;
                    }

                    const refreshLink = document.querySelector('#refresh-button a');
                    if (refreshLink) {
                        const clickEvent = new MouseEvent('click', {
                            bubbles: true,
                            cancelable: true,
                            button: 0
                        });
                        refreshLink.dispatchEvent(clickEvent);
                    }

                    countdownElement.style.display = 'none';
                    return;
                }

                const minutes = Math.floor(remainingMs / 60000);
                const seconds = Math.floor((remainingMs % 60000) / 1000);

                countdownElement.textContent = ` (Verfügbar: ${minutes}:${seconds.toString().padStart(2, '0')})`;
                remainingMs -= 1000;
            };

            updateAvailabilityCountdown();
            if (this.availabilityCountdownInterval) {
                clearInterval(this.availabilityCountdownInterval);
            }
            this.availabilityCountdownInterval = setInterval(updateAvailabilityCountdown, 1000);
        }
    }

    window.WarpTraceAnalyzer = WarpTraceAnalyzer;
    window.currentAnalyzer = new WarpTraceAnalyzer();
})();