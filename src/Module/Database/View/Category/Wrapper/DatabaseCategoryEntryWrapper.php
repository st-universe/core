<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category\Wrapper;

use Override;
use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\DatabaseUserInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyClassRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;

final class DatabaseCategoryEntryWrapper implements DatabaseCategoryEntryWrapperInterface
{
    public function __construct(
        private DatabaseUserRepositoryInterface $databaseUserRepository,
        private DatabaseEntryInterface $databaseEntry,
        private StarSystemRepositoryInterface $starSystemRepository,
        private StationRepositoryInterface $stationRepository,
        private ColonyClassRepositoryInterface $colonyClassRepository,
        private UserInterface $user
    ) {}

    private ?bool $wasEntryDiscovered = null;

    private ?DatabaseUserInterface $userDiscovery = null;

    /**
     * @todo Refactor this
     * @see \Stu\Module\Database\View\DatabaseEntry\DatabaseEntry
     */
    #[Override]
    public function getObject(): mixed
    {
        return match ($this->databaseEntry->getCategory()->getId()) {
            DatabaseCategoryTypeEnum::DATABASE_CATEGORY_STARSYSTEM => $this->starSystemRepository->find($this->databaseEntry->getObjectId()),
            DatabaseCategoryTypeEnum::DATABASE_CATEGORY_TRADEPOST => $this->stationRepository->find($this->databaseEntry->getObjectId()),
            DatabaseCategoryTypeEnum::DATABASE_CATEGORY_COLONY_CLASS => $this->colonyClassRepository->find($this->databaseEntry->getObjectId()),
            default => null,
        };
    }

    #[Override]
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

    #[Override]
    public function getId(): int
    {
        return $this->databaseEntry->getId();
    }

    #[Override]
    public function getObjectId(): int
    {
        return $this->databaseEntry->getObjectId();
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->databaseEntry->getDescription();
    }

    #[Override]
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
