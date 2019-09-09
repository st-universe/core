<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateRelation;

use AccessViolation;
use Alliance;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

final class CreateRelation implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_NEW_RELATION';

    private $createRelationRequest;

    private $entryCreator;

    private $allianceRelationRepository;

    public function __construct(
        CreateRelationRequestInterface $createRelationRequest,
        EntryCreatorInterface $entryCreator,
        AllianceRelationRepositoryInterface $allianceRelationRepository
    ) {
        $this->createRelationRequest = $createRelationRequest;
        $this->entryCreator = $entryCreator;
        $this->allianceRelationRepository = $allianceRelationRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $allianceId = (int) $alliance->getId();
        $userId = $game->getUser()->getId();

        if (!$alliance->currentUserIsDiplomatic()) {
            throw new AccessViolation();
        }

        $opponentId = $this->createRelationRequest->getOpponentId();
        $typeId = $this->createRelationRequest->getRelationType();

        $opp = new Alliance($opponentId);

        $types = [
            ALLIANCE_RELATION_WAR => 1,
            ALLIANCE_RELATION_PEACE => 1,
            ALLIANCE_RELATION_FRIENDS => 1,
            ALLIANCE_RELATION_ALLIED => 1
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
            if ($rel->getType() == ALLIANCE_RELATION_WAR && $typeId != ALLIANCE_RELATION_PEACE) {
                return;
            }
        }
        $obj = $this->allianceRelationRepository->prototype();
        $obj->setAllianceId($allianceId);
        $obj->setRecipientId($opponentId);
        $obj->setType($typeId);

        if ($typeId == ALLIANCE_RELATION_WAR) {
            $this->allianceRelationRepository->truncateByAlliances($allianceId, $opponentId);

            $obj->setDate(time());
            $this->allianceRelationRepository->save($obj);
            $text = sprintf(
                _('Die Allianz %s hat Deiner Allianz den Krieg erklärt'),
                $alliance->getNameWithoutMarkup()
            );
            $opp->sendMessage($text);

            $this->entryCreator->addAllianceEntry(
                sprintf(
                    _('Die Allianz %s hat der Allianz %s den Krieg erklärt'),
                    $alliance->getName(),
                    $opp->getName()
                ),
                $userId
            );
            $game->addInformation(
                sprintf('Der Allianz %s wurde der Krieg erklärt', $opp->getNameWithoutMarkup())
            );
            return;
        }
        $this->allianceRelationRepository->save($obj);

        $text = sprintf(
            'Die Allianz %s hat Deiner Allianz ein Abkommen angeboten',
            $alliance->getNameWithoutMarkup()
        );
        $opp->sendMessage($text);

        $game->addInformation(_('Das Abkommen wurde angeboten'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
