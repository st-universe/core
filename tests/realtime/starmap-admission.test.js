import assert from "node:assert/strict";
import { test } from "node:test";
import { StarmapAdmission } from "../../realtime/starmap-admission.js";
import { loadServer, socket, authorization, event } from "./support/starmap-server.js";

test("a normal token can initialize only one socket and fanout stays bounded", async () => {
	const server = loadServer();
	const claims = authorization(server);
	const allowed = socket();
	await server.connect(allowed, claims);
	for (let i = 0; i < 12; i++) await assert.rejects(server.connect(socket(), claims), /already used/);
	assert.equal(server.clients.size, 1);
	server.dispatch(event());
	await server.flush();
	assert.equal(allowed.messages.filter(message => message.type === "spacecraftState").length, 1);
	allowed.close();
	await assert.rejects(server.connect(socket(), claims), /already used/);
	const reconnected = socket();
	await server.connect(reconnected, authorization(server));
	assert.equal(reconnected.messages[0].type, "ready");
});

test("per-user reservations include asynchronous initialization and close releases capacity", async () => {
	const completions = [];
	const server = loadServer({ getCoverage: () => new Promise(resolve => completions.push(resolve)) });
	const peers = Array.from({ length: 8 }, () => socket());
	const pending = peers.map(ws => server.connect(ws, authorization(server)));
	await assert.rejects(server.connect(socket(), authorization(server)), /connection limit/);
	peers[0].close();
	const replacement = socket();
	pending.push(server.connect(replacement, authorization(server)));
	for (const resolve of completions) resolve(null);
	await Promise.all(pending);
	assert.equal(server.clients.size, 8);
});

test("global and transport-IP limits cannot be bypassed with different account tokens", async () => {
	for (const limits of [{ global: 2 }, { ip: 2 }]) {
		const server = loadServer({ limits });
		await server.connect(socket(), authorization(server, { userId: 1 }));
		await server.connect(socket(), authorization(server, { userId: 2 }));
		await assert.rejects(server.connect(socket(), authorization(server, { userId: 3 })), /connection limit/);
		assert.equal(server.clients.size, 2);
	}
});

test("independent addresses and users retain their own admission capacity", async () => {
	const server = loadServer({ limits: { ip: 1, user: 1 } });
	await server.connect(socket(), authorization(server, { userId: 1 }), "192.0.2.1");
	await server.connect(socket(), authorization(server, { userId: 2 }), "192.0.2.2");
	assert.equal(server.clients.size, 2);
});

test("upgrade-rate accounting bounds identities and recovers after the window", () => {
	let now = 0;
	const admission = new StarmapAdmission({ attempts: 2, globalAttempts: 4, trackedIps: 2 }, () => now);
	admission.attempt("one");
	admission.attempt("one");
	assert.throws(() => admission.attempt("one"), /rate exceeded/);
	admission.attempt("two");
	assert.throws(() => admission.attempt("three"), /rate exceeded/);
	assert.equal(admission.attempts.size, 2);
	now = 60000;
	admission.attempt("three");
	assert.equal(admission.attempts.size, 1);
});

test("nonce backend failures release reserved slots and do not authenticate", async () => {
	let fail = true;
	const server = loadServer({ limits: { global: 1 }, setNonce: async () => { if (fail) throw new Error("backend unavailable"); return "OK"; } });
	await assert.rejects(server.connect(socket(), authorization(server)), /backend unavailable/);
	assert.equal(server.clients.size, 0);
	fail = false;
	await server.connect(socket(), authorization(server));
	assert.equal(server.clients.size, 1);
});

for (const nonce of [undefined, "", "not-a-valid-nonce"]) {
	test(`normal authorization rejects malformed nonce ${String(nonce)}`, async () => {
		const server = loadServer();
		await assert.rejects(server.connect(socket(), authorization(server, { nonce })), /invalid authorization nonce/);
		assert.equal(server.clients.size, 0);
	});
}
