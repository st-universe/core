import assert from "node:assert/strict";
import { test } from "node:test";
import { loadServer, socket, authorization, event, signToken } from "./support/starmap-server.js";

for (const type of ["spacecraftMovement", "spacecraftState", "spacecraftRemoval", "coverageChanged"]) {
	test(`${type} stops when authorization expires even before the timer runs`, async () => {
		const server = loadServer();
		const ws = socket();
		await server.connect(ws, authorization(server));
		server.dispatch(event(type));
		await server.flush();
		assert.equal(ws.messages.some(message => message.type === type), true);
		ws.messages.length = 0;
		server.advance(300000, false);
		server.dispatch(event(type));
		await server.flush();
		assert.deepEqual(ws.messages, []);
		assert.equal(ws.closeCode, 1008);
		assert.equal(server.clients.size, 0);
	});
}

test("idle sockets expire without client messages or map events", async () => {
	const server = loadServer();
	const ws = socket();
	await server.connect(ws, authorization(server));
	server.advance(300000);
	assert.equal(ws.closeCode, 1008);
	assert.equal(server.clients.size, 0);
});

test("expired commands cannot reset tracking or produce output", async () => {
	const server = loadServer();
	const ws = socket();
	await server.connect(ws, authorization(server));
	const client = [...server.clients][0];
	client.tracking.enemies.set(7, {});
	ws.messages.length = 0;
	server.advance(300000, false);
	ws.emit("message", JSON.stringify({ type: "resetTracking" }));
	await client.queue;
	assert.equal(client.tracking.enemies.size, 1);
	assert.deepEqual(ws.messages, []);
});

test("fresh same-user authorization renews expiry and replaces sharing grants", async () => {
	const server = loadServer();
	const ws = socket();
	await server.connect(ws, authorization(server));
	server.advance(240000);
	ws.emit("message", JSON.stringify({ type: "refresh", token: signToken(authorization(server, { sharedUserIds: [] })) }));
	await server.flush();
	server.advance(60000);
	assert.equal(ws.readyState, 1);
	ws.messages.length = 0;
	server.dispatch(event());
	await server.flush();
	assert.deepEqual(ws.messages, []);
	server.dispatch(event("spacecraftState", { spacecraft: { id: 8, userId: 101 } }));
	await server.flush();
	assert.equal(ws.messages.some(message => message.spacecraft?.id === 8), true);
	server.advance(240000);
	assert.equal(ws.closeCode, 1008);
});

test("refresh for another user cannot replace grants or extend expiry", async () => {
	const server = loadServer();
	const ws = socket();
	await server.connect(ws, authorization(server));
	const client = [...server.clients][0];
	const exp = client.exp;
	ws.emit("message", JSON.stringify({ type: "refresh", token: signToken(authorization(server, { userId: 103, exp: exp + 300 })) }));
	await server.flush();
	assert.equal(client.exp, exp);
	assert.equal(client.userId, 101);
	server.advance(300000);
	assert.equal(ws.closeCode, 1008);
});

test("events awaiting coverage cannot send data after authorization expires", async () => {
	let resolveCoverage;
	let calls = 0;
	const server = loadServer({ getCoverage: async () => ++calls === 1 ? null : await new Promise(resolve => { resolveCoverage = resolve; }) });
	const ws = socket();
	await server.connect(ws, authorization(server));
	server.advance(61000);
	ws.messages.length = 0;
	server.dispatch(event());
	await new Promise(setImmediate);
	server.advance(240000, false);
	const client = [...server.clients][0];
	resolveCoverage(null);
	await client.queue;
	assert.deepEqual(ws.messages, []);
	assert.equal(ws.closeCode, 1008);
});
