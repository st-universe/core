<?php

namespace Stu\Module\Tick;

use GameTurn;
use GameTurnData;
use Stu\Module\Tick\Colony\ColonyTickManager;

final class TickManager implements TickManagerInterface
{

    public const PROCESS_COUNT = 1;

    public function work(): void
    {
        $turn = GameTurn::getCurrentTurn();
        $this->endTurn($turn);
        $this->mainLoop();
        $this->startTurn($turn);
    }

    private function endTurn(GameTurnData $turn): void
    {
        $turn->setEnd(time());
        $turn->save();
    }

    private function startTurn(GameTurnData $turn): void
    {
        $obj = new GameTurnData();
        $obj->setStart(time());
        $obj->setTurn($turn->getNextTurn());
        $obj->save();
    }

    private function mainLoop(): void
    {
        while (true) {
            if ($this->hitLockFiles() === false) {
                break;
            }
            sleep(1);
        }
    }

    private function hitLockFiles(): bool
    {
        for ($i = 1; $i <= self::PROCESS_COUNT; $i++) {
            if (@file_exists(ColonyTickManager::LOCKFILE_DIR . 'col' . $i . '.lock')) {
                return true;
            }
        }
        return false;
    }
}
