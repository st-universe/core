import assert from "node:assert/strict";
import { test } from "node:test";
import { StarmapMessageBudget, STARMAP_MAX_PAYLOAD } from "../../realtime/starmap-message-budget.js";
import { loadServer, socket, authorization, event, signToken } from "./support/starmap-server.js";

for (const raw of ["{", "null", "[]", '"text"', '{"type":"unknown"}', '{"type":"refresh"}', '{"type":"scan","token":1}', '{"type":"resetTracking","extra":"value"}']) {
	test(`malformed envelope is rejected before queueing: ${raw}`, async () => {
		const server = loadServer();
		const ws = socket();
		await server.connect(ws, authorization(server));
		const client = [...server.clients][0];
		ws.emit("message", raw);
		assert.equal(ws.closeCode, 1008);
		assert.equal(client.budget.queuedTasks, 0);
		assert.equal(client.budget.queuedBytes, 0);
	});
}

test("binary frames and oversized messages cannot enter the work queue", () => {
	const budget = new StarmapMessageBudget();
	assert.throws(() => budget.read(Buffer.from('{"type":"resetTracking"}'), true), /invalid realtime frame/);
	assert.throws(() => budget.read("a".repeat(STARMAP_MAX_PAYLOAD + 1), false), /invalid realtime frame/);
	assert.equal(budget.queuedTasks, 0);
	assert.equal(budget.queuedBytes, 0);
});

test("queued bytes stay below the cap while an earlier Redis request is blocked", async () => {
	let resolveCoverage;
	let calls = 0;
	const server = loadServer({ getCoverage: async () => ++calls === 1 ? null : new Promise(resolve => { resolveCoverage = resolve; }) });
	const ws = socket();
	await server.connect(ws, authorization(server));
	const client = [...server.clients][0];
	server.advance(61000);
	server.dispatch(event());
	await new Promise(setImmediate);
	for (let i = 0; i < 3; i++) ws.emit("message", JSON.stringify({ type: "scan", token: "a".repeat(200 * 1024) }));
	assert.equal(ws.closeCode, 1013);
	assert.ok(client.budget.queuedBytes <= 512 * 1024);
	assert.equal(client.budget.queuedTasks, 3);
	resolveCoverage(null);
	await client.queue;
	assert.equal(client.budget.queuedBytes, 0);
	assert.equal(client.budget.queuedTasks, 0);
});

test("event fanout cannot retain an unbounded per-client task chain", async () => {
	const server = loadServer();
	const ws = socket();
	await server.connect(ws, authorization(server));
	const client = [...server.clients][0];
	for (let i = 0; i < 129; i++) server.dispatch(event());
	assert.equal(ws.closeCode, 1013);
	assert.equal(client.budget.queuedTasks, 128);
	await client.queue;
	assert.equal(client.budget.queuedTasks, 0);
});

test("a command flood closes its sender while an independent client remains usable", async () => {
	const server = loadServer();
	const abusive = socket();
	const valid = socket();
	await server.connect(abusive, authorization(server));
	await server.connect(valid, authorization(server));
	const client = [...server.clients][0];
	for (let i = 0; i < 61; i++) abusive.emit("message", '{"type":"resetTracking"}');
	assert.equal(abusive.closeCode, 1008);
	assert.equal(valid.readyState, 1);
	await client.queue;
	valid.emit("message", '{"type":"resetTracking"}');
	await server.flush();
	assert.equal(valid.messages.at(-1).type, "tracking");
});

test("normal refresh and tracking commands continue after a message window resets", async () => {
	const server = loadServer();
	const ws = socket();
	await server.connect(ws, authorization(server));
	const client = [...server.clients][0];
	for (let i = 0; i < 60; i++) {
		ws.emit("message", '{"type":"resetTracking"}');
		await client.queue;
	}
	server.advance(10000);
	ws.emit("message", JSON.stringify({ type: "refresh", token: signToken(authorization(server)) }));
	await client.queue;
	assert.equal(ws.readyState, 1);
	assert.equal(client.budget.messages, 1);
	assert.equal(client.budget.queuedTasks, 0);
});

test("identical pending refresh messages share one queued task", async () => {
	const server = loadServer();
	const ws = socket();
	await server.connect(ws, authorization(server));
	const client = [...server.clients][0];
	const refresh = JSON.stringify({ type: "refresh", token: signToken(authorization(server)) });
	for (let i = 0; i < 10; i++) ws.emit("message", refresh);
	assert.equal(client.budget.queuedTasks, 1);
	await client.queue;
	assert.equal(ws.readyState, 1);
	assert.equal(client.budget.queuedTasks, 0);
	assert.equal(client.budget.queuedBytes, 0);
});

test("invalid signed authorization cannot keep an authenticated command channel alive", async () => {
	const server = loadServer();
	const ws = socket();
	await server.connect(ws, authorization(server));
	const client = [...server.clients][0];
	ws.emit("message", JSON.stringify({ type: "refresh", token: "invalid" }));
	await client.queue;
	assert.equal(ws.closeCode, 1008);
	assert.equal(server.clients.size, 0);
});
