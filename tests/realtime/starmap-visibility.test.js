import assert from "node:assert/strict";
import { test } from "node:test";
import { isSpacecraftStaticallyVisible, sanitizeSpacecraftForClient } from "../../realtime/starmap-visibility.js";

function createClient(overrides = {}) {
	return {
		userId: 116,
		allianceId: 6,
		canSeeAllianceShips: true,
		friendlyUserIds: new Set([107, 261, 483]),
		friendlyAllianceIds: new Set([6, 21, 37]),
		enemyUserIds: new Set(),
		enemyAllianceIds: new Set(),
		sharedUserIds: new Set(),
		sharedAllianceIds: new Set(),
		...overrides
	};
}

test("friend contacts and alliance treaties do not grant static map access", () => {
	const client = createClient();
	for (const [userId, allianceId] of [[203, 21], [370, 21], [107, 5], [261, 45], [191, 37], [483, 3]]) {
		for (const type of ["SHIP", "STATION"]) {
			assert.equal(isSpacecraftStaticallyVisible(client, { userId, allianceId, type }), false, `owner ${userId}, ${type}`);
		}
	}
});

test("own spacecraft remain visible without alliance permissions", () => {
	assert.equal(isSpacecraftStaticallyVisible(createClient({ allianceId: null, canSeeAllianceShips: false }), {
		userId: "116", allianceId: null, isCloaked: true
	}), true);
});

test("same-alliance spacecraft require the alliance visibility permission", () => {
	for (const canSeeAllianceShips of [true, false]) {
		for (const type of ["SHIP", "STATION"]) {
			assert.equal(isSpacecraftStaticallyVisible(createClient({ canSeeAllianceShips }), {
				userId: 117, allianceId: "6", type
			}), canSeeAllianceShips);
		}
	}
});

test("unaffiliated users do not share static map access", () => {
	assert.equal(isSpacecraftStaticallyVisible(createClient({ allianceId: null }), {
		userId: 117, allianceId: null
	}), false);
});

test("unknown and neutral spacecraft do not grant static map access", () => {
	const client = createClient();
	assert.equal(isSpacecraftStaticallyVisible(client, null), false);
	assert.equal(isSpacecraftStaticallyVisible(client, { userId: 117, allianceId: 42 }), false);
	assert.equal(isSpacecraftStaticallyVisible(client, { userId: 117 }), false);
});

test("shared live-map positions grant static map access", () => {
	assert.equal(isSpacecraftStaticallyVisible(createClient({ sharedUserIds: new Set([117]) }), {
		userId: 117, allianceId: null
	}), true);
	assert.equal(isSpacecraftStaticallyVisible(createClient({ sharedAllianceIds: new Set([42]) }), {
		userId: 117, allianceId: 42
	}), true);
});

test("foreign live contacts do not disclose ship names, owners, hulls or energy", () => {
	const client = createClient({ enemyUserIds: new Set([117]), sharedUserIds: new Set([117]) });
	const contact = sanitizeSpacecraftForClient(client, {
		id: 123, userId: 117, allianceId: 42, name: "Secret ship", nameHtml: "Secret ship", userName: "Secret owner",
		rumpId: 34, rumpName: "Secret hull", rumpImage: "/assets/ships/34.png", x: 5, y: 6, hull: 100, eps: 50
	});
	assert.equal(contact.id, 123);
	assert.equal(contact.name, "Unbekannte Signatur");
	assert.equal(contact.userId, 0);
	assert.equal(contact.userName, "Unbekannt");
	assert.equal(contact.rumpId, 0);
	assert.equal(contact.rumpImage, "");
	assert.equal(contact.eps, undefined);
	assert.equal(contact.hull, undefined);
	assert.equal(contact.isUnscannedSignature, true);
	assert.equal(contact.isEnemy, false);
});

test("anonymous enemy and neutral payloads are indistinguishable", () => {
	const spacecraft = { id: 123, userId: 117, allianceId: 42, x: 5, y: 6 };
	assert.deepEqual(
		sanitizeSpacecraftForClient(createClient({ enemyUserIds: new Set([117]) }), spacecraft),
		sanitizeSpacecraftForClient(createClient(), spacecraft)
	);
});

test("friendly sensor contacts remain readable without granting internal ship details", () => {
	const contact = sanitizeSpacecraftForClient(createClient(), {
		id: 123, userId: 107, allianceId: 42, name: "Friendly ship", userName: "Friendly owner", hull: 100, eps: 50
	});
	assert.equal(contact.name, "Friendly ship");
	assert.equal(contact.userName, "Friendly owner");
	assert.equal(contact.isFriendly, true);
	assert.equal(contact.eps, undefined);
	assert.equal(contact.hull, undefined);
});

test("cloaked foreign contacts remain unidentified", () => {
	const contact = sanitizeSpacecraftForClient(createClient({ enemyUserIds: new Set([117]) }), {
		id: 123, userId: 117, name: "Secret ship", isCloaked: true, x: 5, y: 6
	});
	assert.equal(contact.name, "?");
	assert.equal(contact.isCloakedSignature, true);
	assert.equal(contact.isEnemy, false);
	assert.equal(contact.userId, 0);
});
