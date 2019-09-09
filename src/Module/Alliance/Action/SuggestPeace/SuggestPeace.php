<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\SuggestPeace;

use AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

final class SuggestPeace implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SUGGEST_PEACE';

    private $suggestPeaceRequest;

    private $allianceRelationRepository;

    public function __construct(
        SuggestPeaceRequestInterface $suggestPeaceRequest,
        AllianceRelationRepositoryInterface $allianceRelationRepository
    ) {
        $this->suggestPeaceRequest = $suggestPeaceRequest;
        $this->allianceRelationRepository = $allianceRelationRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $relation = $this->allianceRelationRepository->find($this->suggestPeaceRequest->getRelationId());
        $alliance = $game->getUser()->getAlliance();

        if ($relation === null || !$alliance->currentUserIsDiplomatic()) {
            throw new AccessViolation();
        }

        $allianceId = (int) $alliance->getId();
        $opponentId = (int) ($relation->getOpponent()->getId() == $allianceId ? $relation->getAlliance()->getId() : $relation->getOpponent()->getId());

        $rel = $this->allianceRelationRepository->getActiveByTypeAndAlliancePair(
            [ALLIANCE_RELATION_PEACE],
            $allianceId,
            $opponentId
        );
        if ($rel !== null) {
            $game->addInformation(_('Der Allianz wird bereits ein Friedensabkommen angeboten'));
            return;
        }
        if (!$relation || ($relation->getRecipientId() != $allianceId && $relation->getAllianceId() != $allianceId)) {
            return;
        }
        if ($relation->getType() != ALLIANCE_RELATION_WAR) {
            return;
        }

        $obj = $this->allianceRelationRepository->prototype();
        $obj->setAllianceId($allianceId);
        $obj->setRecipientId($opponentId);
        $obj->setType(ALLIANCE_RELATION_PEACE);

        $this->allianceRelationRepository->save($obj);

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
