// #### TUTORIAL STUFF

var isTutorial = false;
var hasSlidIn = false;
var hasInnerUpdate = false;

function initPaddPopup() {
    padd = document.createElement('div');
    padd.id = 'padd-popup';
    padd.style.position = 'fixed';
    padd.style.top = '20%';
    padd.style.left = '100%';
    padd.style.transform = 'translate(-50%, 0%)';
    padd.style.width = '350px';
    padd.style.height = '500px';
    padd.style.backgroundColor = '#A6A6A6';
    padd.style.borderRadius = '20px';
    padd.style.boxShadow = 'inset 0 0 0 5px #666666';
    padd.style.zIndex = '1003';
    padd.style.padding = '10px';
    padd.style.display = 'flex';
    padd.style.flexDirection = 'column';
    padd.style.alignItems = 'center';
    padd.style.cursor = 'move';
    padd.style.transition = 'left 0.5s ease-out';


    const screen = document.createElement('div');
    screen.id = 'padd-screen';
    screen.style.backgroundColor = '#000000';
    screen.style.width = '95%';
    screen.style.height = '60%';
    screen.style.border = '2px solid #666666';
    screen.style.marginBottom = '10px';
    screen.style.display = 'flex';
    screen.style.flexDirection = 'column';
    screen.style.alignItems = 'center';
    screen.style.justifyContent = 'center';
    screen.style.color = '#FFFFFF';
    screen.style.padding = '10px';
    screen.style.borderRadius = '10px';

    const titleField = document.createElement('div');
    titleField.id = 'padd-title';
    titleField.style.fontFamily = 'LCARS';
    titleField.style.fontSize = '24px';
    titleField.style.color = '#FFCC00';
    titleField.style.marginBottom = '10px';

    const textField = document.createElement('div');
    textField.id = 'padd-text';
    textField.style.fontFamily = 'LCARS';
    textField.style.fontSize = '18px';
    textField.style.color = '#FFFFFF';
    textField.style.textAlign = 'center';
    textField.style.overflowY = 'auto';
    textField.style.maxHeight = '100%';
    textField.style.width = '100%';
    textField.style.padding = '10px';

    textField.style.scrollbarWidth = 'thin';
    textField.style.scrollbarColor = '#FF6A00 #000000';

    const style = document.createElement('style');
    style.textContent = `
            #padd-text::-webkit-scrollbar {
                width: 8px;
            }
            #padd-text::-webkit-scrollbar-track {
                background: #000000;
            }
            #padd-text::-webkit-scrollbar-thumb {
                background-color: #FF6A00;
                border-radius: 4px;
            }
        `;
    document.head.appendChild(style);

    screen.appendChild(titleField);
    screen.appendChild(textField);


    const buttonPanel = document.createElement('div');
    buttonPanel.style.display = 'flex';
    buttonPanel.style.flexDirection = 'row';
    buttonPanel.style.width = '95%';
    buttonPanel.style.justifyContent = 'space-between';
    buttonPanel.style.marginTop = '10px';

    backButton = document.createElement('div');
    backButton.id = 'back-button';
    backButton.innerText = '◀';
    backButton.style.backgroundColor = '#FF6A00';
    backButton.style.color = '#FFFFFF';
    backButton.style.width = '60px';
    backButton.style.height = '40px';
    backButton.style.borderRadius = '10px';
    backButton.style.display = 'flex';
    backButton.style.alignItems = 'center';
    backButton.style.justifyContent = 'center';
    backButton.style.cursor = 'pointer';
    backButton.style.fontSize = '24px';
    backButton.style.fontFamily = 'LCARS';

    nextButton = document.createElement('div');
    nextButton.id = 'next-button';
    nextButton.innerText = '▶';
    nextButton.style.width = '60px';
    nextButton.style.height = '40px';
    nextButton.style.borderRadius = '10px';
    nextButton.style.display = 'flex';
    nextButton.style.alignItems = 'center';
    nextButton.style.justifyContent = 'center';
    nextButton.style.fontSize = '24px';
    nextButton.style.fontFamily = 'LCARS';

    buttonPanel.appendChild(backButton);
    buttonPanel.appendChild(nextButton);

    padd.appendChild(screen);
    padd.appendChild(buttonPanel);

    document.body.appendChild(padd);

    addDragAndDrop(padd);

    setTimeout(() => {
        padd.style.left = '80%';
        hasSlidIn = true;
    }, 10);
}

function openPaddPopup(stepId) {

    const currentStep = tutorialSteps[stepId];
    const hasInnerUpdate = currentStep.innerUpdate;
    const title = currentStep.title;
    const text = currentStep.text;

    if (!document.getElementById('padd-popup')) {
        initPaddPopup();
    }

    let backButton = document.getElementById('back-button');
    let nextButton = document.getElementById('next-button');

    if (currentStep.previousid != null) {
        backButton.style.backgroundColor = '#FF6A00';
        backButton.style.cursor = 'pointer';
        backButton.onclick = () => {
            updateAndSaveTutorialStep(currentStep.previousid, stepId, false);
        };
    } else {
        backButton.style.backgroundColor = '#666666';
        backButton.style.cursor = 'not-allowed';
        backButton.onclick = null;
    }

    if (hasInnerUpdate || currentStep.nextid == null) {
        nextButton.style.backgroundColor = '#666666';
        nextButton.style.cursor = 'not-allowed';
        nextButton.onclick = null;
    }
    else {
        nextButton.style.backgroundColor = '#FF6A00';
        nextButton.style.cursor = 'pointer';
        nextButton.onclick = () => {
            updateAndSaveTutorialStep(currentStep.nextid, stepId, true);
        };
    }

    document.getElementById('padd-title').innerText = title;
    document.getElementById('padd-text').innerHTML = text;
}

function addDragAndDrop(element) {
    let isDragging = false;
    let offsetX, offsetY;

    element.addEventListener('mousedown', (e) => {
        isDragging = true;
        offsetX = e.clientX - element.getBoundingClientRect().left;
        offsetY = e.clientY - element.getBoundingClientRect().top;
        element.style.cursor = 'grabbing';
        element.style.transition = 'none';
    });

    element.addEventListener('touchstart', (e) => {
        isDragging = true;
        const touch = e.touches[0];
        offsetX = touch.clientX - element.getBoundingClientRect().left;
        offsetY = touch.clientY - element.getBoundingClientRect().top;
        element.style.transition = 'none';
    });


    document.addEventListener('touchmove', (e) => {
        if (isDragging) {
            e.preventDefault();
            const touch = e.touches[0];
            element.style.left = `${touch.clientX - offsetX}px`;
            element.style.top = `${touch.clientY - offsetY}px`;
            element.style.transform = 'none';
        }
    }, { passive: false });


    document.addEventListener('touchend', () => {
        isDragging = false;
    });

    document.addEventListener('mousemove', (e) => {
        if (isDragging) {
            element.style.left = `${e.clientX - offsetX}px`;
            element.style.top = `${e.clientY - offsetY}px`;
            element.style.transform = 'none';
        }
    });

    document.addEventListener('mouseup', () => {
        isDragging = false;
        element.style.cursor = 'move';
    });
}

function initOverlay(innerContentElement) {
    const innerRect = innerContentElement.getBoundingClientRect();
    let overlay = document.getElementById('tutorial-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'tutorial-overlay';
        overlay.style.position = 'fixed';
        overlay.style.top = `${innerRect.top}px`;
        overlay.style.left = `${innerRect.left}px`;
        overlay.style.width = '100vw';
        overlay.style.height = '100vh';
        overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.4)';
        overlay.style.zIndex = '1000';

        document.body.appendChild(overlay);
    }
    return overlay;
}

let frames = [];
window.addEventListener('scroll', updateFramesPositions);
window.addEventListener('resize', updateFramesPositions);

function updateFramePosition(frame, targetElement) {
    const rect = targetElement.getBoundingClientRect();
    const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

    frame.style.top = `${rect.top + scrollTop}px`;
    frame.style.left = `${rect.left + scrollLeft}px`;
    frame.style.width = `${rect.width}px`;
    frame.style.height = `${rect.height}px`;
}

function updateFramesPositions() {
    frames.forEach(({ frame, target }) => {
        updateFramePosition(frame, target);
    });
}


var tutorialSteps = null;
function initTutorialSteps(tutorialStepsJson, startIndex) {
    isTutorial = true;
    tutorialSteps = JSON.parse(tutorialStepsJson);

    const fallbackIndex = tutorialSteps[startIndex].fallbackIndex;
    if (fallbackIndex != null) {
        startIndex = fallbackIndex;
    }

    updateTutorialStep(startIndex);
}

function updateAndSaveTutorialStep(updateId, saveId, isForward) {
    updateTutorialStep(updateId, isForward);
    saveTutorialStep(saveId, isForward);
}

const originalFunctions = {};
function updateTutorialStep(stepId, isForward = true) {

    const currentStep = tutorialSteps[stepId];
    const elementIds = currentStep.elementIds;
    const innerUpdate = currentStep.innerUpdate;
    const elements = elementIds.map(id => document.getElementById(id));
    const innerContentElement = document.getElementById('innerContent');

    const lastStep = tutorialSteps[isForward ? currentStep.previousid : currentStep.nextid];
    if (lastStep != null) {
        if (lastStep.elementIds) {
            const stepElements = lastStep.elementIds.map(id => document.getElementById(id));
            stepElements.forEach(element => {
                if (element) {
                    removeHighlightFromElement();
                }
            });
        }
    }

    const overlay = initOverlay(innerContentElement);
    elements.forEach(element => {

        if (elementIds.includes('clearpage')) {
        } else if (element != null) {

            addHighlightToElement(element);
        }
        else if (element == null) {
            setTimeout(() => {
                updateAndSaveTutorialStep(isForward ? currentStep.nextid : currentStep.previousid, stepId, isForward);
            }, 0);
        }

    });


    initCloseButton(overlay, elements, innerContentElement, stepId);

    if (innerUpdate) {
        if (!originalFunctions[innerUpdate]) {
            originalFunctions[innerUpdate] = window[innerUpdate];
        }

        if (!window[innerUpdate].isModified) {
            window[innerUpdate] = function (...args) {
                window[innerUpdate].isModified = true;

                originalFunctions[innerUpdate].apply(this, args);

                if (currentStep.nextid != null) {
                    setTimeout(() => {
                        updateAndSaveTutorialStep(currentStep.nextid, stepId, true);
                    }, 500);
                }

                window[innerUpdate] = originalFunctions[innerUpdate];
                delete window[innerUpdate].isModified;
            };
        }
    }

    openPaddPopup(stepId);
}

function removeHighlightFromElement() {

    const highlightedElements = document.querySelectorAll('[style*="z-index: 1001"]');
    highlightedElements.forEach(element => {
        element.style.border = '';
        element.style.zIndex = '';
        element.style.position = '';
        element.style.animation = '';
    });

}

function addHighlightToElement(element) {
    if (!document.getElementById('pulse-animation')) {
        const style = document.createElement('style');
        style.id = 'pulse-animation';
        style.innerHTML = `
        @keyframes pulse {
            0% {
                border-color: white;
            }
            50% {
                border-color: yellow;
            }
            100% {
                border-color: white;
            }
        }
        `;
        document.head.appendChild(style);
    }

    element.style.zIndex = '1001';
    element.style.position = 'relative';
    element.style.border = '2px solid white';
    element.style.animation = 'pulse 2s infinite';
}

function initCloseButton(overlay, elements, innerContentElement, stepId) {

    const innerRect = innerContentElement.getBoundingClientRect();
    var closeButton = document.getElementById('tutorial-close-button');
    if (!closeButton) {
        closeButton = document.createElement('button');
        closeButton.id = 'tutorial-close-button';
        closeButton.innerHTML = '<strong>&#10005;</strong>';
        closeButton.style.position = 'absolute';
        closeButton.style.top = `${innerRect.top + 10}px`;
        closeButton.style.left = `${innerRect.left + 10}px`;
        closeButton.style.zIndex = '1002';

        closeButton.style.backgroundColor = '#FF6A00';
        closeButton.style.color = '#FFFFFF';
        closeButton.style.border = 'none';
        closeButton.style.padding = '0';
        closeButton.style.cursor = 'pointer';
        closeButton.style.fontSize = '20px';
        closeButton.style.width = '60px';
        closeButton.style.height = '60px';
        closeButton.style.display = 'flex';
        closeButton.style.alignItems = 'center';
        closeButton.style.justifyContent = 'center';
        closeButton.style.boxShadow = '0 0 15px rgba(255, 106, 0, 0.7)';
        closeButton.style.transition = 'all 0.3s ease';
        closeButton.style.clipPath = 'polygon(0% 0%, 100% 0%, 85% 100%, 0% 100%)';

        closeButton.addEventListener('mouseover', () => {
            closeButton.style.backgroundColor = '#FF8C00';
            closeButton.style.boxShadow = '0 0 25px rgba(255, 140, 0, 1)';
            closeButton.style.transform = 'translateX(5px)';
        });

        closeButton.addEventListener('mouseout', () => {
            closeButton.style.backgroundColor = '#FF6A00';
            closeButton.style.boxShadow = '0 0 15px rgba(255, 106, 0, 0.7)';
            closeButton.style.transform = 'translateX(0)';
        });

        closeButton.addEventListener('mousedown', () => {
            closeButton.style.transform = 'translateX(5px) scale(0.95)';
            closeButton.style.boxShadow = '0 0 10px rgba(255, 140, 0, 0.5)';
        });

        closeButton.addEventListener('mouseup', () => {
            closeButton.style.transform = 'translateX(5px) scale(1)';
            closeButton.style.boxShadow = '0 0 25px rgba(255, 140, 0, 1)';
        });

        document.body.appendChild(closeButton);
    }

    closeButton = document.getElementById('tutorial-close-button');
    closeButton.onclick = () => {
        overlay.remove();

        elements.forEach(element => {
            removeHighlightFromElement();
        });

        const padd = document.getElementById('padd-popup');
        if (padd) {
            padd.remove();
        }

        finishTutorial(stepId);
        closeButton.remove();
    };

    return closeButton;
}

function clearTutorial() {
    const closeButton = document.getElementById('tutorial-close-button');
    if (closeButton) {
        closeButton.remove();
    }

    const overlay = document.getElementById('tutorial-overlay');
    if (overlay) {
        overlay.remove();
    }

    const padd = document.getElementById('padd-popup');
    if (padd) {
        padd.remove();
    }

}

var saveTimeout;
function saveTutorialStep(stepId, isForward) {
    clearTimeout(saveTimeout);

    saveTimeout = setTimeout(function () {

        new Ajax.Request('game.php', {
            method: 'post',
            parameters: {
                B_SET_TUTORIAL: 1,
                currentstep: stepId,
                isforward: isForward ? 1 : 0
            },
            evalScripts: true
        });
    }, 150);
}

function finishTutorial(stepId) {
    new Ajax.Request('game.php', {
        method: 'post',
        parameters: {
            B_FINISH_TUTORIAL: 1,
            stepId: stepId
        },
        evalScripts: true
    });
}
