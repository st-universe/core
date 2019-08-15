<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category\Tal;

use DatabaseUser;
use DatabaseUserData;
use Ship;
use StarSystem;
use Stu\Orm\Entity\DatabaseEntryInterface;

final class DatabaseCategoryEntryTal implements DatabaseCategoryEntryTalInterface
{

    private $databaseEntry;

    public function __construct(
        DatabaseEntryInterface $databaseEntry
    ) {
        $this->databaseEntry = $databaseEntry;
    }

    private $wasEntryDiscovered;

    /**
     * @var DatabaseUserData|null
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
                return new StarSystem($this->databaseEntry->getObjectId());
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
            $result = DatabaseUser::getBy($this->databaseEntry->getId(), currentUser()->getId());
            if ($result === false) {
                $this->wasEntryDiscovered = false;
            } else {
                $this->wasEntryDiscovered = true;
                $this->userDiscovery = $result;
            }
        }

        return $this->wasEntryDiscovered;
    }

    private function getDBUserObject(): ?DatabaseUserData
    {
        if (!$this->wasDiscovered()) {
            return null;
        }
        return DatabaseUser::getBy($this->databaseEntry->getId(), currentUser()->getId());
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
