<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AcceptOffer;

use Override;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

final class AcceptOffer implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ACCEPT_OFFER';

    public function __construct(private AcceptOfferRequestInterface $acceptOfferRequest, private EntryCreatorInterface $entryCreator, private AllianceRelationRepositoryInterface $allianceRelationRepository, private AllianceActionManagerInterface $allianceActionManager) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException();
        }

        $userId = $user->getId();
        $allianceId = $alliance->getId();

        $relation = $this->allianceRelationRepository->find($this->acceptOfferRequest->getRelationId());

        if (!$this->allianceActionManager->mayManageForeignRelations($alliance, $user)) {
            throw new AccessViolationException();
        }

        if ($relation === null || $relation->getOpponentId() !== $allianceId) {
            return;
        }

        if (!$relation->isPending()) {
            return;
        }

        $rel = $this->allianceRelationRepository->getActiveByAlliancePair($relation->getAllianceId(), $relation->getOpponentId());
        if ($rel !== null) {
            $this->allianceRelationRepository->delete($rel);
        }

        $relation->setDate(time());

        $this->allianceRelationRepository->save($relation);

        $text = sprintf(
            _("%s abgeschlossen!\nDie Allianz %s hat hat das Angebot angenommen"),
            $relation->getType()->getDescription(),
            $alliance->getName()
        );

        if ($relation->getType() != AllianceRelationTypeEnum::VASSAL) {
            $this->entryCreator->addEntry(
                sprintf(
                    _('Die Allianzen %s und %s sind ein %s eingegangen'),
                    $relation->getAlliance()->getName(),
                    $relation->getOpponent()->getName(),
                    $relation->getType()->getDescription()
                ),
                $userId,
                $relation->getOpponent()
            );
        }

        if ($relation->getType() == AllianceRelationTypeEnum::VASSAL) {
            $this->entryCreator->addEntry(
                sprintf(
                    _('Die Allianz %s ist nun %s der Allianz %s'),
                    $relation->getOpponent()->getName(),
                    $relation->getType()->getDescription(),
                    $relation->getAlliance()->getName()
                ),
                $userId,
                $relation->getOpponent()
            );
        }

        if ($relation->getAllianceId() === $allianceId) {
            $this->allianceActionManager->sendMessage($relation->getOpponentId(), $text);
        } else {
            $this->allianceActionManager->sendMessage($relation->getAllianceId(), $text);
        }

        $game->getInfo()->addInformation(_('Das Angebot wurden angenommen'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
