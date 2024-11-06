// #### TUTORIAL STUFF

var isTutorial = false;
var hasSlidIn = false;
var hasInnerUpdate = false;

function initPaddPopup(stepId) {
  new Ajax.Request("game.php", {
    method: "post",
    parameters: {
      SHOW_PADD: 1,
    },
    onSuccess: function (transport) {
      document.body.insertAdjacentHTML("beforeend", transport.responseText);
      const padd = document.getElementById("padd-popup");
      addDragAndDrop(padd);

      setTimeout(() => {
        padd.style.left = "80%";
        hasSlidIn = true;
      }, 10);
      openPaddPopup(stepId);
    },
  });
}

function openPaddPopup(stepId) {
  const currentStep = tutorialSteps[stepId];
  const hasInnerUpdate = currentStep.innerUpdate;
  const title = currentStep.title;
  const text = currentStep.text;

  if (!document.getElementById("padd-popup")) {
    initPaddPopup(stepId);
    return;
  }

  let backButton = document.getElementById("back-button");
  let nextButton = document.getElementById("next-button");

  if (currentStep.previousid != null) {
    backButton.className = "nav-button active";
    backButton.onclick = () => {
      updateAndSaveTutorialStep(currentStep.previousid, stepId, false);
    };
  } else {
    backButton.className = "nav-button inactive";
    backButton.onclick = null;
  }

  if (hasInnerUpdate || currentStep.nextid == null) {
    nextButton.className = "nav-button inner-update";
    nextButton.onclick = null;
  } else {
    nextButton.className = "nav-button active";
    nextButton.onclick = () => {
      updateAndSaveTutorialStep(currentStep.nextid, stepId, true);
    };
  }

  document.getElementById("padd-title").innerText = title;
  document.getElementById("padd-text").innerHTML = text;
}

function addDragAndDrop(element) {
  let isDragging = false;
  let offsetX, offsetY;

  element.addEventListener("mousedown", (e) => {
    isDragging = true;
    offsetX = e.clientX - element.getBoundingClientRect().left;
    offsetY = e.clientY - element.getBoundingClientRect().top;
    element.style.cursor = "grabbing";
    element.style.transition = "none";
  });

  element.addEventListener("touchstart", (e) => {
    isDragging = true;
    const touch = e.touches[0];
    offsetX = touch.clientX - element.getBoundingClientRect().left;
    offsetY = touch.clientY - element.getBoundingClientRect().top;
    element.style.transition = "none";
  });

  document.addEventListener(
    "touchmove",
    (e) => {
      if (isDragging) {
        e.preventDefault();
        const touch = e.touches[0];
        element.style.left = `${touch.clientX - offsetX}px`;
        element.style.top = `${touch.clientY - offsetY}px`;
        element.style.transform = "none";
      }
    },
    { passive: false }
  );

  document.addEventListener("touchend", () => {
    isDragging = false;
  });

  document.addEventListener("mousemove", (e) => {
    if (isDragging) {
      element.style.left = `${e.clientX - offsetX}px`;
      element.style.top = `${e.clientY - offsetY}px`;
      element.style.transform = "none";
    }
  });

  document.addEventListener("mouseup", () => {
    isDragging = false;
    element.style.cursor = "move";
  });
}

function initOverlay(innerContentElement) {
  const innerRect = innerContentElement.getBoundingClientRect();
  let overlay = document.getElementById("tutorial-overlay");
  if (!overlay) {
    overlay = document.createElement("div");
    overlay.id = "tutorial-overlay";
    overlay.style.position = "fixed";
    overlay.style.top = `${innerRect.top}px`;
    overlay.style.left = `${innerRect.left}px`;
    overlay.style.width = "100vw";
    overlay.style.height = "100vh";
    overlay.style.backgroundColor = "rgba(0, 0, 0, 0.4)";
    overlay.style.zIndex = "1000";

    document.body.appendChild(overlay);
  }
  return overlay;
}

let frames = [];
window.addEventListener("scroll", updateFramesPositions);
window.addEventListener("resize", updateFramesPositions);

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
  const elements = elementIds.map((id) => document.getElementById(id));
  const innerContentElement = document.getElementById("innerContent");

  const lastStep =
    tutorialSteps[isForward ? currentStep.previousid : currentStep.nextid];
  if (lastStep != null) {
    if (lastStep.elementIds) {
      const stepElements = lastStep.elementIds.map((id) =>
        document.getElementById(id)
      );
      stepElements.forEach((element) => {
        if (element) {
          removeHighlightFromElement();
        }
      });
    }
  }

  const overlay = initOverlay(innerContentElement);
  elements.forEach((element) => {
    if (elementIds.includes("clearpage")) {
    } else if (element != null) {
      addHighlightToElement(element);
    } else if (element == null) {
      setTimeout(() => {
        updateAndSaveTutorialStep(
          isForward ? currentStep.nextid : currentStep.previousid,
          stepId,
          isForward
        );
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
  const highlightedElements = document.querySelectorAll(
    '[style*="z-index: 1001"]'
  );
  highlightedElements.forEach((element) => {
    element.style.border = "";
    element.style.zIndex = "";
    element.style.position = "";
    element.style.animation = "";
  });
}

function addHighlightToElement(element) {
  if (!document.getElementById("pulse-animation")) {
    const style = document.createElement("style");
    style.id = "pulse-animation";
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

  element.style.zIndex = "1001";
  element.style.position = "relative";
  element.style.border = "2px solid white";
  element.style.animation = "pulse 2s infinite";
}

function initCloseButton(overlay, elements, innerContentElement, stepId) {
  const innerRect = innerContentElement.getBoundingClientRect();
  var closeButton = document.getElementById("tutorial-close-button");

  if (!closeButton) {
    new Ajax.Request("game.php", {
      method: "post",
      parameters: {
        SHOW_TUTORIAL_CLOSE: 1,
      },
      onSuccess: function (transport) {
        document.body.insertAdjacentHTML("beforeend", transport.responseText);
        closeButton = document.getElementById("tutorial-close-button");
        closeButton.style.top = `${innerRect.top + 10}px`;
        closeButton.style.left = `${innerRect.left + 10}px`;

        closeButton.onclick = () => {
          overlay.remove();
          elements.forEach((element) => {
            removeHighlightFromElement();
          });
          const padd = document.getElementById("padd-popup");
          if (padd) {
            padd.remove();
          }
          finishTutorial(stepId);
          closeButton.remove();
        };
      },
    });
  }
  return closeButton;
}

function clearTutorial() {
  const closeButton = document.getElementById("tutorial-close-button");
  if (closeButton) {
    closeButton.remove();
  }

  const overlay = document.getElementById("tutorial-overlay");
  if (overlay) {
    overlay.remove();
  }

  const padd = document.getElementById("padd-popup");
  if (padd) {
    padd.remove();
  }
}

var saveTimeout;
function saveTutorialStep(stepId, isForward) {
  clearTimeout(saveTimeout);

  saveTimeout = setTimeout(function () {
    new Ajax.Request("game.php", {
      method: "post",
      parameters: {
        B_SET_TUTORIAL: 1,
        currentstep: stepId,
        isforward: isForward ? 1 : 0,
      },
      evalScripts: true,
    });
  }, 150);
}

function finishTutorial(stepId) {
  new Ajax.Request("game.php", {
    method: "post",
    parameters: {
      B_FINISH_TUTORIAL: 1,
      stepId: stepId,
    },
    evalScripts: true,
  });
}
