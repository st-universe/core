<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Doctrine\ORM\EntityManagerInterface;
use Override;
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
    public function __construct(private ResearchedRepositoryInterface $researchedRepository, private ShipRumpUserRepositoryInterface $shipRumpUserRepository, private PrivateMessageSenderInterface $privateMessageSender, private CreateDatabaseEntryInterface $createDatabaseEntry, private CrewCreatorInterface $crewCreator, private ShipCreatorInterface $shipCreator, private ShipRepositoryInterface $shipRepository, private ShipSystemManagerInterface $shipSystemManager, private CreateUserAwardInterface $createUserAward, private EntityManagerInterface $entityManager)
    {
    }

    #[Override]
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
