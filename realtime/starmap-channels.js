export function starmapChannels(config = {}) {
	const namespace = config.redisNamespace ?? "stu";
	if (typeof namespace !== "string" || namespace.length < 1 || namespace.length > 64 || /[^a-zA-Z0-9_-]/.test(namespace)) {
		throw new Error("Invalid realtime.redisNamespace");
	}
	return {
		streamKey: `${namespace}:realtime:starmap:spacecraft`,
		coveragePrefix: `${namespace}:realtime:starmap:coverage:`
	};
}
