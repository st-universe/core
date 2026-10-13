<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Lib;

use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;

final class UserlistEntry
{
    public function __construct(
        private User $user,
        private ?CrewCountRetrieverInterface $crewCountRetriever = null,
        private ?CrewLimitCalculatorInterface $crewLimitCalculator = null,
        private ?SpacecraftRumpRepositoryInterface $spacecraftRumpRepository = null
    ) {}

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

    public function getVacationStatus(): ?string
    {
        if (!$this->user->isVacationMode()) {
            return null;
        }

        return $this->user->isVacationRequestOldEnough()
            ? _('Aktiv')
            : _('Beantragt (48 Stunden Wartezeit)');
    }

    public function getPendingVacationActivationDate(): ?int
    {
        if (!$this->user->isVacationMode() || $this->user->isVacationRequestOldEnough()) {
            return null;
        }

        return $this->user->getVacationRequestDate() + UserConstants::VACATION_DELAY_IN_SECONDS;
    }

    public function getDeletionStatus(): ?string
    {
        $deletionMark = $this->user->getRegistration()->getDeletionMark();

        return match ($deletionMark) {
            0, UserConstants::DELETION_FORBIDDEN => null,
            UserConstants::DELETION_REQUESTED => _('Angefragt, Bestätigung ausstehend'),
            UserConstants::DELETION_CONFIRMED => _('Zur Löschung vorgemerkt (bestätigt)'),
            UserConstants::DELETION_EXECUTED => _('Löschung ausgeführt'),
            default => sprintf(_('Unbekannter Löschstatus (%d)'), $deletionMark),
        };
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
