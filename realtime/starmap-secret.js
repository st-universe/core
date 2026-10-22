export function realtimeSecret(config, environment = process.env) {
	const secret = environment.STU_REALTIME_SECRET ?? config.game?.map?.encryptionKey;
	if (typeof secret !== "string" || secret.trim().length < 32 ||
		secret === "the key to encrypt map data" || secret === config.db?.database) {
		throw new Error("Configure a random STU_REALTIME_SECRET or game.map.encryptionKey with at least 32 characters");
	}
	return secret;
}
