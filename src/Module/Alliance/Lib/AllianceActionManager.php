<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Noodlehaus\ConfigInterface;
use Override;
use RuntimeException;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Component\Station\Dock\DockTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceJobInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\DockingPrivilegeRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class AllianceActionManager implements AllianceActionManagerInterface
{
    public function __construct(
        private AllianceJobRepositoryInterface $allianceJobRepository,
        private AllianceRepositoryInterface $allianceRepository,
        private DockingPrivilegeRepositoryInterface $dockingPrivilegeRepository,
        private PrivateMessageSenderInterface $privateMessageSender,
        private UserRepositoryInterface $userRepository,
        private ConfigInterface $config
    ) {}

    #[Override]
    public function setJobForUser(int $allianceId, int $userId, int $jobTypeId): void
    {
        $obj = $this->allianceJobRepository->getSingleResultByAllianceAndType(
            $allianceId,
            $jobTypeId
        );
        if ($obj === null) {
            $obj = $this->allianceJobRepository->prototype();
            $obj->setType($jobTypeId);
            $obj->setAlliance($this->allianceRepository->find($allianceId));
        }
        $obj->setUser($this->userRepository->find($userId));

        if (!$obj->getAlliance()->getJobs()->containsKey($jobTypeId)) {
            $obj->getAlliance()->getJobs()->set($jobTypeId, $obj);
        }

        $this->allianceJobRepository->save($obj);
    }

    #[Override]
    public function delete(AllianceInterface $alliance, bool $sendMesage = true): void
    {
        $this->dockingPrivilegeRepository->truncateByTypeAndTarget(DockTypeEnum::ALLIANCE, $alliance->getId());

        $text = sprintf(_('Die Allianz %s wurde aufgelöst'), $alliance->getName());

        foreach ($alliance->getMembers() as $user) {
            if ($sendMesage === true) {
                $this->privateMessageSender->send(UserEnum::USER_NOONE, $user->getId(), $text);
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
    public function mayEdit(AllianceInterface $alliance, UserInterface $user): bool
    {
        $successor = $alliance->getSuccessor();
        $founder = $alliance->getFounder();

        return ($successor !== null && $user === $successor->getUser()
        ) || $user === $founder->getUser();
    }

    #[Override]
    public function mayManageForeignRelations(AllianceInterface $alliance, UserInterface $user): bool
    {
        $diplomatic = $alliance->getDiplomatic();

        if ($diplomatic === null || $diplomatic->getUser() !== $user) {
            return $this->mayEdit($alliance, $user);
        }

        return true;
    }

    #[Override]
    public function sendMessage(int $allianceId, string $text): void
    {
        /** @var AllianceJobInterface[] $jobList */
        $jobList = array_filter(
            $this->allianceJobRepository->getByAlliance($allianceId),
            static fn(AllianceJobInterface $job): bool => $job->getType() !== AllianceEnum::ALLIANCE_JOBS_PENDING
        );

        foreach ($jobList as $job) {
            $this->privateMessageSender->send(UserEnum::USER_NOONE, $job->getUserId(), $text);
        }
    }

    #[Override]
    public function mayEditFactionMode(AllianceInterface $alliance, int $factionId): bool
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
