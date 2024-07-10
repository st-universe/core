<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Management;

use Override;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceUiFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Management implements ViewControllerInterface
{
    /**
     * @var string
     */
    public const string VIEW_IDENTIFIER = 'SHOW_MANAGEMENT';

    public function __construct(private UserRepositoryInterface $userRepository, private AllianceActionManagerInterface $allianceActionManager, private AllianceUiFactoryInterface $allianceUiFactory)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $userId = $game->getUser()->getId();

        if ($alliance === null) {
            return;
        }

        if (!$this->allianceActionManager->mayEdit($alliance, $game->getUser())) {
            return;
        }

        $list = [];
        foreach ($this->userRepository->getByAlliance($alliance) as $member) {
            $list[] = $this->allianceUiFactory->createManagementListItem(
                $alliance,
                $member,
                $userId
            );
        }

        $game->setPageTitle('Allianz verwalten');

        $game->setNavigation([
            [
                'url' => 'alliance.php',
                'title' => 'Allianz',
            ],
            [
                'url' => sprintf('alliance.php?%s=1', Management::VIEW_IDENTIFIER),
                'title' => 'Verwaltung'
            ],
        ]);
        $game->setViewTemplate('html/alliance/alliancemanagement.twig');
        $game->setTemplateVar('ALLIANCE', $alliance);
        $game->setTemplateVar('ALLIANCE_JOB_DIPLOMATIC', AllianceEnum::ALLIANCE_JOBS_DIPLOMATIC);
        $game->setTemplateVar('ALLIANCE_JOB_SUCCESSOR', AllianceEnum::ALLIANCE_JOBS_SUCCESSOR);
        $game->setTemplateVar('ALLIANCE_JOB_FOUNDER', AllianceEnum::ALLIANCE_JOBS_FOUNDER);
        $game->setTemplateVar('MEMBER_LIST', $list);
        $game->setTemplateVar(
            'USER_IS_FOUNDER',
            $alliance->getFounder()->getUserId() === $userId
        );
    }
}
