<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowByPosition;

use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Starmap\Lib\MapSectionHelper;
use Stu\Module\Starmap\View\ShowSection\ShowSectionRequestInterface;

final class ShowByPosition implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STARMAP_POSITION';

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

        $game->setMacroInAjaxWindow('html/macros.xhtml/starmap');

        $helper = new MapSectionHelper();
        $helper->setTemplateVars(
            $game,
            $xCoordinate,
            $yCoordinate,
            $sectionId,
            ModuleViewEnum::MODULE_VIEW_STARMAP,
            self::VIEW_IDENTIFIER
        );
    }
}
