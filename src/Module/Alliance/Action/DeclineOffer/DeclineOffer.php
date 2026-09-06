<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeclineOffer;

use request;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Component\Player\Relation\UserRelationManagerInterface;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\RelationRepositoryInterface;

final class DeclineOffer implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DECLINE_OFFER';

    public function __construct(
        private RelationRepositoryInterface $allianceRelationRepository,
        private UserRelationManagerInterface $userRelationManager,
        private AllianceJobManagerInterface $allianceJobManager,
        private AllianceActionManagerInterface $allianceActionManager
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException();
        }

        $allianceId = $alliance->getId();

        if (!$this->allianceJobManager->hasUserPermission(
            $user,
            $alliance,
            AllianceJobPermissionEnum::CREATE_AGREEMENTS
        )) {
            throw new AccessViolationException();
        }

        $relation = $this->allianceRelationRepository->find(request::getIntFatal('al'));

        if ($relation !== null && !$relation->isPending()) {
            if (!$this->userRelationManager->declinePermissionChange($user, $relation)) {
                $game->getInfo()->addInformation('Die Rechteänderung kann nicht abgelehnt werden');
                return;
            }

            $game->getInfo()->addInformation('Die Rechteänderung wurde abgelehnt');
            return;
        }

        if ($relation === null || $relation->getOpponentId() !== $allianceId) {
            return;
        }

        if (!$relation->isPending()) {
            return;
        }

        $this->allianceRelationRepository->delete($relation);

        $text = sprintf(
            _('%s wurde von der Allianz %s abgelehnt'),
            $relation->getType()->getDescription(),
            $alliance->getName()
        );

        $this->allianceActionManager->sendMessage($relation->getAllianceId(), $text);

        $game->getInfo()->addInformation(_('Das Angebot wurden abgelehnt'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
