<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CancelOffer;

use Override;
use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

final class CancelOffer implements ActionControllerInterface
{
    /**
     * @var string
     */
    public const string ACTION_IDENTIFIER = 'B_CANCEL_OFFER';

    public function __construct(private CancelOfferRequestInterface $cancelOfferRequest, private AllianceRelationRepositoryInterface $allianceRelationRepository, private AllianceActionManagerInterface $allianceActionManager, private PrivateMessageSenderInterface $privateMessageSender)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        if ($alliance === null) {
            throw new AccessViolation();
        }

        $allianceId = $alliance->getId();

        $relation = $this->allianceRelationRepository->find($this->cancelOfferRequest->getRelationId());

        if (!$this->allianceActionManager->mayManageForeignRelations($alliance, $game->getUser())) {
            throw new AccessViolation();
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

        $this->privateMessageSender->send(UserEnum::USER_NOONE, $opponent->getFounder()->getUserId(), $text);
        if ($opponent->getDiplomatic() !== null) {
            $this->privateMessageSender->send(UserEnum::USER_NOONE, $opponent->getDiplomatic()->getUserId(), $text);
        }

        $game->addInformation(_('Das Angebot wurde zurückgezogen'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
