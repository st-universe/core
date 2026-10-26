import assert from "node:assert/strict";
import crypto from "node:crypto";
import { test } from "node:test";
import { liveStarmap } from "./support/live-starmap.js";
import { event } from "./support/starmap-server.js";

test("a fleet movement burst is delivered without waiting behind the blocking stream reader", { timeout: 10000 }, async t => {
	const service = await liveStarmap(t);
	await service.redis.set("test:realtime:starmap:coverage:101:2", JSON.stringify({ generation: 0, snapshotVersion: "fleet", runs: [] }));
	const ws = service.connect({ userId: 101, layerId: 2, nonce: crypto.randomBytes(8).toString("hex"), exp: Math.floor(Date.now() / 1000) + 300 });
	await service.ready(ws);
	const arrivals = [];
	const startedAt = performance.now();
	const received = new Promise(resolve => {
		ws.on("message", raw => {
			const message = JSON.parse(raw);
			if (message.type !== "spacecraftMovement") return;
			arrivals.push(performance.now() - startedAt);
			if (arrivals.length === 5) resolve();
		});
	});
	const batch = service.redis.multi();
	for (let id = 1; id <= 5; id++) {
		batch.xAdd("test:realtime:starmap:spacecraft", "*", { payload: JSON.stringify(event("spacecraftMovement", {
			spacecraft: { id, userId: 101, x: 6, y: 5, name: "Fleet ship " + id }
		})) });
	}
	await batch.exec();
	await received;
	t.diagnostic(`Fleet events received after ${arrivals.map(value => value.toFixed(1)).join(" / ")} ms`);
	assert.ok(arrivals.at(-1) - arrivals[0] < 750, `Fleet events were spread over ${arrivals.at(-1) - arrivals[0]} ms`);
	assert.ok(arrivals.at(-1) < 1500, `Fleet delivery took ${arrivals.at(-1)} ms`);
});
