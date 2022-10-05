<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AcceptOffer;

use Stu\Exception\AccessViolation;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

final class AcceptOffer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACCEPT_OFFER';

    private AcceptOfferRequestInterface $acceptOfferRequest;

    private EntryCreatorInterface $entryCreator;

    private AllianceRelationRepositoryInterface $allianceRelationRepository;

    private AllianceActionManagerInterface $allianceActionManager;

    public function __construct(
        AcceptOfferRequestInterface $acceptOfferRequest,
        EntryCreatorInterface $entryCreator,
        AllianceRelationRepositoryInterface $allianceRelationRepository,
        AllianceActionManagerInterface $allianceActionManager
    ) {
        $this->acceptOfferRequest = $acceptOfferRequest;
        $this->entryCreator = $entryCreator;
        $this->allianceRelationRepository = $allianceRelationRepository;
        $this->allianceActionManager = $allianceActionManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();
        $userId = $user->getId();
        $allianceId = $alliance->getId();

        $relation = $this->allianceRelationRepository->find($this->acceptOfferRequest->getRelationId());

        if (!$this->allianceActionManager->mayManageForeignRelations($allianceId, $userId)) {
            throw new AccessViolation();
        }

        if ($relation === null || $relation->getOpponentId() != $allianceId) {
            return;
        }
        if (!$relation->isPending()) {
            return;
        }
        $rel = $this->allianceRelationRepository->getActiveByAlliancePair($relation->getAllianceId(), $relation->getOpponentId());
        if ($rel) {
            $this->allianceRelationRepository->delete($rel);
        }
        $relation->setDate(time());

        $this->allianceRelationRepository->save($relation);

        $text = sprintf(
            _("%s abgeschlossen!\nDie Allianz %s hat hat das Angebot angenommen"),
            $relation->getTypeDescription(),
            $alliance->getName()
        );

        if ($relation->getType() != AllianceEnum::ALLIANCE_RELATION_VASSAL)
            $this->entryCreator->addAllianceEntry(
                sprintf(
                    _('Die Allianzen %s und %s sind ein %s eingegangen'),
                    $relation->getAlliance()->getName(),
                    $relation->getOpponent()->getName(),
                    $relation->getTypeDescription()
                ),
                $userId
            );
        if ($relation->getType() == AllianceEnum::ALLIANCE_RELATION_VASSAL)
            $this->entryCreator->addAllianceEntry(
                sprintf(
                    _('Die Allianz %s ist nun %s der Allianz %s'),
                    $relation->getOpponent()->getName(),
                    $relation->getTypeDescription(),
                    $relation->getAlliance()->getName()
                ),
                $userId
            );

        if ($relation->getAllianceId() == $allianceId) {
            $this->allianceActionManager->sendMessage($relation->getOpponentId(), $text);
        } else {
            $this->allianceActionManager->sendMessage($relation->getAllianceId(), $text);
        }

        $game->addInformation(_('Das Angebot wurden angemommen'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}