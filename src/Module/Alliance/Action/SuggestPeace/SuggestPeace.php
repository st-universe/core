<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\SuggestPeace;

use AllianceRelation;
use AllianceRelationData;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;

final class SuggestPeace implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SUGGEST_PEACE';

    private $suggestPeaceRequest;

    public function __construct(
        SuggestPeaceRequestInterface $suggestPeaceRequest
    ) {
        $this->suggestPeaceRequest = $suggestPeaceRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $relation = AllianceRelation::getById($this->suggestPeaceRequest->getRelationId());
        $alliance = $game->getUser()->getAlliance();

        $allianceId = $alliance->getId();
        $opponentId = $relation->getOpponent()->getId() == $allianceId ? $relation->getAlliance()->getId() : $relation->getOpponent()->getId();

        $rel = AllianceRelation::getBy(
            sprintf(
                'type = %d AND ((alliance_id = %d AND recipient = %d) OR (alliance_id = %d AND recipient = %d))',
                ALLIANCE_RELATION_PEACE,
                $allianceId,
                $opponentId,
                $opponentId,
                $allianceId
            )
        );
        if ($rel > 0) {
            $game->addInformation(_('Der Allianz wird bereits ein Friedensabkommen angeboten'));
            return;
        }
        if (!$relation || ($relation->getRecipientId() != $allianceId && $relation->getAllianceId() != $allianceId)) {
            return;
        }
        if ($relation->getType() != ALLIANCE_RELATION_WAR) {
            return;
        }

        $obj = new AllianceRelationData();
        $obj->setAllianceId($allianceId);
        $obj->setRecipientId($opponentId);
        $obj->setType(ALLIANCE_RELATION_PEACE);
        $obj->save();

        $text = sprintf(
            _('Die Allianz %s hat Deiner Allianz ein Friedensabkommen angeboten'),
            $alliance->getNameWithoutMarkup()
        );

        if ($relation->getAllianceId() == $allianceId) {
            $relation->getOpponent()->sendMessage($text);
        } else {
            $relation->getAlliance()->sendMessage($text);
        }

        $game->addInformation(_('Der Frieden wurde angeboten'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
