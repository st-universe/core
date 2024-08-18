let staticAmplitude = 0;
let staticWavelength = 0;
let dynamicAmplitude = 0;
let dynamicWavelength = 0;
let amplitudeStepSize = 1;
let wavelengthStepSize = 1;

function initialiseBuoyAnalysis(initialAmplitude, initialWavelength) {
    staticAmplitude = Math.round(initialAmplitude);
    staticWavelength = Math.round(initialWavelength);


    dynamicAmplitude = Math.round(staticAmplitude * (0.25 + Math.random()));

    dynamicWavelength = Math.round(staticWavelength * (0.25 + Math.random()));


    amplitudeStepSize = calculateStepSize(dynamicAmplitude, staticAmplitude);
    wavelengthStepSize = calculateStepSize(dynamicWavelength, staticWavelength);

    drawSinusCurve();
    updateValueDisplays();
}

function calculateStepSize(currentValue, targetValue) {
    const maxSteps = 10;
    const difference = Math.abs(currentValue - targetValue);
    return Math.ceil(difference / maxSteps);
}

function drawSinusCurve() {
    const canvas = document.getElementById('buoyAnalysisCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const offsetX = 50;
    const offsetY = 50;
    const canvasWidth = canvas.width;
    const canvasHeight = canvas.height;
    const drawingWidth = 700;
    const drawingHeight = 300;

    ctx.clearRect(0, 0, canvasWidth, canvasHeight);

    drawGrid(ctx, offsetX, offsetY, drawingWidth, drawingHeight);
    drawAxes(ctx, offsetX, offsetY, drawingWidth, drawingHeight);

    drawCurve(ctx, staticAmplitude, 5, '#ff0000', offsetX, offsetY, drawingWidth, drawingHeight);
    drawCurve(ctx, dynamicAmplitude, dynamicWavelength / staticWavelength * 5, '#00ff00', offsetX, offsetY, drawingWidth, drawingHeight);
}

function drawGrid(ctx, offsetX, offsetY, width, height) {
    const gridColor = '#444444';
    const stepX = width / 20;
    const stepY = height / 10;

    ctx.beginPath();
    for (let x = 0; x <= width; x += stepX) {
        ctx.moveTo(offsetX + x, offsetY);
        ctx.lineTo(offsetX + x, offsetY + height);
    }
    for (let y = 0; y <= height; y += stepY) {
        ctx.moveTo(offsetX, offsetY + y);
        ctx.lineTo(offsetX + width, offsetY + y);
    }
    ctx.strokeStyle = gridColor;
    ctx.stroke();
}

function drawAxes(ctx, offsetX, offsetY, width, height) {
    ctx.beginPath();
    ctx.moveTo(offsetX, offsetY + height / 2);
    ctx.lineTo(offsetX + width, offsetY + height / 2);
    ctx.moveTo(offsetX + width / 2, offsetY);
    ctx.lineTo(offsetX + width / 2, offsetY + height);
    ctx.strokeStyle = '#ffffff';
    ctx.stroke();
}

function drawCurve(ctx, amplitude, wavelength, color, offsetX, offsetY, width, height) {
    ctx.beginPath();
    const scaledAmplitude = amplitude * (height / 2 * 0.9 / staticAmplitude);
    for (let x = 0; x <= width; x++) {
        const y = Math.sin((x / width) * wavelength * Math.PI * 2) * scaledAmplitude;
        ctx.lineTo(offsetX + x, offsetY + height / 2 - y);
    }
    ctx.strokeStyle = color;
    ctx.lineWidth = 2;
    ctx.stroke();
    ctx.lineWidth = 1;
}

function updateValueDisplays() {
    document.getElementById("dynamicAmplitude").textContent = dynamicAmplitude;
    document.getElementById("dynamicWavelength").textContent = dynamicWavelength;
}

function increaseDynamicAmplitude() {
    let previousDifference = Math.abs(dynamicAmplitude - staticAmplitude);
    dynamicAmplitude += amplitudeStepSize;
    let newDifference = Math.abs(dynamicAmplitude - staticAmplitude);


    if (newDifference <= amplitudeStepSize && previousDifference > amplitudeStepSize) {
        dynamicAmplitude = staticAmplitude;
    }

    drawSinusCurve();
    updateValueDisplays();
    checkForMatch();
}

function decreaseDynamicAmplitude() {
    let previousDifference = Math.abs(dynamicAmplitude - staticAmplitude);
    dynamicAmplitude = Math.max(0, dynamicAmplitude - amplitudeStepSize);
    let newDifference = Math.abs(dynamicAmplitude - staticAmplitude);


    if (newDifference <= amplitudeStepSize && previousDifference > amplitudeStepSize) {
        dynamicAmplitude = staticAmplitude;
    }

    drawSinusCurve();
    updateValueDisplays();
    checkForMatch();
}

function increaseDynamicWavelength() {
    let previousDifference = Math.abs(dynamicWavelength - staticWavelength);
    dynamicWavelength += wavelengthStepSize;
    let newDifference = Math.abs(dynamicWavelength - staticWavelength);


    if (newDifference <= wavelengthStepSize && previousDifference > wavelengthStepSize) {
        dynamicWavelength = staticWavelength;
    }

    drawSinusCurve();
    updateValueDisplays();
    checkForMatch();
}

function decreaseDynamicWavelength() {
    let previousDifference = Math.abs(dynamicWavelength - staticWavelength);
    dynamicWavelength = Math.max(1, dynamicWavelength - wavelengthStepSize);
    let newDifference = Math.abs(dynamicWavelength - staticWavelength);

    if (newDifference <= wavelengthStepSize && previousDifference > wavelengthStepSize) {
        dynamicWavelength = staticWavelength;
    }

    drawSinusCurve();
    updateValueDisplays();
    checkForMatch();
}

function checkForMatch() {
    if (dynamicAmplitude === staticAmplitude && dynamicWavelength === staticWavelength) {
        document.getElementById('matchNotification').style.display = 'block';
    } else {
        document.getElementById('matchNotification').style.display = 'none';
    }
}
