<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\View\Overview;

use ColonyShipQueue;
use ContactlistData;
use KNPosting;
use Stu\Module\Communication\Lib\KnTalFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;
use Stu\Orm\Repository\HistoryRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\UserProfileVisitorRepositoryInterface;
use User;

final class Overview implements ViewControllerInterface
{
    private $historyRepository;

    private $allianceBoardTopicRepository;

    private $userProfileVisitorRepository;

    private $knPostRepository;

    private $knTalFactory;

    public function __construct(
        HistoryRepositoryInterface $historyRepository,
        AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository,
        UserProfileVisitorRepositoryInterface $userProfileVisitorRepository,
        KnPostRepositoryInterface $knPostRepository,
        KnTalFactoryInterface $knTalFactory
    ) {
        $this->historyRepository = $historyRepository;
        $this->allianceBoardTopicRepository = $allianceBoardTopicRepository;
        $this->userProfileVisitorRepository = $userProfileVisitorRepository;
        $this->knPostRepository = $knPostRepository;
        $this->knTalFactory = $knTalFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $list = [];

        foreach ($this->knPostRepository->getNewerThenMark((int) $user->getKNMark()) as $post) {
            $list[] = $this->knTalFactory->createKnPostTal($post, $user);
        }

        $game->appendNavigationPart(
            'maindesk.php',
            _('Maindesk')
        );
        $game->setPageTitle(_('/ Maindesk'));
        $game->setTemplateFile('html/maindesk.xhtml');

        $game->setTemplateVar(
            'DISPLAY_FIRST_COLONY_DIALOGUE',
            (int)$user->getActive() === 1
        );
        $game->setTemplateVar('NEW_KN_POSTINGS', $list);
        $game->setTemplateVar(
            'NEW_KN_POSTING_COUNT',
            KNPosting::countInstances('id>' . $user->getKnMark())
        );
        $game->setTemplateVar(
            'RECENT_PROFILE_VISITORS',
            $this->userProfileVisitorRepository->getRecent($userId)
        );
        $game->setTemplateVar(
            'RANDOM_ONLINE_USER',
            User::getListBy(sprintf(
                'WHERE id != %d AND (show_online_status=1 OR id IN (SELECT user_id FROM stu_contactlist WHERE mode = %d AND recipient = %d)) AND lastaction > %d ORDER BY RAND() LIMIT 15',
                $userId,
                ContactlistData::CONTACT_FRIEND,
                $userId,
                (time() - USER_ONLINE_PERIOD)
            ))
        );
        $game->setTemplateVar(
            'SHIP_BUILD_PROGRESS',
            ColonyShipQueue::getByUserId($userId)
        );
        $game->setTemplateVar(
            'RECENT_ALLIANCE_BOARD_TOPICS',
            $this->allianceBoardTopicRepository->getRecentByAlliance((int) $user->getAllianceId())
        );
        $game->setTemplateVar('RECENT_HISTORY', $this->historyRepository->getRecent());
    }
}
