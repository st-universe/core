<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use request;
use Stu\Exception\ShipDoesNotExistException;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Tick\Spacecraft\SpacecraftTickInterface;
use Stu\Module\Tick\Spacecraft\SpacecraftTickManagerInterface;

final class DoManualSpacecraftTick implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SPACECRAFT_TICK';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftTickManagerInterface $spacecraftTickManager,
        private SpacecraftTickInterface $spacecraftTick,
        private SpacecraftLoaderInterface $spacecraftLoader,
        private EntityManagerInterface $entityManager
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTicks::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->isAdmin()) {
            $game->addInformation('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]');
            return;
        }

        //check if single or all ships
        if (!request::getVarByMethod(request::postvars(), 'spacecrafttickid')) {
            $this->spacecraftTickManager->work();
            $game->addInformation("Der Spacecraft-Tick für alle Spacecrafts wurde durchgeführt!");
        } else {
            $shipId = request::postInt('spacecrafttickid');
            $wrapper = $this->spacecraftLoader->find($shipId);

            if ($wrapper === null) {
                throw new ShipDoesNotExistException('Spacecraft does not exist!');
            }

            $this->spacecraftTick->workSpacecraft($wrapper);
            $this->entityManager->flush();

            $game->addInformation("Der Spacecraft-Tick für dieses Spacecraft wurde durchgeführt!");
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
