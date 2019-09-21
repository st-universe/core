<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\Abandon;

use AccessViolation;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ColonyStorageRepositoryInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;

final class Abandon implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_GIVEUP_COLONY';

    private $abandonRequest;

    private $colonyTerraformingRepository;

    private $colonyStorageRepository;

    private $colonyShipQueueRepository;

    private $colonyLibFactory;

    private $colonyRepository;

    public function __construct(
        AbandonRequestInterface $abandonRequest,
        ColonyTerraformingRepositoryInterface $colonyTerraformingRepository,
        ColonyStorageRepositoryInterface $colonyStorageRepository,
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->abandonRequest = $abandonRequest;
        $this->colonyTerraformingRepository = $colonyTerraformingRepository;
        $this->colonyStorageRepository = $colonyStorageRepository;
        $this->colonyShipQueueRepository = $colonyShipQueueRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->colonyRepository = $colonyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyRepository->find($this->abandonRequest->getColonyId());

        if ($colony === null || $colony->getUserId() !== $game->getUser()->getId()) {
            throw new AccessViolation();
        }

        $colonyId = (int) $colony->getId();

        $this->colonyLibFactory->createColonySurface($colony)->updateSurface();

        $colony->setEps(0);
        $colony->setMaxEps(0);
        $colony->setMaxStorage(0);
        $colony->setWorkers(0);
        $colony->setWorkless(0);
        $colony->setMaxBev(0);
        $colony->setImmigrationState(true);
        $colony->setPopulationLimit(0);
        $colony->setUserId(USER_NOONE);
        $colony->setName('');

        $this->colonyRepository->save($colony);

        $this->colonyStorageRepository->truncateByColony($colonyId);

        foreach ($this->colonyTerraformingRepository->getByColony([$colonyId]) as $fieldTerraforming) {
            $this->colonyTerraformingRepository->delete($fieldTerraforming);
        }

        $this->colonyShipQueueRepository->truncateByColony($colonyId);

        $game->addInformation(_('Die Kolonie wurde aufgegeben'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
