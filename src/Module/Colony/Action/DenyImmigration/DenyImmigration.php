<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DenyImmigration;

use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class DenyImmigration implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_PERMIT_IMMIGRATION';

    private $colonyLoader;

    private $colonyGuiHelper;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $game->setView(ShowColony::VIEW_IDENTIFIER, ['COLONY_MENU' => MENU_OPTION]);

        $colony->setImmigrationState(0);
        $colony->save();
        $game->addInformation(_('Die Einwanderung wurde verboten'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
