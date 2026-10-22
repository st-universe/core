import crypto from "node:crypto";
import fs from "node:fs";
import vm from "node:vm";
import { EventEmitter } from "node:events";
import { verifyToken } from "../../../realtime/starmap-token.js";
import { StarmapMessageBudget } from "../../../realtime/starmap-message-budget.js";
import { StarmapAdmission } from "../../../realtime/starmap-admission.js";
import { StarmapContacts } from "../../../realtime/starmap-contacts.js";
import { StarmapTracking, acceptScanToken } from "../../../realtime/starmap-tracking.js";
import { isSpacecraftStaticallyVisible, sanitizeSpacecraftForClient, getSpacecraftRelationship, isSpacecraftConcealedAtPoint } from "../../../realtime/starmap-visibility.js";

const source = fs.readFileSync(new URL("../../../realtime/starmap-server.js", import.meta.url), "utf8");
export const secret = "test-realtime-authorization-secret";

export function signToken(claims) {
	const payload = Buffer.from(JSON.stringify(claims)).toString("base64url");
	return payload + "." + crypto.createHmac("sha256", secret).update(payload).digest("base64url");
}

export function loadServer({ getCoverage = async () => null, getGeneration = async () => null, setNonce, limits } = {}) {
	let now = Date.now();
	const timers = new Map();
	let timerId = 0;
	const clients = new Set();
	const nonces = new Set();
	const admission = new StarmapAdmission(limits, () => now);
	const context = vm.createContext({
		crypto, URL, console: { error() {} }, SECRET: secret, WS_PATH: "/realtime/starmap",
		COVERAGE_RELOAD_MS: 60000, COVERAGE_PREFIX: "test:", SCAN_NONCE_PREFIX: "nonce:", clients, admission,
		redis: { get: key => key.includes("generation:") ? getGeneration(key) : getCoverage(key), set: setNonce || (async key => { if (nonces.has(key)) return null; nonces.add(key); return "OK"; }) },
		Date: class extends Date { static now() { return now; } },
		setTimeout: (callback, delay) => { timers.set(++timerId, { callback, at: now + delay }); return timerId; },
		clearTimeout: id => timers.delete(id),
		verifyToken, StarmapTracking, acceptScanToken, StarmapContacts, StarmapMessageBudget,
		isSpacecraftStaticallyVisible, sanitizeSpacecraftForClient, getSpacecraftRelationship, isSpacecraftConcealedAtPoint
	});
	vm.runInContext(source.slice(source.indexOf("async function handleConnection(")), context);
	return {
		clients,
		timers,
		now: () => now,
		advance(milliseconds, fireTimers = true) {
			now += milliseconds;
			if (fireTimers) {
				for (const [id, timer] of timers) {
					if (timer.at <= now) { timers.delete(id); timer.callback(); }
				}
			}
		},
		connect(ws, claims, ip = "127.0.0.1") {
			return context.handleConnection(ws, { url: "?token=" + signToken(claims), headers: {}, socket: { remoteAddress: ip } });
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
	ws.terminate = () => { ws.terminated = true; ws.readyState = 3; ws.emit("close"); };
	ws.close = (code, reason) => { ws.closeCode = code; ws.closeReason = reason; ws.readyState = 3; ws.emit("close"); };
	return ws;
}

export function authorization(server, overrides = {}) {
	return {
		nonce: crypto.randomBytes(8).toString("hex"), userId: 101, layerId: 2, exp: Math.floor(server.now() / 1000) + 300,
		sharedUserIds: [102], friendlyUserIds: [102], ...overrides
	};
}

export function event(type = "spacecraftState", overrides = {}) {
	return {
		type, layerId: 2, spacecraft: { id: 7, userId: 102, x: 5, y: 5, name: "Test ship" },
		from: { x: 5, y: 5 }, to: { x: 6, y: 5 }, position: { x: 5, y: 5 }, ...overrides
	};
}
