<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CancelContract;

use Override;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

final class CancelContract implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CANCEL_CONTRACT';

    public function __construct(private CancelContractRequestInterface $cancelContractRequest, private EntryCreatorInterface $entryCreator, private AllianceRelationRepositoryInterface $allianceRelationRepository, private AllianceActionManagerInterface $allianceActionManager) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException();
        }

        $allianceId = $alliance->getId();

        $relation = $this->allianceRelationRepository->find($this->cancelContractRequest->getRelationId());

        if (!$this->allianceActionManager->mayManageForeignRelations($alliance, $user)) {
            throw new AccessViolationException();
        }

        if ($relation === null || ($relation->getOpponentId() !== $allianceId && $relation->getAllianceId() !== $allianceId)) {
            return;
        }

        if ($relation->getType() == AllianceRelationTypeEnum::WAR) {
            return;
        }

        if ($relation->isPending()) {
            return;
        }

        $this->allianceRelationRepository->delete($relation);

        $text = sprintf(
            _('Die Allianz %s hat das %s aufgelöst'),
            $alliance->getName(),
            $relation->getType()->getDescription()
        );

        if ($relation->getAllianceId() === $allianceId) {
            $this->allianceActionManager->sendMessage($relation->getOpponentId(), $text);
        } else {
            $this->allianceActionManager->sendMessage($relation->getAllianceId(), $text);
        }

        if ($relation->getType() != AllianceRelationTypeEnum::VASSAL) {
            $this->entryCreator->addEntry(
                sprintf(
                    'Das %s zwischen den Allianzen %s und %s wurde aufgelöst',
                    $relation->getType()->getDescription(),
                    $relation->getAlliance()->getName(),
                    $relation->getOpponent()->getName()
                ),
                $user->getId(),
                $relation->getOpponent()
            );
        }

        if ($relation->getType() == AllianceRelationTypeEnum::VASSAL) {
            $this->entryCreator->addEntry(
                sprintf(
                    'Die Allianz %s ist nicht mehr %s der Allianz %s',
                    $relation->getOpponent()->getName(),
                    $relation->getType()->getDescription(),
                    $relation->getAlliance()->getName()
                ),
                $user->getId(),
                $relation->getOpponent()
            );
        }

        $game->addInformation(_('Das Abkommen wurde aufgelöst'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
