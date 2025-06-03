<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\SuggestPeace;

use Override;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

final class SuggestPeace implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SUGGEST_PEACE';

    public function __construct(private SuggestPeaceRequestInterface $suggestPeaceRequest, private AllianceRelationRepositoryInterface $allianceRelationRepository, private AllianceActionManagerInterface $allianceActionManager)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $relation = $this->allianceRelationRepository->find($this->suggestPeaceRequest->getRelationId());
        $alliance = $game->getUser()->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException();
        }

        $allianceId = $alliance->getId();

        if ($relation === null || !$this->allianceActionManager->mayManageForeignRelations($alliance, $game->getUser())) {
            throw new AccessViolationException();
        }

        $opponentId = $relation->getOpponent()->getId() === $allianceId ? $relation->getAlliance()->getId() : $relation->getOpponent()->getId();

        $rel = $this->allianceRelationRepository->getActiveByTypeAndAlliancePair(
            [AllianceEnum::ALLIANCE_RELATION_PEACE],
            $allianceId,
            $opponentId
        );
        if ($rel !== null) {
            $game->addInformation(_('Der Allianz wird bereits ein Friedensabkommen angeboten'));
            return;
        }

        if (!$relation || ($relation->getOpponentId() !== $allianceId && $relation->getAllianceId() !== $allianceId)) {
            return;
        }

        if ($relation->getType() != AllianceEnum::ALLIANCE_RELATION_WAR) {
            return;
        }

        $obj = $this->allianceRelationRepository->prototype();
        $obj->setAlliance($alliance);
        $obj->setOpponent($relation->getOpponent());
        $obj->setType(AllianceEnum::ALLIANCE_RELATION_PEACE);

        $this->allianceRelationRepository->save($obj);

        $text = sprintf(
            _('Die Allianz %s hat Deiner Allianz ein Friedensabkommen angeboten'),
            $alliance->getName()
        );

        if ($relation->getAllianceId() === $allianceId) {
            $this->allianceActionManager->sendMessage($relation->getOpponentId(), $text);
        } else {
            $this->allianceActionManager->sendMessage($relation->getAllianceId(), $text);
        }

        $game->addInformation(_('Der Frieden wurde angeboten'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
