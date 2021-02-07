<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowLoadShields;

use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowLoadShields implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_LOAD_SHIELDS';

    private ColonyLoaderInterface $colonyLoader;

    private ShowLoadShieldsRequestInterface $showLoadShieldsRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowLoadShieldsRequestInterface $showLoadShieldsRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showLoadShieldsRequest = $showLoadShieldsRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showLoadShieldsRequest->getColonyId(),
            $userId
        );

        $game->setPageTitle(_('Schilde laden'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/colonymacros.xhtml/show_load_shields');
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('SHIELDS_PER_EPS', ColonyEnum::SHIELDS_PER_EPS);
    }
}
