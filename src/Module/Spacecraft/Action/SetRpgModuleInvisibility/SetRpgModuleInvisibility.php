<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\SetRpgModuleInvisibility;

use request;
use Stu\Component\Realtime\SpacecraftMovementPublisherInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class SetRpgModuleInvisibility implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SET_RPG_INVISIBILITY';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private SpacecraftMovementPublisherInterface $spacecraftMovementPublisher
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        if (!$game->isAdmin()) {
            return;
        }

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );
        $spacecraft = $wrapper->get();
        $rpgModule = $wrapper->getRpgModuleSystemData();

        if ($rpgModule === null) {
            $game->getInfo()->addInformation('Aktion nicht möglich, kein RPG-Modul installiert');
            return;
        }

        $isInvisible = request::getIntFatal('state') === 1;
        $rpgModule->setInvisible($isInvisible)->update();

        if ($isInvisible) {
            $this->spacecraftMovementPublisher->publishRemoval($spacecraft);
            $game->getInfo()->addInformation('RPG-Unsichtbarkeit aktiviert');
        } else {
            $this->spacecraftMovementPublisher->publishState($spacecraft);
            $game->getInfo()->addInformation('RPG-Unsichtbarkeit deaktiviert');
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
