<?php
class TickManager {

	private $turn = NULL;

	function __construct() {
		$this->turn = GameTurn::getCurrentTurn();
		$this->endTurn();
		$this->mainLoop();
		$this->startTurn();
	}

	function getTurn() {
		return $this->turn;
	}

	function endTurn() {
		$this->getTurn()->setEnd(time());
		$this->getTurn()->save();
	}

	function startTurn() {
		$obj = new GameTurnData;
		$obj->setStart(time());
		$obj->setTurn($this->getTurn()->getNextTurn());
		$obj->save();
	}

	function mainLoop() {
		while (TRUE) {
			if (!$this->hitLockFiles()) {
				break;
			}
			sleep(1);
		}
	}

	function hitLockFiles() {
		for($i=1;$i<=PROCESS_COUNT;$i++) {
			if (@file_exists(LOCKFILE_DIR.'col'.$i.'.lock')) {
				return TRUE;
			}
		}
		return FALSE;
	}
}
?>
