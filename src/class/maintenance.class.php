<?php

class Maintenance {

	private function startMaintenance() {
		GameConfig::getObjectByOption(CONFIG_GAMESTATE)->setValue(CONFIG_GAMESTATE_VALUE_MAINTENANCE);
	}

	public function handle() {
        $this->startMaintenance();
		$files = dir(MAINTENANCE_DIR);
		while (FALSE !== ($entry = $files->read())) {
			if (!is_file(MAINTENANCE_DIR.$entry)) {
				continue;
			}
			include_once(MAINTENANCE_DIR.$entry);
		}
        $this->finishMaintenance();
	}

	private function finishMaintenance() {
		GameConfig::getObjectByOption(CONFIG_GAMESTATE)->setValue(CONFIG_GAMESTATE_VALUE_ONLINE);
	}
}
