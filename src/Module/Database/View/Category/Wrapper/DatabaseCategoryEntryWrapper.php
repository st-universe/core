<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category\Wrapper;

use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\DatabaseUser;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyClassRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;

final class DatabaseCategoryEntryWrapper implements DatabaseCategoryEntryWrapperInterface
{
    public function __construct(
        private DatabaseUserRepositoryInterface $databaseUserRepository,
        private DatabaseEntry $databaseEntry,
        private StarSystemRepositoryInterface $starSystemRepository,
        private StationRepositoryInterface $stationRepository,
        private ColonyClassRepositoryInterface $colonyClassRepository,
        private User $user
    ) {}

    private ?bool $wasEntryDiscovered = null;

    private ?DatabaseUser $userDiscovery = null;

    /**
     * @todo Refactor this
     * @see \Stu\Module\Database\View\DatabaseEntry\DatabaseEntry
     */
    #[\Override]
    public function getObject(): mixed
    {
        return match ($this->databaseEntry->getCategory()->getId()) {
            DatabaseCategoryTypeEnum::STARSYSTEM->value => $this->starSystemRepository->find($this->databaseEntry->getObjectId()),
            DatabaseCategoryTypeEnum::TRADEPOST->value => $this->stationRepository->find($this->databaseEntry->getObjectId()),
            DatabaseCategoryTypeEnum::COLONY_CLASS->value => $this->colonyClassRepository->find($this->databaseEntry->getObjectId()),
            default => null,
        };
    }

    #[\Override]
    public function wasDiscovered(): bool
    {
        if ($this->wasEntryDiscovered === null) {
            $result = $this->databaseUserRepository->findFor(
                $this->databaseEntry->getId(),
                $this->user->getId()
            );
            if ($result === null) {
                $this->wasEntryDiscovered = false;
            } else {
                $this->wasEntryDiscovered = true;
                $this->userDiscovery = $result;
            }
        }

        return $this->wasEntryDiscovered;
    }

    #[\Override]
    public function getId(): int
    {
        return $this->databaseEntry->getId();
    }

    #[\Override]
    public function getObjectId(): int
    {
        return $this->databaseEntry->getObjectId();
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->databaseEntry->getDescription();
    }

    #[\Override]
    public function getDiscoveryDate(): int
    {
        if ($this->wasDiscovered() === false) {
            return 0;
        }
        $userDiscovery = $this->userDiscovery;
        if ($userDiscovery === null) {
            return 0;
        }

        return $userDiscovery->getDate();
    }
}
