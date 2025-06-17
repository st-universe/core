<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateRelation;

use Override;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Component\Alliance\Event\DiplomaticRelationProposedEvent;
use Stu\Component\Alliance\Event\WarDeclaredEvent;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class CreateRelation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_NEW_RELATION';

    public function __construct(private CreateRelationRequestInterface $createRelationRequest, private AllianceRelationRepositoryInterface $allianceRelationRepository, private AllianceActionManagerInterface $allianceActionManager, private AllianceRepositoryInterface $allianceRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException();
        }

        $allianceId = $alliance->getId();
        $user = $game->getUser();

        if (!$this->allianceActionManager->mayManageForeignRelations($alliance, $user)) {
            throw new AccessViolationException();
        }

        $counterpartId = $this->createRelationRequest->getCounterpartId();
        $typeId = $this->createRelationRequest->getRelationType();

        $counterpart = $this->allianceRepository->find($counterpartId);
        if (
            $counterpart === null
            || $alliance === $counterpart
            || !in_array($typeId, AllianceEnum::ALLOWED_RELATION_TYPES, true)
        ) {
            return;
        }

        $cnt = $this->allianceRelationRepository->getPendingCountByAlliances($allianceId, $counterpartId);
        if ($cnt >= 2) {
            $game->addInformation('Es gibt bereits ein Angebot für diese Allianz');
            return;
        }

        // check if a relation exists
        $existingRelations = $this->allianceRelationRepository->getByAlliancePair($allianceId, $counterpartId);

        // Iteriere durch die gefundenen Einträge
        foreach ($existingRelations as $existingRelation) {
            $existingRelationType = $existingRelation->getType();

            if ($existingRelationType === $typeId) {
                return;
            }
        }

        if ($typeId === AllianceEnum::ALLIANCE_RELATION_WAR) {
            $game->triggerEvent(new WarDeclaredEvent(
                $alliance,
                $counterpart,
                $user
            ));

            $game->addInformation(
                sprintf('Der Allianz %s wurde der Krieg erklärt', $counterpart->getName())
            );
        } else {
            $game->triggerEvent(new DiplomaticRelationProposedEvent(
                $alliance,
                $counterpart,
                $typeId
            ));

            $game->addInformation('Das Abkommen wurde angeboten');
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
