<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Component\Game\GameEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ColonyStorageRepositoryInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ColonyResetter implements ColonyResetterInterface
{
    private $colonyRepository;

    private $userRepository;

    private $colonyLibFactory;

    private $colonyStorageRepository;

    private $colonyTerraformingRepository;

    private $colonyShipQueueRepository;

    public function __construct(
        ColonyRepositoryInterface $colonyRepository,
        UserRepositoryInterface $userRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        ColonyStorageRepositoryInterface $colonyStorageRepository,
        ColonyTerraformingRepositoryInterface $colonyTerraformingRepository,
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository
    ) {
        $this->colonyRepository = $colonyRepository;
        $this->userRepository = $userRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->colonyStorageRepository = $colonyStorageRepository;
        $this->colonyTerraformingRepository = $colonyTerraformingRepository;
        $this->colonyShipQueueRepository = $colonyShipQueueRepository;
    }

    public function reset(
        ColonyInterface $colony
    ): void {
        $this->colonyLibFactory->createColonySurface($colony)->updateSurface();

        $colony->setEps(0)
            ->setMaxEps(0)
            ->setMaxStorage(0)
            ->setWorkers(0)
            ->setWorkless(0)
            ->setMaxBev(0)
            ->setImmigrationstate(true)
            ->setPopulationlimit(0)
            ->setUser($this->userRepository->find(GameEnum::USER_NOONE))
            ->setName('');

        $this->colonyRepository->save($colony);

        $this->colonyStorageRepository->truncateByColony($colony);

        foreach ($this->colonyTerraformingRepository->getByColony([$colony]) as $fieldTerraforming) {
            $this->colonyTerraformingRepository->delete($fieldTerraforming);
        }

        $this->colonyShipQueueRepository->truncateByColony($colony);
    }
}
