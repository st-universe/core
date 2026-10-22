import assert from "node:assert/strict";
import { test } from "node:test";
import { loadServer, socket, authorization, event } from "./support/starmap-server.js";

test("RPG invalidation discloses neither identities nor coordinates to unaware observers", async () => {
	const server = loadServer({ getCoverage: async () => JSON.stringify({ runs: [{ y: 5, startX: 1, endX: 10 }] }) });
	const ws = socket();
	await server.connect(ws, authorization(server));
	ws.messages.length = 0;
	server.dispatch({ type: "spacecraftInvalidation", layerId: 2, spacecraft: { id: 7 } });
	await server.flush();
	assert.deepEqual(ws.messages, []);
});

test("enabling RPG invisibility clears only previously delivered contact handles", async () => {
	const server = loadServer();
	const known = socket();
	const unaware = socket();
	await server.connect(known, authorization(server));
	await server.connect(unaware, authorization(server, { sharedUserIds: [] }));
	server.dispatch(event());
	await server.flush();
	known.messages.length = 0;
	unaware.messages.length = 0;
	server.dispatch({ type: "spacecraftInvalidation", layerId: 2, spacecraft: { id: 7 } });
	await server.flush();
	assert.deepEqual(known.messages, [{ type: "spacecraftRemoval", spacecraft: { id: 7 } }]);
	assert.deepEqual(unaware.messages, []);
	assert.equal([...server.clients][0].tracking.contacts.has(7), false);
});
