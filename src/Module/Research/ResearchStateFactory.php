<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Module\Award\Lib\CreateUserAwardInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpUserRepositoryInterface;

final class ResearchStateFactory implements ResearchStateFactoryInterface
{
    private ResearchedRepositoryInterface $researchedRepository;

    private ShipRumpUserRepositoryInterface $shipRumpUserRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private CreateDatabaseEntryInterface $createDatabaseEntry;

    private CrewCreatorInterface $crewCreator;

    private ShipCreatorInterface $shipCreator;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private EntityManagerInterface $entityManager;

    private CreateUserAwardInterface $createUserAward;

    public function __construct(
        ResearchedRepositoryInterface $researchedRepository,
        ShipRumpUserRepositoryInterface $shipRumpUserRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        CreateDatabaseEntryInterface $createDatabaseEntry,
        CrewCreatorInterface $crewCreator,
        ShipCreatorInterface $shipCreator,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        CreateUserAwardInterface $createUserAward,
        EntityManagerInterface $entityManager
    ) {
        $this->researchedRepository = $researchedRepository;
        $this->shipRumpUserRepository = $shipRumpUserRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->createDatabaseEntry = $createDatabaseEntry;
        $this->crewCreator = $crewCreator;
        $this->shipCreator = $shipCreator;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->entityManager = $entityManager;
        $this->createUserAward = $createUserAward;
    }

    public function createResearchState(): ResearchStateInterface
    {
        return new ResearchState(
            $this->researchedRepository,
            $this->shipRumpUserRepository,
            $this->privateMessageSender,
            $this->createDatabaseEntry,
            $this->crewCreator,
            $this->shipCreator,
            $this->shipRepository,
            $this->shipSystemManager,
            $this->createUserAward,
            $this->entityManager,
        );
    }
}
