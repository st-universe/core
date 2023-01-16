<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowSection;

use Stu\Component\Game\ModuleViewEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Starmap\Lib\MapSectionHelper;
use Stu\Module\Starmap\View\RefreshSection\RefreshSection;

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
        $layerId = $this->showSectionRequest->getLayerId();
        $layer = $this->layerRepository->find($layerId);

        $xCoordinate = $this->showSectionRequest->getXCoordinate($layer);
        $yCoordinate = $this->showSectionRequest->getYCoordinate($layer);
        $sectionId = $this->showSectionRequest->getSectionId();

        //sanity check if user knows layer
        if (!$game->getUser()->hasSeen($layer->getId())) {
            throw new SanityCheckException('user tried to access unseen layer');
        }

        $game->setTemplateFile('html/starmap_section.xhtml');
        $game->appendNavigationPart('starmap.php', _('Sternenkarte'));
        $game->appendNavigationPart(
            sprintf(
                'starmap.php?SHOW_SECTION=1&x=%d&y=%d&sec=%d&layerid=%d',
                $xCoordinate,
                $yCoordinate,
                $sectionId,
                $layer->getId()
            ),
            sprintf(_('Sektion %d anzeigen'), $sectionId)
        );
        $game->setPageTitle(_('Sektion anzeigen'));

        $helper = new MapSectionHelper();
        $helper->setTemplateVars(
            $game,
            $layer,
            $xCoordinate,
            $yCoordinate,
            $sectionId,
            ModuleViewEnum::MODULE_VIEW_STARMAP,
            RefreshSection::VIEW_IDENTIFIER
        );
    }
}
