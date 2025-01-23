<?php

namespace Stu\Module\Database\View\Category\Wrapper;

use Stu\Orm\Entity\ColonyClassInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StarSystemInterface;

interface DatabaseCategoryEntryWrapperInterface
{
    /**
     * @return null|StarSystemInterface|SpacecraftInterface|ColonyClassInterface
     */
    public function getObject(): mixed;

    public function wasDiscovered(): bool;

    public function getId(): int;

    public function getObjectId(): int;

    public function getDescription(): string;

    public function getDiscoveryDate(): int;
}
