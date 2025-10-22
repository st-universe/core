<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Noodlehaus\ConfigInterface;
use Override;
use RuntimeException;
use Stu\Component\Station\Dock\DockTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\DockingPrivilegeRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Repository\AllianceMemberJobRepositoryInterface;

final class AllianceActionManager implements AllianceActionManagerInterface
{
    public function __construct(
        private AllianceJobRepositoryInterface $allianceJobRepository,
        private AllianceRepositoryInterface $allianceRepository,
        private DockingPrivilegeRepositoryInterface $dockingPrivilegeRepository,
        private PrivateMessageSenderInterface $privateMessageSender,
        private UserRepositoryInterface $userRepository,
        private ConfigInterface $config,
        private AllianceJobManagerInterface $allianceJobManager
    ) {}

    #[Override]
    public function assignUserToJob(User $user, AllianceJob $job): void
    {
        $this->allianceJobManager->assignUserToJob($user, $job);
    }

    #[Override]
    public function delete(Alliance $alliance, bool $sendMesage = true): void
    {
        $this->dockingPrivilegeRepository->truncateByTypeAndTarget(DockTypeEnum::ALLIANCE, $alliance->getId());

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

    #[Override]
    public function mayEdit(Alliance $alliance, User $user): bool
    {
        return $this->allianceJobManager->hasUserFounderPermission($user, $alliance)
            || $this->allianceJobManager->hasUserSuccessorPermission($user, $alliance);
    }

    #[Override]
    public function mayManageForeignRelations(Alliance $alliance, User $user): bool
    {
        return $this->allianceJobManager->hasUserDiplomaticPermission($user, $alliance)
            || $this->mayEdit($alliance, $user);
    }

    #[Override]
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

    #[Override]
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
