<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Noodlehaus\ConfigInterface;
use RuntimeException;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Component\Station\Dock\DockTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\DockingPrivilegeRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class AllianceActionManager implements AllianceActionManagerInterface
{
    public function __construct(
        private AllianceJobRepositoryInterface $allianceJobRepository,
        private AllianceRepositoryInterface $allianceRepository,
        private DockingPrivilegeRepositoryInterface $dockingPrivilegeRepository,
        private PrivateMessageSenderInterface $privateMessageSender,
        private UserRepositoryInterface $userRepository,
        private ConfigInterface $config,
        private AllianceJobManagerInterface $allianceJobManager,
        private StationRepositoryInterface $stationRepository
    ) {}

    #[\Override]
    public function assignUserToJob(User $user, AllianceJob $job): void
    {
        $this->allianceJobManager->assignUserToJob($user, $job);
    }

    #[\Override]
    public function delete(Alliance $alliance, bool $sendMesage = true): void
    {
        $this->dockingPrivilegeRepository->truncateByTypeAndTarget(DockTypeEnum::ALLIANCE, $alliance->getId());

        foreach ($alliance->getStations() as $station) {
            $station->setAlliance(null);
            $this->stationRepository->save($station);
        }


        $text = sprintf(_('Die Allianz %s wurde aufgelöst'), $alliance->getName());

        foreach ($alliance->getMembers() as $user) {
            if ($sendMesage === true) {
                $this->privateMessageSender->send(UserConstants::USER_NOONE, $user->getId(), $text);
            }

            $user->setAlliance(null);

            $this->userRepository->save($user);
        }

        if ($alliance->hasAvatar()) {
            $result = @unlink(
                sprintf(
                    '%s/%s/%s.png',
                    $this->config->get('game.webroot'),
                    $this->config->get('game.alliance_avatar_path'),
                    $alliance->getAvatar()
                )
            );

            if ($result === false) {
                throw new RuntimeException('alliance avatar could not be deleted');
            }
        }

        foreach ($alliance->getJobs() as $job) {
            $this->allianceJobRepository->delete($job);
        }

        $this->allianceRepository->delete($alliance);
    }

    #[\Override]
    public function mayEdit(Alliance $alliance, User $user): bool
    {
        return $this->allianceJobManager->hasUserPermission(
            $user,
            $alliance,
            AllianceJobPermissionEnum::EDIT_ALLIANCE->value
        );
    }

    #[\Override]
    public function mayManageForeignRelations(Alliance $alliance, User $user): bool
    {
        return $this->allianceJobManager->hasUserFounderPermission($user, $alliance)
            || $this->allianceJobManager->hasUserSuccessorPermission($user, $alliance)
            || $this->allianceJobManager->hasUserDiplomaticPermission($user, $alliance)
            || $this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::EDIT_DIPLOMATIC_DOCUMENTS->value)
            || $this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::CREATE_AGREEMENTS->value);
    }

    #[\Override]
    public function mayCreateAgreements(Alliance $alliance, User $user): bool
    {
        return $this->allianceJobManager->hasUserPermission(
            $user,
            $alliance,
            AllianceJobPermissionEnum::CREATE_AGREEMENTS->value
        )
            || $this->allianceJobManager->hasUserFounderPermission($user, $alliance)
            || $this->allianceJobManager->hasUserSuccessorPermission($user, $alliance)
            || $this->allianceJobManager->hasUserDiplomaticPermission($user, $alliance);
    }

    #[\Override]
    public function mayManageAlliance(Alliance $alliance, User $user): bool
    {
        return $this->allianceJobManager->hasUserFounderPermission($user, $alliance)
            || $this->allianceJobManager->hasUserSuccessorPermission($user, $alliance)
            || $this->allianceJobManager->hasUserDiplomaticPermission($user, $alliance)
            || $this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::MANAGE_JOBS->value)
            || $this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::VIEW_COLONIES->value)
            || $this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::VIEW_MEMBER_DATA->value)
            || $this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::VIEW_SHIPS->value)
            || $this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::VIEW_ALLIANCE_STORAGE->value);
    }

    #[\Override]
    public function mayEditDiplomaticDocuments(Alliance $alliance, User $user): bool
    {
        return $this->allianceJobManager->hasUserPermission(
            $user,
            $alliance,
            AllianceJobPermissionEnum::EDIT_DIPLOMATIC_DOCUMENTS->value
        ) || $this->allianceJobManager->hasUserFounderPermission($user, $alliance)
            || $this->allianceJobManager->hasUserSuccessorPermission($user, $alliance)
            || $this->allianceJobManager->hasUserDiplomaticPermission($user, $alliance);
    }

    #[\Override]
    public function mayManageApplications(Alliance $alliance, User $user): bool
    {
        return $this->allianceJobManager->hasUserPermission(
            $user,
            $alliance,
            AllianceJobPermissionEnum::MANAGE_APPLICATIONS->value
        );
    }

    #[\Override]
    public function mayManageJobs(Alliance $alliance, User $user): bool
    {
        return $this->allianceJobManager->hasUserPermission(
            $user,
            $alliance,
            AllianceJobPermissionEnum::MANAGE_JOBS->value
        );
    }

    #[\Override]
    public function mayViewColonies(Alliance $alliance, User $user): bool
    {
        return $this->allianceJobManager->hasUserPermission(
            $user,
            $alliance,
            AllianceJobPermissionEnum::VIEW_COLONIES->value
        );
    }

    #[\Override]
    public function mayViewMemberData(Alliance $alliance, User $user): bool
    {
        return $this->allianceJobManager->hasUserPermission(
            $user,
            $alliance,
            AllianceJobPermissionEnum::VIEW_MEMBER_DATA->value
        );
    }

    #[\Override]
    public function mayViewShips(Alliance $alliance, User $user): bool
    {
        return $this->allianceJobManager->hasUserPermission(
            $user,
            $alliance,
            AllianceJobPermissionEnum::VIEW_SHIPS->value
        );
    }

    #[\Override]
    public function mayViewAllianceStorage(Alliance $alliance, User $user): bool
    {
        return $this->allianceJobManager->hasUserPermission(
            $user,
            $alliance,
            AllianceJobPermissionEnum::VIEW_ALLIANCE_STORAGE->value
        );
    }

    #[\Override]
    public function mayViewAllianceHistory(Alliance $alliance, User $user): bool
    {
        return $this->allianceJobManager->hasUserPermission(
            $user,
            $alliance,
            AllianceJobPermissionEnum::VIEW_ALLIANCE_HISTORY->value
        );
    }

    #[\Override]
    public function sendMessage(int $allianceId, string $text): void
    {
        $alliance = $this->allianceRepository->find($allianceId);

        if ($alliance === null) {
            return;
        }

        foreach ($alliance->getMembers() as $member) {
            $this->privateMessageSender->send(UserConstants::USER_NOONE, $member->getId(), $text);
        }
    }

    #[\Override]
    public function mayEditFactionMode(Alliance $alliance, int $factionId): bool
    {
        if ($alliance->getFaction() === null) {
            return true;
        }

        foreach ($alliance->getMembers() as $obj) {
            if ($obj->getFactionId() !== $factionId) {
                return false;
            }
        }

        return true;
    }
}
