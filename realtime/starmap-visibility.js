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

export function sanitizeSpacecraftForClient(client, spacecraft) {
	const relationship = getSpacecraftRelationship(client, spacecraft);
	if (spacecraft.isCloaked && !relationship.hasDetails && !relationship.hasSharedLiveMapPosition) {
		return sanitizeSignature(spacecraft, relationship);
	}

	if (!relationship.isOwn && !relationship.isFriendly) {
		return sanitizeSignature(spacecraft, relationship);
	}

	const result = {
		...spacecraft,
		isOwn: relationship.isOwn,
		isFriendly: relationship.isFriendly,
		isEnemy: relationship.isEnemy,
		hasDetails: relationship.hasDetails
	};

	if (!relationship.hasDetails) {
		delete result.hull;
		delete result.maxHull;
		delete result.shield;
		delete result.maxShield;
		delete result.eps;
		delete result.maxEps;
		delete result.warpdrive;
		delete result.maxWarpdrive;
		delete result.alertState;
		delete result.alertStateName;
	}

	return result;
}

function sanitizeSignature(spacecraft, relationship) {
	const isCloaked = Boolean(spacecraft.isCloaked);
	return {
		id: spacecraft.id,
		name: isCloaked ? "?" : "Unbekannte Signatur",
		nameText: isCloaked ? "?" : "Unbekannte Signatur",
		nameHtml: isCloaked ? "?" : "Unbekannte Signatur",
		type: spacecraft.type,
		userId: 0,
		userName: "Unbekannt",
		userNameText: "Unbekannt",
		userNameHtml: "Unbekannt",
		allianceId: null,
		allianceName: null,
		allianceNameText: null,
		allianceNameHtml: null,
		rumpId: 0,
		rumpName: isCloaked ? "Tarnsignatur" : "Sensorsignatur",
		rumpImage: "",
		x: spacecraft.x,
		y: spacecraft.y,
		inSystem: Boolean(spacecraft.inSystem),
		systemName: spacecraft.systemName || null,
		isCloaked,
		isCloakedSignature: isCloaked,
		isUnscannedSignature: !isCloaked,
		isOwn: relationship.isOwn,
		isFriendly: false,
		isEnemy: false,
		hasDetails: false,
		isSensorContact: true
	};
}

export function getSpacecraftRelationship(client, spacecraft) {
	const ownerId = Number(spacecraft.userId);
	const allianceId = spacecraft.allianceId == null ? null : Number(spacecraft.allianceId);
	const isOwn = ownerId === client.userId;
	const isOwnAlliance = client.allianceId !== null && allianceId === client.allianceId;
	const hasSharedLiveMapPosition = client.sharedUserIds.has(ownerId)
		|| (allianceId !== null && client.sharedAllianceIds.has(allianceId));
	const isFriendly = isOwn
		|| isOwnAlliance
		|| client.friendlyUserIds.has(ownerId)
		|| (allianceId !== null && client.friendlyAllianceIds.has(allianceId));
	const isEnemy = !isFriendly
		&& (
			client.enemyUserIds.has(ownerId)
			|| (allianceId !== null && client.enemyAllianceIds.has(allianceId))
		);

	return {
		isOwn,
		isFriendly,
		isEnemy,
		hasDetails: isOwn || (client.canSeeAllianceShips && isOwnAlliance),
		hasSharedLiveMapPosition
	};
}
