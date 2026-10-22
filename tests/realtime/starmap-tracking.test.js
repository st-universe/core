import assert from "node:assert/strict";
import crypto from "node:crypto";
import { test } from "node:test";
import { StarmapTracking, acceptScanToken } from "../../realtime/starmap-tracking.js";

function source(id, startX, endX) {
	return { id, runs: [{ y: 5, startX, endX }] };
}

function contact(id = 1, x = 5, extra = {}) {
	return { id, x, y: 5, isOwn: false, isFriendly: false, ...extra };
}

function scan(contacts = [contact()], extra = {}) {
	return {
		success: true, layerId: 2, sourceId: 42, x: 5, y: 5,
		spacecrafts: contacts.map(ship => ({ ...ship, userId: 7, isEnemy: true })), ...extra
	};
}

function tracking(sources = [source(42, 4, 6)], contacts = [contact()]) {
	return new StarmapTracking({ sources, contacts });
}

function encryptedScan(scan, secret) {
	const iv = crypto.randomBytes(12);
	const cipher = crypto.createCipheriv("aes-256-gcm", crypto.createHash("sha256").update(secret).digest(), iv);
	const encrypted = Buffer.concat([cipher.update(JSON.stringify(scan)), cipher.final()]);
	return Buffer.concat([iv, cipher.getAuthTag(), encrypted]).toString("base64");
}

function sign(claims = {}, secret = "secret") {
	const body = Buffer.from(JSON.stringify({
		userId: 1, layerId: 2, exp: Math.floor(Date.now() / 1000) + 60, nonce: "0123456789abcdef",
		purpose: "starmapScan", connectionId: "connection", encryptedScan: encryptedScan(scan(), secret), ...claims
	})).toString("base64url");
	return body + "." + crypto.createHmac("sha256", secret).update(body).digest("base64url");
}

test("only a pure enemy scan can create server recognition", () => {
	const state = tracking();
	assert.deepEqual(state.identifiedIds(), []);
	state.applyScan(scan(), "scan1");
	assert.deepEqual(state.identifiedIds(), [1]);
	state.applyScan(scan([contact(), contact(2)], { spacecrafts: [{ ...contact(), userId: 7, isEnemy: true }, { ...contact(2), userId: 8, isEnemy: false }] }), "scan2");
	assert.deepEqual(state.identifiedIds(), []);
});

test("unidentified hostile arrivals erase recognition exactly like neutral arrivals", () => {
	for (const hostile of [false, true]) {
		const state = tracking();
		state.applyScan(scan(), "scan1");
		state.update(contact(2, 5, { isEnemy: hostile }), true);
		assert.deepEqual(state.identifiedIds(), []);
		state.remove(2);
		assert.deepEqual(state.identifiedIds(), []);
	}
});

test("own and allied arrivals do not erase recognition", () => {
	const state = tracking();
	state.applyScan(scan(), "scan1");
	state.update(contact(2, 5, { isOwn: true, isFriendly: true }), true);
	state.update(contact(3, 5, { isFriendly: true }), true);
	assert.deepEqual(state.identifiedIds(), [1]);
});

test("a cohort can split and reunite but a different snapshot cohort resets both groups", () => {
	const state = tracking([source(42, 4, 6)], [contact(), contact(2)]);
	state.applyScan(scan([contact(), contact(2)]), "scan1");
	state.update(contact(2, 6), true, contact(2));
	assert.deepEqual(state.identifiedIds(), [1, 2]);
	state.update(contact(2), true, contact(2, 6));
	assert.deepEqual(state.identifiedIds(), [1, 2]);
	state.update(contact(3, 6), true);
	state.applyScan(scan([contact(3, 6)], { x: 6 }), "scan2");
	state.update(contact(3), true, contact(3, 6));
	assert.deepEqual(state.identifiedIds(), []);
});

test("overlapping sources hand off recognition and the second source continues tracking", () => {
	const state = tracking([source(42, 4, 6), source(43, 6, 9)]);
	state.applyScan(scan(), "scan1");
	state.update(contact(1, 6), true, contact());
	state.update(contact(1, 7), true, contact(1, 6));
	state.update(contact(1, 9), true, contact(1, 7));
	assert.deepEqual(state.identifiedIds(), [1]);
	assert.deepEqual([...state.enemies.get(1).sourceIds], [43]);
});

test("directly adjacent visible fields hand off recognition", () => {
	const state = tracking([source(42, 4, 5), source(43, 6, 8)]);
	state.applyScan(scan(), "scan1");
	state.update(contact(1, 6), true, contact());
	assert.deepEqual(state.identifiedIds(), [1]);
});

test("disconnected coverage does not hand off even when both endpoints are visible", () => {
	const state = tracking([source(42, 4, 5), source(43, 7, 8)]);
	state.applyScan(scan(), "scan1");
	state.update(contact(1, 7), true, contact());
	assert.deepEqual(state.identifiedIds(), []);
});

test("holes inside sensor footprints and unknown contacts on the movement path reset recognition", () => {
	for (const hasBlindField of [true, false]) {
		const sources = hasBlindField ? [source(42, 4, 5), source(43, 7, 9)] : [source(42, 4, 9)];
		const contacts = hasBlindField ? [contact()] : [contact(), contact(2, 6)];
		const state = tracking(sources, contacts);
		state.applyScan(scan(), "scan1");
		state.update(contact(1, 8), true, contact());
		assert.deepEqual(state.identifiedIds(), []);
	}
});

test("unknown cloaked signatures never become red or disclose hostility", () => {
	const state = tracking([source(42, 4, 6)], [contact(1, 5, { isCloakedSignature: true })]);
	state.applyScan(scan([], { spacecrafts: [{ ...contact(), userId: 0, isEnemy: false, isCloakedSignature: true }] }), "scan1");
	assert.deepEqual(state.identifiedIds(), []);
	const known = tracking();
	known.applyScan(scan(), "scan1");
	known.update(contact(2, 5, { isCloakedSignature: true }), true);
	assert.deepEqual(known.identifiedIds(), []);
});

test("new arrivals between snapshot creation and scan authorization prevent red", () => {
	const state = tracking();
	state.update(contact(2), true);
	state.applyScan(scan(), "scan1");
	assert.deepEqual(state.identifiedIds(), []);
});

test("recognition survives coverage refresh with overlapping sources but not loss of all prior observers", () => {
	const sources = [source(42, 4, 6), source(43, 5, 8)];
	const state = tracking(sources);
	state.applyScan(scan(), "scan1");
	state.refresh({ sources: [sources[1]], contacts: [contact()] });
	assert.deepEqual(state.identifiedIds(), [1]);
	state.refresh({ sources: [source(44, 5, 8)], contacts: [contact()] });
	assert.deepEqual(state.identifiedIds(), []);
});

test("leaving coverage and reconnecting require another paid scan", () => {
	const state = tracking();
	state.applyScan(scan(), "scan1");
	state.update(contact(1, 7), false, contact());
	assert.deepEqual(state.identifiedIds(), []);
	assert.deepEqual(tracking().identifiedIds(), []);
});

test("only an authentic paid scan token bound to this account, layer and connection can be accepted", async () => {
	const client = { userId: 1, layerId: 2, connectionId: "connection", tracking: tracking() };
	for (const token of [sign({}, "wrong"), sign({ userId: 2 }), sign({ layerId: 3 }), sign({ connectionId: "other" }),
		sign({ purpose: "starmap" }), sign({ exp: 1 })]) {
		await assert.rejects(acceptScanToken(client, token, "secret", async () => { throw new Error("must not consume"); }));
		assert.deepEqual(client.tracking.identifiedIds(), []);
	}
	await acceptScanToken(client, sign(), "secret", async () => true);
	assert.deepEqual(client.tracking.identifiedIds(), [1]);
});

test("replaying a paid scan token cannot restore recognition after an unknown arrival", async () => {
	const client = { userId: 1, layerId: 2, connectionId: "connection", tracking: tracking() };
	const consumed = new Set();
	const consume = async nonce => {
		if (consumed.has(nonce)) return false;
		consumed.add(nonce);
		return true;
	};
	const token = sign();
	await acceptScanToken(client, token, "secret", consume);
	client.tracking.update(contact(2), true);
	client.tracking.remove(2);
	await assert.rejects(acceptScanToken(client, token, "secret", consume), /already consumed/);
	assert.deepEqual(client.tracking.identifiedIds(), []);
});
