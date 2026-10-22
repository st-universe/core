export class StarmapAdmission {
	constructor(limits = {}, now = Date.now) {
		this.limits = { global: 1024, user: 8, ip: 128, attempts: 60, globalAttempts: 1024, trackedIps: 4096, window: 60000, ...limits };
		this.now = now;
		this.active = 0;
		this.users = new Map();
		this.ips = new Map();
		this.attempts = new Map();
		this.windowStart = this.now();
		this.globalAttempts = 0;
	}

	attempt(ip) {
		const now = this.now();
		if (now - this.windowStart >= this.limits.window) {
			this.windowStart = now;
			this.globalAttempts = 0;
			this.attempts.clear();
		}
		if (++this.globalAttempts > this.limits.globalAttempts) throw new Error("realtime upgrade rate exceeded");
		if (!this.attempts.has(ip) && this.attempts.size >= this.limits.trackedIps) throw new Error("realtime upgrade rate exceeded");
		const attempts = (this.attempts.get(ip) || 0) + 1;
		this.attempts.set(ip, attempts);
		if (attempts > this.limits.attempts) throw new Error("realtime upgrade rate exceeded");
	}

	reserve(userId, ip) {
		if (this.active >= this.limits.global || (this.users.get(userId) || 0) >= this.limits.user ||
			(this.ips.get(ip) || 0) >= this.limits.ip) throw new Error("realtime connection limit exceeded");
		this.active++;
		this.users.set(userId, (this.users.get(userId) || 0) + 1);
		this.ips.set(ip, (this.ips.get(ip) || 0) + 1);
		let released = false;
		return () => {
			if (released) return;
			released = true;
			this.active--;
			for (const [map, key] of [[this.users, userId], [this.ips, ip]]) {
				const count = map.get(key) - 1;
				if (count === 0) map.delete(key);
				else map.set(key, count);
			}
		};
	}
}
