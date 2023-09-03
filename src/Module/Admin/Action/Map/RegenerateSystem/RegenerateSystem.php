<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\RegenerateSystem;

use request;
use Stu\Component\StarSystem\StarSystemCreationInterface;
use Stu\Module\Admin\View\Map\ShowSystem\ShowSystem;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;

final class RegenerateSystem implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'REGENERATE_SYSTEM';

    private StarSystemRepositoryInterface $starSystemRepository;

    private StarSystemCreationInterface $starSystemCreation;

    public function __construct(
        StarSystemRepositoryInterface $starSystemRepository,
        StarSystemCreationInterface $starSystemCreation
    ) {
        $this->starSystemRepository = $starSystemRepository;
        $this->starSystemCreation = $starSystemCreation;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSystem::VIEW_IDENTIFIER);

        $systemId = request::getInt('sysid');

        $starSystem = $this->starSystemRepository->find($systemId);
        if ($starSystem === null) {
            return;
        }

        $map = $starSystem->getMapField();
        if ($map === null) {
            return;
        }

        $this->starSystemCreation->recreateStarSystem($map);

        $game->addInformation('Das System wurde neu generiert.');
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
