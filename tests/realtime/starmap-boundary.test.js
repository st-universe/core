import assert from "node:assert/strict";
import { test } from "node:test";
import { loadServer, socket, authorization, event } from "./support/starmap-server.js";

for (const isCloaked of [false, true]) {
	for (const leaving of [false, true]) {
		test(`${isCloaked ? "tachyon" : "ordinary"} boundary ${leaving ? "departure" : "arrival"} excludes hidden coordinates and direction`, async () => {
			const runs = [{ y: 5, startX: 1, endX: 5 }];
			const server = loadServer({ getCoverage: async () => JSON.stringify({ runs, tachyonRuns: isCloaked ? runs : [] }) });
			const ws = socket();
			await server.connect(ws, authorization(server, { sharedUserIds: [], friendlyUserIds: [] }));
			server.dispatch(event("spacecraftState", { spacecraft: { id: 7, userId: 102, x: 5, y: 5, isCloaked } }));
			await server.flush();
			ws.messages.length = 0;
			const visible = { x: 5, y: 5 };
			const hidden = { x: 6, y: 5 };
			server.dispatch(event("spacecraftMovement", {
				spacecraft: { id: 7, userId: 102, x: leaving ? 6 : 5, y: 5, isCloaked },
				from: leaving ? visible : hidden, to: leaving ? hidden : visible, direction: 3
			}));
			await server.flush();
			const message = ws.messages.find(message => message.type === (leaving ? "spacecraftRemoval" : "spacecraftState"));
			assert.ok(message);
			assert.equal(message.direction, undefined);
			assert.equal(message.from, undefined);
			assert.equal(message.to, undefined);
			assert.equal(JSON.stringify(message).includes('"x":6'), false);
			if (leaving) assert.equal(message.position, undefined);
			else assert.deepEqual(message.position, visible);
		});
	}
}

test("both hidden foreign endpoints remain silent while explicit sharing retains movement", async () => {
	for (const shared of [false, true]) {
		const server = loadServer();
		const ws = socket();
		await server.connect(ws, authorization(server, { sharedUserIds: shared ? [102] : [] }));
		ws.messages.length = 0;
		server.dispatch(event("spacecraftMovement", { direction: 3 }));
		await server.flush();
		assert.equal(ws.messages.some(message => message.type === "spacecraftMovement" && message.direction === 3), shared);
	}
});
