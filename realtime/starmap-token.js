import crypto from "node:crypto";

export function verifyToken(token, secret) {
	const parts = token.split(".");
	if (parts.length !== 2 || !parts[0] || !parts[1]) {
		throw new Error("invalid token");
	}

	const expected = crypto.createHmac("sha256", secret).update(parts[0]).digest();
	const actual = base64UrlDecode(parts[1]);
	if (actual.length !== expected.length || !crypto.timingSafeEqual(actual, expected)) {
		throw new Error("invalid token signature");
	}

	const payload = JSON.parse(base64UrlDecode(parts[0]).toString("utf8"));
	const now = Math.floor(Date.now() / 1000);
	if (!Number.isSafeInteger(Number(payload.exp)) || Number(payload.exp) <= now) {
		throw new Error("expired token");
	}

	return {
		purpose: payload.purpose || "starmap",
		nonce: payload.nonce,
		connectionId: payload.connectionId,
		encryptedScan: payload.encryptedScan,
		exp: Number(payload.exp),
		userId: Number(payload.userId),
		layerId: Number(payload.layerId),
		allianceId: payload.allianceId == null ? null : Number(payload.allianceId),
		canSeeAllianceShips: payload.canSeeAllianceShips === true,
		friendlyUserIds: toNumberSet(payload.friendlyUserIds),
		enemyUserIds: toNumberSet(payload.enemyUserIds),
		friendlyAllianceIds: toNumberSet(payload.friendlyAllianceIds),
		enemyAllianceIds: toNumberSet(payload.enemyAllianceIds),
		sharedUserIds: toNumberSet(payload.sharedUserIds),
		sharedAllianceIds: toNumberSet(payload.sharedAllianceIds)
	};
}

function toNumberSet(values) {
	if (!Array.isArray(values)) {
		return new Set();
	}

	return new Set(values.map(Number).filter(Number.isFinite));
}

function base64UrlDecode(value) {
	const normalized = value.replace(/-/g, "+").replace(/_/g, "/");
	const padding = normalized.length % 4 === 0 ? "" : "=".repeat(4 - (normalized.length % 4));

	return Buffer.from(normalized + padding, "base64");
}
