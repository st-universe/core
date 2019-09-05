<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category\Tal;

use Ship;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\DatabaseUserInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;
use UserData;

final class DatabaseCategoryEntryTal implements DatabaseCategoryEntryTalInterface
{
    private $databaseUserRepository;

    private $databaseEntry;

    private $starSystemRepository;

    private $user;

    public function __construct(
        DatabaseUserRepositoryInterface $databaseUserRepository,
        DatabaseEntryInterface $databaseEntry,
        StarSystemRepositoryInterface $starSystemRepository,
        UserData $user
    ) {
        $this->databaseEntry = $databaseEntry;
        $this->databaseUserRepository = $databaseUserRepository;
        $this->starSystemRepository = $starSystemRepository;
        $this->user = $user;
    }

    private $wasEntryDiscovered;

    /**
     * @var DatabaseUserInterface|null
     */
    private $userDiscovery;

    /**
     * @todo Refactor this
     * @see \Stu\Module\Database\View\DatabaseEntry\DatabaseEntry
     */
    public function getObject()
    {
        switch ($this->databaseEntry->getCategory()->getId()) {
            case DATABASE_CATEGORY_STARSYSTEM:
                return $this->starSystemRepository->find($this->databaseEntry->getObjectId());
                break;
            case DATABASE_CATEGORY_TRADEPOST:
                return new Ship($this->databaseEntry->getObjectId());
                break;
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

    public function getDescription(): string
    {
        return $this->databaseEntry->getDescription();
    }

    public function getDiscoveryDate(): int
    {
        if ($this->wasDiscovered() === false) {
            return 0;
        }
        return (int)$this->userDiscovery->getDate();
    }
}
