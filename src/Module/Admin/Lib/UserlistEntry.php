<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Lib;

use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;

final class UserlistEntry
{
    private ?CrewCountRetrieverInterface $crewCountRetriever = null;
    private ?CrewLimitCalculatorInterface $crewLimitCalculator = null;
    private ?SpacecraftRumpRepositoryInterface $spacecraftRumpRepository = null;

    public function __construct(
        private User $user,
        ?CrewCountRetrieverInterface $crewCountRetriever = null,
        ?CrewLimitCalculatorInterface $crewLimitCalculator = null,
        ?SpacecraftRumpRepositoryInterface $spacecraftRumpRepository = null
    ) {
        $this->crewCountRetriever = $crewCountRetriever;
        $this->crewLimitCalculator = $crewLimitCalculator;
        $this->spacecraftRumpRepository = $spacecraftRumpRepository;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getUserStateDescription(): string
    {
        if ($this->user->isLocked()) {
            return _('GESPERRT');
        }
        return $this->user->getState()->getDescription();
    }

    public function getUserStateColor(): string
    {
        $user = $this->user;
        if ($user->isLocked()) {
            return _("red");
        }
        if ($user->getState() === UserStateEnum::ACTIVE) {
            return _("greenyellow");
        }
        return '#dddddd';
    }

    public function getCrewOnShips(): int
    {
        if ($this->crewCountRetriever === null) {
            return 0;
        }
        return $this->crewCountRetriever->getAssignedToShipsCount($this->user);
    }

    public function getCrewLimit(): int
    {
        if ($this->crewLimitCalculator === null) {
            return 0;
        }
        return $this->crewLimitCalculator->getGlobalCrewLimit($this->user);
    }

    /**
     * @return array<Colony>
     */
    public function getColonies(): array
    {
        return $this->user->getColonies()->toArray();
    }

    /**
     * @return iterable<array{rump_id: int, amount: int, name: string}>
     */
    public function getShipRumpList(): iterable
    {
        if ($this->spacecraftRumpRepository === null) {
            return [];
        }
        return $this->spacecraftRumpRepository->getGroupedInfoByUser($this->user);
    }
}