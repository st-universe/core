<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\View\Overview;

use Stu\Component\Communication\Kn\KnFactoryInterface;
use Stu\Component\Communication\Kn\KnItemInterface;
use Stu\Component\Game\GameEnum;
use Stu\Component\Player\ColonyLimitCalculatorInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\PlayerSetting\Lib\PlayerEnum;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\HistoryRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\ShipyardShipQueueRepositoryInterface;
use Stu\Orm\Repository\UserProfileVisitorRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private HistoryRepositoryInterface $historyRepository;

    private AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository;

    private UserProfileVisitorRepositoryInterface $userProfileVisitorRepository;

    private KnPostRepositoryInterface $knPostRepository;

    private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository;

    private ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository;

    private UserRepositoryInterface $userRepository;

    private KnFactoryInterface $knFactory;

    private ColonyLimitCalculatorInterface $colonyLimitCalculator;

    private LoggerUtilInterface $loggerUtil;

    private int $newAmount;

    public function __construct(
        HistoryRepositoryInterface $historyRepository,
        AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository,
        UserProfileVisitorRepositoryInterface $userProfileVisitorRepository,
        KnPostRepositoryInterface $knPostRepository,
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository,
        UserRepositoryInterface $userRepository,
        KnFactoryInterface $knFactory,
        ColonyLimitCalculatorInterface $colonyLimitCalculator,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->historyRepository = $historyRepository;
        $this->allianceBoardTopicRepository = $allianceBoardTopicRepository;
        $this->userProfileVisitorRepository = $userProfileVisitorRepository;
        $this->knPostRepository = $knPostRepository;
        $this->colonyShipQueueRepository = $colonyShipQueueRepository;
        $this->shipyardShipQueueRepository = $shipyardShipQueueRepository;
        $this->userRepository = $userRepository;
        $this->knFactory = $knFactory;
        $this->colonyLimitCalculator = $colonyLimitCalculator;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
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
            $user->getActive() === PlayerEnum::USER_UNCOLONIZED
        );
        $this->newAmount = $this->knPostRepository->getAmountSince($user->getKNMark());
        $game->setTemplateVar(
            'NEW_KN_POSTING_COUNT',
            $this->newAmount
        );
        $game->setTemplateVar(
            'NEW_KN_POSTINGS',
            array_map(
                function (KnPostInterface $knPost) use ($user): KnItemInterface {
                    $this->newAmount--;
                    $knItem = $this->knFactory->createKnItem(
                        $knPost,
                        $user
                    );
                    $knItem->setMark(((int)floor($this->newAmount / GameEnum::KN_PER_SITE)) * 6);
                    return $knItem;
                },
                $this->knPostRepository->getNewerThenMark($user->getKNMark())
            )
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
            array_merge(
                $this->colonyShipQueueRepository->getByUser($userId),
                $this->shipyardShipQueueRepository->getByUser($userId)
            )
        );
        $game->setTemplateVar(
            'RECENT_ALLIANCE_BOARD_TOPICS',
            $this->allianceBoardTopicRepository->getRecentByAlliance((int) $user->getAllianceId())
        );
        $game->setTemplateVar('USER', $user);
        $game->setTemplateVar('RECENT_HISTORY', $this->historyRepository->getRecent());
        $game->setTemplateVar('PLANET_LIMIT', $this->colonyLimitCalculator->getPlanetColonyLimit($user));
        $game->setTemplateVar('PLANET_COUNT', $this->colonyLimitCalculator->getPlanetColonyCount($user));
        $game->setTemplateVar('MOON_LIMIT', $this->colonyLimitCalculator->getMoonColonyLimit($user));
        $game->setTemplateVar('MOON_COUNT', $this->colonyLimitCalculator->getMoonColonyCount($user));
    }
}
