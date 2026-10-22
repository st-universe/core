import assert from "node:assert/strict";
import { test } from "node:test";
import { loadServer, socket, authorization, event } from "./support/starmap-server.js";

test("authoritative invalidation defeats a stale Redis snapshot without browser cooperation", async () => {
	let generation = 0;
	const runs = [{ y: 5, startX: 1, endX: 10 }];
	const coverage = JSON.stringify({ generation: 0, snapshotVersion: "old", runs, contacts: [] });
	const server = loadServer({ getCoverage: async () => coverage, getGeneration: async () => String(generation) });
	const ws = socket();
	await server.connect(ws, authorization(server, { sharedUserIds: [] }));
	server.dispatch(event());
	await server.flush();
	assert.equal(ws.messages.some(message => message.type === "spacecraftState"), true);
	ws.messages.length = 0;
	generation++;
	server.dispatch(event("spacecraftMovement"));
	await server.flush();
	assert.equal(ws.messages.some(message => message.spacecraft || message.from || message.to || message.position), false);
	assert.equal([...server.clients][0].coverage.runs.length, 0);
});

test("all users of a shared source lose stale coverage and regain only fresh authorized snapshots", async () => {
	let generation = 0;
	let snapshotGeneration = 0;
	const runs = [{ y: 5, startX: 1, endX: 10 }];
	const server = loadServer({
		getCoverage: async () => JSON.stringify({ generation: snapshotGeneration, snapshotVersion: "v" + snapshotGeneration, runs }),
		getGeneration: async () => String(generation)
	});
	const sockets = [socket(), socket()];
	for (let i = 0; i < sockets.length; i++) await server.connect(sockets[i], authorization(server, { userId: 101 + i, sharedUserIds: [], friendlyUserIds: [] }));
	generation++;
	server.dispatch({ type: "coverageChanged", layerId: 2 });
	server.dispatch(event("spacecraftState", { spacecraft: { id: 7, userId: 104, x: 5, y: 5 } }));
	await server.flush();
	for (const ws of sockets) assert.equal(ws.messages.some(message => message.type === "spacecraftState"), false);
	snapshotGeneration = generation;
	server.advance(60001, false);
	server.dispatch(event("spacecraftState", { spacecraft: { id: 7, userId: 104, x: 5, y: 5 } }));
	await server.flush();
	for (const ws of sockets) assert.equal(ws.messages.some(message => message.type === "spacecraftState"), true);
});

test("a failed authoritative generation lookup cannot fall back to old local coverage", async () => {
	let unavailable = false;
	const server = loadServer({
		getCoverage: async () => JSON.stringify({ generation: 0, runs: [{ y: 5, startX: 1, endX: 10 }] }),
		getGeneration: async () => { if (unavailable) throw new Error("isolated Redis failure"); return "0"; }
	});
	const ws = socket();
	await server.connect(ws, authorization(server, { sharedUserIds: [] }));
	ws.messages.length = 0;
	unavailable = true;
	server.dispatch(event());
	await server.flush();
	assert.equal(ws.messages.some(message => message.spacecraft || message.position), false);
});
