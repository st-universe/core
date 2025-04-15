<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowSection;

use Override;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Map\DirectionEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Starmap\Lib\StarmapUiFactoryInterface;
use Stu\Module\Starmap\View\RefreshSection\RefreshSection;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;

final class ShowSection implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SECTION';

    public function __construct(private ShowSectionRequestInterface $showSectionRequest, private StarmapUiFactoryInterface $starmapUiFactory, private LayerRepositoryInterface $layerRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $layerId = $this->showSectionRequest->getLayerId();
        $layer = $this->layerRepository->find($layerId);

        if (!$layer instanceof LayerInterface) {
            throw new SanityCheckException('Invalid layer');
        }

        $section = $this->showSectionRequest->getSection();

        // Sanity check if user knows layer
        if (!$game->getUser()->hasSeen($layer->getId())) {
            throw new SanityCheckException('User tried to access an unseen layer');
        }

        $game->setTemplateVar('TABLE_MACRO', 'html/map/starmapSectionTable.twig');
        $game->setViewTemplate('html/map/starmapSection.twig');
        $game->appendNavigationPart('starmap.php', _('Sternenkarte'));
        $game->setPageTitle(_('Sektion anzeigen'));

        $helper = $this->starmapUiFactory->createMapSectionHelper();
        $directionValue = $this->showSectionRequest->getDirection();
        $newSection = $helper->setTemplateVars(
            $game,
            $layer,
            $section,
            false,
            $directionValue !== null ? DirectionEnum::from($directionValue) : null
        );

        $game->appendNavigationPart(
            sprintf(
                'starmap.php?SHOW_SECTION=1&section=%d&layerid=%d',
                $newSection,
                $layer->getId()
            ),
            sprintf(_('Sektion %d anzeigen'), $newSection)
        );

        $game->addExecuteJS(sprintf(
            "registerNavKeys('%s.php', '%s', '%s');",
            ModuleEnum::STARMAP->value,
            RefreshSection::VIEW_IDENTIFIER,
            'html/map/starmapSectionTable.twig'
        ), GameEnum::JS_EXECUTION_AJAX_UPDATE);

        $game->addExecuteJS("updateNavigation();", GameEnum::JS_EXECUTION_AFTER_RENDER);
    }
}
