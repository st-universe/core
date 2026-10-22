import assert from "node:assert/strict";
import { test } from "node:test";
import { loadServer, socket, authorization, event } from "./support/starmap-server.js";

for (const concealment of ["hidden", "dubious"]) {
	for (const type of ["spacecraftState", "spacecraftRemoval", "spacecraftMovement"]) {
		test(`${concealment} suppresses individual ${type} even through shared live positions`, async () => {
			const server = loadServer({ getCoverage: async () => JSON.stringify({ runs: [{ y: 5, startX: 1, endX: 10 }] }) });
			const ws = socket();
			await server.connect(ws, authorization(server));
			ws.messages.length = 0;
			const point = { x: 5, y: 5, signatureConcealment: concealment };
			server.dispatch(event(type, { position: point, from: point, to: point }));
			await server.flush();
			assert.equal(ws.messages.some(message => message.spacecraft || message.from || message.to || message.position), false);
			assert.equal(JSON.stringify(ws.messages).includes('"id":7'), false);
			assert.equal(ws.messages.some(message => message.type === "signatureInterference"), concealment === "dubious");
		});
	}
	test(`${concealment} retains own and explicitly authorized alliance details`, async () => {
		for (const own of [true, false]) {
			const server = loadServer();
			const ws = socket();
			await server.connect(ws, authorization(server, { allianceId: 6, canSeeAllianceShips: true }));
			ws.messages.length = 0;
			server.dispatch(event("spacecraftState", {
				spacecraft: { id: 7, userId: own ? 101 : 102, allianceId: 6, x: 5, y: 5 },
				position: { x: 5, y: 5, signatureConcealment: concealment }
			}));
			await server.flush();
			assert.equal(ws.messages.some(message => message.type === "spacecraftState" && message.spacecraft.id === 7), true);
		}
	});
}
