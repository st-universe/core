function updateCrewRaceManagement(responseText) {
	var wrapper = document.createElement("div");
	wrapper.innerHTML = responseText;

	var updatedManagement = wrapper.querySelector("#crew-race-management");
	var management = document.getElementById("crew-race-management");
	if (updatedManagement && management) {
		management.outerHTML = updatedManagement.outerHTML;
	}

	var updatedResult = wrapper.querySelector("#result");
	var result = document.getElementById("result");
	if (updatedResult && result) {
		result.outerHTML = updatedResult.outerHTML;
	}
}

document.addEventListener("click", function (event) {
	var button = event.target.closest(".crew-race-action");
	if (!button) {
		return;
	}

	var row = button.closest("[data-crew-race-id]");
	var management = document.getElementById("crew-race-management");
	if (!row || !management || button.disabled) {
		return;
	}

	var action = button.dataset.action;
	var parameters = {
		crew_race_id: row.dataset.crewRaceId,
		sstr: management.dataset.sessionString
	};
	if (action === "save") {
		parameters.B_UPDATE_USER_CREW_RACE_CHANCE = 1;
		parameters.chance = row.querySelector(".crew-race-chance").value;
	} else if (action === "select") {
		parameters.B_SELECT_USER_CREW_RACE = 1;
	} else if (action === "remove") {
		parameters.B_REMOVE_USER_CREW_RACE = 1;
	} else {
		return;
	}

	button.disabled = true;
	new Ajax.Request("/options.php?SHOW_CREW_RACE_MANAGEMENT=1", {
		method: "post",
		parameters: parameters,
		onSuccess: function (response) {
			updateCrewRaceManagement(response.responseText);
		},
		onFailure: function () {
			button.disabled = false;
		}
	});
});
