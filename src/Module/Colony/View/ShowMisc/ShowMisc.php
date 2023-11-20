<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowMisc;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowMisc implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MISC';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId,
            false
        );

        $this->colonyGuiHelper->registerComponents($colony, $game);
        $game->setTemplateVar('CURRENT_MENU', ColonyMenuEnum::MENU_OPTION);

        $game->showMacro(ColonyMenuEnum::MENU_OPTION->getTemplate());
    }
}
