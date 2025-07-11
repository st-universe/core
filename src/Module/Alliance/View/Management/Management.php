<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Management;

use Override;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Component\Alliance\AllianceSettingsEnum;
use Stu\Component\Alliance\Enum\AllianceJobTypeEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceUiFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Entity\AllianceSettings;


final class Management implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_MANAGEMENT';

    public function __construct(private UserRepositoryInterface $userRepository, private AllianceActionManagerInterface $allianceActionManager, private AllianceUiFactoryInterface $allianceUiFactory) {}

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
        $game->setTemplateVar('ALLIANCE_JOB_DIPLOMATIC', AllianceJobTypeEnum::DIPLOMATIC);
        $game->setTemplateVar('ALLIANCE_JOB_SUCCESSOR', AllianceJobTypeEnum::SUCCESSOR);
        $game->setTemplateVar('ALLIANCE_JOB_FOUNDER', AllianceJobTypeEnum::FOUNDER);
        $game->setTemplateVar('MEMBER_LIST', $list);
        $game->setTemplateVar(
            'USER_IS_FOUNDER',
            $alliance->getFounder()->getUserId() === $userId
        );

        $founderDescription = $alliance->getSettings()->filter(
            function (AllianceSettings $setting): bool {
                return $setting->getSetting() === AllianceSettingsEnum::ALLIANCE_FOUNDER_DESCRIPTION;
            }
        )->first();

        $successorDescription = $alliance->getSettings()->filter(
            function (AllianceSettings $setting): bool {
                return $setting->getSetting() === AllianceSettingsEnum::ALLIANCE_SUCCESSOR_DESCRIPTION;
            }
        )->first();

        $diplomatDescription = $alliance->getSettings()->filter(
            function (AllianceSettings $setting): bool {
                return $setting->getSetting() === AllianceSettingsEnum::ALLIANCE_DIPLOMATIC_DESCRIPTION;
            }
        )->first();


        $game->setTemplateVar(
            'FOUNDER_DESCRIPTION',
            $founderDescription !== false ? $founderDescription->getValue() : 'Präsident'
        );

        $game->setTemplateVar(
            'SUCCESSOR_DESCRIPTION',
            $successorDescription !== false ? $successorDescription->getValue() : 'Vize-Präsident'
        );

        $game->setTemplateVar(
            'DIPLOMATIC_DESCRIPTION',
            $diplomatDescription !== false ? $diplomatDescription->getValue() : 'Außenminister'
        );
    }
}
