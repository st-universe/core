import assert from "node:assert/strict";
import { test } from "node:test";
import { loadServer, socket, authorization } from "./support/starmap-server.js";

test("rejected authentication retains a transport error handler", async () => {
	const server = loadServer();
	const ws = socket();
	await assert.rejects(server.connect(ws, authorization(server, { purpose: "invalid" })), /invalid token purpose/);
	assert.equal(ws.listenerCount("error"), 1);
	assert.doesNotThrow(() => ws.emit("error", new Error("Invalid WebSocket frame: invalid opcode 7")));
	assert.equal(ws.terminated, true);
	assert.equal(server.clients.size, 0);
});

test("protocol errors during asynchronous coverage loading are contained", async () => {
	let finishCoverage;
	const server = loadServer({ getCoverage: () => new Promise(resolve => { finishCoverage = resolve; }) });
	const ws = socket();
	const connection = server.connect(ws, authorization(server));
	assert.equal(ws.listenerCount("error"), 1);
	assert.doesNotThrow(() => ws.emit("error", new Error("Invalid WebSocket frame: invalid opcode 7")));
	assert.equal(ws.terminated, true);
	finishCoverage(null);
	await connection;
	assert.deepEqual(ws.messages, []);
});

test("a faulty peer does not prevent other valid connections", async () => {
	const server = loadServer();
	const invalid = socket();
	await assert.rejects(server.connect(invalid, authorization(server, { purpose: "invalid" })));
	invalid.emit("error", new Error("protocol error"));
	const valid = socket();
	await server.connect(valid, authorization(server));
	assert.equal(valid.messages[0].type, "ready");
	assert.equal(valid.readyState, 1);
});
