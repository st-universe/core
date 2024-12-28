<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Override;
use RuntimeException;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

class TholianWebDestruction implements SpacecraftDestructionHandlerInterface
{
    public function __construct(
        private TholianWebRepositoryInterface $tholianWebRepository,
        private SpacecraftSystemRepositoryInterface $spacecraftSystemRepository,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    #[Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations,
    ): void {

        if (!$destroyedSpacecraftWrapper instanceof ShipWrapperInterface) {
            return;
        }

        $tholianWeb = $destroyedSpacecraftWrapper->get()->getTholianWeb();
        if ($tholianWeb === null) {
            return;
        }

        foreach ($tholianWeb->getCapturedShips() as $ship) {
            $ship->setHoldingWeb(null);
            $this->spacecraftRepository->save($ship);
        }

        $owningSpacecraftSystem = $this->spacecraftSystemRepository->getWebOwningShipSystem($tholianWeb->getId());
        if ($owningSpacecraftSystem === null) {
            return;
        }

        /** @var ShipInterface */
        $owningShip = $owningSpacecraftSystem->getSpacecraft();

        $webEmitterSystemData = $this->spacecraftWrapperFactory
            ->wrapShip($owningShip)
            ->getWebEmitterSystemData();

        if ($webEmitterSystemData === null) {
            throw new RuntimeException('this should not happen');
        }

        $webEmitterSystemData->setOwnedWebId(null)
            ->update();

        $this->tholianWebRepository->delete($tholianWeb);
    }
}
