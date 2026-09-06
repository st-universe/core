<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateRelation;

use Psr\EventDispatcher\EventDispatcherInterface;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\Alliance\Enum\RelationPermissionEnum;
use Stu\Component\Alliance\Event\DiplomaticRelationProposedEvent;
use Stu\Component\Alliance\Event\WarDeclaredEvent;
use Stu\Component\Player\Relation\UserRelationManagerInterface;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\RelationPermissionRepositoryInterface;
use Stu\Orm\Repository\RelationRepositoryInterface;

final class CreateRelation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_NEW_RELATION';

    public function __construct(
        private readonly CreateRelationRequestInterface $createRelationRequest,
        private readonly RelationRepositoryInterface $allianceRelationRepository,
        private readonly RelationPermissionRepositoryInterface $relationPermissionRepository,
        private readonly UserRelationManagerInterface $userRelationManager,
        private readonly AllianceJobManagerInterface $allianceJobManager,
        private readonly AllianceRepositoryInterface $allianceRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException();
        }

        $allianceId = $alliance->getId();
        $user = $game->getUser();

        if (!$this->allianceJobManager->hasUserPermission(
            $user,
            $alliance,
            AllianceJobPermissionEnum::CREATE_AGREEMENTS
        )) {
            throw new AccessViolationException();
        }

        $counterpartId = $this->createRelationRequest->getCounterpartId();
        $typeId = $this->createRelationRequest->getRelationType();
        $permissions = $this->createRelationRequest->getPermissions();

        $counterpart = $this->allianceRepository->find($counterpartId);
        $relationType = AllianceRelationTypeEnum::tryFrom($typeId);
        if ($counterpart === null || $alliance->getId() === $counterpart->getId() || $relationType === null) {
            return;
        }
        $permissions = RelationPermissionEnum::sanitize($permissions, $relationType);

        $cnt = $this->allianceRelationRepository->getPendingCountByAlliances($allianceId, $counterpartId);
        if ($cnt >= 2) {
            $game->getInfo()->addInformation('Es gibt bereits ein Angebot für diese Allianz');
            return;
        }

        $existingRelations = $this->allianceRelationRepository->getByAlliancePair(
            $allianceId,
            $counterpartId
        );

        foreach ($existingRelations as $existingRelation) {
            if ($existingRelation->getType() !== $relationType) {
                continue;
            }

            if ($existingRelation->isPending()) {
                return;
            }

            if ($this->relationPermissionRepository->hasSamePermissions($existingRelation, $permissions)) {
                return;
            }

            if ($this->userRelationManager->proposePermissionChange($user, $existingRelation, $permissions)) {
                $game->getInfo()->addInformation('Die Rechteänderung wurde angeboten');
            }
            return;
        }

        if ($relationType === AllianceRelationTypeEnum::WAR) {
            $this->eventDispatcher->dispatch(new WarDeclaredEvent(
                $alliance,
                $counterpart,
                $user
            ));

            $game->getInfo()->addInformation(
                sprintf('Der Allianz %s wurde der Krieg erklärt', $counterpart->getName())
            );
        } else {
            $this->eventDispatcher->dispatch(new DiplomaticRelationProposedEvent(
                $alliance,
                $counterpart,
                $relationType,
                $permissions
            ));

            $game->getInfo()->addInformation('Das Abkommen wurde angeboten');
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
