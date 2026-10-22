import assert from "node:assert/strict";
import crypto from "node:crypto";
import { once } from "node:events";
import { test } from "node:test";
import { STARMAP_MAX_PAYLOAD } from "../../realtime/starmap-message-budget.js";
import { liveStarmap } from "./support/live-starmap.js";

test("real WebSocket transport rejects oversized, binary and malformed frames while valid peers continue", async t => {
	const service = await liveStarmap(t);
	let userId = 1;
	const connect = async () => {
		const ws = service.connect({ userId: userId++, layerId: 2, nonce: crypto.randomBytes(8).toString("hex"), exp: Math.floor(Date.now() / 1000) + 300 });
		await service.ready(ws);
		return ws;
	};
	const valid = await connect();
	for (const [payload, options, expected] of [["a".repeat(STARMAP_MAX_PAYLOAD + 1), {}, 1009], [Buffer.from('{"type":"resetTracking"}'), {}, 1008], ["{", {}, 1008]]) {
		const ws = await connect();
		const closed = once(ws, "close");
		ws.send(payload, options);
		assert.equal((await closed)[0], expected);
	}
	const tracking = new Promise(resolve => valid.on("message", raw => { const message = JSON.parse(raw); if (message.type === "tracking") resolve(message); }));
	valid.send('{"type":"resetTracking"}');
	assert.deepEqual((await tracking).spacecraftIds, []);
	assert.equal(valid.readyState, 1);
});
