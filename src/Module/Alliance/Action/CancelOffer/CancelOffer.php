<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CancelOffer;

use AccessViolation;
use AllianceRelation;
use PM;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class CancelOffer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CANCEL_OFFER';

    private $cancelOfferRequest;

    public function __construct(
        CancelOfferRequestInterface $cancelOfferRequest
    ) {
        $this->cancelOfferRequest = $cancelOfferRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $allianceId = $alliance->getId();

        $relation = AllianceRelation::getById($this->cancelOfferRequest->getRelationId());

        if (!$alliance->currentUserIsDiplomatic()) {
            throw new AccessViolation();
        }

        if (!$relation || $relation->getAllianceId() != $allianceId) {
            return;
        }
        if (!$relation->isPending()) {
            return;
        }

        $relation->deleteFromDatabase();

        $text = sprintf(_('Die Allianz %s hat das Angebot zurückgezogen'), $alliance->getNameWithoutMarkup());

        PM::sendPM(USER_NOONE, $relation->getRecipient()->getFounder()->getUserId(), $text);
        if ($relation->getRecipient()->getDiplomatic()) {
            PM::sendPM(USER_NOONE, $relation->getRecipient()->getDiplomatic()->getUserId(), $text);
        }
        $game->addInformation(_('Das Angebot wurde zurückgezogen'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
