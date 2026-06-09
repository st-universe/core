import crypto from "node:crypto";
import fs from "node:fs";
import http from "node:http";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { createClient } from "redis";
import { WebSocketServer } from "ws";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const rootDir = path.resolve(__dirname, "..");
const config = loadConfig();
const realtimeConfig = config.realtime || {};
const cacheConfig = config.cache || config;

const STREAM_KEY = "stu:realtime:starmap:spacecraft";
const COVERAGE_PREFIX = "stu:realtime:starmap:coverage:";
const COVERAGE_RELOAD_MS = Number(process.env.STU_REALTIME_COVERAGE_RELOAD_MS || realtimeConfig.coverageReloadMs || 60000);
const PORT = Number(process.env.STU_REALTIME_PORT || realtimeConfig.port || 8787);
const HOST = process.env.STU_REALTIME_HOST || realtimeConfig.host || "127.0.0.1";
const WS_PATH = process.env.STU_REALTIME_PATH || realtimeConfig.path || "/realtime/starmap";
const SECRET = process.env.STU_REALTIME_SECRET || config.game?.map?.encryptionKey || config.db?.database;

if (!SECRET) {
	console.error("Missing STU_REALTIME_SECRET or game.map.encryptionKey in config.");
	process.exit(1);
}

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
});

readStream().catch(function (error) {
	console.error("Realtime stream loop stopped:", error);
	process.exit(1);
});

async function handleConnection(ws, request) {
	const url = new URL(request.url || WS_PATH, `http://${request.headers.host || "localhost"}`);
	const token = url.searchParams.get("token") || "";
	const auth = verifyToken(token);
	const client = {
		ws,
		userId: auth.userId,
		layerId: auth.layerId,
		allianceId: auth.allianceId,
		canSeeAllianceShips: auth.canSeeAllianceShips,
		friendlyUserIds: auth.friendlyUserIds,
		enemyUserIds: auth.enemyUserIds,
		friendlyAllianceIds: auth.friendlyAllianceIds,
		enemyAllianceIds: auth.enemyAllianceIds,
		coverage: await loadCoverage(auth.userId, auth.layerId),
		coverageLoadedAt: Date.now()
	};

	clients.add(client);
	sendJson(ws, {
		type: "ready",
		generatedAt: Math.floor(Date.now() / 1000),
		layerId: client.layerId,
		coverageCount: client.coverage.sourceCount,
		coverageFieldCount: client.coverage.fieldCount,
		tachyonFieldCount: client.coverage.tachyonFieldCount
	});

	ws.on("close", function () {
		clients.delete(client);
	});

	ws.on("error", function () {
		clients.delete(client);
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

	if (payload.type !== "spacecraftMovement" || !payload.spacecraft || !payload.from || !payload.to) {
		return;
	}

	for (const client of clients) {
		if (client.layerId !== Number(payload.layerId) || client.ws.readyState !== 1) {
			continue;
		}

		refreshCoverageIfNeeded(client).then(function () {
			const fromVisible = isSpacecraftVisibleAtPoint(client, payload.spacecraft, payload.from);
			const toVisible = isSpacecraftVisibleAtPoint(client, payload.spacecraft, payload.to);
			const staticVisible = isSpacecraftStaticallyVisible(client, payload.spacecraft);
			if (!fromVisible && !toVisible && !staticVisible) {
				return;
			}

			const spacecraft = toVisible || staticVisible
				? sanitizeSpacecraftForClient(client, payload.spacecraft)
				: { id: payload.spacecraft.id };

			sendJson(client.ws, {
				type: "spacecraftMovement",
				generatedAt: payload.generatedAt,
				layerId: payload.layerId,
				direction: payload.direction,
				from: payload.from,
				to: payload.to,
				fromVisible,
				toVisible,
				visible: toVisible,
				staticVisible,
				spacecraft
			});
		}).catch(function (error) {
			console.error("Coverage refresh failed:", error.message);
		});
	}
}

function dispatchStatePayload(payload) {
	if (!payload.spacecraft || !payload.position) {
		return;
	}

	for (const client of clients) {
		if (client.layerId !== Number(payload.layerId) || client.ws.readyState !== 1) {
			continue;
		}

		refreshCoverageIfNeeded(client).then(function () {
			const covered = isPointCovered(client.coverage, payload.position);
			const visible = isSpacecraftVisibleAtPoint(client, payload.spacecraft, payload.position);
			const staticVisible = isSpacecraftStaticallyVisible(client, payload.spacecraft);
			if (!covered && !visible && !staticVisible) {
				return;
			}

			const spacecraft = visible || staticVisible
				? sanitizeSpacecraftForClient(client, payload.spacecraft)
				: { id: payload.spacecraft.id };

			sendJson(client.ws, {
				type: "spacecraftState",
				generatedAt: payload.generatedAt,
				layerId: payload.layerId,
				position: payload.position,
				visible,
				staticVisible,
				spacecraft
			});
		}).catch(function (error) {
			console.error("Coverage refresh failed:", error.message);
		});
	}
}

function dispatchRemovalPayload(payload) {
	if (!payload.spacecraft || !payload.position) {
		return;
	}

	for (const client of clients) {
		if (client.layerId !== Number(payload.layerId) || client.ws.readyState !== 1) {
			continue;
		}

		refreshCoverageIfNeeded(client).then(function () {
			if (!isSpacecraftVisibleAtPoint(client, payload.spacecraft, payload.position)) {
				if (!isSpacecraftStaticallyVisible(client, payload.spacecraft)) {
					return;
				}
			}

			const spacecraft = sanitizeSpacecraftForClient(client, payload.spacecraft);

			if (payload.spacecraft.isCloaked && !spacecraft.isOwn) {
				return;
			}

			sendJson(client.ws, {
				type: "spacecraftRemoval",
				generatedAt: payload.generatedAt,
				layerId: payload.layerId,
				position: payload.position,
				staticVisible: isSpacecraftStaticallyVisible(client, payload.spacecraft),
				spacecraft
			});
		}).catch(function (error) {
			console.error("Coverage refresh failed:", error.message);
		});
	}
}

async function refreshCoverageIfNeeded(client) {
	if (Date.now() - client.coverageLoadedAt < COVERAGE_RELOAD_MS) {
		return;
	}

	client.coverage = await loadCoverage(client.userId, client.layerId);
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

function isSpacecraftStaticallyVisible(client, spacecraft) {
	if (!spacecraft) {
		return false;
	}

	if (Number(spacecraft.userId) === client.userId) {
		return true;
	}

	return client.canSeeAllianceShips
		&& client.allianceId !== null
		&& spacecraft.allianceId !== null
		&& Number(spacecraft.allianceId) === client.allianceId;
}

function sanitizeSpacecraftForClient(client, spacecraft) {
	const relationship = getSpacecraftRelationship(client, spacecraft);
	if (spacecraft.isCloaked && !relationship.hasDetails) {
		return sanitizeCloakedSignature(spacecraft, relationship);
	}

	const result = {
		...spacecraft,
		isOwn: relationship.isOwn,
		isFriendly: relationship.isFriendly,
		isEnemy: relationship.isEnemy,
		hasDetails: relationship.hasDetails
	};

	if (!relationship.hasDetails) {
		delete result.hull;
		delete result.maxHull;
		delete result.shield;
		delete result.maxShield;
		delete result.eps;
		delete result.maxEps;
		delete result.warpdrive;
		delete result.maxWarpdrive;
		delete result.alertState;
		delete result.alertStateName;
	}

	return result;
}

function sanitizeCloakedSignature(spacecraft, relationship) {
	return {
		id: spacecraft.id,
		name: "?",
		nameText: "?",
		nameHtml: "?",
		type: spacecraft.type,
		userId: 0,
		userName: "Unbekannt",
		userNameText: "Unbekannt",
		userNameHtml: "Unbekannt",
		allianceId: null,
		allianceName: null,
		allianceNameText: null,
		allianceNameHtml: null,
		rumpId: 0,
		rumpName: "Tarnsignatur",
		rumpImage: "",
		x: spacecraft.x,
		y: spacecraft.y,
		inSystem: Boolean(spacecraft.inSystem),
		systemName: spacecraft.systemName || null,
		isCloaked: true,
		isCloakedSignature: true,
		isOwn: relationship.isOwn,
		isFriendly: false,
		isEnemy: false,
		hasDetails: false
	};
}

function getSpacecraftRelationship(client, spacecraft) {
	const ownerId = Number(spacecraft.userId);
	const allianceId = spacecraft.allianceId == null ? null : Number(spacecraft.allianceId);
	const isOwn = ownerId === client.userId;
	const isOwnAlliance = client.allianceId !== null && allianceId === client.allianceId;
	const isFriendly = isOwn
		|| isOwnAlliance
		|| client.friendlyUserIds.has(ownerId)
		|| (allianceId !== null && client.friendlyAllianceIds.has(allianceId));
	const isEnemy = !isFriendly
		&& (
			client.enemyUserIds.has(ownerId)
			|| (allianceId !== null && client.enemyAllianceIds.has(allianceId))
		);

	return {
		isOwn,
		isFriendly,
		isEnemy,
		hasDetails: isOwn || (client.canSeeAllianceShips && isOwnAlliance)
	};
}

function verifyToken(token) {
	const parts = token.split(".");
	if (parts.length !== 2 || !parts[0] || !parts[1]) {
		throw new Error("invalid token");
	}

	const expected = crypto.createHmac("sha256", SECRET).update(parts[0]).digest();
	const actual = base64UrlDecode(parts[1]);
	if (actual.length !== expected.length || !crypto.timingSafeEqual(actual, expected)) {
		throw new Error("invalid token signature");
	}

	const payload = JSON.parse(base64UrlDecode(parts[0]).toString("utf8"));
	const now = Math.floor(Date.now() / 1000);
	if (Number(payload.exp) < now) {
		throw new Error("expired token");
	}

	return {
		userId: Number(payload.userId),
		layerId: Number(payload.layerId),
		allianceId: payload.allianceId == null ? null : Number(payload.allianceId),
		canSeeAllianceShips: payload.canSeeAllianceShips === true,
		friendlyUserIds: toNumberSet(payload.friendlyUserIds),
		enemyUserIds: toNumberSet(payload.enemyUserIds),
		friendlyAllianceIds: toNumberSet(payload.friendlyAllianceIds),
		enemyAllianceIds: toNumberSet(payload.enemyAllianceIds)
	};
}

function toNumberSet(values) {
	if (!Array.isArray(values)) {
		return new Set();
	}

	return new Set(values.map(Number).filter(Number.isFinite));
}

function sendJson(ws, payload) {
	ws.send(JSON.stringify(payload));
}

function base64UrlDecode(value) {
	const normalized = value.replace(/-/g, "+").replace(/_/g, "/");
	const padding = normalized.length % 4 === 0 ? "" : "=".repeat(4 - (normalized.length % 4));

	return Buffer.from(normalized + padding, "base64");
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
