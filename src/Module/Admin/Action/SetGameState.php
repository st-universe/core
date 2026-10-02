<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use request;
use Stu\Component\Game\GameStateEnum;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\GameStateInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;

final class SetGameState implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SET_GAME_STATE';

    public function __construct(private GameConfigRepositoryInterface $gameConfigRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowScripts::VIEW_IDENTIFIER);

        $contextState = GameStateEnum::tryFrom(request::postInt('game_state'));
        if ($contextState === null) {
            $context->getInfo()->addInformation(_('Ungültiger Spielmodus'));
            return;
        }

        $config = $this->gameConfigRepository->getByOption(GameStateInterface::CONFIG_GAMESTATE);
        if ($config === null) {
            $context->getInfo()->addInformation(_('Spielmodus-Konfiguration nicht gefunden'));
            return;
        }

        $config->setValue($contextState->value);
        $this->gameConfigRepository->save($config);

        $context->getInfo()->addInformation(sprintf(
            _('Der Spielmodus wurde auf "%s" gesetzt'),
            $contextState->getDescription()
        ));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
