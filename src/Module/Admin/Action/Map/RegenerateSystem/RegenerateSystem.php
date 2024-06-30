<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\RegenerateSystem;

use request;
use RuntimeException;
use Stu\Component\StarSystem\StarSystemCreationInterface;
use Stu\Module\Admin\View\Map\ShowSystem\ShowSystem;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\NamesRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;

final class RegenerateSystem implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'REGENERATE_SYSTEM';

    private StarSystemRepositoryInterface $starSystemRepository;

    private NamesRepositoryInterface $namesRepository;

    private StarSystemCreationInterface $starSystemCreation;

    public function __construct(
        StarSystemRepositoryInterface $starSystemRepository,
        NamesRepositoryInterface $namesRepository,
        StarSystemCreationInterface $starSystemCreation
    ) {
        $this->starSystemRepository = $starSystemRepository;
        $this->namesRepository = $namesRepository;
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

        $map = $starSystem->getMap();
        if ($map === null) {
            return;
        }

        if ($map->getLayer()->isFinished()) {
            $game->addInformation('Der Layer ist fertig, kein Neugenerierung mehr möglich');
            return;
        }

        $systemName = current($this->namesRepository->getRandomFreeSystemNames(1));
        if ($systemName === false) {
            throw new RuntimeException('no free system name available');
        }

        $this->starSystemCreation->recreateStarSystem($map, $systemName->getName());

        $game->addInformation('Das System wurde neu generiert.');
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
