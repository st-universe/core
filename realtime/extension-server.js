import { spawn, spawnSync } from "node:child_process";
import { fileURLToPath } from "node:url";
import path from "node:path";

const coreRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const configuration = spawnSync(process.env.PHP_BINARY || "php", [path.join(coreRoot, "bin/extension-runtime.php"), "--list"], {
  cwd: coreRoot,
  encoding: "utf8",
});
if (configuration.error || configuration.status !== 0) {
  console.error(configuration.error?.message || configuration.stderr);
  process.exit(1);
}

const services = JSON.parse(configuration.stdout);
const children = [];
let stopping = false;
if (process.argv.includes("--with-starmap")) {
  services.unshift({ id: "starmap", entry: path.join(coreRoot, "realtime/starmap-server.js") });
}
for (const service of services) {
  const child = spawn(process.execPath, [service.entry], {
    cwd: coreRoot,
    stdio: "inherit",
    env: { ...process.env, STU_CORE_ROOT: coreRoot },
  });
  children.push(child);
  child.on("error", (error) => {
    console.error(`[realtime:${service.id}] ${error.message}`);
    process.exitCode = 1;
  });
  child.on("exit", (code, signal) => {
    if (!stopping) {
      console.error(`[realtime:${service.id}] stopped (${signal || code})`);
      process.exitCode = code || 1;
    }
  });
}
function stop(signal) {
  stopping = true;
  for (const child of children) child.kill(signal);
  const timer = setTimeout(() => {
    for (const child of children) {
      if (child.exitCode === null && child.signalCode === null) child.kill("SIGKILL");
    }
  }, 5000);
  timer.unref();
}
process.on("SIGINT", () => stop("SIGINT"));
process.on("SIGTERM", () => stop("SIGTERM"));
