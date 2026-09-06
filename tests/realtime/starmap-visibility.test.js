import assert from "node:assert/strict";
import { test } from "node:test";
import { isSpacecraftStaticallyVisible } from "../../realtime/starmap-visibility.js";

function createClient(overrides = {}) {
	return {
		userId: 116,
		allianceId: 6,
		canSeeAllianceShips: true,
		friendlyUserIds: new Set([107, 261, 483]),
		friendlyAllianceIds: new Set([6, 21, 37]),
		enemyUserIds: new Set(),
		enemyAllianceIds: new Set(),
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
