import crypto from "node:crypto";
import fs from "node:fs";
import http from "node:http";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { createClient } from "redis";
import { WebSocketServer } from "ws";
import { isSpacecraftStaticallyVisible, sanitizeSpacecraftForClient, getSpacecraftRelationship } from "./starmap-visibility.js";
import { starmapChannels } from "./starmap-channels.js";
import { verifyToken } from "./starmap-token.js";
import { realtimeSecret } from "./starmap-secret.js";
import { StarmapTracking, acceptScanToken } from "./starmap-tracking.js";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const rootDir = path.resolve(__dirname, "..");
const config = loadConfig();
const realtimeConfig = config.realtime || {};
const cacheConfig = config.cache || config;

const { streamKey: STREAM_KEY, coveragePrefix: COVERAGE_PREFIX, scanNoncePrefix: SCAN_NONCE_PREFIX } = starmapChannels(realtimeConfig);
const COVERAGE_RELOAD_MS = Number(process.env.STU_REALTIME_COVERAGE_RELOAD_MS || realtimeConfig.coverageReloadMs || 60000);
const PORT = Number(process.env.STU_REALTIME_PORT || realtimeConfig.port || 8787);
const HOST = process.env.STU_REALTIME_HOST || realtimeConfig.host || "127.0.0.1";
const WS_PATH = process.env.STU_REALTIME_PATH || realtimeConfig.path || "/realtime/starmap";
const SECRET = realtimeSecret(config);

const redis = createRedisClient();
const clients = new Set();
let lastStreamId = "$";

redis.on("error", function (error) {
	console.error("Redis error:", error.message);
});

await redis.connect();

const server = http.createServer(function (_request, response) {
	response.writeHead(200, { "content-type": "text/plain; charset=utf-8" });
	response.end("STU realtime starmap server\n");
});
const wss = new WebSocketServer({ server, path: WS_PATH });

wss.on("connection", function (ws, request) {
	handleConnection(ws, request).catch(function (error) {
		console.error("WebSocket connection failed:", error.message);
		try {
			ws.close(1011, "Realtime authentication failed");
		} catch {
			// ignore close errors
		}
	});
});

server.listen(PORT, HOST, function () {
	console.log(`STU realtime starmap listening on ${HOST}:${PORT}${WS_PATH}`);
	console.log(`Redis stream: ${STREAM_KEY}`);
});

readStream().catch(function (error) {
	console.error("Realtime stream loop stopped:", error);
	process.exit(1);
});

async function handleConnection(ws, request) {
	const url = new URL(request.url || WS_PATH, `http://${request.headers.host || "localhost"}`);
	const token = url.searchParams.get("token") || "";
	const auth = verifyToken(token, SECRET);
	if (auth.purpose !== "starmap") {
		throw new Error("invalid token purpose");
	}
	const client = {
		ws,
		connectionId: crypto.randomBytes(16).toString("hex"),
		queue: Promise.resolve(),
		userId: auth.userId,
		exp: auth.exp,
		layerId: auth.layerId,
		allianceId: auth.allianceId,
		canSeeAllianceShips: auth.canSeeAllianceShips,
		friendlyUserIds: auth.friendlyUserIds,
		enemyUserIds: auth.enemyUserIds,
		friendlyAllianceIds: auth.friendlyAllianceIds,
		enemyAllianceIds: auth.enemyAllianceIds,
		sharedUserIds: auth.sharedUserIds,
		sharedAllianceIds: auth.sharedAllianceIds,
		coverage: await loadCoverage(auth.userId, auth.layerId),
		coverageLoadedAt: Date.now()
	};
	client.tracking = new StarmapTracking(client.coverage);

	clients.add(client);
	scheduleAuthorizationExpiry(client);
	sendJson(client, {
		type: "ready",
		generatedAt: Math.floor(Date.now() / 1000),
		layerId: client.layerId,
		connectionId: client.connectionId,
		coverageCount: client.coverage.sourceCount,
		coverageFieldCount: client.coverage.fieldCount,
		tachyonFieldCount: client.coverage.tachyonFieldCount
	});
	sendTracking(client, true);

	ws.on("message", function (raw) {
		enqueueClient(client, async function () {
			const message = JSON.parse(raw.toString());
			if (message.type === "scan") {
				await acceptScanToken(client, message.token, SECRET, (nonce, ttl) =>
					redis.set(`${SCAN_NONCE_PREFIX}${nonce}`, "used", { NX: true, EX: ttl }));
				sendTracking(client, true);
			} else if (message.type === "refresh") {
				const updated = verifyToken(message.token, SECRET);
				if (updated.purpose !== "starmap" || updated.userId !== client.userId || updated.layerId !== client.layerId) {
					throw new Error("invalid refresh authorization");
				}
				for (const key of ["exp", "allianceId", "canSeeAllianceShips", "friendlyUserIds", "enemyUserIds", "friendlyAllianceIds",
					"enemyAllianceIds", "sharedUserIds", "sharedAllianceIds"]) {
					client[key] = updated[key];
				}
				scheduleAuthorizationExpiry(client);
				for (const [id, enemy] of client.tracking.enemies) {
					if (!getSpacecraftRelationship(client, enemy).isEnemy) {
						client.tracking.enemies.delete(id);
					}
				}
				client.coverageLoadedAt = 0;
				await refreshCoverageIfNeeded(client);
				sendTracking(client, true);
			} else if (message.type === "resetTracking") {
				client.tracking.enemies.clear();
				sendTracking(client, true);
			}
		});
	});

	ws.on("close", function () {
		clients.delete(client);
		clearTimeout(client.expiryTimer);
	});

	ws.on("error", function () {
		clients.delete(client);
		clearTimeout(client.expiryTimer);
	});
}

async function readStream() {
	for (;;) {
		const response = await redis.xRead(
			{
				key: STREAM_KEY,
				id: lastStreamId
			},
			{
				BLOCK: 1000,
				COUNT: 200
			}
		);

		if (!response) {
			continue;
		}

		for (const stream of response) {
			for (const message of stream.messages) {
				lastStreamId = message.id;
				dispatchPayload(message.message.payload);
			}
		}
	}
}

function dispatchPayload(rawPayload) {
	if (!rawPayload || clients.size === 0) {
		return;
	}

	let payload;
	try {
		payload = JSON.parse(rawPayload);
	} catch {
		return;
	}

	if (payload.type === "spacecraftRemoval") {
		dispatchRemovalPayload(payload);
		return;
	}

	if (payload.type === "spacecraftState") {
		dispatchStatePayload(payload);
		return;
	}

	if (payload.type === "coverageChanged") {
		dispatchCoverageChangedPayload(payload);
		return;
	}

	if (payload.type !== "spacecraftMovement" || !payload.spacecraft || !payload.from || !payload.to) {
		return;
	}

	for (const client of clients) {
		if (client.layerId !== Number(payload.layerId) || !isClientAuthorized(client)) {
			continue;
		}

		enqueueClient(client, async function () {
			await refreshCoverageIfNeeded(client);
			const fromVisible = isSpacecraftVisibleAtPoint(client, payload.spacecraft, payload.from);
			const toVisible = isSpacecraftVisibleAtPoint(client, payload.spacecraft, payload.to);
			const fromCovered = isSpacecraftSensorCoveredAtPoint(client, payload.spacecraft, payload.from);
			const toCovered = isSpacecraftSensorCoveredAtPoint(client, payload.spacecraft, payload.to);
			const staticVisible = isSpacecraftStaticallyVisible(client, payload.spacecraft);
			if (!fromVisible && !toVisible && !staticVisible) {
				return;
			}

			const spacecraft = toVisible || staticVisible
				? sanitizeSpacecraftForClient(client, payload.spacecraft)
				: { id: payload.spacecraft.id };
			validateTrackedRelationship(client, payload.spacecraft);
			client.tracking.update(spacecraft, toVisible || staticVisible, payload.from);

			sendJson(client, {
				type: "spacecraftMovement",
				generatedAt: payload.generatedAt,
				layerId: payload.layerId,
				direction: payload.direction,
				from: payload.from,
				to: payload.to,
				fromVisible,
				toVisible,
				fromCovered,
				toCovered,
				visible: toVisible,
				staticVisible,
				spacecraft
			});
			sendTracking(client);
		});
	}
}

function dispatchCoverageChangedPayload(payload) {
	for (const client of clients) {
		if (client.layerId !== Number(payload.layerId) || !isClientAuthorized(client)) {
			continue;
		}

		client.coverageLoadedAt = 0;
		sendJson(client, {
			type: "coverageChanged",
			generatedAt: payload.generatedAt,
			layerId: payload.layerId
		});
	}
}

function dispatchStatePayload(payload) {
	if (!payload.spacecraft || !payload.position) {
		return;
	}

	for (const client of clients) {
		if (client.layerId !== Number(payload.layerId) || !isClientAuthorized(client)) {
			continue;
		}

		enqueueClient(client, async function () {
			await refreshCoverageIfNeeded(client);
			const covered = isPointCovered(client.coverage, payload.position);
			const visible = isSpacecraftVisibleAtPoint(client, payload.spacecraft, payload.position);
			const staticVisible = isSpacecraftStaticallyVisible(client, payload.spacecraft);
			if (!covered && !visible && !staticVisible) {
				return;
			}

			const spacecraft = visible || staticVisible
				? sanitizeSpacecraftForClient(client, payload.spacecraft)
				: { id: payload.spacecraft.id };
			validateTrackedRelationship(client, payload.spacecraft);
			client.tracking.update(spacecraft, visible || staticVisible);

			sendJson(client, {
				type: "spacecraftState",
				generatedAt: payload.generatedAt,
				layerId: payload.layerId,
				position: payload.position,
				visible,
				staticVisible,
				spacecraft
			});
			sendTracking(client);
		});
	}
}

function dispatchRemovalPayload(payload) {
	if (!payload.spacecraft || !payload.position) {
		return;
	}

	for (const client of clients) {
		if (client.layerId !== Number(payload.layerId) || !isClientAuthorized(client)) {
			continue;
		}

		enqueueClient(client, async function () {
			await refreshCoverageIfNeeded(client);
			if (!isSpacecraftVisibleAtPoint(client, payload.spacecraft, payload.position)) {
				if (!isSpacecraftStaticallyVisible(client, payload.spacecraft)) {
					return;
				}
			}

			const spacecraft = sanitizeSpacecraftForClient(client, payload.spacecraft);

			client.tracking.remove(payload.spacecraft.id);

			sendJson(client, {
				type: "spacecraftRemoval",
				generatedAt: payload.generatedAt,
				layerId: payload.layerId,
				position: payload.position,
				staticVisible: isSpacecraftStaticallyVisible(client, payload.spacecraft),
				spacecraft
			});
			sendTracking(client);
		});
	}
}

async function refreshCoverageIfNeeded(client) {
	if (Date.now() - client.coverageLoadedAt < COVERAGE_RELOAD_MS) {
		return;
	}

	const coverage = await loadCoverage(client.userId, client.layerId);
	if (coverage.snapshotVersion !== client.coverage.snapshotVersion) {
		client.tracking.refresh(coverage);
	}
	client.coverage = coverage;
	client.coverageLoadedAt = Date.now();
}

async function loadCoverage(userId, layerId) {
	const raw = await redis.get(`${COVERAGE_PREFIX}${userId}:${layerId}`);
	if (!raw) {
		return createEmptyCoverage();
	}

	try {
		const coverage = JSON.parse(raw);
		if (Array.isArray(coverage)) {
			return {
				runs: [],
				tachyonRuns: [],
				sourceCount: coverage.length,
				fieldCount: 0,
				tachyonFieldCount: 0
			};
		}

		return {
			snapshotVersion: coverage.snapshotVersion,
			sources: Array.isArray(coverage.sources) ? coverage.sources : [],
			contacts: Array.isArray(coverage.contacts) ? coverage.contacts : [],
			runs: normalizeRuns(coverage.runs),
			tachyonRuns: normalizeRuns(coverage.tachyonRuns),
			sourceCount: Number(coverage.sourceCount) || 0,
			fieldCount: Number(coverage.fieldCount) || 0,
			tachyonFieldCount: Number(coverage.tachyonFieldCount) || 0
		};
	} catch {
		return createEmptyCoverage();
	}
}

function createEmptyCoverage() {
	return {
		snapshotVersion: null,
		sources: [],
		contacts: [],
		runs: [],
		tachyonRuns: [],
		sourceCount: 0,
		fieldCount: 0,
		tachyonFieldCount: 0
	};
}

function normalizeRuns(runs) {
	if (!Array.isArray(runs)) {
		return [];
	}

	return runs
		.map(function (run) {
			return {
				y: Number(run.y),
				startX: Number(run.startX),
				endX: Number(run.endX)
			};
		})
		.filter(function (run) {
			return Number.isFinite(run.y)
				&& Number.isFinite(run.startX)
				&& Number.isFinite(run.endX)
				&& run.startX <= run.endX;
		});
}

function isPointCovered(coverage, point) {
	return isPointInRuns(coverage.runs, point);
}

function isPointTachyonCovered(coverage, point) {
	return isPointInRuns(coverage.tachyonRuns, point);
}

function isPointInRuns(runs, point) {
	const x = Number(point.x);
	const y = Number(point.y);
	if (!Number.isFinite(x) || !Number.isFinite(y)) {
		return false;
	}

	return runs.some(function (run) {
		return y === run.y && x >= run.startX && x <= run.endX;
	});
}

function isSpacecraftVisibleAtPoint(client, spacecraft, point) {
	if (Number(spacecraft.userId) === client.userId) {
		return true;
	}
	if (spacecraft.isCloaked) {
		return isPointTachyonCovered(client.coverage, point);
	}

	return isPointCovered(client.coverage, point);
}

function isSpacecraftSensorCoveredAtPoint(client, spacecraft, point) {
	return spacecraft.isCloaked
		? isPointTachyonCovered(client.coverage, point)
		: isPointCovered(client.coverage, point);
}

function isClientAuthorized(client) {
	if (client.ws.readyState !== 1) {
		return false;
	}
	if (client.exp <= Math.floor(Date.now() / 1000)) {
		clients.delete(client);
		clearTimeout(client.expiryTimer);
		client.ws.close(1008, "Realtime authorization expired");
		return false;
	}
	return true;
}

function scheduleAuthorizationExpiry(client) {
	clearTimeout(client.expiryTimer);
	client.expiryTimer = setTimeout(function () {
		isClientAuthorized(client);
	}, Math.max(0, client.exp * 1000 - Date.now()));
}

function sendJson(client, payload) {
	if (isClientAuthorized(client)) {
		client.ws.send(JSON.stringify(payload));
	}
}

function sendTracking(client, force = false) {
	const spacecraftIds = client.tracking.identifiedIds();
	const version = JSON.stringify(spacecraftIds);
	if (force || client.trackingVersion !== version) {
		client.trackingVersion = version;
		sendJson(client, { type: "tracking", spacecraftIds });
	}
}

function validateTrackedRelationship(client, spacecraft) {
	const id = Number(spacecraft.id);
	const enemy = client.tracking.enemies.get(id);
	if (enemy && (enemy.userId !== Number(spacecraft.userId) || !getSpacecraftRelationship(client, spacecraft).isEnemy)) {
		client.tracking.enemies.delete(id);
	}
}

function enqueueClient(client, task) {
	client.queue = client.queue.then(async function () {
		if (isClientAuthorized(client)) {
			await task();
		}
	}).catch(function (error) {
		client.tracking.enemies.clear();
		sendTracking(client, true);
		console.error("Realtime client update failed:", error.message);
	});
}

function createRedisClient() {
	if (process.env.REDIS_URL) {
		return createClient({ url: process.env.REDIS_URL });
	}

	const socketPath = process.env.REDIS_SOCKET || cacheConfig.redis_socket;
	if (socketPath && !socketPath.includes("/path/to/")) {
		return createClient({
			socket: {
				path: socketPath
			}
		});
	}

	return createClient({
		socket: {
			host: process.env.REDIS_HOST || cacheConfig.redis_host || "127.0.0.1",
			port: Number(process.env.REDIS_PORT || cacheConfig.redis_port || 6379)
		}
	});
}

function loadConfig() {
	const configPath = process.env.STU_CONFIG_PATH || path.join(rootDir, "config", "config.json");
	if (fs.existsSync(configPath)) {
		return JSON.parse(fs.readFileSync(configPath, "utf8"));
	}

	return JSON.parse(fs.readFileSync(path.join(rootDir, "config", "config.dist.json"), "utf8"));
}
