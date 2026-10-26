import { spawn } from "node:child_process";
import { once } from "node:events";
import fs from "node:fs/promises";
import net from "node:net";
import os from "node:os";
import path from "node:path";
import { createClient } from "redis";
import { WebSocket } from "ws";
import { secret, signToken } from "./starmap-server.js";

function waitForOutput(child, expected) {
	return new Promise((resolve, reject) => {
		let output = "";
		const timer = setTimeout(() => reject(new Error("local realtime service did not start")), 5000);
		child.stdout.on("data", chunk => {
			output += chunk.toString();
			if (output.includes(expected)) { clearTimeout(timer); resolve(); }
		});
		child.once("exit", () => { clearTimeout(timer); reject(new Error("local realtime service exited before startup")); });
		child.once("error", error => { clearTimeout(timer); reject(error); });
	});
}

export async function liveStarmap(t) {
	const directory = await fs.mkdtemp(path.join(os.tmpdir(), "core-realtime-test-"));
	const children = [];
	const sockets = [];
	let redisClient;
	t.after(async () => {
		for (const socket of sockets) socket.terminate();
		if (redisClient?.isOpen) await redisClient.close();
		for (const child of children.reverse()) {
			if (child.exitCode !== null) continue;
			const stopped = once(child, "exit");
			child.kill("SIGTERM");
			await stopped;
		}
		await fs.rm(directory, { recursive: true, force: true });
	});
	const socketPath = path.join(directory, "redis.sock");
	const redis = spawn("redis-server", ["--port", "0", "--unixsocket", socketPath, "--unixsocketperm", "700", "--save", "", "--appendonly", "no"], { stdio: ["ignore", "pipe", "pipe"] });
	children.push(redis);
	await waitForOutput(redis, "Ready to accept connections");
	redisClient = createClient({ socket: { path: socketPath } });
	await redisClient.connect();
	const configPath = path.join(directory, "config.json");
	await fs.writeFile(configPath, JSON.stringify({ realtime: { redisNamespace: "test" } }));
	const listener = net.createServer();
	listener.listen(0, "127.0.0.1");
	await once(listener, "listening");
	const port = listener.address().port;
	await new Promise(resolve => listener.close(resolve));
	const server = spawn(process.execPath, ["realtime/starmap-server.js"], {
		cwd: new URL("../../../", import.meta.url),
		env: { ...process.env, STU_CONFIG_PATH: configPath, STU_REALTIME_SECRET: secret, STU_REALTIME_PORT: String(port), STU_REALTIME_HOST: "127.0.0.1", STU_REALTIME_PATH: "/realtime/starmap", REDIS_SOCKET: socketPath, REDIS_URL: "" },
		stdio: ["ignore", "pipe", "pipe"]
	});
	children.push(server);
	await waitForOutput(server, "STU realtime starmap listening");
	return {
		redis: redisClient,
		connect(claims) {
			const ws = new WebSocket(`ws://127.0.0.1:${port}/realtime/starmap?token=${signToken(claims)}`);
			sockets.push(ws);
			return ws;
		},
		ready(ws) {
			return new Promise((resolve, reject) => {
				ws.on("error", reject);
				ws.on("close", () => reject(new Error("connection closed before ready")));
				ws.on("message", raw => { const message = JSON.parse(raw); if (message.type === "ready") resolve(message); });
			});
		}
	};
}
