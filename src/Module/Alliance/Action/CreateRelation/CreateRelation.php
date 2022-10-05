<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateRelation;

use Stu\Exception\AccessViolation;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class CreateRelation implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_NEW_RELATION';

    private CreateRelationRequestInterface $createRelationRequest;

    private EntryCreatorInterface $entryCreator;

    private AllianceRelationRepositoryInterface $allianceRelationRepository;

    private AllianceActionManagerInterface $allianceActionManager;

    private AllianceRepositoryInterface $allianceRepository;

    public function __construct(
        CreateRelationRequestInterface $createRelationRequest,
        EntryCreatorInterface $entryCreator,
        AllianceRelationRepositoryInterface $allianceRelationRepository,
        AllianceActionManagerInterface $allianceActionManager,
        AllianceRepositoryInterface $allianceRepository
    ) {
        $this->createRelationRequest = $createRelationRequest;
        $this->entryCreator = $entryCreator;
        $this->allianceRelationRepository = $allianceRelationRepository;
        $this->allianceActionManager = $allianceActionManager;
        $this->allianceRepository = $allianceRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $allianceId = $alliance->getId();
        $userId = $game->getUser()->getId();

        if (!$this->allianceActionManager->mayManageForeignRelations($allianceId, $userId)) {
            throw new AccessViolation();
        }

        $opponentId = $this->createRelationRequest->getOpponentId();
        $typeId = $this->createRelationRequest->getRelationType();

        $opp = $this->allianceRepository->find($opponentId);
        if ($opp === null) {
            return;
        }

        $types = [
            AllianceEnum::ALLIANCE_RELATION_WAR => 1,
            AllianceEnum::ALLIANCE_RELATION_PEACE => 1,
            AllianceEnum::ALLIANCE_RELATION_FRIENDS => 1,
            AllianceEnum::ALLIANCE_RELATION_ALLIED => 1,
            AllianceEnum::ALLIANCE_RELATION_TRADE => 1,
            AllianceEnum::ALLIANCE_RELATION_VASSAL => 1
        ];

        if (!array_key_exists($typeId, $types)) {
            return;
        }
        if ($alliance->getId() == $opp->getId()) {
            return;
        }
        $cnt = $this->allianceRelationRepository->getPendingCountByAlliances($allianceId, $opponentId);
        if ($cnt >= 2) {
            $game->addInformation(_('Es gibt bereits ein Angebot für diese Allianz'));
            return;
        }

        $rel = $this->allianceRelationRepository->getByAlliancePair($allianceId, $opponentId);
        if ($rel !== null) {
            if ($rel->getType() == $typeId) {
                return;
            }
            if ($rel->getType() == AllianceEnum::ALLIANCE_RELATION_WAR && $typeId != AllianceEnum::ALLIANCE_RELATION_PEACE) {
                return;
            }
        }
        $obj = $this->allianceRelationRepository->prototype();
        $obj->setAlliance($alliance);
        $obj->setOpponent($opp);
        $obj->setType($typeId);

        if ($typeId == AllianceEnum::ALLIANCE_RELATION_WAR) {
            $this->allianceRelationRepository->truncateByAlliances($allianceId, $opponentId);

            $obj->setDate(time());
            $this->allianceRelationRepository->save($obj);
            $text = sprintf(
                _('Die Allianz %s hat Deiner Allianz den Krieg erklärt'),
                $alliance->getName()
            );
            $this->allianceActionManager->sendMessage($opponentId, $text);

            $this->entryCreator->addAllianceEntry(
                sprintf(
                    _('Die Allianz %s hat der Allianz %s den Krieg erklärt'),
                    $alliance->getName(),
                    $opp->getName()
                ),
                $userId
            );
            $game->addInformation(
                sprintf('Der Allianz %s wurde der Krieg erklärt', $opp->getName())
            );
            return;
        }
        $this->allianceRelationRepository->save($obj);

        $text = sprintf(
            'Die Allianz %s hat Deiner Allianz ein Abkommen angeboten',
            $alliance->getName()
        );
        $this->allianceActionManager->sendMessage($opponentId, $text);

        $game->addInformation(_('Das Abkommen wurde angeboten'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}