<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowOrbitManagement;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class ShowOrbitManagement implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ORBITAL_SHIPS';

    private $colonyLoader;

    private $showOrbitManagementRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowOrbitManagementRequestInterface $showOrbitManagementRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showOrbitManagementRequest = $showOrbitManagementRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showOrbitManagementRequest->getColonyId(),
            $userId
        );

        $game->appendNavigationPart(
            sprintf('?%s=1id=%s',
                ShowColony::VIEW_IDENTIFIER,
                $colony->getId()
            ),
            $colony->getNameWithoutMarkup()
        );
        $game->appendNavigationPart(
            sprintf('?id=%d&%s=1',
                static::VIEW_IDENTIFIER,
                $colony->getId()),
            _('Orbitalmanagement')
        );
        $game->setPagetitle(sprintf('%s Orbit', $colony->getNameWithoutMarkup()));
        $game->setTemplateFile('html/orbitalmanagement.xhtml');

        $game->setTemplateVar('COLONY', $colony);
    }
}
