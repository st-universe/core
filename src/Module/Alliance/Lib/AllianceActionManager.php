<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Alliance;
use PM;
use Stu\Orm\Entity\AllianceJobInterface;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

final class AllianceActionManager implements AllianceActionManagerInterface
{
    private $allianceJobRepository;

    private $allianceRelationRepository;

    private $allianceBoardRepository;

    public function __construct(
        AllianceJobRepositoryInterface $allianceJobRepository,
        AllianceRelationRepositoryInterface $allianceRelationRepository,
        AllianceBoardRepositoryInterface $allianceBoardRepository
    ) {
        $this->allianceJobRepository = $allianceJobRepository;
        $this->allianceRelationRepository = $allianceRelationRepository;
        $this->allianceBoardRepository = $allianceBoardRepository;
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
            $obj->setAllianceId($allianceId);
        }
        $obj->setUserId($userId);

        $this->allianceJobRepository->save($obj);
    }

    public function delete(int $allianceId): void
    {
        $this->allianceJobRepository->truncateByAlliance($allianceId);

        $relationList = $this->allianceRelationRepository->getByAlliance($allianceId);

        foreach ($relationList as $relation) {
            $this->allianceRelationRepository->delete($relation);
        }

        $list = $this->allianceBoardRepository->getByAlliance((int)$allianceId);
        foreach ($list as $board) {
            $this->allianceBoardRepository->delete($board);
        }

        $alliance = new Alliance($allianceId);

        $text = sprintf(_('Die Allianz %s wurde aufgelöst'), $alliance->getName());

        foreach ($alliance->getMembers() as $userRelation) {
            if ($alliance->getFounder()->getUserId() != $userRelation->getUserId()) {
                PM::sendPM(USER_NOONE, $userRelation->getUserId(), $text);
            }
            $userRelation->getUser()->setAllianceId(0);
            $userRelation->getUser()->save();
        }
        if ($alliance->getAvatar()) {
            @unlink(sprintf('%s/src/%s%s.png', APP_PATH, AVATAR_ALLIANCE_PATH, $alliance->getAvatar()));
        }

        $alliance->deleteFromDatabase();
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
            PM::sendPM(USER_NOONE, $job->getUserId(), $text);
        }
    }

    public function mayEditFactionMode(\AllianceData $alliance, int $factionId): bool
    {
        if ($alliance->isNew()) {
            return true;
        }
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