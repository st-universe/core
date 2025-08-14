<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Override;
use Stu\Module\Award\Lib\CreateUserAwardInterface;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ShipRumpUserRepositoryInterface;

final class ResearchStateFactory implements ResearchStateFactoryInterface
{
    public function __construct(
        private readonly ResearchedRepositoryInterface $researchedRepository,
        private readonly ShipRumpUserRepositoryInterface $shipRumpUserRepository,
        private readonly PrivateMessageSenderInterface $privateMessageSender,
        private readonly CreateDatabaseEntryInterface $createDatabaseEntry,
        private readonly ShipCreatorInterface $shipCreator,
        private readonly CreateUserAwardInterface $createUserAward
    ) {}

    #[Override]
    public function createResearchState(): ResearchStateInterface
    {
        return new ResearchState(
            $this->researchedRepository,
            $this->shipRumpUserRepository,
            $this->privateMessageSender,
            $this->createDatabaseEntry,
            $this->shipCreator,
            $this->createUserAward
        );
    }
}
