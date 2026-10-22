import crypto from "node:crypto";

export class StarmapContacts {
	constructor() {
		this.handles = new Map();
		this.spacecrafts = new Map();
		this.identified = new Set();
		this.changed = false;
	}

	serialize(spacecraft) {
		const id = Number(spacecraft.id);
		const previous = this.spacecrafts.get(id);
		if (previous && (Boolean(previous.isOwn || previous.isFriendly) !== Boolean(spacecraft.isOwn || spacecraft.isFriendly)
			|| Boolean(previous.isCloakedSignature) !== Boolean(spacecraft.isCloakedSignature))) {
			this.handles.delete(id);
			this.changed = true;
		}
		this.spacecrafts.set(id, spacecraft);
		if (spacecraft.isOwn || spacecraft.isFriendly) {
			this.handles.set(id, id);
		} else if (!this.handles.has(id) || this.handles.get(id) > 0) {
			this.handles.set(id, -crypto.randomInt(1, 2 ** 48));
		}
		return { ...spacecraft, id: this.handles.get(id) };
	}

	forget(id) {
		id = Number(id);
		const handle = this.handles.get(id);
		this.handles.delete(id);
		this.spacecrafts.delete(id);
		this.identified.delete(id);
		return handle;
	}

	resetRecognition(identified) {
		const current = new Set(identified);
		const changed = [...this.identified].some(id => !current.has(id) && this.handles.get(id) < 0);
		if (changed) {
			for (const [id, handle] of this.handles) {
				if (handle < 0) this.handles.delete(id);
			}
		}
		this.identified = current;
		return changed;
	}

	snapshot() {
		return [...this.spacecrafts.values()].map(spacecraft => this.serialize(spacecraft));
	}
}
