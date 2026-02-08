<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateRelation;

use Psr\EventDispatcher\EventDispatcherInterface;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\Alliance\Event\DiplomaticRelationProposedEvent;
use Stu\Component\Alliance\Event\WarDeclaredEvent;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class CreateRelation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_NEW_RELATION';

    public function __construct(
        private readonly CreateRelationRequestInterface $createRelationRequest,
        private readonly AllianceRelationRepositoryInterface $allianceRelationRepository,
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

        if (!$this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::CREATE_AGREEMENTS)) {
            throw new AccessViolationException();
        }

        $counterpartId = $this->createRelationRequest->getCounterpartId();
        $typeId = $this->createRelationRequest->getRelationType();

        $counterpart = $this->allianceRepository->find($counterpartId);
        $relationType = AllianceRelationTypeEnum::tryFrom($typeId);
        if (
            $counterpart === null
            || $alliance->getId() === $counterpart->getId()
            || $relationType === null
        ) {
            return;
        }

        $cnt = $this->allianceRelationRepository->getPendingCountByAlliances($allianceId, $counterpartId);
        if ($cnt >= 2) {
            $game->getInfo()->addInformation('Es gibt bereits ein Angebot für diese Allianz');
            return;
        }

        // check if a relation exists
        $existingRelations = $this->allianceRelationRepository->getByAlliancePair($allianceId, $counterpartId);

        // Iteriere durch die gefundenen Einträge
        foreach ($existingRelations as $existingRelation) {
            $existingRelationType = $existingRelation->getType();

            if ($existingRelationType === $relationType) {
                return;
            }
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
                $relationType
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
