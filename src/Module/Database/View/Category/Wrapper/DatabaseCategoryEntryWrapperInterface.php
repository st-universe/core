<?php

namespace Stu\Module\Database\View\Category\Wrapper;

use Stu\Orm\Entity\ColonyClassInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;

interface DatabaseCategoryEntryWrapperInterface
{
    /**
     * @return null|StarSystemInterface|ShipInterface|ColonyClassInterface
     */
    public function getObject(): mixed;

    public function wasDiscovered(): bool;

    public function getId(): int;

    public function getObjectId(): int;

    public function getDescription(): string;

    public function getDiscoveryDate(): int;
}
