<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CancelContract;

use AccessViolation;
use AllianceRelation;
use HistoryEntry;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class CancelContract implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CANCEL_CONTRACT';

    private $cancelContractRequest;

    public function __construct(
        CancelContractRequestInterface $cancelContractRequest
    ) {
        $this->cancelContractRequest = $cancelContractRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();
        $allianceId = $alliance->getId();

        $relation = AllianceRelation::getById($this->cancelContractRequest->getRelationId());

        if (!$alliance->currentUserIsDiplomatic()) {
            throw new AccessViolation();
        }

        if (!$relation || ($relation->getRecipientId() != $allianceId && $relation->getAllianceId() != $allianceId)) {
            return;
        }
        if ($relation->getType() == ALLIANCE_RELATION_WAR) {
            return;
        }
        if ($relation->isPending()) {
            return;
        }
        $relation->deleteFromDatabase();

        $text = sprintf(
            _('Die Allianz %s hat das %s aufgelöst'),
            $alliance->getNameWithoutMarkup(),
            $relation->getTypeDescription()
        );

        if ($relation->getAllianceId() == $allianceId) {
            $relation->getOpponent()->sendMessage($text);
        } else {
            $relation->getAlliance()->sendMessage($text);
        }

        HistoryEntry::addAllianceEntry(
            sprintf(
                'Das %s zwischen den Allianzen %s und %s wurde aufgelöst',
                $relation->getTypeDescription(),
                $relation->getAlliance()->getName(),
                $relation->getOpponent()->getName()
            ),
            $user->getId()
        );
        $game->addInformation(_('Das Abkommen wurde aufgelöst'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
