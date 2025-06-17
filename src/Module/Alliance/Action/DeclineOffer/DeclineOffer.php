<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeclineOffer;

use Override;
use request;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

final class DeclineOffer implements ActionControllerInterface
{
    /**
     * @var string
     */
    public const string ACTION_IDENTIFIER = 'B_DECLINE_OFFER';

    public function __construct(private AllianceRelationRepositoryInterface $allianceRelationRepository, private AllianceActionManagerInterface $allianceActionManager)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        if ($alliance === null) {
            throw new AccessViolation();
        }

        $allianceId = $alliance->getId();

        if (!$this->allianceActionManager->mayManageForeignRelations($alliance, $user)) {
            throw new AccessViolation();
        }

        $relation = $this->allianceRelationRepository->find(request::getIntFatal('al'));

        if ($relation === null || $relation->getOpponentId() !== $allianceId) {
            return;
        }

        if (!$relation->isPending()) {
            return;
        }

        $this->allianceRelationRepository->delete($relation);

        $text = sprintf(
            _("%s wurde von der Allianz %s abgelehnt"),
            AllianceEnum::relationTypeToDescription($relation->getType()),
            $alliance->getName()
        );

        $this->allianceActionManager->sendMessage($relation->getAllianceId(), $text);

        $game->addInformation(_('Das Angebot wurden abgelehnt'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
