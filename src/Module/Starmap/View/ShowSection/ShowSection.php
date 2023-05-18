<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowSection;

use Stu\Component\Game\ModuleViewEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Starmap\Lib\StarmapUiFactoryInterface;
use Stu\Module\Starmap\View\RefreshSection\RefreshSection;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;

final class ShowSection implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SECTION';

    private ShowSectionRequestInterface $showSectionRequest;

    private LayerRepositoryInterface $layerRepository;

    private StarmapUiFactoryInterface $starmapUiFactory;

    public function __construct(
        ShowSectionRequestInterface $showSectionRequest,
        StarmapUiFactoryInterface $starmapUiFactory,
        LayerRepositoryInterface $layerRepository
    ) {
        $this->showSectionRequest = $showSectionRequest;
        $this->layerRepository = $layerRepository;
        $this->starmapUiFactory = $starmapUiFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $layerId = $this->showSectionRequest->getLayerId();
        $layer = $this->layerRepository->find($layerId);

        if (!$layer instanceof LayerInterface) {
            throw new SanityCheckException('Invalid layer');
        }

        $xCoordinate = $this->showSectionRequest->getXCoordinate($layer);
        $yCoordinate = $this->showSectionRequest->getYCoordinate($layer);
        $sectionId = $this->showSectionRequest->getSectionId();

        // Sanity check if user knows layer
        if (!$game->getUser()->hasSeen($layer->getId())) {
            throw new SanityCheckException('User tried to access an unseen layer');
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

        $helper = $this->starmapUiFactory->createMapSectionHelper();
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