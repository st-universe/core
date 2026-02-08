<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowOrbitManagement;

use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;

final class ShowOrbitManagement implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SPACECRAFT_MANAGEMENT';

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private ShowOrbitManagementRequestInterface $showOrbitManagementRequest,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            $this->showOrbitManagementRequest->getColonyId(),
            $userId,
            false
        );

        $spacecraftGroups = $this->spacecraftWrapperFactory
            ->wrapSpacecraftsAsGroups($colony->getStarsystemMap()->getSpacecraftsWithoutCloak());

        $game->appendNavigationPart(
            'colony.php',
            _('Kolonien')
        );
        $game->appendNavigationPart(
            sprintf(
                '?%s=1&id=%s',
                ShowColony::VIEW_IDENTIFIER,
                $colony->getId()
            ),
            $colony->getName()
        );
        $game->appendNavigationPart(
            sprintf(
                '?%s=1&id=%d',
                self::VIEW_IDENTIFIER,
                $colony->getId()
            ),
            _('Orbitalmanagement')
        );
        $game->setPagetitle(sprintf('%s Orbit', $colony->getName()));
        $game->setViewTemplate('html/colony/menu/orbitalmanagement.twig');

        $game->setTemplateVar('MANAGER_ID', $colony->getId());
        $game->setTemplateVar('SPACECRAFT_GROUPS', $spacecraftGroups);
    }
}
