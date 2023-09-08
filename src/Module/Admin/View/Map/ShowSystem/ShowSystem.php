<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map\ShowSystem;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Starmap\Lib\StarmapUiFactoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;

final class ShowSystem implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SYSTEM';

    private ShowSystemRequestInterface $showSystemRequest;

    private StarSystemRepositoryInterface $starSystemRepository;

    private StarmapUiFactoryInterface $starmapUiFactory;

    public function __construct(
        ShowSystemRequestInterface $showSystemRequest,
        StarmapUiFactoryInterface $starmapUiFactory,
        StarSystemRepositoryInterface $starSystemRepository
    ) {
        $this->showSystemRequest = $showSystemRequest;
        $this->starSystemRepository = $starSystemRepository;
        $this->starmapUiFactory = $starmapUiFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $system = $this->starSystemRepository->find($this->showSystemRequest->getSystemId());

        if ($system === null) {
            return;
        }

        $fields = [];
        foreach (range(1, $system->getMaxY()) as $value) {
            $fields[] = $this->starmapUiFactory->createYRow($system->getLayer(), $value, 1, $system->getMaxX(), $system);
        }

        $game->setTemplateFile('html/admin/mapeditor_system.twig', true);
        $game->appendNavigationPart(sprintf(
            '/admin/?SHOW_MAP_EDITOR=1&layerid=%d',
            $system->getLayer()->getId()
        ), _('Karteneditor'));
        $game->appendNavigationPart(
            sprintf(
                '/admin/?%s=1&sysid=%d',
                static::VIEW_IDENTIFIER,
                $system->getId()
            ),
            sprintf(_('System %s editieren'), $system->getName())
        );

        $previousSystem = $this->starSystemRepository->getPreviousStarSystem($system);
        $nextSystem = $this->starSystemRepository->getNextStarSystem($system);

        $game->setPageTitle(_('Sektion anzeigen'));
        $game->setTemplateVar('SYSTEM', $system);
        $game->setTemplateVar('HEAD_ROW', range(1, $system->getMaxX()));
        $game->setTemplateVar('MAP_FIELDS', $fields);
        $game->setTemplateVar('PREVIOUS', $previousSystem);
        $game->setTemplateVar('NEXT', $nextSystem);

        $game->addExecuteJS(sprintf('var currentId = %d;', $system->getId()));
        $game->addExecuteJS(sprintf('var previousId = %d;', $previousSystem === null ? 0 : $previousSystem->getId()));
        $game->addExecuteJS(sprintf('var nextId = %d;', $nextSystem === null ? 0 : $nextSystem->getId()));
        $game->addExecuteJS("registerSystemEditorNavKey();");
    }
}
