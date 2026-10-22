import assert from "node:assert/strict";
import { test } from "node:test";
import { loadServer, socket, authorization, event } from "./support/starmap-server.js";

for (const reason of ["close", "error"]) {
	test(`early ${reason} cannot register a client after coverage completes`, async () => {
		let finishCoverage;
		const server = loadServer({ getCoverage: () => new Promise(resolve => { finishCoverage = resolve; }) });
		const ws = socket();
		const connection = server.connect(ws, authorization(server));
		assert.equal(ws.listenerCount("close"), 1);
		assert.equal(ws.listenerCount("error"), 1);
		if (reason === "close") ws.close();
		else ws.emit("error", new Error("protocol error"));
		finishCoverage(null);
		await connection;
		assert.equal(server.clients.size, 0);
		assert.equal(server.timers.size, 0);
		assert.deepEqual(ws.messages, []);
	});
}

test("repeated disconnects during initialization do not accumulate clients or timers", async () => {
	const completions = [];
	const server = loadServer({ getCoverage: () => new Promise(resolve => completions.push(resolve)) });
	const connections = [];
	for (let index = 0; index < 100; index++) {
		const ws = socket();
		connections.push(server.connect(ws, authorization(server)));
		ws.close();
	}
	for (const complete of completions) complete(null);
	await Promise.all(connections);
	assert.equal(server.clients.size, 0);
	assert.equal(server.timers.size, 0);
});

for (const reason of ["close", "error"]) {
	test(`initialized client cleanup runs on ${reason} while valid peers continue`, async () => {
		const server = loadServer();
		const broken = socket();
		const valid = socket();
		await server.connect(broken, authorization(server));
		await server.connect(valid, authorization(server));
		assert.equal(server.clients.size, 2);
		assert.equal(server.timers.size, 2);
		if (reason === "close") broken.close();
		else broken.emit("error", new Error("protocol error"));
		assert.equal(server.clients.size, 1);
		assert.equal(server.timers.size, 1);
		valid.messages.length = 0;
		server.dispatch(event());
		await server.flush();
		assert.equal(valid.messages.some(message => message.type === "spacecraftState"), true);
	});
}

test("event dispatch prunes closed clients even without a close callback", async () => {
	const server = loadServer();
	const ws = socket();
	await server.connect(ws, authorization(server));
	ws.readyState = 3;
	server.dispatch(event());
	assert.equal(server.clients.size, 0);
	assert.equal(server.timers.size, 0);
});
