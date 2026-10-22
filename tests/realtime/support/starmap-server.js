import crypto from "node:crypto";
import fs from "node:fs";
import vm from "node:vm";
import { EventEmitter } from "node:events";
import { verifyToken } from "../../../realtime/starmap-token.js";
import { StarmapTracking, acceptScanToken } from "../../../realtime/starmap-tracking.js";
import { isSpacecraftStaticallyVisible, sanitizeSpacecraftForClient, getSpacecraftRelationship } from "../../../realtime/starmap-visibility.js";

const source = fs.readFileSync(new URL("../../../realtime/starmap-server.js", import.meta.url), "utf8");
export const secret = "test-realtime-authorization-secret";

export function signToken(claims) {
	const payload = Buffer.from(JSON.stringify(claims)).toString("base64url");
	return payload + "." + crypto.createHmac("sha256", secret).update(payload).digest("base64url");
}

export function loadServer({ getCoverage = async () => null } = {}) {
	let now = Date.now();
	const timers = new Map();
	let timerId = 0;
	const clients = new Set();
	const context = vm.createContext({
		crypto, URL, console: { error() {} }, SECRET: secret, WS_PATH: "/realtime/starmap",
		COVERAGE_RELOAD_MS: 60000, COVERAGE_PREFIX: "test:", SCAN_NONCE_PREFIX: "nonce:", clients,
		redis: { get: getCoverage, set: async () => "OK" },
		Date: class extends Date { static now() { return now; } },
		setTimeout: (callback, delay) => { timers.set(++timerId, { callback, at: now + delay }); return timerId; },
		clearTimeout: id => timers.delete(id),
		verifyToken, StarmapTracking, acceptScanToken,
		isSpacecraftStaticallyVisible, sanitizeSpacecraftForClient, getSpacecraftRelationship
	});
	vm.runInContext(source.slice(source.indexOf("async function handleConnection(")), context);
	return {
		clients,
		now: () => now,
		advance(milliseconds, fireTimers = true) {
			now += milliseconds;
			if (fireTimers) {
				for (const [id, timer] of timers) {
					if (timer.at <= now) { timers.delete(id); timer.callback(); }
				}
			}
		},
		connect(ws, claims) {
			return context.handleConnection(ws, { url: "?token=" + signToken(claims), headers: {} });
		},
		dispatch: payload => context.dispatchPayload(JSON.stringify(payload)),
		flush: async () => { await Promise.all([...clients].map(client => client.queue)); }
	};
}

export function socket() {
	const ws = new EventEmitter();
	ws.readyState = 1;
	ws.messages = [];
	ws.send = value => ws.messages.push(JSON.parse(value));
	ws.close = (code, reason) => { ws.closeCode = code; ws.closeReason = reason; ws.readyState = 3; ws.emit("close"); };
	return ws;
}

export function authorization(server, overrides = {}) {
	return {
		userId: 101, layerId: 2, exp: Math.floor(server.now() / 1000) + 300,
		sharedUserIds: [102], friendlyUserIds: [102], ...overrides
	};
}

export function event(type = "spacecraftState", overrides = {}) {
	return {
		type, layerId: 2, spacecraft: { id: 7, userId: 102, x: 5, y: 5, name: "Test ship" },
		from: { x: 5, y: 5 }, to: { x: 6, y: 5 }, position: { x: 5, y: 5 }, ...overrides
	};
}
