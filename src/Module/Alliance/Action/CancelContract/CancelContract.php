<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CancelContract;

use AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

final class CancelContract implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CANCEL_CONTRACT';

    private $cancelContractRequest;

    private $entryCreator;

    private $allianceRelationRepository;

    public function __construct(
        CancelContractRequestInterface $cancelContractRequest,
        EntryCreatorInterface $entryCreator,
        AllianceRelationRepositoryInterface $allianceRelationRepository
    ) {
        $this->cancelContractRequest = $cancelContractRequest;
        $this->entryCreator = $entryCreator;
        $this->allianceRelationRepository = $allianceRelationRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();
        $allianceId = $alliance->getId();

        $relation = $this->allianceRelationRepository->find($this->cancelContractRequest->getRelationId());

        if (!$alliance->currentUserIsDiplomatic()) {
            throw new AccessViolation();
        }

        if ($relation === null || ($relation->getRecipientId() != $allianceId && $relation->getAllianceId() != $allianceId)) {
            return;
        }
        if ($relation->getType() == ALLIANCE_RELATION_WAR) {
            return;
        }
        if ($relation->isPending()) {
            return;
        }

        $this->allianceRelationRepository->delete($relation);

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

        $this->entryCreator->addAllianceEntry(
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
