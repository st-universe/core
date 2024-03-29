<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowSection;

use Stu\Component\Game\GameEnum;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Starmap\Lib\StarmapUiFactoryInterface;
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

        $section = $this->showSectionRequest->getSection();

        // Sanity check if user knows layer
        if (!$game->getUser()->hasSeen($layer->getId())) {
            throw new SanityCheckException('User tried to access an unseen layer');
        }

        $game->setTemplateVar('TABLE_MACRO', 'html/starmapSectionTable.twig');
        $game->setViewTemplate('html/starmapSection.twig');
        $game->appendNavigationPart('starmap.php', _('Sternenkarte'));
        $game->setPageTitle(_('Sektion anzeigen'));

        $helper = $this->starmapUiFactory->createMapSectionHelper();
        $newSection = $helper->setTemplateVars(
            $game,
            $layer,
            $section,
            false,
            $this->showSectionRequest->getDirection()
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
            "registerNavKeys('%s.php', '%s', '%s', false, true);",
            ModuleViewEnum::MAP->value,
            self::VIEW_IDENTIFIER,
            'html/starmapSectionTable.twig'
        ), GameEnum::JS_EXECUTION_AFTER_RENDER);

        $game->addExecuteJS("updateNavigation();", GameEnum::JS_EXECUTION_AFTER_RENDER);
    }
}
