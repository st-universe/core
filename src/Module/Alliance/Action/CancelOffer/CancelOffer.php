<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CancelOffer;

use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

final class CancelOffer implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CANCEL_OFFER';

    public function __construct(private CancelOfferRequestInterface $cancelOfferRequest, private AllianceRelationRepositoryInterface $allianceRelationRepository, private AllianceActionManagerInterface $allianceActionManager, private PrivateMessageSenderInterface $privateMessageSender) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException();
        }

        $allianceId = $alliance->getId();

        $relation = $this->allianceRelationRepository->find($this->cancelOfferRequest->getRelationId());

        if (!$this->allianceActionManager->mayManageForeignRelations($alliance, $game->getUser())) {
            throw new AccessViolationException();
        }

        if ($relation === null || $relation->getAllianceId() !== $allianceId) {
            return;
        }

        if (!$relation->isPending()) {
            return;
        }

        $this->allianceRelationRepository->delete($relation);

        $text = sprintf(_('Die Allianz %s hat das Angebot zurückgezogen'), $alliance->getName());

        $opponent = $relation->getOpponent();

        $founderUser = $opponent->getFounder()->getUser();
        if ($founderUser !== null) {
            $this->privateMessageSender->send(UserConstants::USER_NOONE, $founderUser->getId(), $text);
        }

        $diplomaticJob = $opponent->getDiplomatic();
        if ($diplomaticJob !== null) {
            $diplomaticUser = $diplomaticJob->getUser();
            if ($diplomaticUser !== null) {
                $this->privateMessageSender->send(UserConstants::USER_NOONE, $diplomaticUser->getId(), $text);
            }
        }

        $game->getInfo()->addInformation(_('Das Angebot wurde zurückgezogen'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
