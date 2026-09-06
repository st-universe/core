<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\AccessGrantedFeatureEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\CrewRace;
use Stu\Orm\Repository\UserRepositoryInterface;

final class CrewRaceSubmissionNotifier
{
    public function __construct(
        private readonly StuConfigInterface $config,
        private readonly UserRepositoryInterface $userRepository,
        private readonly PrivateMessageSenderInterface $privateMessageSender
    ) {}

    public function notify(CrewRace $crewRace, bool $resubmitted): void
    {
        $recipientIds = [];
        foreach ($this->config->getGameSettings()->getGrantedFeatures() as $entry) {
            if ($entry['feature'] === AccessGrantedFeatureEnum::CREW_RACE_MODERATION->value) {
                foreach ($entry['userIds'] as $userId) {
                    $recipientIds[$userId] = $userId;
                }
            }
        }

        foreach ($recipientIds as $userId) {
            if ($this->userRepository->find($userId) === null) {
                continue;
            }
            $this->privateMessageSender->send(
                UserConstants::USER_NOONE,
                $userId,
                sprintf(
                    $resubmitted
                        ? _('Die Crew-Rasse %s wurde von Spieler %d überarbeitet und erneut zur Freigabe eingereicht')
                        : _('Die Crew-Rasse %s wurde von Spieler %d zur Freigabe eingereicht'),
                    $crewRace->getDescription(),
                    $crewRace->getCreatorUserId()
                ),
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                'database.php?SHOW_CREW_RACE_MODERATION=1'
            );
        }
    }
}
