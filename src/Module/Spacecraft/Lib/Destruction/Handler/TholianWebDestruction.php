<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use RuntimeException;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\TholianWeb;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

class TholianWebDestruction implements SpacecraftDestructionHandlerInterface
{
    public function __construct(
        private TholianWebRepositoryInterface $tholianWebRepository,
        private SpacecraftSystemRepositoryInterface $spacecraftSystemRepository,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private TholianWebUtilInterface $tholianWebUtil
    ) {}

    #[\Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations,
    ): void {

        $tholianWeb = $destroyedSpacecraftWrapper->get();
        if (!$tholianWeb instanceof TholianWeb) {
            return;
        }

        foreach ($tholianWeb->getCapturedSpacecrafts() as $spacecraft) {
            $spacecraft->setHoldingWeb(null);
            $this->spacecraftRepository->save($spacecraft);
        }

        $owningSpacecraftSystem = $this->spacecraftSystemRepository->getWebOwningShipSystem($tholianWeb->getId());
        if ($owningSpacecraftSystem === null) {
            return;
        }

        $this->tholianWebUtil->resetWebHelpers($tholianWeb, $this->spacecraftWrapperFactory);

        /** @var Ship */
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
