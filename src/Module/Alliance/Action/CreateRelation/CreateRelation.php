<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateRelation;

use AccessViolation;
use Alliance;
use AllianceRelation;
use AllianceRelationData;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;

final class CreateRelation implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_NEW_RELATION';

    private $createRelationRequest;

    private $entryCreator;

    public function __construct(
        CreateRelationRequestInterface $createRelationRequest,
        EntryCreatorInterface $entryCreator
    ) {
        $this->createRelationRequest = $createRelationRequest;
        $this->entryCreator = $entryCreator;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $allianceId = $alliance->getId();
        $userId = $game->getUser()->getId();

        if (!$alliance->currentUserIsDiplomatic()) {
            throw new AccessViolation();
        }

        $opponentId = $this->createRelationRequest->getOpponentId();
        $typeId = $this->createRelationRequest->getRelationType();

        $opp = new Alliance($opponentId);

        if (!AllianceRelation::isValidRelationType($typeId)) {
            return;
        }
        if ($alliance->getId() == $opp->getId()) {
            return;
        }
        $cnt = AllianceRelation::countInstances(
            sprintf(
                'date = 0 AND ((alliance_id = %d AND recipient = %d) OR (alliance_id = %d AND recipient = %d))',
                $allianceId,
                $opponentId,
                $opponentId,
                $allianceId
            )
        );
        if ($cnt >= 2) {
            $game->addInformation(_('Es gibt bereits ein Angebot für diese Allianz'));
            return;
        }

        $rel = AllianceRelation::getBy(
            sprintf(
                '(alliance_id = %d AND recipient = %d) OR (alliance_id = %d AND recipient = %d)',
                $allianceId,
                $opponentId,
                $opponentId,
                $allianceId
            )
        );
        if ($rel) {
            if ($rel->getType() == $typeId) {
                return;
            }
            if ($rel->getType() == ALLIANCE_RELATION_WAR && $typeId != ALLIANCE_RELATION_PEACE) {
                return;
            }
        }
        $obj = new AllianceRelationData();
        $obj->setAllianceId($allianceId);
        $obj->setRecipientId($opponentId);
        $obj->setType($typeId);

        if ($typeId == ALLIANCE_RELATION_WAR) {
            AllianceRelation::truncateBy(
                sprintf(
                    '(alliance_id = %d AND recipient = %d) OR (alliance_id = %d AND recipient = %d)',
                    $allianceId,
                    $opponentId,
                    $opponentId,
                    $allianceId
                )
            );
            $obj->setDate(time());
            $obj->save();
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
        $obj->save();

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
