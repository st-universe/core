import assert from "node:assert/strict";
import crypto from "node:crypto";
import { once } from "node:events";
import { test } from "node:test";
import { liveStarmap } from "./support/live-starmap.js";

function authorization(userId = 101) {
	return { userId, layerId: 2, nonce: crypto.randomBytes(8).toString("hex"), exp: Math.floor(Date.now() / 1000) + 300 };
}

test("real WebSocket admission rejects token replay and enforces user quota before upgrading", async t => {
	const service = await liveStarmap(t);
	const claims = authorization();
	const accepted = service.connect(claims);
	await service.ready(accepted);
	const replay = service.connect(claims);
	const [replayCode] = await once(replay, "close");
	assert.equal(replayCode, 1006);
	assert.equal(accepted.readyState, 1);
	for (let i = 1; i < 8; i++) await service.ready(service.connect(authorization()));
	const overflow = service.connect(authorization());
	let opened = false;
	overflow.on("open", () => { opened = true; });
	await assert.rejects(service.ready(overflow), /401/);
	assert.equal(opened, false);
	await service.ready(service.connect(authorization(102)));
	accepted.close();
	await once(accepted, "close");
	await service.ready(service.connect(authorization()));
});
