<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\UserRelation;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\Player\Relation\UserRelationManagerInterface;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\View\Relations\Relations;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Repository\UserRelationRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ManageUserRelation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_MANAGE_USER_RELATION';

    public function __construct(
        private readonly ManageUserRelationRequestInterface $manageUserRelationRequest,
        private readonly UserRelationManagerInterface $userRelationManager,
        private readonly UserRelationRepositoryInterface $userRelationRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(Relations::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $alliance = $user->getAlliance();
        if ($alliance === null) {
            throw new AccessViolationException();
        }

        match ($this->manageUserRelationRequest->getAction()) {
            'create' => $this->createRelation($game, $alliance),
            'accept' => $this->acceptRelation($game),
            'cancel' => $this->cancelRelation($game),
            'decline' => $this->declineRelation($game),
            'peace' => $this->suggestPeace($game),
            default => $game->getInfo()->addInformation('Ungültige Aktion'),
        };
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }

    private function createRelation(GameControllerInterface $game, Alliance $alliance): void
    {
        $user = $game->getUser();
        $source = $this->userRelationManager->getRepresentedParty($user);
        $recipient = $this->userRepository->find($this->manageUserRelationRequest->getUserId());
        $type = AllianceRelationTypeEnum::tryFrom($this->manageUserRelationRequest->getRelationType());

        if (
            !$source instanceof Alliance
            || $source->getId() !== $alliance->getId()
            || $recipient === null
            || !$recipient->isContactable()
            || $recipient->getAlliance() !== null
            || $type === null
            || $type === AllianceRelationTypeEnum::PEACE
        ) {
            $game->getInfo()->addInformation('Das Abkommen kann nicht erstellt werden');
            return;
        }

        $relation = $this->userRelationManager->create($user, $alliance, $recipient, $type);
        if ($relation === null) {
            $game->getInfo()->addInformation('Das Abkommen kann nicht erstellt werden oder ist bereits vorhanden');
            return;
        }

        $game->getInfo()->addInformation(
            $type === AllianceRelationTypeEnum::WAR
                ? 'Der Krieg wurde erklärt'
                : 'Das Abkommen wurde angeboten'
        );
    }

    private function acceptRelation(GameControllerInterface $game): void
    {
        $relation = $this->userRelationRepository->find($this->manageUserRelationRequest->getRelationId());
        if ($relation === null || !$this->userRelationManager->accept($game->getUser(), $relation)) {
            $game->getInfo()->addInformation('Das Angebot kann nicht angenommen werden');
            return;
        }

        $game->getInfo()->addInformation('Das Angebot wurde angenommen');
    }

    private function cancelRelation(GameControllerInterface $game): void
    {
        $relation = $this->userRelationRepository->find($this->manageUserRelationRequest->getRelationId());
        if ($relation === null || !$this->userRelationManager->cancel($game->getUser(), $relation)) {
            $game->getInfo()->addInformation('Das Abkommen kann nicht aufgelöst werden');
            return;
        }

        $game->getInfo()->addInformation('Das Abkommen wurde aufgelöst');
    }

    private function declineRelation(GameControllerInterface $game): void
    {
        $relation = $this->userRelationRepository->find($this->manageUserRelationRequest->getRelationId());
        if ($relation === null || !$this->userRelationManager->decline($game->getUser(), $relation)) {
            $game->getInfo()->addInformation('Das Angebot kann nicht abgelehnt werden');
            return;
        }

        $game->getInfo()->addInformation('Das Angebot wurde abgelehnt');
    }

    private function suggestPeace(GameControllerInterface $game): void
    {
        $relation = $this->userRelationRepository->find($this->manageUserRelationRequest->getRelationId());
        if ($relation === null || !$this->userRelationManager->suggestPeace($game->getUser(), $relation)) {
            $game->getInfo()->addInformation('Der Frieden kann nicht angeboten werden');
            return;
        }

        $game->getInfo()->addInformation('Der Frieden wurde angeboten');
    }
}
