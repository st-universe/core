<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class TorpedoFabProvider implements GuiComponentProviderInterface
{
    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    public function __construct(
        TorpedoTypeRepositoryInterface $torpedoTypeRepository
    ) {
        $this->torpedoTypeRepository = $torpedoTypeRepository;
    }

    public function setTemplateVariables(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game
    ): void {

        $game->setTemplateVar(
            'BUILDABLE_TORPEDO_TYPES',
            $this->torpedoTypeRepository->getForUser($game->getUser()->getId())
        );
    }
}
