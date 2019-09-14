<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CancelOffer;

use AccessViolation;
use PM;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

final class CancelOffer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CANCEL_OFFER';

    private $cancelOfferRequest;

    private $allianceRelationRepository;

    private $allianceActionManager;

    public function __construct(
        CancelOfferRequestInterface $cancelOfferRequest,
        AllianceRelationRepositoryInterface $allianceRelationRepository,
        AllianceActionManagerInterface $allianceActionManager
    ) {
        $this->cancelOfferRequest = $cancelOfferRequest;
        $this->allianceRelationRepository = $allianceRelationRepository;
        $this->allianceActionManager = $allianceActionManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $allianceId = $alliance->getId();

        $relation = $this->allianceRelationRepository->find($this->cancelOfferRequest->getRelationId());

        if (!$this->allianceActionManager->mayManageForeignRelations($allianceId, $game->getUser()->getId())) {
            throw new AccessViolation();
        }

        if ($relation === null || $relation->getAllianceId() != $allianceId) {
            return;
        }
        if (!$relation->isPending()) {
            return;
        }

        $this->allianceRelationRepository->delete($relation);

        $text = sprintf(_('Die Allianz %s hat das Angebot zurückgezogen'), $alliance->getName());

        $opponent = $relation->getOpponent();

        PM::sendPM(USER_NOONE, $opponent->getFounder()->getUserId(), $text);
        if ($opponent->getDiplomatic()) {
            PM::sendPM(USER_NOONE, $opponent->getDiplomatic()->getUserId(), $text);
        }
        $game->addInformation(_('Das Angebot wurde zurückgezogen'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
