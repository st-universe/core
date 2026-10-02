<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib;

use BadMethodCallException;
use Stu\Orm\Entity\GameTurn;
use Stu\Orm\Repository\GameTurnRepositoryInterface;

final class GameTurnProvider implements GameTurnProviderInterface
{
    private ?GameTurn $currentRound = null;

    public function __construct(
        private readonly GameTurnRepositoryInterface $gameTurnRepository
    ) {}

    public function getCurrentRound(): GameTurn
    {
        if ($this->currentRound === null) {
            $this->currentRound = $this->gameTurnRepository->getCurrent();
            if ($this->currentRound === null) {
                throw new BadMethodCallException('no current round existing');
            }
        }
        return $this->currentRound;
    }
}
