import assert from "node:assert/strict";
import { test } from "node:test";
import { StarmapContacts } from "../../realtime/starmap-contacts.js";
import { loadServer, socket, authorization, event } from "./support/starmap-server.js";

const foreign = { id: 987654, userId: 103, x: 5, y: 5, isSensorContact: true };

test("anonymous handles are private to observation lifetimes and never database ids", () => {
	const contacts = new StarmapContacts();
	const first = contacts.serialize(foreign);
	assert.ok(Number.isSafeInteger(first.id) && first.id < 0);
	assert.equal(contacts.serialize({ ...foreign, x: 6 }).id, first.id);
	assert.notEqual(new StarmapContacts().serialize(foreign).id, first.id);
	contacts.forget(foreign.id);
	assert.notEqual(contacts.serialize(foreign).id, first.id);
});

test("recognition loss rotates all anonymous handles without a translation oracle", () => {
	const contacts = new StarmapContacts();
	const old = contacts.serialize(foreign).id;
	contacts.serialize({ id: 42, isOwn: true });
	contacts.resetRecognition([foreign.id]);
	assert.equal(contacts.resetRecognition([]), true);
	const snapshot = contacts.snapshot();
	assert.notEqual(snapshot.find(c => !c.isOwn).id, old);
	assert.equal(snapshot.find(c => c.isOwn).id, 42);
	assert.equal(JSON.stringify(snapshot).includes(String(foreign.id)), false);
});

test("cloak and revoked friendly identity rotate contact handles", () => {
	const contacts = new StarmapContacts();
	const old = contacts.serialize(foreign).id;
	assert.notEqual(contacts.serialize({ ...foreign, isCloakedSignature: true }).id, old);
	assert.equal(contacts.serialize({ ...foreign, isFriendly: true }).id, foreign.id);
	assert.ok(contacts.serialize(foreign).id < 0);
});

test("websocket reconnect snapshots and subsequent events use opaque foreign handles", async () => {
	const raw = JSON.stringify({ snapshotVersion: "a", runs: [{ y: 5, startX: 1, endX: 10 }], spacecrafts: [foreign], contacts: [foreign] });
	const server = loadServer({ getCoverage: async () => raw });
	const ws = socket();
	await server.connect(ws, authorization(server, { friendlyUserIds: [], sharedUserIds: [] }));
	const handle = ws.messages.find(message => message.type === "ready").spacecrafts[0].id;
	assert.ok(handle < 0);
	server.dispatch(event("spacecraftState", { spacecraft: foreign }));
	await server.flush();
	assert.equal(ws.messages.find(message => message.type === "spacecraftState").spacecraft.id, handle);
	const second = socket();
	await server.connect(second, authorization(server, { friendlyUserIds: [], sharedUserIds: [] }));
	assert.notEqual(second.messages.find(message => message.type === "ready").spacecrafts[0].id, handle);
});
