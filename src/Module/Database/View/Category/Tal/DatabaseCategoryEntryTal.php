<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category\Tal;

use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\DatabaseUserInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyClassRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;

final class DatabaseCategoryEntryTal implements DatabaseCategoryEntryTalInterface
{
    private DatabaseUserRepositoryInterface $databaseUserRepository;

    private DatabaseEntryInterface $databaseEntry;

    private StarSystemRepositoryInterface $starSystemRepository;

    private ShipRepositoryInterface $shipRepository;

    private ColonyClassRepositoryInterface $colonyClassRepository;

    private UserInterface $user;

    public function __construct(
        DatabaseUserRepositoryInterface $databaseUserRepository,
        DatabaseEntryInterface $databaseEntry,
        StarSystemRepositoryInterface $starSystemRepository,
        ShipRepositoryInterface $shipRepository,
        ColonyClassRepositoryInterface $colonyClassRepository,
        UserInterface $user
    ) {
        $this->databaseEntry = $databaseEntry;
        $this->databaseUserRepository = $databaseUserRepository;
        $this->starSystemRepository = $starSystemRepository;
        $this->shipRepository = $shipRepository;
        $this->colonyClassRepository = $colonyClassRepository;
        $this->user = $user;
    }

    private ?bool $wasEntryDiscovered = null;

    private ?DatabaseUserInterface $userDiscovery = null;

    /**
     * @todo Refactor this
     * @see \Stu\Module\Database\View\DatabaseEntry\DatabaseEntry
     */
    public function getObject(): mixed
    {
        switch ($this->databaseEntry->getCategory()->getId()) {
            case DatabaseCategoryTypeEnum::DATABASE_CATEGORY_STARSYSTEM:
                return $this->starSystemRepository->find($this->databaseEntry->getObjectId());
            case DatabaseCategoryTypeEnum::DATABASE_CATEGORY_TRADEPOST:
                return $this->shipRepository->find($this->databaseEntry->getObjectId());
            case DatabaseCategoryTypeEnum::DATABASE_CATEGORY_COLONY_CLASS:
                return $this->colonyClassRepository->find($this->databaseEntry->getObjectId());
        }

        return null;
    }

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

    public function getId(): int
    {
        return $this->databaseEntry->getId();
    }

    public function getObjectId(): int
    {
        return $this->databaseEntry->getObjectId();
    }

    public function getDescription(): string
    {
        return $this->databaseEntry->getDescription();
    }

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
