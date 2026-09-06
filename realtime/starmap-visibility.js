export function isSpacecraftStaticallyVisible(client, spacecraft) {
	if (!spacecraft) {
		return false;
	}

	if (Number(spacecraft.userId) === client.userId) {
		return true;
	}
	if (client.sharedUserIds.has(Number(spacecraft.userId))) {
		return true;
	}
	if (spacecraft.allianceId != null && client.sharedAllianceIds.has(Number(spacecraft.allianceId))) {
		return true;
	}

	return client.canSeeAllianceShips
		&& client.allianceId !== null
		&& spacecraft.allianceId != null
		&& Number(spacecraft.allianceId) === client.allianceId;
}
