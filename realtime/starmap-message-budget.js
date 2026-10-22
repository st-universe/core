export const STARMAP_MAX_PAYLOAD = 256 * 1024;

export class StarmapMessageBudget {
	constructor(now = Date.now) {
		this.now = now;
		this.windowStart = now();
		this.messages = 0;
		this.queuedTasks = 0;
		this.queuedBytes = 0;
	}

	read(raw, isBinary) {
		const bytes = Buffer.byteLength(raw);
		if (isBinary || bytes > STARMAP_MAX_PAYLOAD) throw new Error("invalid realtime frame");
		if (this.now() - this.windowStart >= 10000) {
			this.windowStart = this.now();
			this.messages = 0;
		}
		if (++this.messages > 60) throw new Error("realtime message rate exceeded");
		const message = JSON.parse(raw.toString());
		if (!message || Array.isArray(message) || typeof message !== "object" ||
			!["scan", "refresh", "resetTracking"].includes(message.type) ||
			Object.keys(message).some(key => !["type", "token"].includes(key)) ||
			(message.type === "resetTracking" ? message.token !== undefined : typeof message.token !== "string" || !message.token)) {
			throw new Error("invalid realtime message");
		}
		return { message, bytes };
	}

	reserve(bytes = 0) {
		if (this.queuedTasks >= 128 || this.queuedBytes + bytes > 512 * 1024) throw new Error("realtime queue limit exceeded");
		this.queuedTasks++;
		this.queuedBytes += bytes;
		return () => {
			this.queuedTasks--;
			this.queuedBytes -= bytes;
		};
	}
}
