<?php

class Maintenance {

	function __construct() {
		$this->startMaintenance();
		$this->doMaintenance();
		$this->finishMaintenance();
	}

	private function startMaintenance() {
		GameConfig::getObjectByOption(CONFIG_GAMESTATE)->setValue(CONFIG_GAMESTATE_VALUE_MAINTENANCE);;	
	}

	public function handle() {
		$files = dir(MAINTENANCE_DIR);
		while (FALSE !== ($entry = $files->read())) {
			if (!is_file(MAINTENANCE_DIR.$entry)) {
				continue;
			}
			if (strpos($entry,".swp")) {
				continue;
			}
			include_once(MAINTENANCE_DIR.$entry);
		}
	}

	private function finshMaintenance() {
		GameConfig::getObjectByOption(CONFIG_GAMESTATE)->setValue(CONFIG_GAMESTATE_VALUE_ONLINE);;	
	}
}
?>
