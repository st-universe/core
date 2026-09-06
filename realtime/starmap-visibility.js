export function isSpacecraftStaticallyVisible(client, spacecraft) {
	if (!spacecraft) {
		return false;
	}

	if (Number(spacecraft.userId) === client.userId) {
		return true;
	}

	return client.canSeeAllianceShips
		&& client.allianceId !== null
		&& spacecraft.allianceId != null
		&& Number(spacecraft.allianceId) === client.allianceId;
}
