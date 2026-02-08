<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class TorpedoFabProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(private TorpedoTypeRepositoryInterface $torpedoTypeRepository) {}

    #[\Override]
    public function setTemplateVariables(
        $entity,
        GameControllerInterface $game
    ): void {

        $game->setTemplateVar(
            'BUILDABLE_TORPEDO_TYPES',
            $this->torpedoTypeRepository->getForUser($game->getUser()->getId())
        );
    }
}
