<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\AllowImmigration;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class AllowImmigration implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ALLOW_IMMIGRATION';

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

        $colony->setImmigrationState(1);
        $colony->save();
        $game->addInformation(_('Die Einwanderung wurde erlaubt'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
