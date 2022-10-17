<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowSection;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Starmap\Lib\MapSectionHelper;

final class ShowSection implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SECTION';

    private ShowSectionRequestInterface $showSectionRequest;

    public function __construct(
        ShowSectionRequestInterface $showSectionRequest
    ) {
        $this->showSectionRequest = $showSectionRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $xCoordinate = $this->showSectionRequest->getXCoordinate();
        $yCoordinate = $this->showSectionRequest->getYCoordinate();
        $sectionId = $this->showSectionRequest->getSectionId();

        $game->setTemplateFile('html/starmap_section.xhtml');
        $game->appendNavigationPart('starmap.php', _('Sternenkarte'));
        $game->appendNavigationPart(
            sprintf(
                'starmap.php?SHOW_SECTION=1&x=%d&y=%d&sec=%d',
                $xCoordinate,
                $yCoordinate,
                $sectionId
            ),
            sprintf(_('Sektion %d anzeigen'), $sectionId)
        );
        $game->setPageTitle(_('Sektion anzeigen'));

        $helper = new MapSectionHelper();
        $helper->setTemplateVars($game, $xCoordinate, $yCoordinate, $sectionId, self::VIEW_IDENTIFIER);
    }
}
