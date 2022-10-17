<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowSubspaceTelescope;

use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Starmap\Lib\MapSectionHelper;
use Stu\Module\Starmap\View\ShowSection\ShowSectionRequestInterface;

final class RefreshSubspaceSection implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'REFRESH_SUBSPACE_SECTION';

    private ShowSectionRequestInterface $request;

    public function __construct(
        ShowSectionRequestInterface $request
    ) {
        $this->request = $request;
    }

    public function handle(GameControllerInterface $game): void
    {
        $xCoordinate = $this->request->getXCoordinate();
        $yCoordinate = $this->request->getYCoordinate();
        $sectionId = $this->request->getSectionId();

        $game->showMacro('html/colonymacros.xhtml/telescope_nav');

        $helper = new MapSectionHelper();
        $helper->setTemplateVars(
            $game,
            $xCoordinate,
            $yCoordinate,
            $sectionId,
            ModuleViewEnum::MODULE_VIEW_COLONY,
            self::VIEW_IDENTIFIER
        );
    }
}
