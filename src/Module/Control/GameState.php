<?php

namespace Stu\Module\Control;

use Override;
use Stu\Component\Game\GameStateEnum;
use Stu\Exception\MaintenanceGameStateException;
use Stu\Exception\RelocationGameStateException;
use Stu\Exception\ResetGameStateException;
use Stu\Orm\Entity\GameConfig;
use Stu\Orm\Repository\GameConfigRepositoryInterface;

final class GameState implements GameStateInterface
{
    /** @var GameConfig[]|null */
    private ?array $gameConfig = null;

    public function __construct(
        private readonly GameConfigRepositoryInterface $gameConfigRepository
    ) {}

    #[Override]
    public function getGameState(): GameStateEnum
    {
        return GameStateEnum::from($this->getGameConfig()[self::CONFIG_GAMESTATE]->getValue());
    }

    #[Override]
    public function checkGameState(bool $isAdmin): void
    {
        $gameState = $this->getGameState();

        if ($gameState === GameStateEnum::MAINTENANCE && !$isAdmin) {
            throw new MaintenanceGameStateException();
        }

        if ($gameState === GameStateEnum::RESET) {
            throw new ResetGameStateException();
        }

        if ($gameState === GameStateEnum::RELOCATION) {
            throw new RelocationGameStateException();
        }
    }
    /**
     * @return array<int, GameConfig>
     */
    private function getGameConfig(): array
    {
        if ($this->gameConfig === null) {
            $this->gameConfig = [];

            foreach ($this->gameConfigRepository->findAll() as $item) {
                $this->gameConfig[$item->getOption()] = $item;
            }
        }
        return $this->gameConfig;
    }
}
