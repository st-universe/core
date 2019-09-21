<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Module\Communication\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceJobInterface;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\DockingPrivilegeRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class AllianceActionManager implements AllianceActionManagerInterface
{
    private $allianceJobRepository;

    private $allianceRelationRepository;

    private $allianceBoardRepository;

    private $allianceRepository;

    private $dockingPrivilegeRepository;

    private $privateMessageSender;

    private $userRepository;

    public function __construct(
        AllianceJobRepositoryInterface $allianceJobRepository,
        AllianceRelationRepositoryInterface $allianceRelationRepository,
        AllianceBoardRepositoryInterface $allianceBoardRepository,
        AllianceRepositoryInterface $allianceRepository,
        DockingPrivilegeRepositoryInterface $dockingPrivilegeRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        UserRepositoryInterface $userRepository
    ) {
        $this->allianceJobRepository = $allianceJobRepository;
        $this->allianceRelationRepository = $allianceRelationRepository;
        $this->allianceBoardRepository = $allianceBoardRepository;
        $this->allianceRepository = $allianceRepository;
        $this->dockingPrivilegeRepository = $dockingPrivilegeRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->userRepository = $userRepository;
    }

    public function setJobForUser(int $allianceId, int $userId, int $jobTypeId): void
    {
        $obj = $this->allianceJobRepository->getSingleResultByAllianceAndType(
            $allianceId,
            $jobTypeId
        );
        if (!$obj) {
            $obj = $this->allianceJobRepository->prototype();
            $obj->setType($jobTypeId);
            $obj->setAlliance($this->allianceRepository->find($allianceId));
        }
        $obj->setUserId($userId);

        $this->allianceJobRepository->save($obj);
    }

    public function delete(int $allianceId): void
    {
        $alliance = $this->allianceRepository->find($allianceId);
        if ($alliance === null) {
            return;
        }

        $this->dockingPrivilegeRepository->truncateByTypeAndTarget(DOCK_PRIVILEGE_ALLIANCE, $allianceId);

        $text = sprintf(_('Die Allianz %s wurde aufgelöst'), $alliance->getName());

        foreach ($alliance->getMembers() as $userRelation) {
            $this->privateMessageSender->send(USER_NOONE, $userRelation->getUserId(), $text);
            $userRelation->getUser()->setAllianceId(0);

            $this->userRepository->save($userRelation->getUser());
        }
        if ($alliance->getAvatar()) {
            @unlink(sprintf('%s/src/%s%s.png', APP_PATH, AVATAR_ALLIANCE_PATH, $alliance->getAvatar()));
        }

        $this->allianceRepository->delete($alliance);
    }

    public function mayEdit(int $allianceId, int $userId): bool
    {
        $successor = $this->allianceJobRepository->getSingleResultByAllianceAndType($allianceId,
            ALLIANCE_JOBS_SUCCESSOR);
        $founder = $this->allianceJobRepository->getSingleResultByAllianceAndType($allianceId, ALLIANCE_JOBS_FOUNDER);

        return (
                $successor !== null && $userId === $successor->getUserId()
            ) || $userId === $founder->getUserId();
    }

    public function mayManageForeignRelations(int $allianceId, int $userId): bool
    {
        $job = $this->allianceJobRepository->getSingleResultByAllianceAndType($allianceId, ALLIANCE_JOBS_DIPLOMATIC);

        if ($job === null || $job->getUserId() !== $userId) {
            return $this->mayEdit($allianceId, $userId);
        }

        return true;
    }

    public function sendMessage(int $allianceId, string $text): void
    {
        /** @var AllianceJobInterface[] $jobList */
        $jobList = array_filter(
            $this->allianceJobRepository->getByAlliance($allianceId),
            function (AllianceJobInterface $job): bool {
                return $job->getType() !== ALLIANCE_JOBS_PENDING;
            }
        );

        foreach ($jobList as $job) {
            $this->privateMessageSender->send(USER_NOONE, $job->getUserId(), $text);
        }
    }

    public function mayEditFactionMode(AllianceInterface $alliance, int $factionId): bool
    {
        if ($alliance->getFactionId() != 0) {
            return true;
        }
        foreach ($alliance->getMembers() as $key => $obj) {
            if ($obj->getUser()->getFaction() !== $factionId) {
                return false;
            }
        }
        return true;
    }
}