import crypto from "node:crypto";
import { verifyToken } from "./starmap-token.js";

export class StarmapTracking {
	constructor(coverage) {
		this.contacts = new Map();
		this.fields = new Map();
		this.enemies = new Map();
		this.sources = new Map();
		this.refresh(coverage);
	}

	refresh(coverage) {
		this.sources = new Map((coverage.sources || []).map(source => [Number(source.id), buildKeys(source.runs || [])]));
		const previous = this.contacts;
		this.contacts = new Map();
		this.fields = new Map();
		for (const contact of coverage.contacts || []) {
			const old = previous.get(Number(contact.id));
			if (old && (old.x !== Number(contact.x) || old.y !== Number(contact.y))) {
				this.enemies.delete(Number(contact.id));
			}
			this.store(contact);
		}
		for (const [id, enemy] of this.enemies) {
			const contact = this.contacts.get(id);
			const sources = contact ? this.sourcesAt(contact) : new Set();
			if (!contact || ![...enemy.sourceIds].some(sourceId => sources.has(sourceId))) {
				this.enemies.delete(id);
			} else {
				enemy.sourceIds = sources;
			}
		}
		for (const key of this.fields.keys()) {
			this.reconcileField(key);
		}
	}

	store(spacecraft) {
		const contact = {
			id: Number(spacecraft.id), x: Number(spacecraft.x), y: Number(spacecraft.y),
			isOwn: Boolean(spacecraft.isOwn), isFriendly: Boolean(spacecraft.isFriendly),
			isCloakedSignature: Boolean(spacecraft.isCloakedSignature)
		};
		if (![contact.id, contact.x, contact.y].every(Number.isFinite)) {
			return;
		}
		this.contacts.set(contact.id, contact);
		const key = fieldKey(contact);
		if (!this.fields.has(key)) {
			this.fields.set(key, new Set());
		}
		this.fields.get(key).add(contact.id);
	}

	update(spacecraft, visible, from = null) {
		const id = Number(spacecraft.id);
		const previous = this.contacts.get(id);
		this.removeContact(id);
		if (!visible) {
			this.enemies.delete(id);
			return;
		}
		this.store(spacecraft);
		const contact = this.contacts.get(id);
		const enemy = this.enemies.get(id);
		if (enemy && (!contact || contact.isOwn || contact.isFriendly || contact.isCloakedSignature ||
			!this.follow(enemy, from || previous || contact, contact))) {
			this.enemies.delete(id);
		}
		if (contact) {
			this.reconcileField(fieldKey(contact));
		}
	}

	follow(enemy, from, to) {
		const steps = Math.max(Math.abs(Number(to.x) - Number(from.x)), Math.abs(Number(to.y) - Number(from.y)));
		if (!Number.isFinite(steps) || ![...enemy.sourceIds].some(id => this.sources.get(id)?.has(fieldKey(from)))) {
			return false;
		}
		for (let step = 0; step <= steps; step++) {
			const fraction = steps === 0 ? 0 : step / steps;
			const point = {
				x: Math.round(Number(from.x) + (Number(to.x) - Number(from.x)) * fraction),
				y: Math.round(Number(from.y) + (Number(to.y) - Number(from.y)) * fraction)
			};
			const sources = this.sourcesAt(point);
			if (sources.size === 0) {
				return false;
			}
			const foreignIds = [...(this.fields.get(fieldKey(point)) || [])].filter(id => {
				const contact = this.contacts.get(id);
				return !contact.isOwn && !contact.isFriendly;
			});
			if (foreignIds.some(id => this.enemies.get(id)?.scanId !== enemy.scanId)) {
				for (const id of foreignIds) {
					this.enemies.delete(id);
				}
				return false;
			}
			enemy.sourceIds = sources;
		}
		return true;
	}

	sourcesAt(point) {
		const key = fieldKey(point);
		return new Set([...this.sources].filter(([_id, keys]) => keys.has(key)).map(([id]) => id));
	}

	removeContact(id) {
		const old = this.contacts.get(id);
		this.contacts.delete(id);
		if (old) {
			const key = fieldKey(old);
			const ids = this.fields.get(key);
			ids?.delete(id);
			if (ids?.size === 0) {
				this.fields.delete(key);
			}
		}
	}

	remove(id) {
		this.removeContact(Number(id));
		this.enemies.delete(Number(id));
	}

	applyScan(scan, scanId) {
		const key = fieldKey(scan);
		const scanned = scan.spacecrafts || [];
		const foreignIds = [...(this.fields.get(key) || [])].filter(id => {
			const contact = this.contacts.get(id);
			return !contact.isOwn && !contact.isFriendly;
		});
		for (const id of foreignIds) {
			this.enemies.delete(id);
		}
		const scannedIds = new Set(scanned.map(contact => Number(contact.id)));
		if (scanned.length === 0 || !scanned.every(contact => contact.isEnemy && Number(contact.userId) > 0 && !contact.isCloakedSignature) ||
			foreignIds.length !== scannedIds.size || !foreignIds.every(id => scannedIds.has(id)) ||
			!this.sources.get(Number(scan.sourceId))?.has(key)) {
			return;
		}
		for (const contact of scanned) {
			this.enemies.set(Number(contact.id), {
				scanId, sourceIds: this.sourcesAt(scan), userId: Number(contact.userId), allianceId: contact.allianceId
			});
		}
		this.reconcileField(key);
	}

	reconcileField(key) {
		const foreignIds = [...(this.fields.get(key) || [])].filter(id => {
			const contact = this.contacts.get(id);
			return !contact.isOwn && !contact.isFriendly;
		});
		const scanIds = new Set(foreignIds.map(id => this.enemies.get(id)?.scanId));
		if (scanIds.has(undefined) || scanIds.size > 1 || foreignIds.some(id => this.contacts.get(id).isCloakedSignature)) {
			for (const id of foreignIds) {
				this.enemies.delete(id);
			}
		}
	}

	identifiedIds() {
		return [...this.enemies.keys()].sort((a, b) => a - b);
	}
}

export async function acceptScanToken(client, token, secret, consumeNonce) {
	const auth = verifyToken(token, secret);
	if (auth.purpose !== "starmapScan" || auth.userId !== client.userId || auth.layerId !== client.layerId ||
		auth.connectionId !== client.connectionId || typeof auth.nonce !== "string" || !/^[a-f0-9]{16}$/.test(auth.nonce)) {
		throw new Error("invalid scan authorization");
	}
	if (typeof auth.encryptedScan !== "string") throw new Error("missing encrypted scan");
	const encrypted = Buffer.from(auth.encryptedScan, "base64");
	const decipher = crypto.createDecipheriv("aes-256-gcm", crypto.createHash("sha256").update(secret).digest(), encrypted.subarray(0, 12));
	decipher.setAuthTag(encrypted.subarray(12, 28));
	const scan = JSON.parse(Buffer.concat([decipher.update(encrypted.subarray(28)), decipher.final()]).toString("utf8"));
	if (scan.success !== true || Number(scan.layerId) !== client.layerId || !Array.isArray(scan.spacecrafts)) {
		throw new Error("invalid scan snapshot");
	}
	if (!await consumeNonce(auth.nonce, Math.max(1, auth.exp - Math.floor(Date.now() / 1000) + 1))) {
		throw new Error("scan authorization already consumed");
	}
	client.tracking.applyScan(scan, auth.nonce);
}

function buildKeys(runs) {
	const keys = new Set();
	for (const run of runs) {
		for (let x = Number(run.startX); x <= Number(run.endX); x++) {
			keys.add(x + ":" + Number(run.y));
		}
	}
	return keys;
}

function fieldKey(point) {
	return Number(point.x) + ":" + Number(point.y);
}
