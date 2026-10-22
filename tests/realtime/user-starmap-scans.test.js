import assert from "node:assert/strict";
import fs from "node:fs";
import vm from "node:vm";
import { test } from "node:test";

const source = fs.readFileSync(new URL("../../src/Public/static/js/userStarmap.js", import.meta.url), "utf8");

function loadMap(fetch) {
	const window = {};
	vm.runInNewContext(source.replace("window.initUserStarmap = initUserStarmap;", `
		window.map = { handleRealtimeMessage, getContactStatsForField, addSensorSpacecraft, removeSensorSpacecraft,
			scanField, appendSpacecraftTooltipHtml, buildScanControlsHtml };
	`), {
		window, fetch, URLSearchParams, requestAnimationFrame: () => 1,
		document: { readyState: "loading", addEventListener() {} }
	});
	return window.map;
}

function createState() {
	return {
		sensorSources: new Map([[42, { id: 42, name: "Phalanx", eps: 30, keys: new Set(["5:5", "6:5"]) }]]),
		scanEnergyCost: 15, identifiedEnemyIds: new Set(), connectionId: "connection", sensorSpacecraftById: new Map(), sensorSpacecraftByField: new Map(),
		spacecraftById: new Map(), spacecraftByField: new Map(), fieldByKey: new Map(), scanSnapshots: new Map(),
		scanSourceByField: new Map(), scanPending: false, scanMessage: "", selectedField: null,
		fieldDetails: { innerHTML: "" }, tooltip: { style: {} }, dataLoaded: false, drawQueued: false,
		showSensorContacts: true, scanUrl: "starmap.php?B_SCAN_USER_STARMAP=1", sessionString: "before", layerId: 2
	};
}

function enemy(id = 1, x = 5) {
	return { id, x, y: 5, isEnemy: true, isOwn: false, isFriendly: false, nameHtml: "Enemy ship" };
}

function stats(map, state, x = 5) {
	return map.getContactStatsForField(state, x, 5, state.sensorSpacecraftByField.get(x + ":5")?.spacecrafts || []);
}

test("live contacts stay neutral until the server confirms scan recognition", () => {
	const map = loadMap();
	const state = createState();
	map.addSensorSpacecraft(state, { ...enemy(), isEnemy: false });
	assert.equal(stats(map, state).enemyCount, 0);
	map.handleRealtimeMessage(state, JSON.stringify({ type: "tracking", spacecraftIds: [1] }));
	assert.equal(stats(map, state).enemyCount, 1);
	map.addSensorSpacecraft(state, { ...enemy(2), isEnemy: false });
	assert.equal(stats(map, state).enemyCount, 0);
	map.handleRealtimeMessage(state, JSON.stringify({ type: "tracking", spacecraftIds: [] }));
	map.removeSensorSpacecraft(state, 2);
	assert.equal(stats(map, state).enemyCount, 0);
});

test("a paid scan stores a fixed snapshot, renews the session token and updates the chosen source energy", async () => {
	let request;
	const map = loadMap(async (url, options) => {
		request = { url, options };
		return { ok: true, json: async () => ({ sessionString: "after", result: {
			success: true, sourceId: 42, eps: 15, scannedAt: 123, x: 5, y: 5, spacecrafts: [enemy()]
		} }) };
	});
	const state = createState();
	map.addSensorSpacecraft(state, enemy());
	map.scanField(state, { x: 5, y: 5 }, 42);
	await new Promise(setImmediate);
	assert.equal(request.options.method, "POST");
	assert.equal(request.options.body.get("sourceid"), "42");
	assert.equal(request.options.body.get("sstr"), "before");
	assert.equal(state.sessionString, "after");
	assert.equal(state.sensorSources.get(42).eps, 15);
	assert.equal(state.scanPending, false);
	assert.equal(state.identifiedEnemyIds.size, 0);
	map.removeSensorSpacecraft(state, 1);
	map.addSensorSpacecraft(state, enemy(1, 6));
	assert.equal(state.scanSnapshots.get("5:5").spacecrafts[0].x, 5);
	assert.equal(state.scanSnapshots.has("6:5"), false);
});

test("failed scans preserve previous snapshots and cannot reveal or identify new contacts", async () => {
	const map = loadMap(async () => ({ ok: true, json: async () => ({ sessionString: "after", result: {
		success: false, sourceId: 42, eps: 0, message: "Nicht genügend Energie"
	} }) }));
	const state = createState();
	const previous = { scannedAt: 100, spacecrafts: [] };
	state.scanSnapshots.set("5:5", previous);
	map.addSensorSpacecraft(state, enemy());
	map.scanField(state, { x: 5, y: 5 }, 42);
	await new Promise(setImmediate);
	assert.equal(state.scanSnapshots.get("5:5"), previous);
	assert.equal(state.identifiedEnemyIds.size, 0);
	assert.equal(state.scanMessage, "Nicht genügend Energie");
});

test("scan recognition is requested with the paid token on the same live connection", async () => {
	let request;
	const messages = [];
	const map = loadMap(async (_url, options) => {
		request = options;
		return { ok: true, json: async () => ({ result: {
			success: true, eps: 15, scannedAt: 123, x: 5, y: 5, spacecrafts: [enemy()], trackingToken: "paid-token"
		} }) };
	});
	const state = createState();
	state.realtimeSocket = { readyState: 1, send: value => messages.push(JSON.parse(value)) };
	map.scanField(state, { x: 5, y: 5 }, 42);
	await new Promise(setImmediate);
	assert.equal(request.body.get("connectionid"), "connection");
	assert.deepEqual(messages, [{ type: "scan", token: "paid-token" }]);
	assert.equal(state.identifiedEnemyIds.size, 0);
});

test("a scan response from a disconnected session cannot identify contacts on a new connection", async () => {
	const messages = [];
	let reply;
	const map = loadMap(() => new Promise(resolve => { reply = resolve; }));
	const state = createState();
	state.realtimeSocket = { readyState: 1, send: value => messages.push(JSON.parse(value)) };
	map.scanField(state, { x: 5, y: 5 }, 42);
	state.connectionId = "new-connection";
	reply({ ok: true, json: async () => ({ result: {
		success: true, eps: 15, scannedAt: 123, x: 5, y: 5, spacecrafts: [enemy()], trackingToken: "old-token"
	} }) });
	await new Promise(setImmediate);
	assert.deepEqual(messages, []);
	assert.equal(state.scanSnapshots.has("5:5"), true);
});

test("mixed scans do not identify enemies and source selection contains only sources covering the field", async () => {
	const map = loadMap(async () => ({ ok: true, json: async () => ({ result: {
		success: true, eps: 15, scannedAt: 123, x: 5, y: 5, spacecrafts: [enemy(), { id: 2, x: 5, y: 5 }]
	} }) }));
	const state = createState();
	map.addSensorSpacecraft(state, enemy());
	state.sensorSources.set(43, { id: 43, name: "Basis", eps: 50, keys: new Set(["5:5"]) });
	state.sensorSources.set(44, { id: 44, name: "Außer Reichweite", eps: 50, keys: new Set(["9:9"]) });
	const html = map.buildScanControlsHtml(state, { x: 5, y: 5 });
	assert.match(html, /Phalanx/);
	assert.match(html, /Basis/);
	assert.doesNotMatch(html, /Außer Reichweite/);
	map.scanField(state, { x: 5, y: 5 }, 42);
	await new Promise(setImmediate);
	assert.equal(state.identifiedEnemyIds.size, 0);
});

test("hover shows friendly live contacts and scanned foreign contacts without empty enemy headings", () => {
	const map = loadMap();
	const state = createState();
	map.addSensorSpacecraft(state, { ...enemy(), isUnscannedSignature: true });
	map.addSensorSpacecraft(state, { id: 2, x: 5, y: 5, isFriendly: true, nameHtml: "Ally", userNameHtml: "Friend" });
	let html = [];
	map.appendSpacecraftTooltipHtml(state, { x: 5, y: 5 }, html);
	assert.match(html.join(""), /Ally/);
	assert.doesNotMatch(html.join(""), /Enemy ship|Feinde/);
	state.scanSnapshots.set("5:5", { scannedAt: 123, spacecrafts: [enemy()] });
	html = [];
	map.appendSpacecraftTooltipHtml(state, { x: 5, y: 5 }, html);
	assert.match(html.join(""), /Enemy ship|Feinde/);
	assert.match(html.join(""), /Scanstand/);
});

test("coordinate-free removal clears a previously known marker and its animation", () => {
	const map = loadMap();
	const state = createState();
	state.contactAnimations = new Map([[1, { from: { x: 5, y: 5 } }]]);
	map.addSensorSpacecraft(state, enemy());
	map.handleRealtimeMessage(state, JSON.stringify({ type: "spacecraftRemoval", layerId: 2, spacecraft: { id: 1 } }));
	assert.equal(state.sensorSpacecraftById.has(1), false);
	assert.equal(state.sensorSpacecraftByField.has("5:5"), false);
	assert.equal(state.contactAnimations.has(1), false);
});

test("connection snapshots replace old anonymous handles and leave scan history fixed", () => {
	const map = loadMap();
	const state = createState();
	state.contactAnimations = new Map();
	state.contactAnimationFrame = 0;
	state.realtimeStaticSpacecraftIds = new Set();
	state.status = {};
	map.addSensorSpacecraft(state, { ...enemy(-1), isEnemy: false });
	state.scanSnapshots.set("5:5", { spacecrafts: [enemy(-8)] });
	map.handleRealtimeMessage(state, JSON.stringify({ type: "ready", connectionId: "new", spacecrafts: [{ ...enemy(-2), isSensorContact: true }] }));
	assert.equal(state.connectionId, "new");
	assert.equal(state.sensorSpacecraftById.has(-1), false);
	assert.equal(state.sensorSpacecraftById.has(-2), true);
	map.handleRealtimeMessage(state, JSON.stringify({ type: "contactsSnapshot", spacecrafts: [{ ...enemy(-3), isSensorContact: true }] }));
	assert.equal(state.sensorSpacecraftById.has(-2), false);
	assert.equal(state.sensorSpacecraftById.has(-3), true);
	assert.equal(state.scanSnapshots.get("5:5").spacecrafts[0].id, -8);
});
