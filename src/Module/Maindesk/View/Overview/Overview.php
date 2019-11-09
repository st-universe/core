<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\View\Overview;

use Stu\Component\Communication\Kn\KnFactoryInterface;
use Stu\Component\Communication\Kn\KnItemInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\HistoryRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\UserProfileVisitorRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private $historyRepository;

    private $allianceBoardTopicRepository;

    private $userProfileVisitorRepository;

    private $knPostRepository;

    private $colonyShipQueueRepository;

    private $userRepository;

    private $knFactory;

    public function __construct(
        HistoryRepositoryInterface $historyRepository,
        AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository,
        UserProfileVisitorRepositoryInterface $userProfileVisitorRepository,
        KnPostRepositoryInterface $knPostRepository,
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        UserRepositoryInterface $userRepository,
        KnFactoryInterface $knFactory
    ) {
        $this->historyRepository = $historyRepository;
        $this->allianceBoardTopicRepository = $allianceBoardTopicRepository;
        $this->userProfileVisitorRepository = $userProfileVisitorRepository;
        $this->knPostRepository = $knPostRepository;
        $this->colonyShipQueueRepository = $colonyShipQueueRepository;
        $this->userRepository = $userRepository;
        $this->knFactory = $knFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $game->appendNavigationPart(
            'maindesk.php',
            _('Maindesk')
        );
        $game->setPageTitle(_('/ Maindesk'));
        $game->setTemplateFile('html/maindesk.xhtml');

        $game->setTemplateVar(
            'DISPLAY_FIRST_COLONY_DIALOGUE',
            $user->getActive() === 1
        );
        $game->setTemplateVar(
            'NEW_KN_POSTINGS',
            array_map(
                function (KnPostInterface $knPost) use ($user): KnItemInterface {
                    return $this->knFactory->createKnItem(
                        $knPost,
                        $user
                    );
                },
                $this->knPostRepository->getNewerThenMark($user->getKNMark())
            )
        );
        $game->setTemplateVar(
            'NEW_KN_POSTING_COUNT',
            $this->knPostRepository->getAmountSince($user->getKNMark())
        );
        $game->setTemplateVar(
            'RECENT_PROFILE_VISITORS',
            $this->userProfileVisitorRepository->getRecent($userId)
        );
        $game->setTemplateVar(
            'RANDOM_ONLINE_USER',
            $this->userRepository->getOrderedByLastaction(15, $userId, time() - GameEnum::USER_ONLINE_PERIOD)
        );
        $game->setTemplateVar(
            'SHIP_BUILD_PROGRESS',
            $this->colonyShipQueueRepository->getByUser($userId)
        );
        $game->setTemplateVar(
            'RECENT_ALLIANCE_BOARD_TOPICS',
            $this->allianceBoardTopicRepository->getRecentByAlliance((int) $user->getAllianceId())
        );
        $game->setTemplateVar('RECENT_HISTORY', $this->historyRepository->getRecent());
    }
}
