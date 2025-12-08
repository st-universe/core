<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Provider;

use BadMethodCallException;
use Doctrine\Common\Collections\Collection;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;

class ManagerProviderSpacecraft implements ManagerProviderInterface
{
    public function __construct(
        private SpacecraftWrapperInterface $wrapper
    ) {}

    #[\Override]
    public function getUser(): User
    {
        return $this->wrapper->get()->getUser();
    }

    #[\Override]
    public function getEps(): int
    {
        return 0;
    }

    #[\Override]
    public function lowerEps(int $amount): ManagerProviderInterface
    {
        throw new BadMethodCallException('spacecraft can not lower eps for transfer');
    }

    #[\Override]
    public function getName(): string
    {
        $spacecraft = $this->wrapper->get();

        return sprintf(
            '%s %s',
            $spacecraft->getRump()->getName(),
            $spacecraft->getName(),
        );
    }

    #[\Override]
    public function getSectorString(): string
    {
        return $this->wrapper->get()->getSectorString();
    }

    #[\Override]
    public function getFreeCrewAmount(): int
    {
        return 0;
    }

    #[\Override]
    public function addCrewAssignment(Spacecraft $spacecraft, int $amount): void
    {
        throw new BadMethodCallException('spacecraft can not add crew for transfer');
    }

    #[\Override]
    public function getFreeCrewStorage(): int
    {
        return 0;
    }

    #[\Override]
    public function addCrewAssignments(array $crewAssignments): void
    {
        throw new BadMethodCallException('spacecraft can not add crew for transfer');
    }

    #[\Override]
    public function getStorage(): Collection
    {
        return $this->wrapper->get()->getStorage();
    }

    #[\Override]
    public function upperStorage(Commodity $commodity, int $amount): void
    {
        throw new BadMethodCallException('spacecraft can not upper storage for transfer');
    }

    #[\Override]
    public function lowerStorage(Commodity $commodity, int $amount): void
    {
        throw new BadMethodCallException('spacecraft can not lower storage for transfer');
    }

    public function getReactorLoad(): int
    {
        $reactor = $this->wrapper->getReactorWrapper();
        return $reactor ? $reactor->getLoad() : 0;
    }

    public function lowerReactorLoad(int $amount): void
    {
        $reactor = $this->wrapper->getReactorWrapper();
        if ($reactor === null) {
            throw new BadMethodCallException('spacecraft has no reactor');
        }

        $reactor->changeLoad(-$amount);
    }
}
