<?php

namespace Stu\Module\Database\View\Category\Wrapper;

use Stu\Orm\Entity\ColonyClass;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\StarSystem;

interface DatabaseCategoryEntryWrapperInterface
{
    /**
     * @return null|StarSystem|Spacecraft|ColonyClass
     */
    public function getObject(): mixed;

    public function wasDiscovered(): bool;

    public function getId(): int;

    public function getObjectId(): int;

    public function getDescription(): string;

    public function getDiscoveryDate(): int;
}
