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
            $fields[] = $this->starmapUiFactory->createYRow(0, $value, 1, $system->getMaxX(), $system->getId());
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
        $game->setPageTitle(_('Sektion anzeigen'));
        $game->setTemplateVar('SYSTEM_ID', $system->getId());
        $game->setTemplateVar('HEAD_ROW', range(1, $system->getMaxX()));
        $game->setTemplateVar('MAP_FIELDS', $fields);
        $game->setTemplateVar('PREVIOUS', $this->starSystemRepository->getPreviousStarSystem($system));
        $game->setTemplateVar('NEXT', $this->starSystemRepository->getNextStarSystem($system));
    }
}
