<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use request;
use Stu\Component\Game\GameStateEnum;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\GameStateInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;

final class SetGameState implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SET_GAME_STATE';

    public function __construct(private GameConfigRepositoryInterface $gameConfigRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowScripts::VIEW_IDENTIFIER);

        if (!$game->isAdmin()) {
            $game->getInfo()->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $gameState = GameStateEnum::tryFrom(request::postInt('game_state'));
        if ($gameState === null) {
            $game->getInfo()->addInformation(_('Ungültiger Spielmodus'));
            return;
        }

        $config = $this->gameConfigRepository->getByOption(GameStateInterface::CONFIG_GAMESTATE);
        if ($config === null) {
            $game->getInfo()->addInformation(_('Spielmodus-Konfiguration nicht gefunden'));
            return;
        }

        $config->setValue($gameState->value);
        $this->gameConfigRepository->save($config);

        $game->getInfo()->addInformation(sprintf(
            _('Der Spielmodus wurde auf "%s" gesetzt'),
            $gameState->getDescription()
        ));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
