<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Override;
use Stu\Component\Borg\BorgSpherePhaseEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;

final class FinishBorgSphereEvent implements SpacecraftDestructionHandlerInterface
{
    public function __construct(private BorgSphereRepositoryInterface $repository) {}

    #[Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {
        $event = $this->repository->getByShip($destroyedSpacecraftWrapper->get());
        if ($event === null || $event->isFinished()) {
            return;
        }
        $event->setPhase(BorgSpherePhaseEnum::DESTROYED)->setShipId(null);
        $this->repository->save($event);
        $subject = $event->getConfiguration()->isAssimilationEnabled() ? 'Der Borg-Kubus' : 'Die Borg-Sphäre';
        $informations->addInformation($subject . ' wurde zerstört. Dort können keine weiteren Naniten geborgen werden');
    }
}
