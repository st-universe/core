<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\UserRelation;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\Player\Relation\UserRelationManagerInterface;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\View\Relations\Relations;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Repository\RelationRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ManageUserRelation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_MANAGE_USER_RELATION';

    public function __construct(
        private readonly ManageUserRelationRequestInterface $manageUserRelationRequest,
        private readonly UserRelationManagerInterface $userRelationManager,
        private readonly RelationRepositoryInterface $userRelationRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(Relations::VIEW_IDENTIFIER);

        $user = $context->getUser();
        $alliance = $user->getAlliance();
        if ($alliance === null) {
            throw new AccessViolationException();
        }

        match ($this->manageUserRelationRequest->getAction()) {
            'create' => $this->createRelation($context, $alliance),
            'accept' => $this->acceptRelation($context),
            'cancel' => $this->cancelRelation($context),
            'decline' => $this->declineRelation($context),
            'peace' => $this->suggestPeace($context),
            'update' => $this->updatePermissions($context),
            'accept_permissions' => $this->acceptPermissions($context),
            'decline_permissions' => $this->declinePermissions($context),
            'cancel_permissions' => $this->cancelPermissions($context),
            default => $context->getInfo()->addInformation('Ungültige Aktion')
        };
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }

    private function createRelation(ActionControllerContext $context, Alliance $alliance): void
    {
        $user = $context->getUser();
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
            $context->getInfo()->addInformation('Das Abkommen kann nicht erstellt werden');
            return;
        }

        $relation = $this->userRelationManager->create(
            $user,
            $alliance,
            $recipient,
            $type,
            $this->manageUserRelationRequest->getPermissions()
        );
        if ($relation === null) {
            $context->getInfo()->addInformation(
                'Das Abkommen kann nicht erstellt werden oder ist bereits vorhanden'
            );
            return;
        }

        $context->getInfo()->addInformation(
            $type === AllianceRelationTypeEnum::WAR
                ? 'Der Krieg wurde erklärt'
                : 'Das Abkommen wurde angeboten'
        );
    }

    private function acceptRelation(ActionControllerContext $context): void
    {
        $relation = $this->userRelationRepository->find($this->manageUserRelationRequest->getRelationId());
        if ($relation === null || !$this->userRelationManager->accept($context->getUser(), $relation)) {
            $context->getInfo()->addInformation('Das Angebot kann nicht angenommen werden');
            return;
        }

        $context->getInfo()->addInformation('Das Angebot wurde angenommen');
    }

    private function cancelRelation(ActionControllerContext $context): void
    {
        $relation = $this->userRelationRepository->find($this->manageUserRelationRequest->getRelationId());
        if ($relation === null || !$this->userRelationManager->cancel($context->getUser(), $relation)) {
            $context->getInfo()->addInformation('Das Abkommen kann nicht aufgelöst werden');
            return;
        }

        $context->getInfo()->addInformation('Das Abkommen wurde aufgelöst');
    }

    private function declineRelation(ActionControllerContext $context): void
    {
        $relation = $this->userRelationRepository->find($this->manageUserRelationRequest->getRelationId());
        if ($relation === null || !$this->userRelationManager->decline($context->getUser(), $relation)) {
            $context->getInfo()->addInformation('Das Angebot kann nicht abgelehnt werden');
            return;
        }

        $context->getInfo()->addInformation('Das Angebot wurde abgelehnt');
    }

    private function suggestPeace(ActionControllerContext $context): void
    {
        $relation = $this->userRelationRepository->find($this->manageUserRelationRequest->getRelationId());
        if ($relation === null || !$this->userRelationManager->suggestPeace($context->getUser(), $relation)) {
            $context->getInfo()->addInformation('Der Frieden kann nicht angeboten werden');
            return;
        }

        $context->getInfo()->addInformation('Der Frieden wurde angeboten');
    }

    private function acceptPermissions(ActionControllerContext $context): void
    {
        $relation = $this->userRelationRepository->find($this->manageUserRelationRequest->getRelationId());
        if (
            $relation === null
            || !$this->userRelationManager->acceptPermissionChange($context->getUser(), $relation)
        ) {
            $context->getInfo()->addInformation('Die Rechteänderung kann nicht angenommen werden');
            return;
        }

        $context->getInfo()->addInformation('Die Rechteänderung wurde angenommen');
    }

    private function declinePermissions(ActionControllerContext $context): void
    {
        $relation = $this->userRelationRepository->find($this->manageUserRelationRequest->getRelationId());
        if (
            $relation === null
            || !$this->userRelationManager->declinePermissionChange($context->getUser(), $relation)
        ) {
            $context->getInfo()->addInformation('Die Rechteänderung kann nicht abgelehnt werden');
            return;
        }

        $context->getInfo()->addInformation('Die Rechteänderung wurde abgelehnt');
    }

    private function cancelPermissions(ActionControllerContext $context): void
    {
        $relation = $this->userRelationRepository->find($this->manageUserRelationRequest->getRelationId());
        if (
            $relation === null
            || !$this->userRelationManager->cancelPermissionChange($context->getUser(), $relation)
        ) {
            $context->getInfo()->addInformation('Die Rechteänderung kann nicht zurückgezogen werden');
            return;
        }

        $context->getInfo()->addInformation('Die Rechteänderung wurde zurückgezogen');
    }

    private function updatePermissions(ActionControllerContext $context): void
    {
        $relation = $this->userRelationRepository->find($this->manageUserRelationRequest->getRelationId());
        if (
            $relation === null
            || !$this->userRelationManager->proposePermissionChange(
                $context->getUser(),
                $relation,
                $this->manageUserRelationRequest->getPermissions()
            )
        ) {
            $context->getInfo()->addInformation('Die Rechte können nicht geändert werden');
            return;
        }

        $context->getInfo()->addInformation('Die Rechteänderung wurde angeboten');
    }
}
