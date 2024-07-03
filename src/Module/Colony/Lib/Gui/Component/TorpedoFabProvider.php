<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class TorpedoFabProvider implements GuiComponentProviderInterface
{
    public function __construct(private TorpedoTypeRepositoryInterface $torpedoTypeRepository)
    {
    }

    #[Override]
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
