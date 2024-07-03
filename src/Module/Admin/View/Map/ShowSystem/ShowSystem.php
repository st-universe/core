<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map\ShowSystem;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Starmap\Lib\StarmapUiFactoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;

final class ShowSystem implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SYSTEM';

    public function __construct(private ShowSystemRequestInterface $showSystemRequest, private StarmapUiFactoryInterface $starmapUiFactory, private StarSystemRepositoryInterface $starSystemRepository)
    {
    }

    #[Override]
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

        $game->setTemplateFile('html/admin/mapeditor_system.twig');
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

        $game->setPageTitle(sprintf(_('System %s editieren'), $system->getName()));
        $game->setTemplateVar('SYSTEM', $system);
        $game->setTemplateVar('HEAD_ROW', range(1, $system->getMaxX()));
        $game->setTemplateVar('MAP_FIELDS', $fields);
        $game->setTemplateVar('PREVIOUS', $previousSystem);
        $game->setTemplateVar('NEXT', $nextSystem);

        $game->addExecuteJS(sprintf(
            'registerSystemEditorNavKeys(%d, %d, %d);',
            $previousSystem === null ? 0 : $previousSystem->getId(),
            $system->getId(),
            $nextSystem === null ? 0 : $nextSystem->getId()
        ));
    }
}
