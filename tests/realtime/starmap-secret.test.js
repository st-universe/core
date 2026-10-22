import assert from "node:assert/strict";
import { test } from "node:test";
import { realtimeSecret } from "../../realtime/starmap-secret.js";

const secret = "1234567890abcdef".repeat(4);
const override = "fedcba0987654321".repeat(4);

test("missing secrets, distributed defaults and database-name fallbacks are rejected", () => {
	for (const value of [null, undefined, "", "the key to encrypt map data", "stu", 123, "a".repeat(31), " ".repeat(64)]) {
		assert.throws(() => realtimeSecret({ game: { map: { encryptionKey: value } }, db: { database: "stu" } }, {}));
	}
	assert.throws(() => realtimeSecret({ db: { database: "stu" } }, {}));
});

test("dedicated configured and environment keys use the same explicit precedence", () => {
	assert.equal(realtimeSecret({ game: { map: { encryptionKey: secret } } }, {}), secret);
	assert.equal(realtimeSecret({ game: { map: { encryptionKey: secret } } }, { STU_REALTIME_SECRET: override }), override);
	assert.throws(() => realtimeSecret({ game: { map: { encryptionKey: secret } } }, { STU_REALTIME_SECRET: "" }));
});

test("known database keys cannot be used as signing material", () => {
	assert.throws(() => realtimeSecret({ game: { map: { encryptionKey: secret } }, db: { database: secret } }, {}));
	assert.throws(() => realtimeSecret({ db: { database: secret } }, { STU_REALTIME_SECRET: secret }));
});
