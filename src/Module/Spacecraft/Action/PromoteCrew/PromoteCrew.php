<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\PromoteCrew;

use request;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\View\ShowCrewmanDetails\ShowCrewmanDetails;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;

final class PromoteCrew implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_PROMOTE_CREW';

    public function __construct(
        private CrewRepositoryInterface $crewRepository,
        private UserCrewRankRepositoryInterface $userCrewRankRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowCrewmanDetails::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $crew = $this->crewRepository->find(request::indInt('id'));
        if ($crew === null || $crew->getUser()->getId() !== $user->getId()) {
            throw new AccessViolationException();
        }

        $nextRank = $crew->getRank()->getNextRank();
        if ($nextRank === null) {
            $game->getInfo()->addInformation('Der Crewman hat bereits den höchsten Rang');
            return;
        }

        if ($crew->getHighestSkillExpertise() < $nextRank->getNeededExpertise()) {
            $game->getInfo()->addInformationf(
                '%s benötigt mindestens %d Expertise in einer Fähigkeit für die Beförderung zum %s',
                $crew->getName(),
                $nextRank->getNeededExpertise(),
                $this->userCrewRankRepository->getRankName($user, $nextRank)
            );
            return;
        }

        $promotionLimit = $nextRank->getPromotionLimit();
        if ($promotionLimit !== null && $this->crewRepository->getAmountByUserAndRank($user, $nextRank) >= $promotionLimit) {
            $game->getInfo()->addInformationf(
                'Es können gleichzeitig maximal %d Crewman im Rang %s geführt werden',
                $promotionLimit,
                $this->userCrewRankRepository->getRankName($user, $nextRank)
            );
            return;
        }

        $crew->setRank($nextRank);
        $this->crewRepository->save($crew);

        $game->getInfo()->addInformationf(
            '%s wurde zum %s befördert',
            $crew->getName(),
            $this->userCrewRankRepository->getRankName($user, $nextRank)
        );
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
