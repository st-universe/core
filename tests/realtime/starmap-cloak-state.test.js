import assert from "node:assert/strict";
import { test } from "node:test";
import { loadServer, socket, authorization, event } from "./support/starmap-server.js";

const runs = [{ y: 5, startX: 1, endX: 10 }];
function coverage(overrides = {}) {
	return JSON.stringify({ runs, tachyonRuns: [], contacts: [], ...overrides });
}
function hiddenEvent(overrides = {}) {
	return event("spacecraftState", {
		spacecraft: { id: 7, userId: 102, allianceId: 6, x: 5, y: 5, name: "Hidden ship", isCloaked: true },
		...overrides
	});
}

test("ordinary coverage cannot reveal an unknown cloaked ship after RPG unhide", async () => {
	const server = loadServer({ getCoverage: async () => coverage() });
	const ws = socket();
	await server.connect(ws, authorization(server, { sharedUserIds: [] }));
	const handle = [...server.clients][0].contacts.handles.get(7);
	ws.messages.length = 0;
	server.dispatch(hiddenEvent());
	await server.flush();
	assert.deepEqual(ws.messages, []);
});

test("previously known contacts receive only one coordinate-free removal when hidden", async () => {
	const server = loadServer({ getCoverage: async () => coverage({ contacts: [{ id: 7, x: 2, y: 5 }], spacecrafts: [{ id: 7, x: 2, y: 5, isSensorContact: true }] }) });
	const ws = socket();
	await server.connect(ws, authorization(server, { sharedUserIds: [] }));
	const handle = [...server.clients][0].contacts.handles.get(7);
	ws.messages.length = 0;
	server.dispatch(hiddenEvent());
	await server.flush();
	assert.equal(ws.messages.length, 1);
	assert.deepEqual(ws.messages[0], { type: "spacecraftRemoval", layerId: 2, spacecraft: { id: handle } });
	assert.equal([...server.clients][0].tracking.contacts.has(7), false);
	ws.messages.length = 0;
	server.dispatch(hiddenEvent({ position: { x: 8, y: 5 } }));
	await server.flush();
	assert.deepEqual(ws.messages, []);
});

test("cloak activation removes a contact that was delivered over ordinary sensors", async () => {
	const server = loadServer({ getCoverage: async () => coverage() });
	const ws = socket();
	await server.connect(ws, authorization(server, { sharedUserIds: [] }));
	server.dispatch(event());
	await server.flush();
	assert.equal(ws.messages.some(message => message.type === "spacecraftState" && message.visible), true);
	const handle = [...server.clients][0].contacts.handles.get(7);
	ws.messages.length = 0;
	server.dispatch(hiddenEvent());
	await server.flush();
	assert.deepEqual(ws.messages, [{ type: "spacecraftRemoval", layerId: 2, spacecraft: { id: handle } }]);
});

for (const [name, grants, tachyonRuns, owner] of [
	["tachyon sensors", {}, runs, 102],
	["own spacecraft", {}, [], 101],
	["explicit player sharing", { sharedUserIds: [102] }, [], 102],
	["explicit alliance sharing", { sharedAllianceIds: [6] }, [], 102],
	["alliance ship permission", { allianceId: 6, canSeeAllianceShips: true }, [], 102]
]) {
	test(`${name} still allows cloaked state delivery`, async () => {
		const server = loadServer({ getCoverage: async () => coverage({ tachyonRuns }) });
		const ws = socket();
		await server.connect(ws, authorization(server, { sharedUserIds: [], ...grants }));
		ws.messages.length = 0;
		const payload = hiddenEvent();
		payload.spacecraft.userId = owner;
		server.dispatch(payload);
		await server.flush();
		assert.equal(ws.messages.some(message => message.type === "spacecraftState" && message.position.x === 5), true);
	});
}
