<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\SuggestPeace;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

final class SuggestPeace implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SUGGEST_PEACE';

    public function __construct(private SuggestPeaceRequestInterface $suggestPeaceRequest, private AllianceRelationRepositoryInterface $allianceRelationRepository, private AllianceActionManagerInterface $allianceActionManager) {}

    #[\Override]
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
            [AllianceRelationTypeEnum::PEACE->value],
            $allianceId,
            $opponentId
        );
        if ($rel !== null) {
            $game->getInfo()->addInformation(_('Der Allianz wird bereits ein Friedensabkommen angeboten'));
            return;
        }

        if ($relation->getOpponentId() !== $allianceId && $relation->getAlliance()->getId() !== $alliance->getId()) {
            return;
        }

        if ($relation->getType() != AllianceRelationTypeEnum::WAR) {
            return;
        }

        $obj = $this->allianceRelationRepository->prototype();
        $obj->setAlliance($alliance);

        if ($relation->getAlliance()->getId() === $alliance->getId()) {
            $obj->setOpponent($relation->getOpponent());
        } else {
            $obj->setOpponent($relation->getAlliance());
        }

        $obj->setType(AllianceRelationTypeEnum::PEACE);

        $this->allianceRelationRepository->save($obj);

        $text = sprintf(
            _('Die Allianz %s hat Deiner Allianz ein Friedensabkommen angeboten'),
            $alliance->getName()
        );

        if ($relation->getAlliance()->getId() === $alliance->getId()) {
            $this->allianceActionManager->sendMessage($relation->getOpponentId(), $text);
        } else {
            $this->allianceActionManager->sendMessage($relation->getAllianceId(), $text);
        }

        $game->getInfo()->addInformation(_('Der Frieden wurde angeboten'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
