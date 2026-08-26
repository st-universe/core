<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\CreateUserRelation;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\Player\Relation\UserRelationManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\View\ShowContactList\ShowContactList;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class CreateUserRelation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_USER_RELATION';

    public function __construct(
        private readonly CreateUserRelationRequestInterface $createUserRelationRequest,
        private readonly UserRelationManagerInterface $userRelationManager,
        private readonly UserRepositoryInterface $userRepository,
        private readonly AllianceRepositoryInterface $allianceRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowContactList::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $source = $this->userRelationManager->getRepresentedParty($user);
        if ($source === null) {
            $game->getInfo()->addInformation('Du hast keine Berechtigung, Abkommen für Deine Allianz zu erstellen');
            return;
        }

        $type = AllianceRelationTypeEnum::tryFrom($this->createUserRelationRequest->getRelationType());
        if (
            $type === null
            || $type === AllianceRelationTypeEnum::PEACE
        ) {
            $game->getInfo()->addInformation('Ungültiger Abkommenstyp');
            return;
        }

        $target = $this->getTarget($source instanceof Alliance);
        if ($target === null) {
            $game->getInfo()->addInformation('Die gewählte Vertragspartei existiert nicht oder kann kein Abkommen schließen');
            return;
        }

        $relation = $this->userRelationManager->create($user, $source, $target, $type);
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

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }

    private function getTarget(bool $sourceIsAlliance): User|Alliance|null
    {
        $targetId = $this->createUserRelationRequest->getTargetId();
        $targetType = $this->createUserRelationRequest->getTargetType();

        if ($targetType === 1) {
            $user = $this->userRepository->find($targetId);
            if ($user === null || !$user->isContactable() || $user->getAlliance() !== null) {
                return null;
            }

            return $user;
        }

        if (!$sourceIsAlliance && $targetType === 2) {
            return $this->allianceRepository->find($targetId);
        }

        return null;
    }
}
